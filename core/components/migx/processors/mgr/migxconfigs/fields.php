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
    $allfields = array();

    if (is_array($tabs) && count($tabs) > 0) {
        foreach ($tabs as $tab) {
            $layout_id = 0;
            $column_id = 0;
            $field = $tab;
            unset($field['fields']);
            $field['MIGXtype'] = 'formtab';

            $field['MIGXtyperender'] = '<h3>' . $field['MIGXtype'] . '</h3>';
            $allfields[] = $field;
            $fields = is_array($tab['fields']) ? $tab['fields'] : $modx->fromJson($tab['fields']);
            $tab['fields'] = $fields;
            $formtabs[] = $tab;            
            $newfields = array();
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($field['MIGXlayoutid']) && $field['MIGXlayoutid'] != $layout_id) {
                        $layout_id = $field['MIGXlayoutid'];
                        $column_id = 0;
                        $tmp_field = array();
                        $tmp_field['MIGXtype'] = 'layout';
                        $tmp_field['MIGXtyperender'] = '<h3>.' . $tmp_field['MIGXtype'] . '</h3>';
                        $tmp_field['MIGXlayoutcaption'] = $modx->getOption('MIGXlayoutcaption', $field, '');
                        $tmp_field['MIGXlayoutstyle'] = $modx->getOption('MIGXlayoutstyle', $field, '');                        
                        $newfields[] = $tmp_field;
                    }
                    if (isset($field['MIGXcolumnid']) && $field['MIGXcolumnid'] != $column_id) {
                        $column_id = $field['MIGXcolumnid'];
                        $tmp_field = array();
                        $tmp_field['MIGXtype'] = 'column';
                        $tmp_field['MIGXtyperender'] = '<h3>..' . $tmp_field['MIGXtype'] . '</h3>';
                        $tmp_field['field'] = $modx->getOption('MIGXcolumnwidth', $field, '');
                        $tmp_field['MIGXcolumnminwidth'] = $modx->getOption('MIGXcolumnminwidth', $field, '');
                        $tmp_field['MIGXcolumncaption'] = $modx->getOption('MIGXcolumncaption', $field, '');
                        $tmp_field['MIGXcolumnstyle'] = $modx->getOption('MIGXcolumnstyle', $field, '');
                        $newfields[] = $tmp_field;
                    }

                    $field['MIGXtype'] = 'field';
                    $field['MIGXtyperender'] = '<h3>...' . $field['MIGXtype'] . '</h3>';
                    $newfields[] = $field;
                }
            }

            $tab['fields'] = $newfields;
            $allfields = array_merge($allfields, $newfields);
        }
    }

    $record['formtabs'] = $modx->migx->indent($modx->toJson($formtabs));
    $record['formlayouts'] = $modx->toJson($allfields);

}
