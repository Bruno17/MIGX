<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config=$modx->migx->customconfigs;

$includeTVs = isset($config['includeTVs']) ? explode(',', $config['includeTVs']) : array();

$classname = $config['classname'];
//$classname = 'modResource';

if ($this->modx->lexicon)
{
    $this->modx->lexicon->load($packageName.':default');
}

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start',$scriptProperties,0);
$limit = $modx->getOption('limit',$scriptProperties,20);
$sort = $modx->getOption('sort',$scriptProperties,'id');
$dir = $modx->getOption('dir',$scriptProperties,'ASC');
$year = $modx->getOption('year',$scriptProperties,'all');
$month = $modx->getOption('month',$scriptProperties,'all');
$showtrash = $modx->getOption('showtrash',$scriptProperties,'');
$resource_id = $modx->getOption('resource_id',$scriptProperties,false);


$c = $modx->newQuery($classname);

//example for tvFilters
$status = $modx->getOption('status',$scriptProperties,'all');
if ($status != 'all'){
    $filter = 'auftrag_status=='.$status;
    $modx->migx->tvFilters($filter,$c);
}
//example for filtering by year/month in createdon	
if ($year != 'all'){
    $c->where("YEAR(" . $modx->escape($classname) . '.' . $modx->escape('createdon') . ") = " .$year, xPDOQuery::SQL_AND);		
}
if ($month != 'all'){
    $c->where("MONTH(" . $modx->escape($classname) . '.' . $modx->escape('createdon') . ") = " .$month, xPDOQuery::SQL_AND);		
}
/*
if (!empty($showtrash)){
    $c->where(array($classname.'.deleted' => '1'));	
}else{
	$c->where(array($classname.'.deleted' => '0'));	
}
*/
if ($resource_id){
    $c->where(array($classname.'.'.$config['idfield_local'] => $resource_id));		
}

$count = $modx->getCount($classname,$c);

$c->select('
    `'.$classname.'`.*
');

//sortbyTV, if sortfield is given in includeTVs TV-List
if (in_array($sort,$includeTVs)){
    $modx->migx->sortTV($sort,$c,$dir);
}
else{
    $c->sortby($sort,$dir);
}


if ($isCombo || $isLimit) {
    $c->limit($limit,$start);
}

//$c->prepare(); echo $c->toSql();
$collection = $modx->getCollection($classname, $c);
$tvPrefix = '';

$rows=array();
foreach ($collection as $resourceId => $resource) {
    $tvs = $resource->ToArray();
    if (!empty($includeTVs)) {
        if (empty($includeTVList)) {
            $templateVars = $resource->getMany('TemplateVars');
        }
        foreach ($templateVars as $tvId => $templateVar) {
            if (!empty($includeTVList) && !in_array($templateVar->get('name'), $includeTVList)) continue;
            if ($processTVs && (empty($processTVList) || in_array($templateVar->get('name'), $processTVList))) {
                $tvs[$tvPrefix . $templateVar->get('name')] = $templateVar->renderOutput($resource->get('id'));
            } else {
                $tvs[$tvPrefix . $templateVar->get('name')] = $templateVar->getValue($resource->get('id'));
            }
        }
    }    
    
	$rows[]=$tvs;
}

