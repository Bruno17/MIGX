<?php

/*
* the packageName where you have your classes
* this can be used in processors
*/
//$this->customconfigs['packageName'] = 'migx';
/*
* the table-prefix for your package
*/
//$this->customconfigs['prefix'] = null;
/*
* the tablename of the maintable
* this can be used in processors - see example processors
*/
//$this->customconfigs['tablename']='telephonedir';
//$this->customconfigs['classname'] = 'migxConfig';
/*
* xdbedit-taskname
* xdbedit uses the grid and the processor-pathes with that name
*/
//$this->customconfigs['task'] = 'migxconfigs';
/*
* the caption of xdbedit-form
*/
$this->customconfigs['formcaption'] = "[[%migx]]";
$this->customconfigs['cmptabcaption'] = "[[%migx]]";
$this->customconfigs['cmptabdescription'] = "[[%migx.management_desc]]";

//$this->customconfigs['auto_create_tables'] = true;

$this->customconfigs['win_id'] = 'showselectorgrid';

/*
* the tabs and input-fields for your xdbedit-page
* outerarray: caption for Tab and fields
* innerarray of fields:
* field - the tablefield
* caption - the form-caption for that field
* inputTV - the TV which is used as input-type
* without inputTV or if not found it uses text-type
* 
*/


$tabs = '
[
{"caption":"[[%migx.selector_options]]", "fields": [
    {"field":"name","caption":"Name"},
    {"field":"selectorgrid","caption":"[[%migx.selector_options]]","inputTVtype":"migxdb","gridload_mode":"2"}
]}
]
';

$this->customconfigs['tabs'] = $this->modx->fromJson($tabs);
$this->customconfigs['gridload_mode'] = '2';

$columns = '
[
{
  "header": "ID"
, "width": "10"
, "dataIndex": "id"
},
{
  "header": "Name"
, "width": "10"
, "dataIndex": "name"
},
{
  "header": "Value"
, "width": "10"
, "dataIndex": "value"
},
{
  "header": "Image"
, "width": "10"
, "dataIndex": "image"
, "renderer":"this.renderImage"
}
,
{
  "header": "Select"
, "width": "10"
, "dataIndex": "selectoraction"
, "renderer":"this.renderOptionSelector"
}
]
';

$this->customconfigs['columns'] = $this->modx->fromJson($columns);


$extrahandlers = array();
if (isset($this->customconfigs['extrahandlers'])) {
    $extrahandlers = explode('||', $this->customconfigs['extrahandlers']);
}
$extrahandlers[] = 'this.selectSelectorOption';
$this->customconfigs['extrahandlers'] = implode('||',$extrahandlers);
