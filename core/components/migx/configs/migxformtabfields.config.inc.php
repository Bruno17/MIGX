<?php

$tabs ='
[
{"caption":"Fields", "field": [
{"field":"field","caption":"Fieldname"},
{"field":"caption","caption":"Caption"},
{"field":"inputTV","caption":"Input TV"},
{"field":"inputTVtype","caption":"Input TV type"},
{"field":"configs","caption":"Configs"}
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
