<?php
use xPDO\Transport\xPDOTransport;

/**
 * Create tables
 *
 * THIS SCRIPT IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package migx
 * @subpackage build.scripts
 *
 * @var \xPDO\Transport\xPDOTransport $transport
 * @var array $object
 * @var array $options
 */

$modx =& $transport->xpdo;

if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UNINSTALL) return true;

$manager = $modx->getManager();

$manager->createObjectContainer(\Migx\Model\migxConfig::class);
$manager->createObjectContainer(\Migx\Model\migxFormtab::class);
$manager->createObjectContainer(\Migx\Model\migxFormtabField::class);
$manager->createObjectContainer(\Migx\Model\migxConfigElement::class);
$manager->createObjectContainer(\Migx\Model\migxElement::class);

return true;
