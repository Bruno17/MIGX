<?php
/**
 * snippets transport file for MIGX extra
 *
 * Copyright 2013 by Bruno Perner b.perner@gmx.de
 * Created on 05-16-2014
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
/* @var xPDOObject[] $snippets */


$snippets = array();

$snippets[1] = $modx->newObject('modSnippet');
$snippets[1]->fromArray(array(
    'id' => '1',
    'property_preprocess' => '',
    'name' => 'getImageList',
    'description' => '',
    'properties' => array(),
), '', true, true);
$snippets[1]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/getimagelist.snippet.php'));

$snippets[2] = $modx->newObject('modSnippet');
$snippets[2]->fromArray(array(
    'id' => '2',
    'property_preprocess' => '',
    'name' => 'migxGetRelations',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[2]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migxgetrelations.snippet.php'));

$snippets[3] = $modx->newObject('modSnippet');
$snippets[3]->fromArray(array(
    'id' => '3',
    'property_preprocess' => '',
    'name' => 'migx',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[3]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migx.snippet.php'));

$snippets[4] = $modx->newObject('modSnippet');
$snippets[4]->fromArray(array(
    'id' => '4',
    'property_preprocess' => '',
    'name' => 'migxLoopCollection',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[4]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migxloopcollection.snippet.php'));

$snippets[5] = $modx->newObject('modSnippet');
$snippets[5]->fromArray(array(
    'id' => '5',
    'property_preprocess' => '',
    'name' => 'migxResourceMediaPath',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[5]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migxresourcemediapath.snippet.php'));

$snippets[6] = $modx->newObject('modSnippet');
$snippets[6]->fromArray(array(
    'id' => '6',
    'property_preprocess' => '',
    'name' => 'migxImageUpload',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[6]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migximageupload.snippet.php'));

$snippets[7] = $modx->newObject('modSnippet');
$snippets[7]->fromArray(array(
    'id' => '7',
    'property_preprocess' => '',
    'name' => 'migxChunklistToJson',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[7]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migxchunklisttojson.snippet.php'));

$snippets[8] = $modx->newObject('modSnippet');
$snippets[8]->fromArray(array(
    'id' => '8',
    'property_preprocess' => '',
    'name' => 'migxSwitchDetailChunk',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[8]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migxswitchdetailchunk.snippet.php'));

$snippets[9] = $modx->newObject('modSnippet');
$snippets[9]->fromArray(array(
    'id' => '9',
    'property_preprocess' => '',
    'name' => 'getSwitchColumnCol',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[9]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/getswitchcolumncol.snippet.php'));

$snippets[10] = $modx->newObject('modSnippet');
$snippets[10]->fromArray(array(
    'id' => '10',
    'property_preprocess' => '',
    'name' => 'getDayliMIGXrecord',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[10]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/getdaylimigxrecord.snippet.php'));

$snippets[11] = $modx->newObject('modSnippet');
$snippets[11]->fromArray(array(
    'id' => '11',
    'property_preprocess' => '',
    'name' => 'filterbytag',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[11]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/filterbytag.snippet.php'));

$snippets[12] = $modx->newObject('modSnippet');
$snippets[12]->fromArray(array(
    'id' => '12',
    'property_preprocess' => '',
    'name' => 'migxObjectMediaPath',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[12]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migxobjectmediapath.snippet.php'));

$snippets[13] = $modx->newObject('modSnippet');
$snippets[13]->fromArray(array(
    'id' => '13',
    'property_preprocess' => '',
    'name' => 'exportMIGX2db',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[13]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/exportmigx2db.snippet.php'));

$snippets[14] = $modx->newObject('modSnippet');
$snippets[14]->fromArray(array(
    'id' => '14',
    'property_preprocess' => '',
    'name' => 'preparedatewhere',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[14]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/preparedatewhere.snippet.php'));

$snippets[15] = $modx->newObject('modSnippet');
$snippets[15]->fromArray(array(
    'id' => '15',
    'property_preprocess' => '',
    'name' => 'migxJsonToPlaceholders',
    'description' => '',
    'properties' => '',
), '', true, true);
$snippets[15]->setContent(file_get_contents($sources['source_core'] . '/elements/snippets/migxjsontoplaceholders.snippet.php'));

return $snippets;
