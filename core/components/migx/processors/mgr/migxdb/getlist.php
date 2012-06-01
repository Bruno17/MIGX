<?php

$task = $modx->migx->getTask();
$filename = basename(__file__);
$processorspath = dirname(dirname(__file__)). '/' ;

if ($processor_file = $modx->migx->findProcessor($processorspath,$filename)){
    include_once ($processor_file);    
}


$rows = is_array($rows) ? $rows : array();

return $this->outputArray($rows, $count);
