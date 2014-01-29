<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

$parents = $config['parents'];
$parents = explode (',',$parents);

$textfield = $config['gridfilters'][$scriptProperties['searchname']]['combotextfield'];
$idfield = $config['gridfilters'][$scriptProperties['searchname']]['comboidfield'];
$idfield = empty($idfield) ? $textfield : $idfield;

$prefix = !empty($config['prefix']) ? $config['prefix'] : null;
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

if ($this->modx->lexicon) {
    $this->modx->lexicon->load($packageName . ':default');
}

//$joinalias = 'Parent';

if (!empty($joinalias)) {
    if ($fkMeta = $modx->getFKDefinition($classname, $joinalias)) {
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

$c = $modx->newQuery($classname);
//$count = $modx->getCount($classname,$c);

$c->where(array('id:IN'=>$parents));

$execute = true;

switch ($mode) {
    case 'year':
        $sort = $modx->getOption('sort', $scriptProperties, 'YEAR(`' . $classname . '`.`' . $textfield . '`)');
        $dir = $modx->getOption('dir', $scriptProperties, 'DESC');
        $c->select('id,YEAR(' . $idfield . ') as combo_id , YEAR(' . $textfield . ') as combo_name');
        break;
    case 'month':
        if ($scriptProperties['year'] == 'all') {
            $rows = array();
            $execute = false;
        } else {
            $sort = $modx->getOption('sort', $scriptProperties, 'MONTH(`' . $classname . '`.`' . $textfield . '`)');
            $dir = $modx->getOption('dir', $scriptProperties, 'ASC');
            $c->select('id, MONTH(' . $idfield . ') as combo_id, MONTH(' . $textfield . ') as combo_name,YEAR(`' . $classname . '`.`' . $textfield . '`) as year');
            //$c->where("YEAR(" . $modx->escape($classname) . '.' . $modx->escape('createdon') . ") = " .$scriptProperties['year'], xPDOQuery::SQL_AND);
        }

        break;
    default:
        $sort = $modx->getOption('sort', $scriptProperties,  $textfield );
        $dir = $modx->getOption('dir', $scriptProperties, 'ASC');
        if (!empty($joinalias)) {
            $c->leftjoin($joinclass, $joinalias);
            //$c->select($modx->getSelectColumns($joinclass, $joinalias, $joinalias . '_'));
        }
        $c->select($classname.'.id, ' . $idfield . ' as combo_id, ' . $textfield . ' as combo_name');
        break;
}

if ($execute) {

    $c->groupby('combo_name');
    $c->sortby($sort, $dir);
    $stmt = $c->prepare();
    //echo $c->toSql();
    $stmt->execute();
    $rows = $stmt->fetchAll();
}

$count = count($rows);

$emtpytext = $config['gridfilters'][$scriptProperties['searchname']]['emptytext'];
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
