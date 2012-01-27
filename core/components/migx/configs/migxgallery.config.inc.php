<?php

        //$this->config['auto_create_tables']=false; //default is true
        /*
         * the packageName where you have your classes
         * this can be used in processors
         */        
        $this->customconfigs['packageName']='migxgallery';
        /*
         * the table-prefix for your package
         */
		$this->customconfigs['prefix']=null;
        /*
         * the tablename of the maintable
         * this can be used in processors - see example processors
         */
		//$this->customconfigs['tablename']='telephonedir';
        $this->customconfigs['classname']='migxGallery';
		/*
		 * xdbedit-taskname
		 * xdbedit uses the grid and the processor-pathes with that name
		 */
		$this->customconfigs['task']='migxgallery';
        /*
         * the caption of xdbedit-form
         */		
		$this->customconfigs['formcaption']='Image';
        
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
		$this->customconfigs['tabs']=			
			array(
                array(
                    'caption'=>'Image',
                    'fields'=>array(
                    array(
                        'field'=>'title',
                        'caption'=>'Title'
                    ),
                    array(
                        'field'=>'image',
                        'caption'=>'Image',
                        'inputTV'=>'image'
                    ),
                    array(
                        'field'=>'description',
                        'caption'=>'Description'
                ))),
                array(
                    'caption'=>'Dates',
                    'fields'=>array(
                    array(
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
                    
$columns = '
[
{
  "header": "ID"
, "width": "10"
, "dataIndex": "id"
},
{
  "header": "Title"
, "width": "10"
, "dataIndex": "title"
},  
{
  "header": "Description"
, "width": "10"
, "dataIndex": "description"
},{
    "header": "Image"
    ,"width": "160"
    ,"sortable": "false"
    ,"dataIndex": "image"
    ,"renderer": "this.renderImage"
}]
';                    
                    
$this->customconfigs['columns'] = $this->modx->fromJson($columns);   
                    
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