<?php

$success = true;
if ($object->xpdo) {
    /** @var modX $modx */
    $modx =& $object->xpdo;
 
    $modx->getVersionData();
    if (version_compare($modx->version['full_version'], '2.3', '<')) {
        $modx->log(modX::LOG_LEVEL_INFO, 'Install menu for MODX Revolution 2.2.x');
    } else {
        $modx->log(modX::LOG_LEVEL_INFO, 'Install menu for MODX Revolution 2.3.x');
    }
}
 
return $success;