<?php

/**
 * XdbEdit
 *
 * Copyright 2010 by Bruno Perner <b.perner@gmx.de>
 *
 * This file is part of XdbEdit, for editing custom-tables in MODx Revolution CMP.
 *
 * XdbEdit is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * XdbEdit is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * XdbEdit; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA 
 *
 * @package xdbedit
 */
/**
 * Update and Create-processor for xdbedit
 *
 * @package xdbedit
 * @subpackage processors
 */
//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));


if (empty($scriptProperties['object_id'])) {
    $updateerror = true;
    $errormsg = $modx->lexicon('quip.thread_err_ns');
    return;
}

$config = $modx->migx->customconfigs;

$includeTVList = $modx->getOption('includeTVList', $config, '');
$includeTVList = !empty($includeTVList) ? explode(',', $includeTVList) : array();
$includeTVs = $modx->getOption('includeTVs', $config, false);
$classname = 'modResource';

//$saveTVs = false;

if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

if (isset($scriptProperties['data'])) {
    //$scriptProperties = array_merge($scriptProperties, $modx->fromJson($scriptProperties['data']));
    $data = $modx->fromJson($scriptProperties['data']);
}

$data['id'] = $modx->getOption('object_id', $scriptProperties, null);

$parent = $modx->getOption('resource_id', $scriptProperties, false);
$checkresponse = true;

switch ($scriptProperties['task']) {
    case 'publish':
        $response = $modx->runProcessor('resource/publish', $data);
        break;
    case 'unpublish':
        $response = $modx->runProcessor('resource/unpublish', $data);
        break;
    case 'delete':
        $response = $modx->runProcessor('resource/delete', $data);
        break;
    case 'recall':
        $object = $modx->getObject($classname, $scriptProperties['object_id']);
        $object->set('deleted', '0');
        $object->save();
        $checkresponse = false;
        break;

    default:

        //$modx->migx->loadConfigs();
        //$tabs = $modx->migx->getTabs();

        $data['context_key'] = $modx->getOption('context_key', $data, $scriptProperties['wctx']);
        if ($includeTVs) {
            $c = $modx->newQuery('modTemplateVar');
            $collection = $modx->getCollection('modTemplateVar', $c);
            foreach ($collection as $tv) {
                $tvname = $tv->get('name');
                if (isset($data[$tvname])) {
                    $data['tv' . $tv->get('id')] = $data[$tvname];
                    unset($data[$tvname]);
                }
            }

            $data['tvs'] = 1;
        }


        if ($scriptProperties['object_id'] == 'new') {
            //$object = $modx->newObject($classname);
            if (!empty($parent)){
                $data['parent'] = $parent;
            }
            $response = $modx->runProcessor('resource/create', $data);
        } else {
            //$object = $modx->getObject($classname, $scriptProperties['object_id']);
            //if (empty($object)) return $modx->error->failure($modx->lexicon('quip.thread_err_nf'));
            $response = $modx->runProcessor('resource/update', $data);
        }
}

if ($checkresponse) {
    if ($response->isError()) {
        $updateerror = true;
        $errormsg = $response->getMessage();
    }
    $object = $response->getObject();
}
