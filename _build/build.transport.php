<?php
/*
 * @package blox
 * @subpackage build
 *
 * bloX build script
 */
$mtime = microtime();
$mtime = explode(' ', $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);

/* define package */
define('PKG_NAME', 'AddHeaderfiles');
define('PKG_NAME_LOWER', strtolower(PKG_NAME));
define('PKG_VERSION', '0.5');
define('PKG_RELEASE', 'pl');

/* define sources */
$root = dirname(dirname(__FILE__)) . '/';
$sources = array(
	'root' => $root,
	'build' => $root . '_build/',
	'data' => $root . '_build/data/',
	'resolvers' => $root . '_build/resolvers/',
	'properties' => $root . '_build/properties/',
	'chunks' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/chunks/',
	'snippets' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/snippets/',
	'plugins' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/plugins/',
	'lexicon' => $root . 'core/components/' . PKG_NAME_LOWER . '/lexicon/',
	'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
	'pages' => $root . 'core/components/' . PKG_NAME_LOWER . '/elements/pages/',
	'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
	'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
);
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once $sources['build'] . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
require_once $sources['build'] . '/includes/functions.php';

$modx = new modX();
$modx->initialize('mgr');
echo '<pre>'; /* used for nice formatting of log messages */
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/');

/* create category */
$category = $modx->newObject('modCategory');
$category->set('id', 1);
$category->set('category', PKG_NAME);
$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in category.');
flush();

/* add snippets */
$snippets = include $sources['data'] . 'transport.snippets.php';
if (is_array($snippets)) {
	$category->addMany($snippets, 'Snippets');
} else {
	$modx->log(modX::LOG_LEVEL_FATAL, 'Adding snippets failed.');
}
$modx->log(modX::LOG_LEVEL_INFO, 'Packaged in ' . count($snippets) . ' snippets.');
flush();
unset($snippets);

/* create category vehicle */
$attr = array(
	xPDOTransport::UNIQUE_KEY => 'category',
	xPDOTransport::PRESERVE_KEYS => false,
	xPDOTransport::UPDATE_OBJECT => true,
	xPDOTransport::RELATED_OBJECTS => true,
	xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
		'Snippets' => array(
			xPDOTransport::PRESERVE_KEYS => false,
			xPDOTransport::UPDATE_OBJECT => true,
			xPDOTransport::UNIQUE_KEY => 'name',
		),
	)
);
$vehicle = $builder->createVehicle($category, $attr);

$modx->log(modX::LOG_LEVEL_INFO, 'Adding file resolvers to plugin...');
$vehicle->resolve('file', array(
	'source' => $sources['source_core'],
	'target' => "return MODX_CORE_PATH . 'components/';",
));
$builder->putVehicle($vehicle);

/* now pack in the license file, readme and changelog */
$modx->log(modX::LOG_LEVEL_INFO, 'Added package attributes and setup options.');
$builder->setPackageAttributes(array(
	'license' => file_get_contents($sources['docs'] . 'license.txt'),
	'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
	'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
));

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO, 'Packing up transport package zip...');
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO, "\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");

exit();