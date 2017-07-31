<?php


$renderers[] = '---==';
foreach ($renderer as $key => $value) {
    $renderers[] = $key;
}

$editorsoptions[] = '---==';
foreach ($editors as $key => $value) {
    $editorsoptions[] = $key;
}

$tabs = '
[
{"caption":"Column", "fields": [
{"field":"header","caption":"Header"},
{"field":"dataIndex","caption":"Field"},
{"field":"width","caption":"Column width"},
{"field":"sortable","caption":"Sortable","inputTVtype":"listbox","inputOptionValues":"yes==true||no==false","default":"false"},
{"field":"show_in_grid","caption":"Show in Grid","inputTVtype":"listbox","inputOptionValues":"yes==1||no==0","default":"1"}
]},
{"caption":"Renderer", "fields": [
{"field":"customrenderer","caption":"Custom Renderer"},
{"field":"renderer","caption":"Renderer","inputTVtype":"listbox","inputOptionValues":"' . implode('||', $renderers) . '"},
{"field":"clickaction","caption":"on Click","inputTVtype":"listbox","inputOptionValues":"||switchOption||selectFromGrid"},
{"field":"selectorconfig","caption":"SelectFromGrid config"},
{"field":"renderchunktpl","caption":"renderChunk template","inputTVtype":"textarea"},
{"field":"renderoptions","caption":"Renderoptions","inputTVtype":"migx","configs":"migxcolumnrenderoptions"}
]},
{"caption":"Cell Editor", "fields": [
{"field":"editor","caption":"Editor","inputTVtype":"listbox","inputOptionValues":"' . implode('||', $editorsoptions) . '"}
]}
] 
';

include 'selectdbfields.inc.php';

$gridfunctions['this.selectDbFields'] = "
selectDbFields: function(btn,e) {
        this.call_collectmigxitems_once=true;
        var formtabs = '';
        var fields = Ext.get('tv' + this.tv).getValue();
        this.loadWin(btn,e,0,'a',Ext.util.JSON.encode({'selectDbFields':1,'formtabs':formtabs,'fields':fields}));
	}
";

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


$this->customconfigs['tabs'] = $this->modx->fromJson($tabs);
$this->customconfigs['columns'] = $this->modx->fromJson($columns);
