<?php
$object_id = 'new';
$config = $modx->migx->customconfigs;

$hooksnippets = $modx->fromJson($modx->getOption('hooksnippets',$config,''));
if (is_array($hooksnippets)){
    $hooksnippet_aftergetfields = $modx->getOption('aftergetfields',$hooksnippets,'');
}

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);

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
}else{
    $xpdo = &$modx;    
}

$sender = 'default/fields';

$classname = $config['classname'];

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

$joins = isset($config['joins']) && !empty($config['joins']) ? $modx->fromJson($config['joins']) : false;

if (!empty($joinalias)) {
    if ($fkMeta = $xpdo->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
    } else {
        $joinalias = '';
    }
}

if ($this->modx->lexicon) {
    $this->modx->lexicon->load($packageName . ':default');
}

if (empty($scriptProperties['object_id']) || $scriptProperties['object_id'] == 'new') {
    if ($object = $xpdo->newObject($classname)){
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
    if ($object = $xpdo->getObject($classname, $c)){
        $object_id = $object->get('id');
    }
}

$_SESSION['migxWorkingObjectid'] = $object_id;

//handle json fields
if ($object){
    $record = $object->toArray();
}
else{
    $record = array();
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
