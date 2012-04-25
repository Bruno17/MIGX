<?php

$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

if (!empty($joinalias)) {
    if ($fkMeta = $modx->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
    } else {
        $joinalias = '';
    }
}

if ($this->modx->lexicon) {
    $this->modx->lexicon->load($packageName . ':default');
}

if (empty($scriptProperties['object_id']) || $scriptProperties['object_id'] == 'new') {
    $object = $modx->newObject($classname);
    $object->set('object_id', 'new');
} else {
    $c = $modx->newQuery($classname, $scriptProperties['object_id']);
    $c->select('
        `' . $classname . '`.*,
    	`' . $classname . '`.`id` AS `object_id`
    ');
    if (!empty($joinalias)) {
        $c->leftjoin($joinclass, $joinalias);
        $c->select($modx->getSelectColumns($joinclass, $joinalias, 'Joined_'));
    }
    $object = $modx->getObject($classname, $c);
}

//handle json fields
$record = $object->toArray();
foreach ($record as $field => $fieldvalue) {
    if (!empty($fieldvalue) && is_array($fieldvalue)) {
        foreach ($fieldvalue as $key => $value) {
            $record[$field . '.' . $key] = $value;
        }
    }
}
