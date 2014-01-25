<?php

if (empty($scriptProperties['object_id'])) {

    return $modx->error->failure($modx->lexicon('quip.thread_err_ns'));

}

$config = $modx->migx->customconfigs;
$modx->setOption(xPDO::OPT_AUTO_CREATE_TABLES, $config['auto_create_tables']);

$prefix = $config['prefix'];
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

//$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';
$joinclass = 'rrResourceRelation';

if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}
/*
if (!empty($joinalias)) {
if ($fkMeta = $modx->getFKDefinition($classname, $joinalias)) {
$joinclass = $fkMeta['class'];
$joinvalues = array();
} else {
$joinalias = '';
}
if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')) {
$localkey = $joinFkMeta['local'];
}
}
*/

switch ($config['sourceortarget']) {
    case 'target':    
        $source_id = $scriptProperties['object_id'];
        $target_id = $scriptProperties['resource_id'];
    break;
    
    case 'source':
    default:
        $source_id = $scriptProperties['resource_id'];
        $target_id = $scriptProperties['object_id'];
    break;    
}


switch ($scriptProperties['task']) {
    case 'publish':
        if ($joinobject = $modx->getObject($joinclass, array('source_id' => $source_id , 'target_id' => $target_id))) {
            $joinobject->set('active', '1');
            $joinobject->set('published', '1');
        } else {
            $joinobject = $modx->newObject($joinclass);
            $joinobject->set('active', '1');
            $joinobject->set('published', '1');
            $joinobject->set('source_id', $source_id);
            $joinobject->set('target_id', $target_id);
        }
        $joinobject->save();
        break;
    case 'unpublish':
        if ($joinobject = $modx->getObject($joinclass, array('source_id' => $source_id , 'target_id' => $target_id))) {
            $joinobject->set('published', '0');
            $joinobject->save();
        }
        break;
    default:
        break;
}

//clear cache for all contexts
$collection = $modx->getCollection('modContext');
foreach ($collection as $context) {
    $contexts = $context->get('key');
}
$modx->cacheManager->refresh(array(
    'db' => array(),
    'auto_publish' => array('contexts' => $contexts),
    'context_settings' => array('contexts' => $contexts),
    'resource' => array('contexts' => $contexts),
    ));


return $modx->error->success();

?>