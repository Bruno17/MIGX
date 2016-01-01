<?php

//print_r($this->customconfigs);

$record = $this->modx->fromJson($this->modx->getOption('record_json',$_REQUEST,''));
$type = $this->modx->getOption('MIGXtype',$record,'');
$tempParams = $this->modx->fromJson($this->modx->getOption('tempParams',$_REQUEST,''));
$type = $this->modx->getOption('type',$tempParams,$type);

$tabs_formtab ='
[
{"caption":"Tabs", "fields": [
{"field":"MIGXtype","inputTVtype":"hidden","default":"formtab"},
{"field":"MIGXtyperender","inputTVtype":"hidden","default":"<h3>formtab</h3>"},
{"field":"caption","caption":"Caption"},
{"field":"print_before_tabs","caption":"Display above Tabs","inputTVtype":"listbox","description":"Display this tab-content before the other tabs. (Works only if it is the first tab)","inputOptionValues":"no==0||yes==1"}
]}
] 
';  

$tabs_layout ='
[
{"caption":"Layout", "fields": [
{"field":"MIGXtype","inputTVtype":"hidden","default":"layout"},
{"field":"MIGXtyperender","inputTVtype":"hidden","default":"<h3>.layout</h3>"},
{"field":"MIGXlayoutcaption","caption":"Caption"},
{"field":"MIGXlayoutstyle","caption":"Style"}
]}
] 
';

$tabs_column ='
[
{"caption":"Column", "fields": [
{"field":"MIGXtype","inputTVtype":"hidden","default":"column"},
{"field":"MIGXtyperender","inputTVtype":"hidden","default":"<h3>..column</h3>"},
{"field":"field","caption":"Column width","description":"default:100% - For two columns try: calc(50% - 10px)"},
{"field":"MIGXcolumnminwidth","caption":"Column min-width","description":"if you have inputTVtypes with hardcoded width (ex: listbox), try to set a min-with"},
{"field":"MIGXcolumncaption","caption":"Caption"},
{"field":"MIGXcolumnstyle","caption":"Style"}
]}
] 
';                    

$tabs_field ='
[
{"caption":"Field", "fields": [
{"field":"MIGXtype","inputTVtype":"hidden","default":"field"},
{"field":"MIGXtyperender","inputTVtype":"hidden","default":"<h3>...field</h3>"},
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

switch($type){
    case 'field':
    $tabs = $tabs_field;
    break;
    case 'formtab':
    $tabs = $tabs_formtab;
    break;
    case 'layout':
    $tabs = $tabs_layout;    
    break;  
    case 'column':
    $tabs = $tabs_column;    
    break;
    default:
    $tabs = '';
    break;      
}

   

$columns = '
[
{
  "header": "Type"
, "width": "10"
, "dataIndex": "MIGXtyperender"
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

$this->customconfigs['win_id']= 'migxformlayouts';    
$this->customconfigs['disable_add_item'] = 1;                     
$this->customconfigs['tabs']= $this->modx->fromJson($tabs);   
$this->customconfigs['columns'] = $this->modx->fromJson($columns); 

$gridactionbuttons['add_formtab']['text'] = "'[[%migx.add_formtab]]'";
$gridactionbuttons['add_formtab']['handler'] = 'this.addFormtab';
$gridactionbuttons['add_formtab']['scope'] = 'this';
$gridactionbuttons['add_formtab']['standalone'] = '1';
$gridactionbuttons['add_formtab']['active'] = 1;
$gridfunctions['this.addFormtab'] = "
addFormtab: function(btn,e) {
        var s=this.getStore();
        this.loadWin(btn,e,s.getCount(),'a',Ext.util.JSON.encode({'type':'formtab'}));   
	}
";

$gridactionbuttons['add_field']['text'] = "'[[%migx.add_field]]'";
$gridactionbuttons['add_field']['handler'] = 'this.addField';
$gridactionbuttons['add_field']['scope'] = 'this';
$gridactionbuttons['add_field']['standalone'] = '1';
$gridactionbuttons['add_field']['active'] = 1;
$gridfunctions['this.addField'] = "
addField: function(btn,e) {
        var s=this.getStore();
        this.loadWin(btn,e,s.getCount(),'a',Ext.util.JSON.encode({'type':'field'}));   
	}
";

$gridactionbuttons['add_layout']['text'] = "'[[%migx.add_formlayout]]'";
$gridactionbuttons['add_layout']['handler'] = 'this.addFormLayout';
$gridactionbuttons['add_layout']['scope'] = 'this';
$gridactionbuttons['add_layout']['standalone'] = '1';
$gridactionbuttons['add_layout']['active'] = 1;
$gridfunctions['this.addFormLayout'] = "
addFormLayout: function(btn,e) {
        var s=this.getStore();
        this.addNewItem({'MIGXtype':'layout','MIGXtyperender':'<h3>.layout</h3>'}); 
	}
";

$gridactionbuttons['add_layoutcolumn']['text'] = "'[[%migx.add_layoutcolumn]]'";
$gridactionbuttons['add_layoutcolumn']['handler'] = 'this.addLayoutcolumn';
$gridactionbuttons['add_layoutcolumn']['scope'] = 'this';
$gridactionbuttons['add_layoutcolumn']['standalone'] = '1';
$gridactionbuttons['add_layoutcolumn']['active'] = 1;
$gridfunctions['this.addLayoutcolumn'] = "
addLayoutcolumn: function(btn,e) {
        var s=this.getStore();
        this.loadWin(btn,e,s.getCount(),'a',Ext.util.JSON.encode({'type':'column'})); 
          
	}
";





