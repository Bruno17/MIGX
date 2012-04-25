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

$joinalias = isset($config['join_alias']) ? $config['join_alias'] : '';


if ($modx->lexicon) {
    $modx->lexicon->load($packageName . ':default');
}

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

switch ($scriptProperties['task']) {
    case 'activate':
        if ($joinobject = $modx->getObject($joinclass, array('resource_id' => $scriptProperties['resource_id'], $localkey => $scriptProperties['object_id']))) {
            $joinobject->set('active', '1');
        } else {
            $joinobject = $modx->newObject($joinclass);
            $joinobject->set('active', '1');
            $joinobject->set('resource_id', $scriptProperties['resource_id']);
            $joinobject->set($localkey, $scriptProperties['object_id']);
        }
        $joinobject->save();
        break;
    case 'deactivate':
        if ($joinobject = $modx->getObject($joinclass, array('resource_id' => $scriptProperties['resource_id'], $localkey => $scriptProperties['object_id']))) {
            $joinobject->set('active', '0');
            $joinobject->save();
        }    
        break;
    default:
        break;
}

//clear cache
$paths = array(
    'config.cache.php',
    'sitePublishing.idx.php',
    'registry/mgr/workspace/',
    'lexicon/',
    );
$contexts = $modx->getCollection('modContext');
foreach ($contexts as $context) {
    $paths[] = $context->get('key') . '/';
}

$options = array(
    'publishing' => 1,
    'extensions' => array(
        '.cache.php',
        '.msg.php',
        '.tpl.php'),
    );
if ($modx->getOption('cache_db')) $options['objects'] = '*';
$results = $modx->cacheManager->clearCache($paths, $options);

return $modx->error->success();

?>