<?php


/*
* the packageName where you have your classes
* this can be used in processors
*/
$this->customconfigs['packageName'] = 'migx';
/*
* the table-prefix for your package
*/
$this->customconfigs['prefix'] = null;
/*
* the tablename of the maintable
* this can be used in processors - see example processors
*/
//$this->customconfigs['tablename']='telephonedir';
$this->customconfigs['classname'] = 'migxConfig';
/*
* xdbedit-taskname
* xdbedit uses the grid and the processor-pathes with that name
*/
$this->customconfigs['task'] = 'migxconfigs';
/*
* the caption of xdbedit-form
*/
$this->customconfigs['formcaption'] = 'Image';

$this->customconfigs['auto_create_tables'] = true;


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

$inputType = !empty($_REQUEST['tempParams']) && $_REQUEST['tempParams'] == 'raw' ? 'textarea' : 'migx';
$menus = array();
foreach ($gridcontextmenus as $key=>$value){
    $menus[] = $key;
}
$actionbuttons = array();
foreach ($gridactionbuttons as $key=>$value){
    $actionbuttons[] = $key;
}

$tabs = '
[
{"caption":"Settings", "fields": [
    {"field":"name","caption":"Name"}
]},
{"caption":"formtabs", "fields": [
    {"field":"formtabs","caption":"Formtabs","inputTVtype":"' . $inputType . '","configs":"migxformtabs"}

]},
{"caption":"Columns", "fields": [
    {"field":"columns","caption":"Columns","inputTVtype":"' . $inputType . '","configs":"migxcolumns"}
]},
{"caption":"Contextmenues", "fields": [
    {"field":"contextmenus","caption":"Contextmenues","inputTVtype":"checkbox","inputOptionValues":"'.implode('||',$menus).'"}
]},
{"caption":"Actionbuttons", "fields": [
    {"field":"actionbuttons","caption":"Actionbuttons","inputTVtype":"checkbox","inputOptionValues":"'.implode('||',$actionbuttons).'"}
]}
]
';

$this->customconfigs['tabs'] = $this->modx->fromJson($tabs);
/*
$this->customconfigs['tabs']=			
array(
array(
'caption'=>'Rechnungdaten',
'fields'=>array(
array(
'field'=>'nr',
'caption'=>'Nr'
),
array(
'field'=>'basket',
'caption'=>'Positionen',
'inputTV'=>'migxBasket'
))),
array(
'caption'=>'Dates',
'fields'=>array(
array(
'field'=>'pub_date',
'caption'=>'Publish on',
'inputTV'=>'datum'
),array(
'field'=>'unpub_date',
'caption'=>'Unpublish on',
'inputTV'=>'datum'
),array(
'field'=>'publishedon',
'caption'=>'Published on',
'inputTV'=>'datum'
),array(
'field'=>'ow_publishedon',
'caption'=>'Published on',
'inputTV'=>'overwrite'
),array(
'field'=>'createdon',
'caption'=>'Created on',
'inputTV'=>'datum'
),array(
'field'=>'ow_createdon',
'caption'=>'Created on',
'inputTV'=>'overwrite'
))));

*/
$columns = '
[
{
  "header": "ID"
, "width": "10"
, "dataIndex": "id"
, "sortable": "true"
},
{
  "header": "Name"
, "width": "10"
, "dataIndex": "name"
}
]
';

$this->customconfigs['columns'] = $this->modx->fromJson($columns);

$gridcontextmenus['editraw']['active'] = 1; 

$gridcontextmenus['update']['active'] = 1;
$gridcontextmenus['publish']['active'] = 0;
$gridcontextmenus['unpublish']['active'] = 0;
$gridcontextmenus['recall_remove_delete']['active'] = 0;

$gridactionbuttons['addItem']['active'] = 1;
$gridactionbuttons['bulk']['active'] = 1;
$gridactionbuttons['toggletrash']['active'] = 1;



/*);

/*
* here you can load your package(s) or in the processors
* 
*/
/*
$prefix = $this->customconfigs['prefix'];
$packageName = $this->customconfigs['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/'.$packageName.'/';
$modelpath = $packagepath.'model/';

$modx->addPackage($packageName,$modelpath,$prefix);
$classname = $this->getClassName($tablename);

if ($this->modx->lexicon)
{
$this->modx->lexicon->load($packageName.':default');
}
*/
