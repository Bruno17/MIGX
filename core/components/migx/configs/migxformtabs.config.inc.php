<?php

$tabs ='
[
{"caption":"Tabs", "fields": [
{"field":"caption","caption":"Caption"},
{"field":"print_before_tabs","caption":"Display above Tabs","inputTVtype":"listbox","description":"Display this tab-content before the other tabs. (Works only if it is the first tab)","inputOptionValues":"no==0||yes==1"},
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