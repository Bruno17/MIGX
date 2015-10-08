<?php

//if (!$modx->hasPermission('quip.comment_approve')) return $modx->error->failure($modx->lexicon('access_denied'));
$config = $modx->migx->customconfigs;
$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);
    $packageName = isset($packageNames[0]) ? $packageNames[0] : '';    

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
    if ($this->modx->lexicon) {
        $this->modx->lexicon->load($packageName . ':default');
    }    
}else{
    $xpdo = &$modx;    
}
$classname = $config['classname'];

if (empty($scriptProperties['objects'])) {
    return $modx->error->failure($modx->lexicon('quip.comment_err_ns'));
}

$objectIds = explode(',',$scriptProperties['objects']);

foreach ($objectIds as $id) {
    $object = $xpdo->getObject($classname,$id);
    if ($object == null) continue;
switch ($scriptProperties['task']) {
	case 'publish':
        $object->set('published','1');
        $object->set('publishedon',strftime('%Y-%m-%d %H:%M:%S'));
        $object->set('publishedby',$modx->user->get('id'));  
	    break;
	case 'delete':
        $object->set('deleted','1');
        $object->set('deletedon',strftime('%Y-%m-%d %H:%M:%S'));
        $object->set('deletedby',$modx->user->get('id'));  
	    break;				
	case 'unpublish':
        $object->set('unpublishedon', strftime('%Y-%m-%d %H:%M:%S'));
        $object->set('published', '0');
		$object->set('unpublishedby',$modx->user->get('id'));//feld fehlt noch  
	    break;		
    default:
	break;
	}

    if ($object->save() === false) {
        return $modx->error->failure($modx->lexicon('quip.comment_err_save'));
    }
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
