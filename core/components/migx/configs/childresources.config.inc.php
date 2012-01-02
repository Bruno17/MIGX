<?php

        //$this->config['auto_create_tables']=false; //default is true
        /*
         * the packageName where you have your classes
         * this can be used in processors
         */        
        $this->customconfigs['packageName']='example';
        /*
         * the table-prefix for your package
         */
		$this->customconfigs['prefix']='modx_example_';
        /*
         * the tablename of the maintable
         * this can be used in processors - see example processors
         */
		$this->customconfigs['tablename']='telephonedir';
		/*
		 * xdbedit-taskname
		 * xdbedit uses the grid and the processor-pathes with that name
		 */
		$this->customconfigs['task']='childresources';
        /*
         * the caption of xdbedit-form
         */		
		$this->customconfigs['formcaption']='Contact';
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
                    'caption'=>'Contact',
                    'fields'=>array(
                    array(
                        'field'=>'pagetitle',
                        'caption'=>'Pagetitle'
                    ),
                    array(
                        'field'=>'jobtitle',
                        'caption'=>'Job Title',
                        'inputTV'=>'image'
                    ),
                    array(
                        'field'=>'region',
                        'caption'=>'Region',
                        'inputTV'=>'region'
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