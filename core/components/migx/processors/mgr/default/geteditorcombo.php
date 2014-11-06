<?php

$config = $modx->migx->customconfigs;

$fields = $modx->migx->extractFieldsFromTabs($modx->fromJson($modx->getOption('formtabs', $config, '')));
$fieldname = $modx->getOption('field', $scriptProperties, '');
$field = $modx->getOption($fieldname, $fields, '');

if (isset($field['inputTV']) && $tv = $this->modx->getObject('modTemplateVar', array('name' => $field['inputTV']))) {

}

if (!empty($field['inputTVtype'])) {
    $tv = $this->modx->newObject('modTemplateVar');
    $tv->set('type', $field['inputTVtype']);
}

if (!$tv) {
    $tv = $this->modx->newObject('modTemplateVar');
    $tv->set('type', 'text');
}

if (!empty($field['inputOptionValues'])) {
    $tv->set('elements', $field['inputOptionValues']);
}

$resource_id = 0;
$inputoptions = $tv->parseInputOptions($tv->processBindings($tv->get('elements'),$resouce_id));
$options = array();
if (is_array($inputoptions)){
    foreach ($inputoptions as $option){
        $parts = explode('==',$option);
        $label = $parts[0];
        $value = isset($parts[1]) ? $parts[1] : $parts[0];
        $options[] = array('combo_name' => $label, 'combo_id' => $value);    
    }
}

$count = count($options);
return $this->outputArray($options, $count);
