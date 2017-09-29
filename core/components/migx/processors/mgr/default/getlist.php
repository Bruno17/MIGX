<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);
    $packageName = isset($packageNames[0]) ? $packageNames[0] : '';    

    if (count($packageNames) == '1') {
        //for now connecting also to foreign databases, only with one package by default possible
        $xpdo = $modx->migx->getXpdoInstanceAndAddPackage($config);
    } else {
        //all packages must have the same prefix for now!
        foreach ($packageNames as $packageName) {
            $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
            $modelpath = $packagepath . 'model/';
            if (is_dir($modelpath)) {
                $modx->addPackage($packageName, $modelpath, $prefix);
            }

        }
        $xpdo = &$modx;
    }
    if ($this->modx->lexicon) {
        $this->modx->lexicon->load($packageName . ':default');
    }    
}else{
    $xpdo = &$modx;    
}

$classname = $config['classname'];
$checkdeleted = isset($config['gridactionbuttons']['toggletrash']['active']) && !empty($config['gridactionbuttons']['toggletrash']['active']) ? true : false;
$joins = isset($config['joins']) && !empty($config['joins']) ? $modx->fromJson($config['joins']) : false;

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

if (!empty($joinalias)) {
    if ($fkMeta = $xpdo->getFKDefinition($classname, $joinalias)) {
        //print_r($fkMeta);

        $joinclass = $fkMeta['class'];
        if($fkMeta['owner'] == 'foreign'){
            $joinfield = $fkMeta['foreign'];
		    //$parent_joinfield = $fkMeta['local']; 
		} elseif ($fkMeta['owner'] == 'local'){
            $joinfield = $fkMeta['local'];
		    //$parent_joinfield = $fkMeta['foreign']; 
		}
    } else {
        $joinalias = '';
    }
}

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start', $scriptProperties, 0);
$limit = $modx->getOption('limit', $scriptProperties, 20);
$sort = !empty($config['getlistsort']) ? $config['getlistsort'] : $xpdo->getPK($classname);
$sort = $modx->getOption('sort', $scriptProperties, $sort);
$requestsort = $modx->getOption('sort', $scriptProperties, '');
$dir = !empty($config['getlistsortdir']) ? $config['getlistsortdir'] : 'ASC';
$dir = $modx->getOption('dir', $scriptProperties, $dir);
$showtrash = $modx->getOption('showtrash', $scriptProperties, '');
$object_id = $modx->getOption('object_id', $scriptProperties, '');
$resource_id = $modx->getOption('resource_id', $scriptProperties, is_object($modx->resource) ? $modx->resource->get('id') : false);
$resource_id = !empty($object_id) ? $object_id : $resource_id;

$sortConfig = $modx->getOption('sortconfig', $config, '');

if (!empty($sortConfig)) {
    $sort = !empty($requestsort) ? $requestsort : '';
    if (!is_array($sortConfig)) {
        $sortConfig = $modx->fromJson($sortConfig);
    }
}

$where = !empty($config['getlistwhere']) ? $config['getlistwhere'] : '';
$where = $modx->getOption('where', $scriptProperties, $where);

$chunk = $modx->newObject('modChunk');
$chunk->setCacheable(false);
$chunk->setContent($where);
$where = $chunk->process($scriptProperties);

$c = $xpdo->newQuery($classname);
$c->select($xpdo->getSelectColumns($classname, $classname));

if (!empty($joinalias)) {
    /*
    if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
    $localkey = $joinFkMeta['local'];
    }    
    */
    $c->leftjoin($joinclass, $joinalias);
    $c->select($xpdo->getSelectColumns($joinclass, $joinalias, 'Joined_'));
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
        $joinvalue = $resource_id;
        if ($parent_object = $modx->getObject($joinclass,$resource_id)){
			$joinvalue = $parent_object->get($joinfield);
        }
        $c->where(array($joinalias . '.' . $joinfield => $joinvalue));
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

$count = $xpdo->getCount($classname, $c);

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
if ($collection = $xpdo->getCollection($classname, $c)) {
    $pk = $xpdo->getPK($classname);
    foreach ($collection as $object) {
        $row = $object->toArray();
        $row['id'] = !isset($row['id']) ? $row[$pk] : $row['id'];
        $rows[] = $row;
    }
}

$rows = $modx->migx->checkRenderOptions($rows);
