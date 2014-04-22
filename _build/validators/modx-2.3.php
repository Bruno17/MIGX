<?php

$success = false;
if ($object->xpdo) {
    /** @var modX $modx */
    $modx =& $object->xpdo;
 
    $modx->getVersionData();
    if (version_compare($modx->version['full_version'], '2.3', '>=')) {
        $success = true;
    } else {
        $modx->log(modX::LOG_LEVEL_WARN, 'Skipping menu for MODX Revolution 2.3');
    }
}
 
return $success;