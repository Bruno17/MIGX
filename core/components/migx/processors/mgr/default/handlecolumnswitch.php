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
 * Columnswitch-processor for migxdb
 *
 * @package migx
 * @subpackage processors
 */
//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));

//return $modx->error->failure('huhu');

if (empty($scriptProperties['object_id'])) {
    return $modx->error->failure($modx->lexicon('quip.thread_err_ns'));
    return;
}

$config = $modx->migx->customconfigs;
$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);
    $packageName = isset($packageNames[0]) ? $packageNames[0] : '';    

    if (count($packageNames) == '1') {
        //for now connecting also to foreign databases, only with one package by default possible
        $xpdo = $modx->migx->getXpdoInstanceAndAddPackage($config);
    } else {
        //all packages must have the same prefix for now!
        foreach ($packageNames as $packageName) {
            $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
            $modelpath = $packagepath . 'model/';
            if (is_dir($modelpath)) {
                $modx->addPackage($packageName, $modelpath, $prefix);
            }

        }
        $xpdo = &$modx;
    }
    if ($this->modx->lexicon) {
        $this->modx->lexicon->load($packageName . ':default');
    }    
}else{
    $xpdo = &$modx;    
}
$classname = $config['classname'];

$col = $modx->getOption('col', $scriptProperties, '');
$idx = $modx->getOption('idx', $scriptProperties, '');
$tv_type = $modx->getOption('tv_type', $scriptProperties, '');

if (empty($col)) {
    return $modx->error->failure('no column');
}

$modx->migx->loadConfigs();
$renderoptions = $modx->migx->getColumnRenderOptions($col); 
$fallback_idx = 'x';
if (is_array($renderoptions)){
    $newoptions = array();
    foreach ($renderoptions as $key => $renderoption){
        $option = $modx->fromJson($renderoption);
        if (isset($option['use_as_fallback']) && !empty($option['use_as_fallback'])){
            //don't add option, which was set to use_as_fallback
            $fallback_idx = $key;
        }else{
            $newoptions[$key] = $option;
        }
    }
    $renderoptions = $newoptions;
}

$first_idx = 0;
if (empty($fallback_idx)){
    $first_idx = 1;
}
if ($idx+1 == $fallback_idx){
    $idx++;
}

$columnrenderoption = $renderoptions[$idx];
$nextcolumnrenderoptions = isset($renderoptions[$idx+1]) ? $renderoptions[$idx+1] : $renderoptions[$first_idx];
//$nextcolumnrenderoptions = $modx->fromJson($nextcolumnrenderoptions);

$value = $nextcolumnrenderoptions['value']; 

if ($tv_type == 'migx'){
    $nextcolumnrenderoptions['tv_type'] = 'migx';
    return $modx->error->success('',$nextcolumnrenderoptions);    
}

$object = $xpdo->getObject($classname, $scriptProperties['object_id']);
$object->set($col, $value);
switch ($col) {
    case 'published':
        if ($value == '1') {
            $object->set('publishedon', strftime('%Y-%m-%d %H:%M:%S'));
            $unpub = $object->get('unpub_date');
            if ($unpub < strftime('%Y-%m-%d %H:%M:%S')) {
                $object->set('unpub_date', null);
            }
        }

        break;
    case 'deleted':
        if ($value == '1'){
            $object->set('deletedon', strftime('%Y-%m-%d %H:%M:%S'));
            $object->set('deletedby', $modx->user->get('id'));            
        }
        break;
}

if ($object->save() == false) {
    return $modx->error->failure($modx->lexicon('quip.thread_err_save'));
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
return $modx->error->success();
?>
