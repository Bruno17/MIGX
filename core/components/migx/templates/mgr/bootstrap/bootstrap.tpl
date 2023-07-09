<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

use {$namespace}\{$namespace};
use xPDO\xPDO;

// Add the service
try {
    // Add the package and model classes
    $modx->addPackage('{$namespace}\\Model\\', $namespace['path'] . 'src/', {$table_prefix}, '{$namespace}\\');

    if (class_exists('{$namespace}\\{$namespace}')) {
        $modx->services->add('{$namespace}', function($c) use ($modx) {
            return new {$namespace}($modx);
        });
    }
}
catch (\Exception $e) {
    $modx->log(xPDO::LOG_LEVEL_ERROR, $e->getMessage());
}
