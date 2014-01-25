<?php

        $this->customconfigs['auto_create_tables']=true; 
        /*
         * the packageName where you have your classes
         * this can be used in processors
         */        
        $this->customconfigs['packageName']='migxresconnections';
        /*
         * the table-prefix for your package
         */
		$this->customconfigs['prefix']=null;
        /*
         * the tablename of the maintable
         * this can be used in processors - see example processors
         */
		//$this->customconfigs['tablename']='telephonedir';
		/*
		 * xdbedit-taskname
		 * xdbedit uses the grid and the processor-pathes with that name
		 */
		$this->customconfigs['task']='resconnections';
        $this->customconfigs['classname']='migxResConnection';
        
        /* create your own grid and set it here */
        //$this->customconfigs['grid']= 'dragdrop';
        
        
        /*
         * the caption of xdbedit-form
         */		
		$this->customconfigs['formcaption']='Connection';
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

        /* here you can include your TVs */
        
        //$this->customconfigs['includeTVs'] = 'migx1,name,latest_comment_date';
        $this->customconfigs['includeTVs'] = '';
        $this->customconfigs['idfield_local'] = 'resource_id_a';
        $this->customconfigs['idfield_foreign'] = 'resource_id_b';
        
        //$tvfields = $this->generateTvTab($this->customconfigs['includeTVs']);
        
        $this->customconfigs['tabs'] = array();
         
        $this->customconfigs['tabs'][] = 
                array(
                    'caption'=>'Connection',
                    'fields'=>array(
                    array(
                        'field'=>$this->customconfigs['idfield_foreign'],
                        'caption'=>'Connected Resource'
                    )));
        
        if (!empty($tvfields)){
            $this->customconfigs['tabs'][] = 
                    array(
                        'caption'=>'TVs',
                        'fields'=>$tvfields
                    );             
        }

        $this->customconfigs['tabs'][] =
                    array(
                    'caption'=>'Settings',
                    'fields'=>array(
                    array(
                        'field'=>'published',
                        'caption'=>'Published',
						'inputTV'=>'checkboxTV'
                    ),array(
                        'field'=>'hidemenu',
                        'caption'=>'Hide from Menu',
						'inputTV'=>'checkboxTV'
                    )));     

        $this->customconfigs['tabs'][] =
                    array(
                    'caption'=>'Dates',
                    'fields'=>array(
                    array(
                        'field'=>'pub_date',
                        'caption'=>'Publish on',
						'inputTV'=>'dateTV'
                    ),array(
                        'field'=>'unpub_date',
                        'caption'=>'Unpublish on',
						'inputTV'=>'dateTV'
                    ),array(
                        'field'=>'publishedon',
                        'caption'=>'Published on',
						'inputTV'=>'dateTV'
                    ),array(
                        'field'=>'ow_publishedon',
                        'caption'=>'Published on',
						'inputTV'=>'overwriteTV'
                    ),array(
                        'field'=>'createdon',
                        'caption'=>'Created on',
						'inputTV'=>'dateTV'
                    ),array(
                        'field'=>'ow_createdon',
                        'caption'=>'Created on',
						'inputTV'=>'overwriteTV'
                    )));                  
         

                    
$columns = '
[
{
  "header": "ID"
, "width": "10"
, "dataIndex": "id"
, "sortable": "true"
},{
  "header": "Connected Resource"
, "width": "40"
, "dataIndex": "'.$this->customconfigs['idfield_foreign'].'"
, "sortable": "true"
}]
';                

$this->customconfigs['columns'] = $this->modx->fromJson($columns);                          
                    
                    
/*);

/*
* here you can load your package(s) or in the processors
* 
*/

$prefix = $this->customconfigs['prefix'];
$packageName = $this->customconfigs['packageName'];
       
$packagepath = $this->modx->getOption('core_path') . 'components/'.$packageName.'/';
$modelpath = $packagepath.'model/';

$this->modx->addPackage($packageName,$modelpath,$prefix);
//$classname = $this->getClassName($tablename);

if ($this->modx->lexicon)
{
    $this->modx->lexicon->load($packageName.':default');
}
			