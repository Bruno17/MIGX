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
if (empty($objectid) && isset($_REQUEST['object_id'])) {
    $objectid = $_REQUEST['object_id'];
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
        $permissions = octdec('0' . (int)($modx->getOption('new_folder_permissions', null, '755', true)));
        if (!@mkdir($fullpath, $permissions, true)) {
            $modx->log(MODX_LOG_LEVEL_ERROR, sprintf('[migxResourceMediaPath]: could not create directory %s).', $fullpath));
        }
        else{
            chmod($fullpath, $permissions); 
        }
}

return $path;