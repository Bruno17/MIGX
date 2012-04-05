<?php

        //$this->config['auto_create_tables']=false; //default is true
        /*
         * the packageName where you have your classes
         * this can be used in processors
         */        
        $this->customconfigs['packageName']='migx';
        /*
         * the table-prefix for your package
         */
		$this->customconfigs['prefix']=null;
        /*
         * the tablename of the maintable
         * this can be used in processors - see example processors
         */
		//$this->customconfigs['tablename']='telephonedir';
        $this->customconfigs['classname']='migxConfig';
		/*
		 * xdbedit-taskname
		 * xdbedit uses the grid and the processor-pathes with that name
		 */
		$this->customconfigs['task']='migxconfigs';
        /*
         * the caption of xdbedit-form
         */		
		$this->customconfigs['formcaption']='Image';
        
        $this->customconfigs['auto_create_tables'] = true;
        
        /*
        if (is_object($this->modx->resource)){
            $res_id = $this->modx->resource->get('id');
        }
        else {
            $res_id = $_REQUEST['resource_id'];
        }
        
        $container_id = '26';
        
        $is_container = $res_id == $container_id;
        $this->customconfigs['is_container'] = $is_container;
        */
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
/*
$customer_field = '';
if ($is_container){
$customer_field = ',{"field":"customerid","caption":"Kunde","inputTV":"customer_resourcelist"}';    
}  
*/

$rawValues = '
,
{"caption":"raw Values", "fields": [
    {"field":"formtabs","caption":"Formtabs","inputTV":"textareaTV"},
    {"field":"columns","caption":"Columns","inputTV":"textareaTV"}
]}
';

$tabs ='
[
{"caption":"Settings", "fields": [
    {"field":"name","caption":"Name"}
]},
{"caption":"formtabs", "fields": [
    {"field":"formtabs","caption":"Formtabs","inputTV":"migx_formtabs"}

]},
{"caption":"Columns", "fields": [
    {"field":"columns","caption":"Columns","inputTVtype":"migx","configs":"migxcolumns"}
]}
]
';         
         
$this->customconfigs['tabs']= $this->modx->fromJson($tabs); 
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

if ($is_container){
    
    $column['header'] = 'Kunde';
    $column['dataIndex'] = 'customerid';
    $column['sortable'] = 'true';
    $this->customconfigs['columns'][] = $column;    
}  

$this->customconfigs['gridfunctions'] =
"
    ,sendCustomerMail: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/".$this->customconfigs['task']."/sendcustomermail'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,mode: 'customer'
            }
            /*
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
            */
        });
    }
    ,sendTestMail: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/".$this->customconfigs['task']."/sendcustomermail'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,mode: 'test'
            }
            /*
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
            */
        });
    }    		     
";

$this->customconfigs['gridcontextmenus'] =
"
        m.push('-');
        m.push({
            text: 'Rechnung versenden'
            ,handler: this.sendCustomerMail
        });
        m.push('-');
        m.push({
            text: 'Testmail senden'
            ,handler: this.sendTestMail
        });                    
";

                    
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