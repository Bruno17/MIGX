<?php

/**
 * MIGXdb
 *
 * Copyright 2012 by Bruno Perner <b.perner@gmx.de>
 *
 * This file is part of MIGXdb, for editing custom-tables in MODx Revolution CMP.
 *
 * MIGXdb is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MIGXdb is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MIGXdb; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA 
 *
 * @package migx
 */
/**
 * Update and Create-processor for migxdb
 *
 * @package migx
 * @subpackage processors
 */
//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));

//return $modx->error->failure('huhu');

if (empty($scriptProperties['object_id'])) {
    $updateerror = true;
    $errormsg = $modx->lexicon('quip.thread_err_ns');
    return;
}

$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];


$modx->setOption(xPDO::OPT_AUTO_CREATE_TABLES, $config['auto_create_tables']);

if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

if (isset($scriptProperties['data'])) {
    $scriptProperties = array_merge($scriptProperties, $modx->fromJson($scriptProperties['data']));
}

$resource_id = $modx->getOption('resource_id', $scriptProperties, false);

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

if (!empty($joinalias)) {
    if ($fkMeta = $modx->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
        $joinvalues = array();
    } else {
        $joinalias = '';
    }
}

switch ($scriptProperties['task']) {
    case 'publish':
        $object = $modx->getObject($classname, $scriptProperties['object_id']);
        $object->set('publishedon', strftime('%Y-%m-%d %H:%M:%S'));
        $object->set('published', '1');
        $unpub = $object->get('unpub_date');
        if ($unpub < strftime('%Y-%m-%d %H:%M:%S')) {
            $object->set('unpub_date', null);
        }
        break;
    case 'unpublish':
        $object = $modx->getObject($classname, $scriptProperties['object_id']);
        $object->set('unpublishedon', strftime('%Y-%m-%d %H:%M:%S'));
        $object->set('published', '0');
        $object->set('unpublishedby', $modx->user->get('id')); //feld fehlt noch
        $pub = $object->get('pub_date');
        if ($pub < strftime('%Y-%m-%d %H:%M:%S')) {
            $object->set('pub_date', null);
        }
        break;
    case 'delete':
        $object = $modx->getObject($classname, $scriptProperties['object_id']);
        $object->set('deletedon', strftime('%Y-%m-%d %H:%M:%S'));
        $object->set('deleted', '1');
        $object->set('deletedby', $modx->user->get('id'));
        break;
    case 'recall':
        $object = $modx->getObject($classname, $scriptProperties['object_id']);
        $object->set('deleted', '0');
        break;
    default:

        $modx->migx->loadConfigs();
        $tabs = $modx->migx->getTabs();
        $fieldid = 0;
        $postvalues = array();

        foreach ($scriptProperties as $field => $value) {
            $fieldid++;
            /* handles checkboxes & multiple selects elements */
            if (is_array($value)) {
                $featureInsert = array();
                while (list($featureValue, $featureItem) = each($value)) {
                    $featureInsert[count($featureInsert)] = $featureItem;
                }
                $value = implode('||', $featureInsert);
            }

            $field = explode('.', $field);

            if (count($field) > 1) {
                //extended field (json-array)
                $postvalues[$field[0]][$field[1]] = $value;
            } else {
                $postvalues[$field[0]] = $value;
            }

            if (!empty($joinalias)) {
                // check for jointable- fields
                //$len = strlen($joinalias)+1;
                if (substr($field[0], 0, 7) == 'Joined_') {
                    $joinvalues[substr($field[0], 7)] = $value;
                    unset($postvalues[$field[0]]);
                } 
            }
        }

        if ($scriptProperties['object_id'] == 'new') {
            $object = $modx->newObject($classname);
            $tempvalues['createdon'] = strftime('%Y-%m-%d %H:%M:%S');
            $postvalues['createdby'] = $modx->user->get('id');
            //handle published
            $postvalues['published'] = isset($postvalues['published']) ? $postvalues['published'] : '1';
        } else {
            $object = $modx->getObject($classname, $scriptProperties['object_id']);
            if (empty($object)) return $modx->error->failure($modx->lexicon('quip.thread_err_nf'));
            $postvalues['editedon'] = strftime('%Y-%m-%d %H:%M:%S');
            $postvalues['editedby'] = $modx->user->get('id');
            $tempvalues['createdon'] = $object->get('createdon');
            $tempvalues['publishedon'] = $object->get('publishedon');
        }


        if ($postvalues['published'] == '1') {
            $pub = $object->get('published');
            if (empty($pub)) {
                $tempvalues['publishedon'] = strftime('%Y-%m-%d %H:%M:%S');
                $postvalues['publishedby'] = $modx->user->get('id');
            }
            /*
            $unpub = $object->get('unpub_date');
            if ($unpub < strftime('%Y-%m-%d %H:%M:%S'))
            {
            $postvalues['unpub_date'] = null;
            }
            */
        }
        /*
        if ($postvalues['published'] == '0')
        {
        $pub = $object->get('pub_date');
        if ($pub < strftime('%Y-%m-%d %H:%M:%S'))
        {
        $postvalues['pub_date'] = null;
        }
        }
        */
        //overwrites
        if (empty($postvalues['ow_createdon'])) {
            $postvalues['createdon'] = $tempvalues['createdon'];
        }
        if (empty($postvalues['ow_publishedon'])) {
            $postvalues['publishedon'] = $tempvalues['publishedon'];
        }

        if (!$config['is_container'] && !empty($postvalues['resource_id'])) {
            $postvalues['customerid'] = $postvalues['resource_id'];
        }

        if ($modx->migx->checkForConnectedResource($resource_id, $config)) {

        } else {
            unset($postvalues['resource_id']);
        }

        $object->fromArray($postvalues);
}

return;

if ($object->save() == false) {
    $updateerror = true;
    $errormsg = $modx->lexicon('quip.thread_err_save');
    return;
}

if (!empty($joinalias)) {

    if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')) {
        $localkey = $joinFkMeta['local'];
    }
    if ($joinobject = $modx->getObject($joinclass, array('resource_id' => $scriptProperties['resource_id'], $localkey => $object->get('id')))) {
        $joinobject->fromArray($joinvalues);
    } else {
        $joinobject = $modx->newObject($joinclass);
        $joinobject->fromArray($joinvalues);
        $joinobject->set('active', '1');
        $joinobject->set('resource_id', $scriptProperties['resource_id']);
        $joinobject->set($localkey, $object->get('id'));
    }
    $joinobject->save();
}

//clear cache for all contexts
$collection = $modx->getCollection('modContext');
foreach ($collection as $context) {
    $contexts = $context->get('key');
}
$modx->cacheManager->refresh(array(
    'db' => array(),
    'auto_publish' => array('contexts' => $contexts),
    'context_settings' => array('contexts' => $contexts),
    'resource' => array('contexts' => $contexts),
    ));

?>
