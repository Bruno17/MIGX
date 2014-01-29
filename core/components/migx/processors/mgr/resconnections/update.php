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

$modx->setOption(xPDO::OPT_AUTO_CREATE_TABLES,$config['auto_create_tables']);

$classname = $config['classname'];

$saveTVs = false;

if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

if (isset($scriptProperties['data'])) {
    $scriptProperties = array_merge($scriptProperties, $modx->fromJson($scriptProperties['data']));
}

$resource_id = $modx->getOption('resource_id', $scriptProperties, false);

    switch ($scriptProperties['task']) {
        case 'publish':
            $object = $modx->getObject($classname, $scriptProperties['object_id']);
            $object->set('publishedon', time());
            $object->set('published', '1');
            $unpub = $object->get('unpub_date');
            if ($unpub < time()) {
                $object->set('unpub_date', null);
            }
            break;
        case 'unpublish':
            $object = $modx->getObject($classname, $scriptProperties['object_id']);
            $object->set('unpublishedon', time());
            $object->set('published', '0');
            $object->set('unpublishedby', $modx->user->get('id')); //feld fehlt noch
            $pub = $object->get('pub_date');
            if ($pub < time()) {
                $object->set('pub_date', null);
            }
            break;
        case 'delete':
            $object = $modx->getObject($classname, $scriptProperties['object_id']);
            $object->set('deletedon', time());
            $object->set('deleted', '1');
            $object->set('deletedby', $modx->user->get('id'));
            break;
        case 'recall':
            $object = $modx->getObject($classname, $scriptProperties['object_id']);
            $object->set('deleted', '0');
            break;
            
        default:

            // set context_key and load fields from config-file
            //$modx->xdbedit->context=$scriptProperties['context_key'];
            $modx->migx->loadConfigs();
            $tabs = $modx->migx->getTabs();
            $fieldid = 0;
            $postvalues = array();

            foreach ($scriptProperties as $field => $value) {
                $fieldid++;
                //$value = $scriptProperties['tv'.$fieldid];
                /*
                switch ($tv->get('type')) {
                case 'url':
                if ($scriptProperties['tv' . $row['name'] . '_prefix'] != '--') {
                $value = str_replace(array('ftp://','http://'),'', $value);
                $value = $scriptProperties['tv'.$tv->get('id').'_prefix'].$value;
                }
                break;
                default:
                // handles checkboxes & multiple selects elements 
                if (is_array($value)) {
                $featureInsert = array();
                while (list($featureValue, $featureItem) = each($value)) {
                $featureInsert[count($featureInsert)] = $featureItem;
                }
                $value = implode('||',$featureInsert);
                }
                break;
                }
                */
                /* handles checkboxes & multiple selects elements */
                if (is_array($value)) {
                    $featureInsert = array();
                    while (list($featureValue, $featureItem) = each($value)) {
                        $featureInsert[count($featureInsert)] = $featureItem;
                    }
                    $value = implode('||', $featureInsert);
                }
                $postvalues[$field] = $value;
            }

            if ($scriptProperties['object_id'] == 'new') {
                $object = $modx->newObject($classname);
                $tempvalues['createdon'] = time();
                $postvalues['createdby'] = $modx->user->get('id');
                $postvalues[$config['idfield_local']] = $resource_id;
            } else {
                $object = $modx->getObject($classname, $scriptProperties['object_id']);
                if (empty($object)) return $modx->error->failure($modx->lexicon('quip.thread_err_nf'));
                $postvalues['editedon'] = time();
                $postvalues['editedby'] = $modx->user->get('id');
                $tempvalues['createdon'] = $object->get('createdon');
                $tempvalues['publishedon'] = $object->get('publishedon');
            }
            //handle published
            if ($postvalues['published'] == '1') {
                $pub = $object->get('published');
                if (empty($pub)) {
                    $tempvalues['publishedon'] = time();
                    $postvalues['publishedby'] = $modx->user->get('id');
                }
                $unpub = $object->get('unpub_date');
                if ($unpub < time()) {
                    $postvalues['unpub_date'] = null;
                }
            }
            if ($postvalues['published'] == '0') {
                $pub = $object->get('pub_date');
                if ($pub < time()) {
                    $postvalues['pub_date'] = null;
                }
            }

            /* alias creation:
            $resource=$modx->newObject('modResource');
            $oldalias = $object->get('alias');
            if (empty($oldalias)) {
            $oldalias='';
            $tempvalues['alias'] = $resource->cleanAlias($postvalues['pagetitle']);
            }
            else{
            $tempvalues['alias'] = $oldalias;
            } 
            */
            //overwrites
            if (empty($postvalues['ow_createdon'])) {
                $postvalues['createdon'] = $tempvalues['createdon'];
            }
            if (empty($postvalues['ow_publishedon'])) {
                $postvalues['publishedon'] = $tempvalues['publishedon'];
            }
            /* handle alias
            if (empty($postvalues['ow_alias'])) {
            
            $postvalues['alias'] = $tempvalues['alias']; 
            }
            else{
            //if posted empty alias generate new one from pagetitle
            if (empty($postvalues['alias'])) {
            $postvalues['alias'] = $resource->cleanAlias($postvalues['pagetitle']);
            }
            else{
            $postvalues['alias'] = $resource->cleanAlias($postvalues['alias']);
            } 			
            }
            //if new alias was created check if same alias exists for same day

            //$configs['classname']=$classname;
            $getnews = $modx->getService('getnews','Getnews',$modx->getOption('core_path').'components/newsandmore/model/newsandmore/',$configs);    	
            $createdon = strtotime($postvalues['createdon']);
            
            $params['year']=strftime('%Y', $createdon);
            $params['month']=strftime('%m', $createdon);
            $params['day']=strftime('%d', $createdon);
            $params['alias']=$postvalues['alias'];
            $params['published']='all';
            $params['deleted']='all';
            $params['exclude']=$scriptProperties['object_id'];
            
            $existingobject=$getnews->getpage($params);
            if ($getnews->lastcount>0){
            $updateerror=true;
            $errormsg='
            Objekt konnte nicht gespeichert werden!<br/>
            Der Alias ist nicht eindeutig für dieses Erstellungsdatum<br/>
            Bitte manuell einen eindeutigen Alias eintragen.<br/>
            alias: '.$postvalues['alias'].'<br/>
            Erstellungsdatum: '.strftime('%d.%m.%Y', $createdon).'<br/>
            
            ';
            return ;						
            }
            
            
            unset($resource);
            */
            //$postvalues['context_key']=$scriptProperties['context_key'];

            $object->fromArray($postvalues);
            //$saveTVs = true;


    }

    if ($object->save() == false) {
        $updateerror = true;
        $errormsg = $modx->lexicon('Object could not be saved');
        return;
    }

    //save TVs
    if ($saveTVs) {
        foreach ($postvalues as $field => $value) {
            if ($tv = $modx->getObject('modTemplateVar', array('name' => $field))) {
                /* handles checkboxes & multiple selects elements */
                if (is_array($value)) {
                    $featureInsert = array();
                    while (list($featureValue, $featureItem) = each($value)) {
                        $featureInsert[count($featureInsert)] = $featureItem;
                    }
                    $value = implode('||', $featureInsert);
                }
                $tv->setValue($object->get('id'), $value);
                $tv->save();
            }
        }
    }


    //clear cache
    $contexts = 'web';
    $contexts = explode(',', $contexts);
    $partitions['resource'] = array('contexts' => $contexts);
    $partitions['context_settings'] = array('contexts' => $contexts);
    $results = array();
    $modx->cacheManager->refresh($partitions, $results);


?>
