<?php

/**
 * Loads the TV panel for the resource page.
 *
 * Note: This page is not to be accessed directly.
 *
 * @package modx
 * @subpackage manager
 */

//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));
/*
$resourceClass= isset ($_REQUEST['class_key']) ? $_REQUEST['class_key'] : 'modDocument';
$resourceClass = $modx->sanitizeString($resourceClass);
$resourceClass = str_replace(array('../','..','/','\\'),'',$resourceClass);
$resourceDir= strtolower(substr($resourceClass, 3));

$resourceId = isset($_REQUEST['resource']) ? intval($_REQUEST['resource']) : 0;

$onResourceTVFormPrerender = $modx->invokeEvent('OnResourceTVFormPrerender',array(
'resource' => $resourceId,
));
if (is_array($onResourceTVFormPrerender)) {
$onResourceTVFormPrerender = implode('',$onResourceTVFormPrerender);
}
*/

$modx->getService('smarty', 'smarty.modSmarty');

if (!isset($modx->smarty)) {
    $modx->getService('smarty', 'smarty.modSmarty', '', array('template_dir' => $modx->getOption('manager_path') . 'templates/' . $modx->getOption('manager_theme', null, 'default') . '/', ));
}
$modx->smarty->template_dir = $modx->getOption('manager_path') . 'templates/' . $modx->getOption('manager_theme', null, 'default') . '/';

$modx->smarty->assign('OnResourceTVFormPrerender', $onResourceTVFormPrerender);
$modx->smarty->assign('_config', $modx->config);

if (file_exists(MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php')) {
    require_once MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php';
    require_once MODX_CORE_PATH . 'model/modx/modmanagercontrollerdeprecated.class.php';
    $c = new modManagerControllerDeprecated($this->modx, array());
    $modx->controller = call_user_func_array(array($c, 'getInstance'), array($this->modx, 'modManagerControllerDeprecated', array()));
}


/*
$delegateView= dirname(__FILE__) . '/' . $resourceDir . '/' . basename(__FILE__);
if (file_exists($delegateView)) {
$overridden= include_once ($delegateView);
if ($overridden !== false) {
return;
}
}
*/
//get dataobject:
//if (empty($scriptProperties['object_id'])) return $modx->error->failure('oehh..');

$task = $modx->migx->getTask();
$getObject = dirname(dirname(__file__)) . '/' . $task . '/' . basename(__file__);
if (file_exists($getObject)) {
    $overridden = include_once ($getObject);
    if ($overridden !== false) {
        // return;
    }
}


//$object = $modx->getObject('Angebote',$scriptProperties['angebot']);
if (empty($object)) return $modx->error->failure($modx->lexicon('quip.thread_err_nf'));
//if (!$thread->checkPolicy('view')) return $modx->error->failure($modx->lexicon('access_denied'));

//return $modx->error->success('',$angebot);

//echo '<pre>'.print_r($angebot->toArray(),1).'</pre>';

$modx->migx->loadConfigs();
$tabs = $modx->migx->getTabs();
$fieldid = 0;
$allfields[] = array();
$categories = array();
$modx->migx->createForm($tabs, $record, $allfields, $categories, $scriptProperties);

$modx->smarty->assign('fields', $modx->toJSON($allfields));
$modx->smarty->assign('customconfigs', $modx->migx->customconfigs);
$modx->smarty->assign('object', $object);
$modx->smarty->assign('categories', $categories);
$modx->smarty->assign('win_id', $scriptProperties['tv_id']); 
$modx->smarty->assign('win_id', isset ($modx->migx->customconfigs['win_id']) ? $modx->migx->customconfigs['win_id'] :  $scriptProperties['tv_id']);  
//$modx->smarty->assign('id_update_window', 'modx-window-midb-grid-update');

if (!empty($_REQUEST['showCheckbox'])) {
    $modx->smarty->assign('showCheckbox', 1);
}

$modx->smarty->template_dir = $modx->migx->config['corePath'] . 'templates/';
return $modx->smarty->fetch('mgr/fields.tpl');
