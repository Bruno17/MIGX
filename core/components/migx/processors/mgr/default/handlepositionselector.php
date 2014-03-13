<?php

$config = $modx->migx->customconfigs;

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

$checkdeleted = isset($config['gridactionbuttons']['toggletrash']['active']) &&
    !empty($config['gridactionbuttons']['toggletrash']['active']) ? true : false;

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);
    //all packages must have the same prefix for now!
    foreach ($packageNames as $packageName) {
        $packagepath = $modx->getOption('core_path') . 'components/' . $packageName .
            '/';
        $modelpath = $packagepath . 'model/';
        if (is_dir($modelpath)) {
            $modx->addPackage($packageName, $modelpath, $prefix);
        }

    }
}

$classname = $config['classname'];

$newpos_id = $modx->getOption('new_pos_id', $scriptProperties, 0);
$col = $modx->getOption('col', $scriptProperties, '');
$object_id = $modx->getOption('object_id', $scriptProperties, 0);
$showtrash = $modx->getOption('showtrash', $scriptProperties, '');


$col = explode(':', $col);
if (!empty($newpos_id) && !empty($object_id) && count($col) > 1) {
    $workingobject = $modx->getObject($classname, $object_id);
    $posfield = $col[0];
    $position = $col[1];

    //$parent = $workingobject->get('parent');
    $c = $modx->newQuery($classname);
    //$c->where(array('deleted'=>0 , 'parent'=>$parent));
    if ($checkdeleted) {
        if (!empty($showtrash)) {
            $c->where(array($classname . '.deleted' => '1'));
        } else {
            $c->where(array($classname . '.deleted' => '0'));
        }
    }
    
    $c->sortby($posfield);
    //$c->sortby('name');
    
    if ($collection = $modx->getCollection($classname, $c)) {
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
