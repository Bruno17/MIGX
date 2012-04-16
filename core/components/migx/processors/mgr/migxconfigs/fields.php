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

//handle json fields
$record = $object->toArray();
foreach ($record as $field=>$fieldvalue){
    if (!empty($fieldvalue) && is_array($fieldvalue)){
        foreach ($fieldvalue as $key => $value){
            $record[$field.'.'.$key] = $value;
        }
    }    
}

if (!empty($scriptProperties['tempParams']) && $scriptProperties['tempParams']=='raw'){

    
}else{
    $tabs = $modx->fromJson($record['formtabs']);
    foreach ($tabs as $tab){
       $fields = is_array($tab['fields']) ? $modx->toJson($tab['fields']) : $tab['fields'];
       $tab['fields'] = $fields;
       $formtabs[]=$tab;     
    }
    $record['formtabs'] = $modx->toJson($formtabs);    
}