<?php

$category = $modx->getOption('category',$scriptProperties,'');

$classname = 'modChunk';
$rows = array();

$c = $modx->newQuery($classname);
$c->select($modx->getSelectColumns($classname,$classname,'',array('id','name')));
$c->sortby('name');

if (!empty($category)){
    $c->where(array('category' => $category));
}
//$c->prepare();echo $c->toSql();
if ($collection = $modx->getCollection($classname,$c)){
    $i = 0;
    foreach ($collection as $object){
        $row['MIGX_id'] = (string) $i;
        $row['name'] = $object->get('name');
        $row['selected'] = '0';
        $rows[] = $row;
        $i++;
    }
}

return $modx->toJson($rows);