<?php
$task=$modx->migx->getTask();
$filename = basename(__file__);
$processorspath = dirname(dirname(__file__)). '/' ;
$updateerror=false;
$filenames = array();

$data = $modx->getOption('data',$scriptProperties,'');
$items = $modx->getOption('items',$scriptProperties,'');
$index = $modx->getOption('index',$scriptProperties,'append');
$isnew = $modx->getOption('isnew',$scriptProperties,0);

$data = $modx->fromJson($data);
$items = $modx->fromJson($items);

unset($data['undefined']);


if (isset($data['migx_selectorgrid_value'])){
  $filename = str_replace('.php','_selectfromgrid.php',$filename);    
}


if ($processor_file = $modx->migx->findProcessor($processorspath,$filename,$filenames)){
    include_once ($processor_file);    
}


if ($updateerror){
	return $modx->error->failure($errormsg);	
}

return $modx->error->success('',$items);