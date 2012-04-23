<?php

$tabs ='
[
{"caption":"Tabs", "fields": [
{"field":"caption","caption":"Caption"},
{"field":"fields","caption":"Fields","inputTVtype":"migx","configs":"migxformtabfields"}
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
  "header": "Tab Caption"
, "width": "10"
, "dataIndex": "caption"
}]
';

$this->customconfigs['win_id']= 'migxformtabs';                      
$this->customconfigs['tabs']= $this->modx->fromJson($tabs);                     
$this->customconfigs['columns'] = $this->modx->fromJson($columns); 