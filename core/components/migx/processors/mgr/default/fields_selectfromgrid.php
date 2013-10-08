<?php

$config = $modx->migx->customconfigs;
$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;

if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])){
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';    
}
$packageName = $config['packageName'];

//print_r($tempParams);

$col = $modx->getOption('col',$tempParams,'');
$idx = $modx->getOption('idx',$tempParams,'');
$renderoptions = $modx->migx->getColumnRenderOptions($col, 'idx');
//print_r($renderoptions[$idx]);

//handle json fields
$record = $modx->fromJson($renderoptions[$idx]);
