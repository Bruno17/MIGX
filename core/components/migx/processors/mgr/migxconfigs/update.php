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

//return $modx->error->failure('huhu');

if (empty($scriptProperties['object_id'])) {
    $updateerror = true;
    $errormsg = $modx->lexicon('quip.thread_err_ns');
    return;
}

$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];
$task = $modx->getOption('task', $scriptProperties, '');

$packagepath = $modx->migx->findPackagePath($packageName); 
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

$modx->setOption(xPDO::OPT_AUTO_CREATE_TABLES, $config['auto_create_tables']);

if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

if (isset($scriptProperties['data'])) {
    $scriptProperties = array_merge($modx->fromJson($scriptProperties['data']),$scriptProperties);
}


switch ($task) {
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
                foreach ($value as $featureValue => $featureItem){
                    $featureInsert[] = $featureItem;
                }
                $value = implode('||', $featureInsert);
            }

            if ($field != 'configs') {
                $field = explode('.', $field);
                if (count($field) > 1) {
                    //extended field (json-array)
                    $postvalues[$field[0]][$field[1]] = $value;
                } else {
                    if (!empty($field[0]) && is_string($field[0])){
                        $postvalues[$field[0]] = $value;
                    }
                }
            }


        }
        if (isset($postvalues['newcategory']) && !empty($postvalues['newcategory'])) {
            $postvalues['category'] = $postvalues['newcategory'];
        }
        if (isset($postvalues['jsonexport'])) {
            $postvalues = $modx->migx->importconfig($modx->fromJson($postvalues['jsonexport']));
        }
        if ($scriptProperties['object_id'] == 'new') {
            $object = $modx->newObject($classname);
            $tempvalues['createdon'] = strftime('%Y-%m-%d %H:%M:%S');
            $postvalues['createdby'] = $modx->user->get('id');
        } else {
            $object = $modx->getObject($classname, $scriptProperties['object_id']);
            if (empty($object))
                return $modx->error->failure($modx->lexicon('quip.thread_err_nf'));
            $postvalues['editedon'] = strftime('%Y-%m-%d %H:%M:%S');
            $postvalues['editedby'] = $modx->user->get('id');
            $tempvalues['createdon'] = $object->get('createdon');
            $tempvalues['publishedon'] = $object->get('publishedon');
        }

        $newtabs = array();
        if (isset($postvalues['formtabs'])) {
            $formtabs = $modx->fromJson($postvalues['formtabs']);
            if (is_array($formtabs) && count($formtabs) > 0) {
                foreach ($formtabs as $tab) {
                    $fields = is_array($tab['fields']) ? $tab['fields'] : $modx->fromJson($tab['fields']);
                    $tab['fields'] = $fields;
                    $newtabs[] = $tab;
                }
                $postvalues['formtabs'] = $modx->toJson($newtabs);
            }

        }

        if (isset($postvalues['formlayouts'])) {
            $newtabs = array();
            $formlayouts = $modx->fromJson($postvalues['formlayouts']);
            if (is_array($formlayouts) && count($formlayouts) > 0) {
                $fields = array();
                $tab = false;
                $layout_id = 0; 
                $column_id = 0;
                $columnwidth = 0;
                $columncaption = '';
                $columnstyle = '';
                $columnminwidth = '';                          
                $layoutcaption = '';
                $layoutstyle = '';                   
                foreach ($formlayouts as $formlayout) {
                    $type = $modx->getOption('MIGXtype', $formlayout, '');
                    switch ($type) {
                        case 'formtab':
                            if ($tab) {
                                //next tab
                                $tab['fields'] = $fields;
                                $newtabs[] = $tab;
                                $fields = array();
                            }
                            $tab = $formlayout;
                            $layout_id = 0; 
                            $column_id = 0;
                            $columnwidth = 0; 
                            $columncaption = '';
                            $columnstyle = '';
                            $columnminwidth = '';                          
                            $layoutcaption = '';
                            $layoutstyle = '';                                                         
                            break;
                        case 'layout':
                            $layout_id++;
                            $column_id = 0;
                            $columnwidth = 0;
                            $columncaption = '';
                            $columnstyle = '';
                            $columnminwidth = '';                          
                            $layoutcaption = $modx->getOption('MIGXlayoutcaption', $formlayout, '');
                            $layoutstyle = $modx->getOption('MIGXlayoutstyle', $formlayout, '');                            
                            break;
                        case 'column':
                            $column_id++;
                            $columnwidth = $modx->getOption('field', $formlayout, '');
                            $columnminwidth = $modx->getOption('MIGXcolumnminwidth', $formlayout, '');
                            $columncaption = $modx->getOption('MIGXcolumncaption', $formlayout, '');
                            $columnstyle = $modx->getOption('MIGXcolumnstyle', $formlayout, '');
                            break;
                        case 'field':
                            if (!$tab) {
                                $tab = array();
                                $tab['caption'] = 'undefined';
                            }
                            $field = $formlayout;
                            $field['MIGXlayoutid'] = $layout_id;
                            $field['MIGXcolumnid'] = $column_id;
                            $field['MIGXcolumnwidth'] = $columnwidth;
                            $field['MIGXcolumnminwidth'] = $columnminwidth;
                            $field['MIGXcolumnstyle'] = $columnstyle;
                            $field['MIGXcolumncaption'] = $columncaption;
                            $field['MIGXlayoutstyle'] = $layoutstyle;
                            $field['MIGXlayoutcaption'] = $layoutcaption;                            
                            $fields[] = $field;
                            break;
                    }


                }
                if ($tab) {
                    //last tab
                    $tab['fields'] = $fields;
                    $newtabs[] = $tab;
                    $fields = array();
                }
                $postvalues['formtabs'] = $modx->toJson($newtabs);
            }

        }

        $newcolumns = array();
        if (isset($postvalues['columns'])) {
            $columns = $modx->fromJson($postvalues['columns']);
            if (is_array($columns) && count($columns) > 0) {
                foreach ($columns as $column) {
                    if (isset($column['customrenderer']) && !empty($column['customrenderer'])) {
                        $column['renderer'] = $column['customrenderer'];

                    }
                    $newcolumns[] = $column;
                }
                $postvalues['columns'] = $modx->toJson($newcolumns);
            }

        }

        //handle published
        $postvalues['published'] = isset($postvalues['published']) ? $postvalues['published'] : '1';

        /*
        if ($postvalues['published']=='1'){
        $pub=$object->get('published');
        if (empty($pub)){
        $tempvalues['publishedon']=strftime('%Y-%m-%d %H:%M:%S');
        $postvalues['publishedby']=$modx->user->get('id');				    	
        }
        $unpub=$object->get('unpub_date');
        if($unpub<strftime('%Y-%m-%d %H:%M:%S')){
        $postvalues['unpub_date']=NULL;
        }			
        }
        if ($postvalues['published']=='0'){
        $pub=$object->get('pub_date');
        if($pub<strftime('%Y-%m-%d %H:%M:%S')){
        $postvalues['pub_date']=NULL;
        }
        }
        */
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
        if (isset($tempvalues['createdon']) && empty($postvalues['ow_createdon'])) {
            $postvalues['createdon'] = $tempvalues['createdon'];
        }
        if (isset($tempvalues['publishedon']) && empty($postvalues['ow_publishedon'])) {
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

        if (isset($config['is_container']) && !$config['is_container'] && !empty($postvalues['resource_id'])) {
            $postvalues['customerid'] = $postvalues['resource_id'];
        }

        $object->fromArray($postvalues);


        //$object->set('configs',$modx->toJson($postvalues['configs']));
}


if ($object->save() == false) {
    $updateerror = true;
    $errormsg = $modx->lexicon('quip.thread_err_save');
    return;
}


//clear cache for all contexts
$collection = $modx->getCollection('modContext');
foreach ($collection as $context) {
    $contexts[] = $context->get('key');
}
$modx->cacheManager->refresh(array(
    'db' => array(),
    'auto_publish' => array('contexts' => $contexts),
    'context_settings' => array('contexts' => $contexts),
    'resource' => array('contexts' => $contexts),
    ));
?>
