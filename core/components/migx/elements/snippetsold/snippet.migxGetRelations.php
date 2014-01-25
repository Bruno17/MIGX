<?php

$id = $modx->getOption('id', $scriptProperties, $modx->resource->get('id'));
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, '');
$element = $modx->getOption('element', $scriptProperties, 'getResources');
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, ',');
$sourceWhere = $modx->getOption('sourceWhere', $scriptProperties, '');
$ignoreRelationIfEmpty = $modx->getOption('ignoreRelationIfEmpty', $scriptProperties, false);
$inheritFromParents = $modx->getOption('inheritFromParents', $scriptProperties, false);
$parentIDs = $inheritFromParents ? array_merge(array($id), $modx->getParentIds($id)) : array($id);

$packageName = 'resourcerelations';

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = 'rrResourceRelation';
$output = '';

foreach ($parentIDs as $id) {
    if (!empty($id)) {
        $output = '';
                
        $c = $modx->newQuery($classname, array('target_id' => $id, 'published' => '1'));
        $c->select($modx->getSelectColumns($classname, $classname));

        if (!empty($sourceWhere)) {
            $sourceWhere_ar = $modx->fromJson($sourceWhere);
            if (is_array($sourceWhere_ar)) {
                $where = array();
                foreach ($sourceWhere_ar as $key => $value) {
                    $where['Source.' . $key] = $value;
                }
                $joinclass = 'modResource';
                $joinalias = 'Source';
                $selectfields = 'id';
                $selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
                $c->leftjoin($joinclass, $joinalias);
                $c->select($modx->getSelectColumns($joinclass, $joinalias, $joinalias . '_', $selectfields));
                $c->where($where);
            }
        }

        //$c->prepare(); echo $c->toSql();
        if ($c->prepare() && $c->stmt->execute()) {
            $collection = $c->stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        foreach ($collection as $row) {
            $ids[] = $row['source_id'];
        }
        $output = implode($outputSeparator, $ids);
    }
    if (!empty($output)){
        break;
    }
}


if (!empty($element)) {
    if (empty($output) && $ignoreRelationIfEmpty) {
        return $modx->runSnippet($element, $scriptProperties);
    } else {
        $scriptProperties['resources'] = $output;
        $scriptProperties['parents'] = '9999999';
        return $modx->runSnippet($element, $scriptProperties);
    }


}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}

return $output;