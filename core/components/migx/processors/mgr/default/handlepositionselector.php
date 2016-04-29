<?php

$config = $modx->migx->customconfigs;

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);

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
} else {
    $xpdo = &$modx;
}

$classname = $config['classname'];
$checkdeleted = isset($config['gridactionbuttons']['toggletrash']['active']) && !empty($config['gridactionbuttons']['toggletrash']['active']) ? true : false;
$newpos_id = $modx->getOption('new_pos_id', $scriptProperties, 0);
$col = $modx->getOption('col', $scriptProperties, '');
$object_id = $modx->getOption('object_id', $scriptProperties, 0);
$showtrash = $modx->getOption('showtrash', $scriptProperties, '');

$resource_id = $modx->getOption('co_id', $scriptProperties, is_object($modx->resource) ? $modx->resource->get('id') : false);

$col = explode(':', $col);
if (!empty($newpos_id) && !empty($object_id) && count($col) > 1) {
    $workingobject = $xpdo->getObject($classname, $object_id);
    $posfield = $col[0];
    $position = $col[1];

    $joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

    if (!empty($joinalias)) {
        if ($fkMeta = $xpdo->getFKDefinition($classname, $joinalias)) {
            $joinclass = $fkMeta['class'];
            $joinfield = $fkMeta[$fkMeta['owner']];
        } else {
            $joinalias = '';
        }
    }

    //$parent = $workingobject->get('parent');
    $c = $xpdo->newQuery($classname);
    //$c->where(array('deleted'=>0 , 'parent'=>$parent));
    $c->select($xpdo->getSelectColumns($classname, $classname));

    if (!empty($joinalias)) {
        /*
        if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
        $localkey = $joinFkMeta['local'];
        }    
        */
        $c->leftjoin($joinclass, $joinalias);
        $c->select($xpdo->getSelectColumns($joinclass, $joinalias, 'Joined_'));
    }

    if ($modx->migx->checkForConnectedResource($resource_id, $config)) {
        if (!empty($joinalias)) {
            $c->where(array($joinalias . '.' . $joinfield => $resource_id));
        } else {
            $c->where(array($classname . '.resource_id' => $resource_id));
        }
    }

    if ($checkdeleted) {
        if (!empty($showtrash)) {
            $c->where(array($classname . '.deleted' => '1'));
        } else {
            $c->where(array($classname . '.deleted' => '0'));
        }
    }


    $c->sortby($posfield);
    //$c->sortby('name');

    if ($collection = $xpdo->getCollection($classname, $c)) {
        $curpos = 1;
        foreach ($collection as $object) {
            $id = $object->get('id');
            if ($id == $newpos_id && $position == 'before') {
                $workingobject->set($posfield, $curpos);
                $workingobject->save();
                $curpos++;
            }
            if ($id != $object_id) {
                $object->set($posfield, $curpos);
                $object->save();
                $curpos++;
            }
            if ($id == $newpos_id && $position == 'after') {
                $workingobject->set($posfield, $curpos);
                $workingobject->save();
                $curpos++;
            }
        }
    }
}


$modx->cacheManager->refresh(array(
    'db' => array(),
    'auto_publish' => array('contexts' => $contexts),
    'context_settings' => array('contexts' => $contexts),
    'resource' => array('contexts' => $contexts),
    ));
return $modx->error->success();
