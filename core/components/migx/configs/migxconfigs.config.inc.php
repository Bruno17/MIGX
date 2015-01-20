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
$this->customconfigs['formcaption'] = "[[%migx]]";
$this->customconfigs['cmptabcaption'] = "[[%migx]]";
$this->customconfigs['cmptabdescription'] = "[[%migx.management_desc]]";

$this->customconfigs['auto_create_tables'] = true;

$this->customconfigs['win_id'] = 'migxconfigs';

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

$mf_options = array();
$mf_options[] = '---==0';
$classname = 'migxConfig';
$c = $this->modx->newQuery($classname);
$c->select($this->modx->getSelectColumns($classname, $classname, '', array('id', 'name')));
$c->sortby('name');
if ($collection = $this->modx->getCollection($classname, $c)) {
    foreach ($collection as $object) {
        $mf_options[] = $object->get('name') . '==' . $object->get('id');
    }
}

if (!empty($_REQUEST['tempParams']) && $_REQUEST['tempParams'] == 'export_import') {
    $tabs = '
[
{"caption":"Json", "fields": [
    {"field":"jsonexport","caption":"Json","inputTVtype":"textarea"}
]}
]
';
} else {
    $inputType = !empty($_REQUEST['tempParams']) && $_REQUEST['tempParams'] == 'raw' ? 'textarea' : 'migx';

    $menus = array();
    foreach ($gridcontextmenus as $key => $value) {
        $menus[] = $key;
    }
    $actionbuttons = array();
    foreach ($gridactionbuttons as $key => $value) {
        $actionbuttons[] = $key;
    }
    $windowbuttons = array();
    foreach ($winbuttons as $key => $value) {
        $windowbuttons[] = $key;
    }    
    $allhandlers = array();
    foreach ($gridfunctions as $key => $value) {
        $allhandlers[] = $key;
    }

    $prefixes = array();
    $prefixes[] = 'default Prefix==0';
    $prefixes[] = 'Custom Prefix==1';

    $tabs = '
[
{"caption":"Settings", "fields": [
    {"field":"name","caption":"Name"},
    {"field":"extended.migx_add","caption":"[[%migx.add_replacement]]"},
    {"field":"extended.disable_add_item","caption":"Disable Add Items","inputTVtype":"checkbox","inputOptionValues":"disabled==1"},
    {"field":"extended.add_items_directly","caption":"Add Items directly","description":"without modal window","inputTVtype":"checkbox","inputOptionValues":"add directly==1"},
    {"field":"extended.formcaption","caption":"Form Caption","description":"placeholders like [[+pagetitle]] can be used"},
    {"field":"extended.update_win_title","caption":"Window Title"},
    {"field":"extended.win_id","caption":"unique MIGX ID"},
    {"field":"extended.maxRecords","caption":"max MIGX records"},
    {"field":"extended.addNewItemAt","caption":"Add new MIGX records at","inputTVtype":"listbox","inputOptionValues":"bottom||top","default":"bottom"}
]},
{"caption":"formtabs", "fields": [
    {"field":"formtabs","caption":"Formtabs","inputTVtype":"' . $inputType . '","configs":"migxformtabs"},
    {"field":"extended.multiple_formtabs","caption":"Multiple Formtabs","inputTVtype":"listbox-multiple","inputOptionValues":"' . implode('||', $mf_options) . '"},
    {"field":"extended.multiple_formtabs_label","caption":"Multiple Formtabs Label","description":"Label for formtabs-selectbox"},
    {"field":"extended.multiple_formtabs_field","caption":"Multiple Formtabs Field","description":"Fieldname for this value. Default:MIGX_formname"}, 
    {"field":"extended.multiple_formtabs_optionstext","caption":"Multiple Formtabs Optionstext","description":"Text in formtabs-selectbox for this config"},
    {"field":"extended.multiple_formtabs_optionsvalue","caption":"Multiple Formtabs Optionsvalue","description":"Value in formtabs-selectbox for this config. Default is the name of this config."}        
]},
{"caption":"Columns", "fields": [
    {"field":"columns","caption":"Columns","inputTVtype":"' . $inputType . '","configs":"migxcolumns"}
]},
{"caption":"Contextmenues", "fields": [
    {"field":"contextmenus","caption":"Contextmenues","inputTVtype":"checkbox","inputOptionValues":"' . implode('||', $menus) . '"}
]},
{"caption":"Columnbuttons", "fields": [
    {"field":"columnbuttons","caption":"Columnbuttons","inputTVtype":"checkbox","inputOptionValues":"' . implode('||', $menus) . '"}
]},
{"caption":"Actionbuttons", "fields": [
    {"field":"extended.actionbuttonsperrow","caption":"Buttons per row","inputTVtype":"listbox","inputOptionValues":"1||2||3||4||5","default":"4"},
    {"field":"actionbuttons","caption":"Actionbuttons","inputTVtype":"checkbox","inputOptionValues":"' . implode('||', $actionbuttons) . '"}
    
]},
{"caption":"Window Buttons", "fields": [
    {"field":"extended.winbuttonslist","caption":"Window Buttons","inputTVtype":"checkbox","inputOptionValues":"' . implode('||', $windowbuttons) . '"}
]},
{"caption":"Handlers", "fields": [
    {"field":"extended.extrahandlers","caption":"Extra Handlers","inputTVtype":"checkbox","inputOptionValues":"' . implode('||', $allhandlers) . '"}
]},
{"caption":"Db-Filters", "fields": [
    {"field":"extended.filtersperrow","caption":"Filters per row","inputTVtype":"listbox","inputOptionValues":"1||2||3||4||5","default":"4"},
    {"field":"filters","caption":"Filters","inputTVtype":"migx","configs":"migxdbfilters"}
]},
{"caption":"MIGXdb-Settings", "fields": [
    {"field":"extended.packageName","caption":"Package"},
    {"field":"extended.classname","caption":"Classname"},
    {"field":"extended.task","caption":"Processors Path"},
    {"field":"extended.getlistsort","caption":"getlist defaultsort"},
    {"field":"extended.getlistsortdir","caption":"getlist defaultsortdir"},
    {"field":"extended.sortconfig","caption":"Sort Config","description":"multifield-sortconfig - json-format","inputTVtype":"textarea"},
    {"field":"extended.gridpagesize","caption":"Items per Page Default (default=10)"},
    {"field":"extended.use_custom_prefix","caption":"Prefix","inputTVtype":"listbox","inputOptionValues":"' . implode('||', $prefixes) . '"},
    {"field":"extended.prefix","caption":"Custom Prefix"},
    {"field":"extended.grid","caption":"Grid"},
    {"field":"extended.gridload_mode","caption":"Load Grid","inputTVtype":"listbox","inputOptionValues":"by Button==1||auto==2","default":"1"},
    {"field":"extended.check_resid","caption":"Check Resource","inputTVtype":"listbox","inputOptionValues":"yes==1||no==0||@TV","default":"0"},
    {"field":"extended.check_resid_TV","caption":"Check Resource TV"},
    {"field":"extended.join_alias","caption":"Join Alias"},
    {"field":"extended.has_jointable","caption":"Has Extra Connection Table","inputTVtype":"listbox","inputOptionValues":"yes||no","default":"yes"},
    {"field":"extended.getlistwhere","caption":"Where"},
    {"field":"extended.joins","caption":"Joins","inputTVtype":"textarea"},
    {"field":"extended.hooksnippets","caption":"Hook Snippets","description":"Example:{\"aftersave\":\"myaftersave_snippet\"}","inputTVtype":"textarea"}
]},
{"caption":"CMP-Settings", "fields": [
    {"field":"extended.cmpmaincaption","caption":"Main Caption"},
    {"field":"extended.cmptabcaption","caption":"Tab Caption"},
    {"field":"extended.cmptabdescription","caption":"Tab Description"},
    {"field":"extended.cmptabcontroller","caption":"Custom Tab Controller"}
]},
{"caption":"MIGXfe-Settings", "fields": [
    {"field":"extended.winbuttons","caption":"Window Buttons","inputTVtype":"textarea","description":"js-code, running on window-creation. See migxfe/templates/web/form/form.tpl and winbuttons.tpl"},
    {"field":"extended.onsubmitsuccess","caption":"On Submit success","inputTVtype":"textarea","description":"js-code, running on submit success"},
    {"field":"extended.submitparams","caption":"Submit params","inputTVtype":"textarea","description":"additional submit params"}
]}
]
';
}


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
},
{
  "header": "Deleted"
, "width": "10"
, "dataIndex": "deleted"
, "show_in_grid" : "false"
}
]
';

$this->customconfigs['columns'] = $this->modx->fromJson($columns);

$filter = array();
$filter['name'] = 'searchconfig';
$filter['label'] = 'search';
$filter['emptytext'] = 'search...';
$filter['type'] = 'textbox';
$filter['getlistwhere'] = '{"name:LIKE":"%[[+searchconfig]]%"}';

$this->customconfigs['filters'] = array();
$this->customconfigs['filters'][] = $filter;

$gridcontextmenus['editraw']['active'] = 1;
$gridcontextmenus['export_import']['active'] = 1;
$gridcontextmenus['export_to_package']['active'] = 1;

$gridcontextmenus['update']['active'] = 1;
$gridcontextmenus['publish']['active'] = 0;
$gridcontextmenus['unpublish']['active'] = 0;
$gridcontextmenus['recall_remove_delete']['active'] = 1;

$gridactionbuttons['addItem']['active'] = 1;
$gridactionbuttons['bulk']['active'] = 1;
$gridactionbuttons['toggletrash']['active'] = 1;
$gridactionbuttons['import_from_package']['active'] = 1;


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
