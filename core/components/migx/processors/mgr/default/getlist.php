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
        if (is_dir($modelpath)){
            $modx->addPackage($packageName, $modelpath, $prefix);
        } 
        
    }
}

$classname = $config['classname'];

$joins = isset($config['joins']) && !empty($config['joins']) ? $modx->fromJson($config['joins']) : false;

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

if (!empty($joinalias)) {
    if ($fkMeta = $modx->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
        $joinfield = $fkMeta[$fkMeta['owner']];
    } else {
        $joinalias = '';
    }
}


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

if (!empty($joinalias)) {
    /*
    if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
    $localkey = $joinFkMeta['local'];
    }    
    */
    $c->leftjoin($joinclass, $joinalias);
    $c->select($modx->getSelectColumns($joinclass, $joinalias, 'Joined_'));
}

if ($joins) {
    $modx->migx->prepareJoins($classname, $joins, $c);
}

/*
$c->leftjoin('poProduktFormat','ProduktFormat', 'format_id = poFormat.id AND product_id ='.$scriptProperties['object_id']);
//$c->select($classname.'.*');

$c->select('ProduktFormat.format_id,ProduktFormat.calctype,ProduktFormat.price,ProduktFormat.published AS pof_published');
*/

//print_r($config['gridfilters']);

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


if ($modx->migx->checkForConnectedResource($resource_id, $config)) {
    if (!empty($joinalias)) {
        $c->where(array($joinalias . '.' . $joinfield => $resource_id));
    } else {
        $c->where(array($classname . '.resource_id' => $resource_id));
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
        $row['id'] = !isset($row['id']) ? $row[$pk] : $row['id'];
        $rows[] = $row;
    }
}

$rows = $modx->migx->checkRenderOptions($rows);
