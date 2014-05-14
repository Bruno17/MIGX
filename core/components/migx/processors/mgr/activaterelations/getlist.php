<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

$checkdeleted = isset($config['gridactionbuttons']['toggletrash']['active']) && !empty($config['gridactionbuttons']['toggletrash']['active']) ? true : false;

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);
    //all packages must have the same prefix for now!
    foreach ($packageNames as $packageName) {
        $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
        $modelpath = $packagepath . 'model/';
        if (is_dir($modelpath)) {
            $modx->addPackage($packageName, $modelpath, $prefix);
        }

    }
}

$classname = $config['classname'];

//join all applications with the currently edited product
$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

$joinconfig = $modx->fromJson($joinalias);

$joinclass = $modx->getOption('classname', $joinconfig, '');
$local = $modx->getOption('local', $joinconfig, '');
$foreign = $modx->getOption('foreign', $joinconfig, '');

$object_id = $modx->getOption('object_id', $scriptProperties, '');
$joins = '[{"alias":"Joined","classname":"' . $joinclass . '","on":"Joined.' . $foreign . '=' . $classname . '.id AND Joined.' . $local . '=' . $object_id . '"}]';
$joins = $modx->fromJson($joins);


if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

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

if (isset($sortConfig)) {
    $sort = '';
}

$where = !empty($config['getlistwhere']) ? $config['getlistwhere'] : '';
$where = $modx->getOption('where', $scriptProperties, $where);

$c = $modx->newQuery($classname);
$c->select($modx->getSelectColumns($classname, $classname));


if ($joins) {
    $modx->migx->prepareJoins($classname, $joins, $c);
}

if (isset($config['gridfilters']) && count($config['gridfilters']) > 0) {
    foreach ($config['gridfilters'] as $filter) {

        if (!empty($filter['getlistwhere'])) {

            $requestvalue = $modx->getOption($filter['name'], $scriptProperties, 'all');

            if (isset($scriptProperties[$filter['name']]) && $requestvalue != 'all') {

                $chunk = $modx->newObject('modChunk');
                $chunk->setCacheable(false);
                $chunk->setContent($filter['getlistwhere']);
                $fwhere = $chunk->process($scriptProperties);
                $fwhere = strpos($fwhere, '{') === 0 ? $modx->fromJson($fwhere) : $fwhere;

                $c->where($fwhere);
            }
        }
    }
}


if ($checkdeleted) {
    if (!empty($showtrash)) {
        $c->where(array($classname . '.deleted' => '1'));
    } else {
        $c->where(array($classname . '.deleted' => '0'));
    }
}

if (!empty($where)) {
    $c->where($modx->fromJson($where));
}

$count = $modx->getCount($classname, $c);

if (empty($sort)) {
    if (is_array($sortConfig)) {
        foreach ($sortConfig as $sort) {
            $sortby = $sort['sortby'];
            $sortdir = isset($sort['sortdir']) ? $sort['sortdir'] : 'ASC';
            $c->sortby($sortby, $sortdir);
        }
    }


} else {
    $c->sortby($sort, $dir);
}
if ($isCombo || $isLimit) {
    $c->limit($limit, $start);
}
//$c->sortby($sort,$dir);
//$c->prepare();echo $c->toSql();
$rows = array();
if ($collection = $modx->getCollection($classname, $c)) {
    $pk = $modx->getPK($classname);
    foreach ($collection as $object) {
        $row = $object->toArray();
        $row['Joined_active'] = !empty($row['Joined_id']) ? 1 : 0;
        $row['id'] = !isset($row['id']) ? $row[$pk] : $row['id'];
        $rows[] = $row;
    }
}


$rows = $modx->migx->checkRenderOptions($rows);
