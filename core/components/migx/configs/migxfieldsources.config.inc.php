<?php

$tabs ='
[
{"caption":"Mediasources", "fields": [
{"field":"context","caption":"Context"},
{"field":"sourceid","caption":"Source"}
]}
] 
';         

$columns = '
[
{
  "header": "Context"
, "width": "10"
, "dataIndex": "context"
},
{
  "header": "Source ID"
, "width": "10"
, "dataIndex": "sourceid"
}]
';

$this->customconfigs['win_id']= 'migxfieldsources';                         
$this->customconfigs['tabs']= $this->modx->fromJson($tabs);   
$this->customconfigs['columns'] = $this->modx->fromJson($columns); 
