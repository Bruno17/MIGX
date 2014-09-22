<?php
/*
getXpdoInstanceAndAddPackage - properties

$prefix
$usecustomprefix
$packageName


prepareQuery - properties:

$limit
$offset
$totalVar
$where
$queries
$sortConfig
$groupby
$joins
$selectfields
$classname
$debug

renderOutput - properties:

$tpl
$wrapperTpl
$toSeparatePlaceholders
$toPlaceholder
$outputSeparator
$placeholdersKeyField
$toJsonPlaceholder
$jsonVarKey
$addfields

*/


$migx = $modx->getService('migx', 'Migx', $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/migx/', $scriptProperties);
if (!($migx instanceof Migx))
    return '';
//$modx->migx = &$migx;

$xpdo = $migx->getXpdoInstanceAndAddPackage($scriptProperties);

$defaultcontext = 'web';
$migx->working_context = isset($modx->resource) ? $modx->resource->get('context_key') : $defaultcontext;

$c = $migx->prepareQuery($xpdo,$scriptProperties);
$rows = $migx->getCollection($c);

$output = $migx->renderOutput($rows,$scriptProperties);

return $output;