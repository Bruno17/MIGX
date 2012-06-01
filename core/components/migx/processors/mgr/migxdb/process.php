<?php

$task = $modx->migx->getTask();
$filename = $scriptProperties['processaction'].'.php';
$processorspath = dirname(dirname(__file__)). '/' ;
$filenames = array();

if ($processor_file = $modx->migx->findProcessor($processorspath,$filename,$filenames)){
    $o = include $processor_file;
    return $o;    
}

$message = 'could not find any of these processor-files: <br /> ' . implode('<br />',$filenames);

return $modx->error->failure($message);
