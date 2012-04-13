<?php

$config=$modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/'.$packageName.'/';
$modelpath = $packagepath.'model/';

$modx->addPackage($packageName,$modelpath,$prefix);
$classname = $config['classname'];


if ($this->modx->lexicon)
{
    $this->modx->lexicon->load($packageName.':default');
}

if (empty($scriptProperties['object_id'])||$scriptProperties['object_id']=='new') {
	$object = $modx->newObject($classname);
	$object->set('object_id','new');
}
else
{
    $c = $modx->newQuery($classname, $scriptProperties['object_id']);

    $c->select('
        `'.$classname.'`.*,
    	`'.$classname.'`.`id` AS `object_id`
    ');
    $object = $modx->getObject($classname, $c);
}

$record = $object->toArray();

if (!empty($record['extended']) && is_array($record['extended'])){
    foreach ($record['extended'] as $key => $value){
        $record['extended.'.$key] = $value;
    }
}