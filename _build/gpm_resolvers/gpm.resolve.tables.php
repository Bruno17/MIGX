<?php
/**
 * Resolve creating db tables
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package migx
 * @subpackage build
 *
 * @var mixed $object
 * @var modX $modx
 * @var array $options
 */

if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:
            $modelPath = $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/';
            
            $modx->addPackage('migx', $modelPath, null);


            $manager = $modx->getManager();

            $manager->createObjectContainer('migxConfig');
            $manager->createObjectContainer('migxFormtab');
            $manager->createObjectContainer('migxFormtabField');
            $manager->createObjectContainer('migxConfigElement');
            $manager->createObjectContainer('migxElement');

            break;
    }
}

return true;