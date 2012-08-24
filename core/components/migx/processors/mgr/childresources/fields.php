<?php

$config = $modx->migx->customconfigs;
$includeTVList = $modx->getOption('includeTVList', $config, '');
$includeTVList = !empty($includeTVList) ? explode(',', $includeTVList) : array();
$includeTVs = $modx->getOption('includeTVs', $config, false);

$classname = 'modResource';
/*
if ($this->modx->lexicon) {
    $this->modx->lexicon->load($packageName . ':default');
}
*/
if (empty($scriptProperties['object_id']) || $scriptProperties['object_id'] == 'new') {
    $object = $modx->newObject($classname);
    $object->set('object_id', 'new');
    $object->set('show_in_tree',0);
} else {
    $c = $modx->newQuery($classname, $scriptProperties['object_id']);

    $c->select('
        `' . $classname . '`.*,
    	`' . $classname . '`.`id` AS `object_id`
    ');
    $object = $modx->getObject($classname, $c);
}

$record = $object->toArray();

if ($includeTVs) {
    foreach ($includeTVList as $tvname) {
        if ($tv = $this->modx->getObject('modTemplateVar', array('name' => $tvname))){
            $record[$tvname] = $tv->getValue($object->get('id'));
        }
        
    }
}
