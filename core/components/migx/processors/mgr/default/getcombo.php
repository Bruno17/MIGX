<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;
$searchname = $modx->getOption('searchname', $scriptProperties, '');
$gridfilters = $modx->getOption('gridfilters', $config, '');
$filterconfig = $modx->getOption($searchname, $gridfilters, '');
$where = $modx->getOption('combowhere', $filterconfig, '');

$textfield = $modx->getOption('combotextfield', $filterconfig, '');
$idfield = $modx->getOption('comboidfield', $filterconfig, '');
$idfield = empty($idfield) ? $textfield : $idfield;

$packageName = $modx->getOption('packageName', $config, '');
$packageName = isset($filterconfig['combopackagename']) && !empty($filterconfig['combopackagename']) ? $filterconfig['combopackagename'] : $packageName;

$prefix = null;

if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}
if (isset($filterconfig['combo_use_custom_prefix']) && !empty($filterconfig['combo_use_custom_prefix'])) {
    $prefix = isset($filterconfig['comboprefix']) ? $filterconfig['comboprefix'] : '';
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
$classname = $modx->getOption('classname',$config,'');
$comboclassname = $modx->getOption('comboclassname',$filterconfig,'');
$joins = isset($config['joins']) && !empty($config['joins']) ? $modx->fromJson($config['joins']) : false;
$joinalias = '';

//if specific classname was set, use specific classname and joins
if (!empty($comboclassname)){
    $classname = $comboclassname;
    $joins = isset($filterconfig['combojoins']) && !empty($filterconfig['combojoins']) ? $modx->fromJson($filterconfig['combojoins']) : false;    

}else{
    $joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';    
}

if (!empty($joinalias)) {
    if ($fkMeta = $xpdo->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
    } else {
        $joinalias = '';
    }
}

/* setup default properties */
$isLimit = !empty($scriptProperties['limit']);
$isCombo = !empty($scriptProperties['combo']);
$start = $modx->getOption('start', $scriptProperties, 0);
$limit = $modx->getOption('limit', $scriptProperties, 20);
$mode = $modx->getOption('searchname', $scriptProperties, 'year');
$context = $modx->getOption('context', $scriptProperties, 'alle');

$c = $xpdo->newQuery($classname);
//$count = $modx->getCount($classname,$c);

$execute = true;

switch ($mode) {
    case 'year':
        $sort = $modx->getOption('sort', $scriptProperties, 'YEAR(`' . $classname . '`.`' . $textfield . '`)');
        $dir = $modx->getOption('dir', $scriptProperties, 'DESC');
        $c->select('id,YEAR(' . $textfield . ') as combo_id , YEAR(' . $textfield . ') as combo_name');
        break;
    case 'month':
        if ($scriptProperties['year'] == 'all') {
            $rows = array();
            $execute = false;
        } else {
            $sort = $modx->getOption('sort', $scriptProperties, 'MONTH(`' . $classname . '`.`' . $textfield . '`)');
            $dir = $modx->getOption('dir', $scriptProperties, 'ASC');
            $c->select('id, MONTH(' . $textfield . ') as combo_id, MONTH(' . $textfield . ') as combo_name,YEAR(`' . $classname . '`.`' . $textfield . '`) as year');
            //$c->where("YEAR(" . $modx->escape($classname) . '.' . $modx->escape('createdon') . ") = " .$scriptProperties['year'], xPDOQuery::SQL_AND);
        }

        break;
    default:
        $sort = $modx->getOption('sort', $scriptProperties, $textfield);
        $dir = $modx->getOption('dir', $scriptProperties, 'ASC');
        if (!empty($joinalias)) {
            $c->leftjoin($joinclass, $joinalias);
            //$c->select($modx->getSelectColumns($joinclass, $joinalias, $joinalias . '_'));
        }
        $c->select($classname . '.id, ' . $idfield . ' as combo_id, ' . $textfield . ' as combo_name');
        break;
}

if ($joins) {
    $modx->migx->prepareJoins($classname, $joins, $c);
}

if ($execute) {

    if (!empty($where)) {

        $chunk = $modx->newObject('modChunk');
        $chunk->setCacheable(false);
        $chunk->setContent($where);
        $fwhere = $chunk->process($scriptProperties);
        $fwhere = strpos($fwhere, '{') === 0 ? $modx->fromJson($fwhere) : $fwhere;
        
        $c->where($fwhere);
    }

    $c->groupby('combo_name');
    $c->sortby($sort, $dir);
    $stmt = $c->prepare();
    //echo $c->toSql();
    $stmt->execute();
    $rows = $stmt->fetchAll();
}

$count = count($rows);

$emtpytext = $modx->getOption('emptytext', $filterconfig, '');
$emtpytext = empty($emtpytext) ? 'all' : $emtpytext;

$rows = array_merge(array(array('combo_id' => 'all', 'combo_name' => $emtpytext)), $rows);
//$c->prepare(); echo $c->toSql();
/*
$collection = $modx->getCollection($classname, $c);
$rows=array();
foreach ($collection as $row){
$rows[]=$row->toArray();
}
$count=count($rows);
*/
return $this->outputArray($rows, $count);
