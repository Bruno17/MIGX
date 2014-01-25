<?php

$pathTpl = $modx->getOption('pathTpl', $scriptProperties, '');
$objectid = $modx->getOption('objectid', $scriptProperties, '');
$createfolder = $modx->getOption('createFolder', $scriptProperties, '1');
$path = '';
$createpath = false;

if (empty($objectid) && $modx->getPlaceholder('objectid')) {
    // placeholder was set by some script on frontend for example
    $objectid = $modx->getPlaceholder('objectid');
}
if (empty($objectid)) {

    //set Session - var in fields.php - processor
    if (isset($_SESSION['migxWorkingObjectid'])) {
        $objectid = $_SESSION['migxWorkingObjectid'];
        $createpath = !empty($createfolder);
    }

}


$path = str_replace('{id}', $objectid, $pathTpl);

$fullpath = $modx->getOption('base_path') . $path;

if ($createpath && !file_exists($fullpath)) {
    mkdir($fullpath, 0755, true);
}

return $path;
