<?php
/**
 * plugins transport file for MIGX extra
 *
 * Copyright 2013 by Bruno Perner b.perner@gmx.de
 * Created on 05-11-2014
 *
 * @package migx
 * @subpackage build
 */

if (! function_exists('stripPhpTags')) {
    function stripPhpTags($filename) {
        $o = file_get_contents($filename);
        $o = str_replace('<' . '?' . 'php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */
/* @var xPDOObject[] $plugins */


$plugins = array();

$plugins[1] = $modx->newObject('modPlugin');
$plugins[1]->fromArray(array(
    'id' => '1',
    'property_preprocess' => '',
    'name' => 'MIGX',
    'description' => '',
    'properties' => array(),
    'disabled' => '',
), '', true, true);
$plugins[1]->setContent(file_get_contents($sources['source_core'] . '/elements/plugins/migx.plugin.php'));

$plugins[2] = $modx->newObject('modPlugin');
$plugins[2]->fromArray(array(
    'id' => '2',
    'property_preprocess' => '',
    'name' => 'MIGXquip',
    'description' => '',
    'properties' => array(),
    'disabled' => '1',
), '', true, true);
$plugins[2]->setContent(file_get_contents($sources['source_core'] . '/elements/plugins/migxquip.plugin.php'));

return $plugins;
