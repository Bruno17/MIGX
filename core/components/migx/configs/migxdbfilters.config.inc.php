<?php

$filters = array();
foreach ($gridfilters as $key => $value) {
    $filters[] = $key;
}

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