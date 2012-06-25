<?php


$renderers[] = '---==';
foreach ($renderer as $key => $value) {
    $renderers[] = $key;
}

$tabs = '
[
{"caption":"Columns", "fields": [
{"field":"header","caption":"Header"},
{"field":"dataIndex","caption":"Field"},
{"field":"width","caption":"Column width"},
{"field":"renderer","caption":"Renderer","inputTVtype":"listbox","inputOptionValues":"' . implode('||', $renderers) . '"},
{"field":"sortable","caption":"Sortable","inputTVtype":"listbox","inputOptionValues":"yes==true||no==false","default":"false"},
{"field":"show_in_grid","caption":"Show in Grid","inputTVtype":"listbox","inputOptionValues":"yes==1||no==0","default":"1"}
]}
] 
';

$columns = '
[
{
  "header": "Header"
, "width": "10"
, "dataIndex": "header"
},
{
  "header": "Field"
, "width": "10"
, "dataIndex": "dataIndex"
},
{
  "header": "Renderer"
, "width": "10"
, "dataIndex": "renderer"
}
]
';


$this->customconfigs['tabs'] = $this->modx->fromJson($tabs);
$this->customconfigs['columns'] = $this->modx->fromJson($columns);
