<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

$includeTVList = $modx->getOption('includeTVList', $config, '');
$includeTVList = !empty($includeTVList) ? explode(',', $includeTVList) : array();
$includeTVs = $modx->getOption('includeTVs', $config, false);
$processTVList = $modx->getOption('processTVList', $config, '');
$processTVList = !empty($processTVList) ? explode(',', $processTVList) : array();
$processTVs = $modx->getOption('processTVs', $config, false);
$commentsEnabled = $modx->getOption('commentsEnabled', $config, false);

$classname = 'modResource';

$joins = isset($config['joins']) && !empty($config['joins']) ? $modx->fromJson($config['joins']) : false;

/*
if ($this->modx->lexicon) {
    $this->modx->lexicon->load($packageName . ':default');
}
*/

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start', $scriptProperties, 0);
$limit = $modx->getOption('limit', $scriptProperties, 20);
$sort = !empty($config['getlistsort']) ? $config['getlistsort'] : 'modResource.id';
$dir = !empty($config['getlistsortdir']) ? $config['getlistsortdir'] : 'ASC';
$dir = $modx->getOption('dir', $scriptProperties, $dir);
$year = $modx->getOption('year', $scriptProperties, 'all');
$month = $modx->getOption('month', $scriptProperties, 'all');
$showtrash = $modx->getOption('showtrash', $scriptProperties, '');
$resource_id = $modx->getOption('resource_id', $scriptProperties, false);
$where = !empty($config['getlistwhere']) ? $config['getlistwhere'] : '';

$c = $modx->newQuery($classname);

$c->select($modx->getSelectColumns($classname, $classname));

//example for tvFilters
$status = $modx->getOption('status', $scriptProperties, 'all');
if ($status != 'all') {
    $filter = 'auftrag_status==' . $status;
    $modx->migx->tvFilters($filter, $c);
}
//example for filtering by year/month in createdon
if ($year != 'all') {
    $c->where("YEAR(" . $modx->escape($classname) . '.' . $modx->escape('createdon') . ") = " . $year, xPDOQuery::SQL_AND);
}
if ($month != 'all') {
    $c->where("MONTH(" . $modx->escape($classname) . '.' . $modx->escape('createdon') . ") = " . $month, xPDOQuery::SQL_AND);
}

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
                $filter = explode('::', $fwhere);
                if ($filter[0] == 'tvFilter') {
                    //tvFilter::categories=inArray=[[+category]]
                    $modx->migx->tvFilters($filter[1], $c);
                } else {
                    $fwhere = strpos($fwhere, '{') === 0 ? $modx->fromJson($fwhere) : $fwhere;
                    $c->where($fwhere);
                }


            }
        }
    }
}


if (!empty($showtrash)) {
    $c->where(array($classname . '.deleted' => '1'));
} else {
    $c->where(array($classname . '.deleted' => '0'));
}

if ($resource_id) {
    $c->where(array($classname . '.parent' => $resource_id));
}

if (!empty($where)) {
    $c->where($modx->fromJson($where));
}

$count = $modx->getCount($classname, $c);

if ($commentsEnabled) {
    $commentsQuery = $modx->newQuery('quipComment');
    $commentsQuery->innerJoin('quipThread', 'Thread');
    $commentsQuery->where(array('Thread.resource = modResource.id', ));
    $commentsQuery->select(array('COUNT(' . $modx->getSelectColumns('quipComment', 'quipComment', '', array('id')) . ')', ));
    $commentsQuery->construct();
    $c->select(array('(' . $commentsQuery->toSQL() . ') AS ' . $modx->escape('comments'), ));
}

//sortbyTV, if sortfield is given in includeTVs TV-List
if (in_array($sort, $includeTVList)) {
    $modx->migx->sortTV($sort, $c, $dir);
} else {
    $c->sortby($sort, $dir);
}


if ($isCombo || $isLimit) {
    $c->limit($limit, $start);
}

//$c->prepare(); echo $c->toSql();
$collection = $modx->getCollection($classname, $c);
$tvPrefix = '';

$rows = array();
foreach ($collection as $resourceId => $resource) {
    $tvs = $resource->ToArray();
    if (!empty($includeTVs)) {
        if (empty($includeTVList)) {
            $templateVars = $resource->getMany('TemplateVars');
        } else {
            $c = $modx->newQuery('modTemplateVar');
            $c->where(array('name:IN' => $includeTVList));
            $templateVars = $modx->getCollection('modTemplateVar', $c);
        }
        if ($templateVars) {
            foreach ($templateVars as $tvId => $templateVar) {
                //if (!empty($includeTVList) && !in_array($templateVar->get('name'), $includeTVList)) continue;
                if ($processTVs && (empty($processTVList) || in_array($templateVar->get('name'), $processTVList))) {
                    $tvs[$tvPrefix . $templateVar->get('name')] = $templateVar->renderOutput($resource->get('id'));
                } else {
                    $tvs[$tvPrefix . $templateVar->get('name')] = $templateVar->getValue($resource->get('id'));
                }
            }
        }

    }

    $rows[] = $tvs;
}
