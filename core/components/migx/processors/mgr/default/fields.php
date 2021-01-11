<?php

$object_id = 'new';
$config = $modx->migx->customconfigs;

$hooksnippets = $modx->fromJson($modx->getOption('hooksnippets', $config, ''));
if (is_array($hooksnippets)) {
    $hooksnippet_aftergetfields = $modx->getOption('aftergetfields', $hooksnippets, '');
}

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
        foreach ($packageNames as $p) {
            $packagepath = $modx->getOption('core_path') . 'components/' . $p . '/';
            $modelpath = $packagepath . 'model/';
            if (is_dir($modelpath)) {
                $modx->addPackage($p, $modelpath, $prefix);
            }

        }
        $xpdo = &$modx;
    }

    if ($this->modx->lexicon) {
        $this->modx->lexicon->load($packageName . ':default');
    }

} else {
    $xpdo = &$modx;
}

$sender = 'default/fields';

$classname = $config['classname'];

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

$joins = false;
$export_joins = '';
if (isset($config['joins']) && !empty($config['joins'])){
    $joins =  $modx->fromJson($config['joins']);
    $export_joins = $config['joins'];    
}


if (!empty($joinalias)) {
    if ($fkMeta = $xpdo->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
    } else {
        $joinalias = '';
    }
}

if (empty($scriptProperties['object_id']) || $scriptProperties['object_id'] == 'new') {
    if ($object = $xpdo->newObject($classname)) {
        $object->set('object_id', 'new');
    }

} else {
    $c = $xpdo->newQuery($classname, $scriptProperties['object_id']);
    $pk = $xpdo->getPK($classname);
    $c->select('
        `' . $classname . '`.*,
    	`' . $classname . '`.`' . $pk . '` AS `object_id`
    ');
    if (!empty($joinalias)) {
        $c->leftjoin($joinclass, $joinalias);
        $c->select($xpdo->getSelectColumns($joinclass, $joinalias, 'Joined_'));
    }
    if ($joins) {
        $modx->migx->prepareJoins($classname, $joins, $c);
    }
    if ($object = $xpdo->getObject($classname, $c)) {
        $object_id = $object->get('id');
    }
}

$_SESSION['migxWorkingObjectid'] = $object_id;

//handle json fields
if ($object) {
    $record = $object->toArray();
} else {
    $record = array();
}

$tempParams = $modx->getOption('tempParams',$scriptProperties,'');
if ($tempParams == 'importcsv'){
    
    $settingName = $modx->getOption('core_path') . 'components/' . $packageName . '/import/' . $classname . '.settings.js';
    $defaultSettingName = $modx->getOption('core_path') . 'components/' . $packageName . '/import/' . $classname . '.default_settings.js';
    if (file_exists($settingName)){
        $record = json_decode(file_get_contents($settingName),true);
    } elseif (file_exists($defaultSettingName)){
        $record = json_decode(file_get_contents($defaultSettingName),true);    
    }
    $defaultfile = str_replace($modx->getOption('base_path'),'',$modx->getOption('core_path')) . 'components/' . $packageName . '/import/' . $classname . '.csv';
    $record['file'] = isset($record['file']) && !empty($record['file']) ? $record['file'] : $defaultfile ;
    if (isset($record['settings'])){
        if (!is_array($record['settings'])){
            $record['settings'] = array($record['settings']);
        }
        $record['settings'] = array_diff($record['settings'],array('save_settings'));    
    }
}
if ($tempParams == 'exportcsv'){
    $record = array();
    $settingName = $modx->getOption('core_path') . 'components/' . $packageName . '/import/' . $classname . '.default_settings.js';
    if (file_exists($settingName)){
        $import_record = json_decode(file_get_contents($settingName),true);
        if (is_array($import_record)){
            foreach ($import_record as $key => $value){
                $record['import_' . $key] = $value;
            }
        }
    }
   
    $defaultfile = str_replace($modx->getOption('base_path'),'',$modx->getOption('core_path')) . 'components/' . $packageName . '/import/' . $classname . '.csv';
    $record['import_file'] = isset($record['import_file']) && !empty($record['import_file']) ? $record['import_file'] : $defaultfile ;
    $defaultfile = str_replace($modx->getOption('base_path'),'',$modx->getOption('core_path')) . 'components/' . $packageName . '/export/' . $classname . '.csv';
    $record['export_file'] = isset($record['export_file']) && !empty($record['export_file']) ? $record['export_file'] : $defaultfile ;    
    if (isset($record['import_settings'])){
        if (!is_array($record['import_settings'])){
            $record['import_settings'] = array($record['import_settings']);
        }        
        $record['import_settings'] = array_diff($record['import_settings'],array('save_settings'));    
    }
    $record['export_joins'] = $export_joins;
   
}


//$hooksnippet_aftergetfields = 'viacor_aftergetfields';
if (!empty($hooksnippet_aftergetfields)) {
    $object->set('record_fields', $record);
    $snippetProperties = array();
    $snippetProperties['object'] = &$object;
    $snippetProperties['scriptProperties'] = $scriptProperties;
    $result = $modx->runSnippet($hooksnippet_aftergetfields, $snippetProperties);
    $result = $modx->fromJson($result);
    $error = $modx->getOption('error', $result, '');
    if (!empty($error)) {
        $updateerror = true;
        $errormsg = $error;
        return;
    }
    $record = $object->get('record_fields');
}

foreach ($record as $field => $fieldvalue) {
    if (!empty($fieldvalue) && is_array($fieldvalue)) {
        foreach ($fieldvalue as $key => $value) {
            $record[$field . '.' . $key] = $value;
        }
    }
}

$modx->migx->record_fields = $record;
