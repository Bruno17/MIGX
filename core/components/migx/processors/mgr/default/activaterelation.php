<?php

if (empty($scriptProperties['object_id'])) {

    return $modx->error->failure($modx->lexicon('quip.thread_err_ns'));

}

$config = $modx->migx->customconfigs;
$modx->setOption(xPDO::OPT_AUTO_CREATE_TABLES, $config['auto_create_tables']);

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
}else{
    $xpdo = &$modx;    
}

$classname = $config['classname'];

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';


if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

if (!empty($joinalias)) {
    if ($fkMeta = $xpdo->getFKDefinition($classname, $joinalias)) {
        $joinclass = $fkMeta['class'];
        $joinvalues = array();
    } else {
        $joinalias = '';
    }
    if ($joinFkMeta = $xpdo->getFKDefinition($joinclass, 'Resource')) {
        $localkey = $joinFkMeta['local'];
    }
}

switch ($scriptProperties['task']) {
    case 'activate':
        if ($joinobject = $xpdo->getObject($joinclass, array('resource_id' => $scriptProperties['resource_id'], $localkey => $scriptProperties['object_id']))) {
            $joinobject->set('active', '1');
        } else {
            $joinobject = $xpdo->newObject($joinclass);
            $joinobject->set('active', '1');
            $joinobject->set('resource_id', $scriptProperties['resource_id']);
            $joinobject->set($localkey, $scriptProperties['object_id']);
        }
        $joinobject->save();
        break;
    case 'deactivate':
        if ($joinobject = $xpdo->getObject($joinclass, array('resource_id' => $scriptProperties['resource_id'], $localkey => $scriptProperties['object_id']))) {
            $joinobject->set('active', '0');
            $joinobject->save();
        }    
        break;
    default:
        break;
}

//clear cache for all contexts
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

return $modx->error->success();

?>