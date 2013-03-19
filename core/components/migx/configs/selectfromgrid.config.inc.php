<?php

$scriptProperties = $_REQUEST;

// special actions, for example the showSelector - action
$tempParams = $this->modx->getOption('tempParams', $scriptProperties, '');

if (!empty($tempParams)) {
    $tempParams = $this->modx->fromJson($tempParams);
    $col = $this->modx->getOption('col', $tempParams, '');
}

$this->customconfigs['formcaption'] = "Select record(s)";
$this->customconfigs['cmptabcaption'] = "[[%migx]]";
$this->customconfigs['cmptabdescription'] = "[[%migx.management_desc]]";

//$this->customconfigs['auto_create_tables'] = true;

$this->customconfigs['win_id'] = 'showselector';

/*
* the tabs and input-fields for your xdbedit-page
* outerarray: caption for Tab and fields
* innerarray of fields:
* field - the tablefield
* caption - the form-caption for that field
* inputTV - the TV which is used as input-type
* without inputTV or if not found it uses text-type
* 
*/


$tabs = '
[
{"caption":"[[%migx.selector_options]]", "fields": [
    {"field":"migx_selectorgrid_column","inputTVtype":"hidden","default":"'.$col.'"},
    {"field":"migx_selectorgrid_value","caption":"[[%migx.selector_options]]","inputTVtype":"migxdb","configs":"showselectorgrid"}
]}
]
';

$this->customconfigs['tabs'] = $this->modx->fromJson($tabs);
