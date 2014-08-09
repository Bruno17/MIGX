<?php
$category = $modx->getOption('category', $scriptProperties, '');
$format = $modx->getOption('format', $scriptProperties, 'json');

$classname = 'modChunk';
$rows = array();
$output = '';

$c = $modx->newQuery($classname);
$c->select($modx->getSelectColumns($classname, $classname, '', array('id', 'name')));
$c->sortby('name');

if (!empty($category)) {
    $c->where(array('category' => $category));
}
//$c->prepare();echo $c->toSql();
if ($collection = $modx->getCollection($classname, $c)) {
    $i = 0;

    switch ($format) {
        case 'json':
            foreach ($collection as $object) {
                $row['MIGX_id'] = (string )$i;
                $row['name'] = $object->get('name');
                $row['selected'] = '0';
                $rows[] = $row;
                $i++;
            }
            $output = $modx->toJson($rows);
            break;
        
        case 'optionlist':
            foreach ($collection as $object) {
                $rows[] = $object->get('name');
                $i++;
            }
            $output = implode('||',$rows);      
        break;
            
    }


}

return $output;