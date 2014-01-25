<?php

//if (!$modx->hasPermission('quip.comment_approve')) return $modx->error->failure($modx->lexicon('access_denied'));
$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName .
    '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

if (empty($scriptProperties['objects'])) {
    return $modx->error->failure($modx->lexicon('quip.comment_err_ns'));
}

$objectIds = explode(',',$scriptProperties['objects']);

foreach ($objectIds as $id) {
    $object = $modx->getObject($classname,$id);
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

return $modx->error->success();
