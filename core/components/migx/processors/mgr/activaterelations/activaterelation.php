<?php

if (empty($scriptProperties['object_id'])) {

    return $modx->error->failure($modx->lexicon('error'));

}

$config = $modx->migx->customconfigs;

$prefix = $modx->getOption('prefix', $config, null);
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

//$joinalias = '{"classname":"ProductApplication","local":"product","foreign":"application"}';
$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';

$joinconfig = $modx->fromJson($joinalias);

$joinclass = $modx->getOption('classname',$joinconfig,'');
$local = $modx->getOption('local',$joinconfig,'');
$foreign = $modx->getOption('foreign',$joinconfig,'');

if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

$product_id = $modx->getOption('co_id',$scriptProperties,0);
$application_id = $modx->getOption('object_id',$scriptProperties,0);

switch ($scriptProperties['task']) {
    case 'activate':
        if ($joinobject = $modx->getObject($joinclass, array($local => $product_id, $foreign => $application_id))) {

        } else {
            $joinobject = $modx->newObject($joinclass);
            $joinobject->set($local, $product_id);
            $joinobject->set($foreign, $application_id);
        }
        $joinobject->save();
        break;
    case 'deactivate':
        $c = $modx->newQuery($joinclass);
        $c->where(array($local => $product_id, $foreign => $application_id));
    
        if ($collection = $modx->getCollection($joinclass, $c)) {
            foreach ($collection as $joinobject){
                $joinobject->remove();
            }
        }
        break;
    default:
        break;
}


//clear cache for all contexts
/*
$collection = $modx->getCollection('modContext');
foreach ($collection as $context) {
    $contexts[] = $context->get('key');
}
$modx->cacheManager->refresh(array(
    'db' => array(),
    'auto_publish' => array('contexts' => $contexts),
    'context_settings' => array('contexts' => $contexts),
    'resource' => array('contexts' => $contexts),
    ));
*/
return $modx->error->success();
