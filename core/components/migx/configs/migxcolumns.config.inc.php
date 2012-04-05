<?php

$tabs ='
[
{"caption":"Columns", "fields": [
{"field":"header","caption":"Header"},
{"field":"dataIndex","caption":"Field"},
{"field":"renderer","caption":"Renderer"}
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

                    
$this->customconfigs['tabs']= $this->modx->fromJson($tabs);                     
$this->customconfigs['columns'] = $this->modx->fromJson($columns); 
