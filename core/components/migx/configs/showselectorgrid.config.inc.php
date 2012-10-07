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

//$base_url = $this->modx->getOption('base_url');
$img = '<a href="#" ><img class="controlBtn {3} {4} {5}" src="{0}" alt="{1}" title="{2}"></a>';
$renderer['this.renderOptionSelector'] = "
renderOptionSelector : function(val, md, rec, row, col, s) {
    //var column = this.getColumnModel().getColumnAt(col);
    //var ro = Ext.util.JSON.decode(rec.json[column.dataIndex+'_ro']);
    var renderImage, altText, handler, classname;
    renderImage = '/assets/components/migx/style/images/tick.png';
    handler = 'this.selectSelectorOption';
    classname = 'test';
    altText = 'test';
    return String.format('{$img}', renderImage, altText, altText, classname, handler, col);
}
";

$gridfunctions['this.selectSelectorOption'] = "
selectSelectorOption: function(n,e,col) {
    var btn,params;
    console.log(this);
    console.log(this.menu.record.data.name);
    var column = this.getColumnModel().getColumnAt(col);
    var ro_json = this.menu.record.json[column.dataIndex+'_ro'];
    var ro = Ext.util.JSON.decode(ro_json);
    
    return;
    if (ro.clickaction == 'showSelector'){
        console.log(ro);
        params = {
            action: ro.clickaction
            ,col: column.dataIndex
            ,idx: ro.idx            
        }
        
        this.loadWin(btn,e,'u', Ext.util.JSON.encode(params));        
    }

    return;
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'handlecolumnswitch'
                ,col: column.dataIndex
                ,idx: ro.idx
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }	
";
$extrahandlers = array();
if (isset($this->customconfigs['extrahandlers'])) {
    $extrahandlers = explode('||', $this->customconfigs['extrahandlers']);
}
$extrahandlers[] = 'this.selectSelectorOption';
$this->customconfigs['extrahandlers'] = implode('||',$extrahandlers);
