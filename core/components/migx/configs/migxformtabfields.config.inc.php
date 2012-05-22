<?php

$tabs ='
[
{"caption":"Field", "fields": [
{"field":"field","caption":"Fieldname"},
{"field":"caption","caption":"Caption"},
{"field":"inputTV","caption":"Input TV"},
{"field":"inputTVtype","caption":"Input TV type"},
{"field":"configs","caption":"Configs"}
]},
{"caption":"Mediasources", "fields": [
{"field":"sourceFrom","caption":"source From","inputTVtype":"listbox","inputOptionValues":"config||tv||migx"},
{"field":"sources","caption":"Sources","inputTVtype":"migx","configs":"migxfieldsources"}
]},
{"caption":"Input Options", "fields": [
{"field":"inputOptionValues","caption":"Input Option Values"},
{"field":"default","caption":"Default Value"}
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
  "header": "Fieldname"
, "width": "10"
, "dataIndex": "field"
},
{
  "header": "Caption"
, "width": "10"
, "dataIndex": "caption"
},
{
  "header": "Input TV"
, "width": "10"
, "dataIndex": "inputTV"
}]
';

$this->customconfigs['win_id']= 'migxformtabfields';                         
$this->customconfigs['tabs']= $this->modx->fromJson($tabs);   
$this->customconfigs['columns'] = $this->modx->fromJson($columns); 
