<?php

$config = $modx->migx->customconfigs;

$reqConfigs = $modx->getOption('reqConfigs',$_REQUEST);
$configs = $modx->getOption('configs',$_REQUEST);

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

$tablename = $modx->getTableName($classname);

$maxdate = strftime ('%Y-%m-%d %H:%M:%S',time() - (360 * 24 * 60 * 60));

//echo "update {$tablename} set deleted=1 where createdon < '{$maxdate}'";

$modx->exec("delete from {$tablename} where deleted = 1");

return $modx->error->success();