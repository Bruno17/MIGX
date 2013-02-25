<?php


$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];
$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';
$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

if ($object = $modx->getObject($classname, $scriptProperties['object_id'])) {
    $row = $modx->migx->recursive_decode($object->toArray());
    $packageName = $row['extended']['packageName'];
    if (!empty($packageName)) {
        $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
        $configpath = $packagepath . 'migxconfigs/';
        $filepath = $configpath . $row['name'] . '.config.js';
        if (file_exists($packagepath)) {
            if (!is_dir($configpath)) {
                mkdir($configpath, 0755);
            }
            if (is_dir($configpath)) {
                $fp = @fopen($filepath, 'w+');
                if ($fp) {
                    $result = @fwrite($fp, $modx->migx->indent($modx->toJson($row)));
                    @fclose($fp);
                }
                if ($result) {
                    $message = 'Config exported to ' . $filepath;
                    return $modx->error->success($message);
                }
            }
        }
    }
}

$message = 'Could not write ' . $filepath;

return $modx->error->failure($message);


