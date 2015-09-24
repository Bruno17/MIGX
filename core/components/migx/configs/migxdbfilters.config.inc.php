<?php

$filters = array();
foreach ($gridfilters as $key => $value) {
    $filters[] = $key;
}

$prefixes = array();
$prefixes[] = 'default Prefix==0';
$prefixes[] = 'Custom Prefix==1';

$tabs = '
[
{"caption":"Filter", "fields": [
{"field":"name","caption":"filter Name"},
{"field":"label","caption":"Label"},
{"field":"emptytext","caption":"Empty Text"},
{"field":"type","caption":"Filter Type","inputTVtype":"listbox","inputOptionValues":"' . implode('||', $filters) . '"},
{"field":"getlistwhere","caption":"getlist-where","inputTVtype":"textarea"},
{"field":"getcomboprocessor","caption":"getcombo processor"},
{"field":"combotextfield","caption":"getcombo textfield"},
{"field":"comboidfield","caption":"getcombo idfield (optional)"},
{"field":"combowhere","caption":"getcombo where (optional)"},
{"field":"comboclassname","caption":"getcombo classname (optional)"},
{"field":"combopackagename","caption":"getcombo packageName (optional)"},
{"field":"combo_use_custom_prefix","caption":"Prefix","inputTVtype":"listbox","inputOptionValues":"' . implode('||', $prefixes) . '"},
{"field":"comboprefix","caption":"Custom Prefix"},
{"field":"combojoins","caption":"getcombo joins (optional)"},
{"field":"comboparent","caption":"parent combobox (name)"},
{"field":"default","caption":"default value"}
]}
] 
';

$columns = '
[
{
  "header": "ID"
, "width": "10"
, "dataIndex": "MIGX_id"
},
{
  "header": "Label"
, "width": "10"
, "dataIndex": "label"
},
{
  "header": "Type"
, "width": "10"
, "dataIndex": "type"
}]
';

$this->customconfigs['win_id'] = 'migxfilters';
$this->customconfigs['tabs'] = $this->modx->fromJson($tabs);
$this->customconfigs['columns'] = $this->modx->fromJson($columns);
