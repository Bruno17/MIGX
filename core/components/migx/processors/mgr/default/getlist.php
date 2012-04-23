<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

$prefix = !empty($config['prefix']) ?  $config['prefix'] : null;

$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName .
    '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];


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

//print_r($config['gridfilters']);

foreach ($config['gridfilters'] as $filter) {

    if (!empty($filter['getlistwhere'])) {

        $requestvalue = $modx->getOption($filter['name'], $scriptProperties, 'all');

        if (isset($scriptProperties[$filter['name']]) && $requestvalue != 'all') {
            
            $chunk = $modx->newObject('modChunk');
            $chunk->setCacheable(false);
            $chunk->setContent($filter['getlistwhere']);
            $where = $chunk->process($scriptProperties);
            $where = strpos($where, '{') === 0 ? $modx->fromJson($where) : $where ;
       
            $c->where($where);
        }
    }
}


if ($modx->migx->checkForConnectedResource($resource_id, $config)) {
    $c->where(array($classname . '.resource_id' => $resource_id));
}


if (!empty($showtrash)) {
    $c->where(array($classname . '.deleted' => '1'));
} else {
    $c->where(array($classname . '.deleted' => '0'));
}
$count = $modx->getCount($classname, $c);

$c->select('
    `' . $classname . '`.*
');
$c->sortby($sort, $dir);
if ($isCombo || $isLimit) {
    $c->limit($limit, $start);
}
//$c->sortby($sort,$dir);
//$c->prepare(); echo $c->toSql();
$collection = $modx->getCollection($classname, $c);

$rows = array();
foreach ($collection as $row) {
    $rows[] = $row->toArray();
}
