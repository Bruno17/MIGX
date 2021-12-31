<?php
use xPDO\Transport\xPDOTransport;

/**
 * Include bootstrap when installing the package
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package migx
 * @subpackage build
 *
 * @var \MODX\Revolution\modNamespace $object
 * @var \MODX\Revolution\modX $modx
 * @var array $options
 */

$modx =& $object->xpdo;
if ($options[xPDOTransport::PACKAGE_ACTION] !== xPDOTransport::ACTION_INSTALL) return true;

$bootstrap = $object->getCorePath() . 'bootstrap.php';
if (file_exists($bootstrap)) {
    $namespace = $object->toArray();
    $namespace['path'] = $object->getCorePath();
    $namespace['assets_path'] = $object->getAssetsPath();

    require $object->getCorePath() . 'bootstrap.php';
}

return true;
