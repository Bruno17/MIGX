<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start', $scriptProperties, 0);
$limit = $modx->getOption('limit', $scriptProperties, 20);
$sort = !empty($config['getlistsort']) ? $config['getlistsort'] : 'id';
$sort = $modx->getOption('sort', $scriptProperties, $sort);
$dir = !empty($config['getlistsortdir']) ? $config['getlistsortdir'] : 'ASC';
$dir = $modx->getOption('dir', $scriptProperties, $dir);
$showtrash = $modx->getOption('showtrash', $scriptProperties, '');
$object_id = $modx->getOption('object_id', $scriptProperties, '');
$resource_id = $modx->getOption('resource_id', $scriptProperties, is_object($modx->resource) ? $modx->resource->get('id') : false);
$resource_id = !empty($object_id) ? $object_id : $resource_id;


$where = !empty($config['getlistwhere']) ? $config['getlistwhere'] : '';
$where = $modx->getOption('where', $scriptProperties, $where);

$properties['configs'] = $modx->getOption('reqConfigs', $scriptProperties, '');
$modx->migx->loadConfigs(true, true, $properties, $sender);
$col = $modx->getOption('col',$tempParams,'');
$renderoptions = $modx->migx->getColumnRenderOptions($col, $indexfield = 'idx');
//print_r($renderoptions);

foreach ($renderoptions as $option){
    $row = $modx->fromJson($option);
    $rows[] = $row;
}

$rows = $modx->migx->checkRenderOptions($rows);

