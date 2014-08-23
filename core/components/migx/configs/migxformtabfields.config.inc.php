<?php

$tabs ='
[
{"caption":"Field", "fields": [
{"field":"field","caption":"Fieldname"},
{"field":"caption","caption":"Caption"},
{"field":"description","caption":"Description","inputTVtype":"textarea"},
{"field":"description_is_code","caption":"Description is Code","inputTVtype":"listbox","description":"Display the description as Form-code. MODX-tags gets parsed there.","inputOptionValues":"no==0||yes==1"},
{"field":"inputTV","caption":"Input TV"},
{"field":"inputTVtype","caption":"Input TV type"},
{"field":"validation","caption":"Validation","description":"Example: required"},
{"field":"configs","caption":"Configs","inputTVtype":"textarea","description":"this can be used either for a migx-TV-configname or for input-properties of any TV-type as json-string"},
{"field":"restrictive_condition","caption":"Restrictive Condition (MODX tags)","inputTVtype":"textarea","description":"An empty result will show this field"},
{"field":"display","caption":"Display","inputTVtype":"listbox","inputOptionValues":"yes==||no==none"}
]},
{"caption":"Mediasources", "fields": [
{"field":"sourceFrom","caption":"source From","inputTVtype":"listbox","inputOptionValues":"config||tv||migx"},
{"field":"sources","caption":"Sources","inputTVtype":"migx","configs":"migxfieldsources"}
]},
{"caption":"Input Options", "fields": [
{"field":"inputOptionValues","caption":"Input Option Values"},
{"field":"default","caption":"Default Value"},
{"field":"useDefaultIfEmpty","caption":"Use Default if Empty","inputTVtype":"listbox","inputOptionValues":"no==0||yes==1","description":"for example, when working with real TVs of resources, you would set this to yes, otherwise, the default-value is only used for new items"}
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
