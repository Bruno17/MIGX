<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

$prefix = isset ($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;

$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

if (!empty($joinalias)) {
    if ($fkMeta = $modx->getFKDefinition($classname, $joinalias)){
        $joinclass = $fkMeta['class'];
    }
    else{
        $joinalias = '';    
    }
}

if ($this->modx->lexicon) {
    $this->modx->lexicon->load($packageName . ':default');
}

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start', $scriptProperties, 0);
$limit = $modx->getOption('limit', $scriptProperties, 20);
$sort = $modx->getOption('sort', $scriptProperties, 'id');
$dir = $modx->getOption('dir', $scriptProperties, 'ASC');
$showtrash = $modx->getOption('showtrash', $scriptProperties, '');
$resource_id = $modx->getOption('resource_id', $scriptProperties, false);

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
                $where = $chunk->process($scriptProperties);
                $where = strpos($where, '{') === 0 ? $modx->fromJson($where) : $where;

                $c->where($where);
            }
        }
    }
}


if ($modx->migx->checkForConnectedResource($resource_id, $config)) {
    if (!empty($joinalias)) {
        $c->where(array($joinalias . '.resource_id' => $resource_id));
    } else {
        $c->where(array($classname . '.resource_id' => $resource_id));
    }
}

$count = $modx->getCount($classname, $c);

$c->sortby('source_id', $dir);
$c->sortby('target_id', $dir);
if ($isCombo || $isLimit) {
    $c->limit($limit, $start);
}
//$c->sortby($sort,$dir);
//$c->prepare();echo $c->toSql();
$collection = $modx->getCollection($classname, $c);

$rows = array();
foreach ($collection as $object) {
    $row = $object->toArray();
    $rows[] = $row;
}
