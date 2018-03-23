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

migxGetObject - properties

$id
$toPlaceholders - if not empty, output to placeholders with specified prefix or print the array, if 'print_r' is the property-value

*/

$id = $modx->getOption('id',$scriptProperties,'');
$scriptProperties['limit'] = 1;

$migx = $modx->getService('migx', 'Migx', $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/migx/', $scriptProperties);
if (!($migx instanceof Migx))
    return '';
//$modx->migx = &$migx;

$xpdo = $migx->getXpdoInstanceAndAddPackage($scriptProperties);

$defaultcontext = 'web';
$migx->working_context = isset($modx->resource) ? $modx->resource->get('context_key') : $defaultcontext;

$c = $migx->prepareQuery($xpdo,$scriptProperties);
if (!empty($id)){
    $c->where(array('id'=>$id));
	$c->prepare();
}	
$rows = $migx->getCollection($c);

$output = $migx->renderOutput($rows,$scriptProperties);

return $output;