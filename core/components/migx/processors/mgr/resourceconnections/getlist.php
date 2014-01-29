<?php

$config = $modx->migx->customconfigs;
//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));
$resource_id = $modx->getOption('resource_id', $scriptProperties, false);

if (empty($scriptProperties['type']) || $scriptProperties['type'] == 'all') {
    $parents = $config['parents'];
}

$selectfields = 'id,pagetitle';

$joinaliases = $modx->fromJson($config['joinaliases']);

$prefix = !empty($config['prefix']) ? $config['prefix'] : null;

$packageName = $config['packageName'];
$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';
$modx->addPackage($packageName, $modelpath, $prefix);

$classname = $config['classname'];

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

if (!empty($joinalias)) {
    if ($fkMeta = $modx->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
    } else {
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
$sort = $modx->getOption('sort', $scriptProperties, '');
$dir = $modx->getOption('dir', $scriptProperties, 'ASC');
$showtrash = $modx->getOption('showtrash', $scriptProperties, '');

$c = $modx->newQuery($classname);
$selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
$c->select($modx->getSelectColumns($classname, $classname, '', $selectfields));

if (!empty($joinalias)) {
    /*
    if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
    $localkey = $joinFkMeta['local'];
    }    
    */
    $c->leftjoin($joinclass, $joinalias);
    $c->select($modx->getSelectColumns($joinclass, $joinalias, 'Joined_'));
}

foreach ($joinaliases as $join) {
    $joinalias = $join['alias'];
    if (!empty($joinalias)) {
        if (!empty($join['classname'])) {
            $joinclass = $join['classname'];
        } elseif ($fkMeta = $modx->getFKDefinition($classname, $joinalias)) {
            $joinclass = $fkMeta['class'];
        } else {
            $joinalias = '';
        }
        if (!empty($joinalias)) {
            /*
            if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
            $localkey = $joinFkMeta['local'];
            }    
            */
            $selectfields = !empty($join['selectfields']) ? explode(',', $join['selectfields']) : null;
            $on = !empty($join['on']) ? $join['on'] : null;
            $c->leftjoin($joinclass, $joinalias, $on);
            $c->select($modx->getSelectColumns($joinclass, $joinalias, $joinalias . '_', $selectfields));
        }
    }
}

/*
$c->leftjoin('poProduktFormat','ProduktFormat', 'format_id = poFormat.id AND product_id ='.$scriptProperties['object_id']);
//$c->select($classname.'.*');

$c->select('ProduktFormat.format_id,ProduktFormat.calctype,ProduktFormat.price,ProduktFormat.published AS pof_published');
*/

//print_r($config['gridfilters']);
if (!empty($parents)) {
    $c->where(array('parent:IN' => explode(',', $parents)));
}


if (!empty($config['where'])){
    $c->where($modx->fromJson($config['where']));

}

if (count($config['gridfilters']) > 0) {
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


if (!empty($showtrash)) {
    $c->where(array($classname . '.deleted' => '1'));
} else {
    $c->where(array($classname . '.deleted' => '0'));
}
$count = $modx->getCount($classname, $c);

if (empty($sort)) {
    $sorts = $modx->fromJson($custom['sort']);
    if (is_array($sorts)){
        foreach ($sorts as $sort){
            $sortby = $sort[0];
            $sortdir = isset ($sort[1]) ? $sort[1]:'ASC';
            $c->sortby($sortby, $sortdir);    
        }
    }
    

} else {
    $c->sortby($sort, $dir);
}

if ($isCombo || $isLimit) {
    $c->limit($limit, $start);
}

//$c->prepare();echo $c->toSql();
if ($c->prepare() && $c->stmt->execute()) {
    //echo $c->toSql();
    //$debug['sql'] = $c->toSql();
    $collection = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
}
foreach ($collection as $row) {
    $row['published'] = !empty($row['ResourceRelation_published']) ? 1 : 0;
    $row['Joined_active'] = !empty($row['ResourceRelation_active']) ? 1 : 0;
    $rows[] = $row;
}


//$c->sortby($sort,$dir);
//$c->prepare();echo $c->toSql();
//$collection = $modx->getCollection($classname, $c);
/*
$rows = array();
foreach ($collection as $row) {
$rows[] = $row->toArray();
}
*/
