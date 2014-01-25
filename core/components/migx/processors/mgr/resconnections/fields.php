<?php

$config = $modx->migx->customconfigs;
$includeTVs = isset($config['includeTVs']) ? explode(',', $config['includeTVs']) : array();

$classname = $config['classname'];

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
    $object = $modx->getObject($classname, $c);
}

$record = $object->toArray();

/*
foreach ($includeTVs as $tvname) {
    $record[$tvname] = $object->getTVValue($tvname);
}
*/
