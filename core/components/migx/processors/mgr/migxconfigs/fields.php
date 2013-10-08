<?php

$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];
$sender = 'migxconfigs/fields';

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
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


//handle json fields
$record = $object->toArray();

$modx->migx->configsObject = &$object;

if (!empty($scriptProperties['tempParams']) && $scriptProperties['tempParams'] == 'export_import') {
    $temprecord = $record;
    unset($temprecord['id'], $temprecord['name'], $temprecord['createdby'], $temprecord['createdon'], $temprecord['editedby'], $temprecord['editedon'], $temprecord['deleted'], $temprecord['deletedon'], $temprecord['deletedby'],
        $temprecord['published'], $temprecord['publishedon'], $temprecord['publishedby'], $temprecord['object_id']);

    $row = $modx->migx->recursive_decode($temprecord);
    $record['jsonexport'] = $modx->migx->indent($modx->toJson($row));
} else {

    foreach ($record as $field => $fieldvalue) {
        if (!empty($fieldvalue) && is_array($fieldvalue)) {
            foreach ($fieldvalue as $key => $value) {
                $record[$field . '.' . $key] = $value;
            }
        }
    }


    $tabs = $modx->fromJson($record['formtabs']);
    $formtabs = array();
    if (is_array($tabs) && count($tabs) > 0) {
        foreach ($tabs as $tab) {
            $fields = is_array($tab['fields']) ? $tab['fields'] :$modx->fromJson($tab['fields']) ;
            $tab['fields'] = $fields;
            $formtabs[] = $tab;
        }
    }

    $record['formtabs'] = $modx->toJson($formtabs);

}
