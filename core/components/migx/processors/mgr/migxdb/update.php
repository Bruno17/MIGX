<?php
$task=$modx->migx->getTask();
$filename = basename(__file__);
$processorspath = dirname(dirname(__file__)). '/' ;
$updateerror=false;
$filenames = array();
if ($processor_file = $modx->migx->findProcessor($processorspath,$filename,$filenames)){
    include_once ($processor_file);    
}

if ($updateerror){
	return $modx->error->failure($errormsg);	
}

return $modx->error->success('',$object);