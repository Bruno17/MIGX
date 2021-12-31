<?php
/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

$modx->addPackage('Migx\Model', $namespace['path'] . 'src/', null, 'Migx\\');

/*
$modx->services->add('Migx', function($c) use ($modx) {
    return new Migx\Migx($modx);
});
*/