<?php

/**
 * Loads the TV panel for the resource page.
 *
 * Note: This page is not to be accessed directly.
 *
 * @package migx
 * @subpackage processors
 */

//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));

require_once dirname(dirname(dirname(__FILE__))) .'/model/migx/migx.class.php';
$migx = new Migx($modx);

$modx->getService('smarty', 'smarty.modSmarty');

if (file_exists(MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php')) {
    require_once MODX_CORE_PATH . 'model/modx/modmanagercontroller.class.php';
    require_once MODX_CORE_PATH . 'model/modx/modmanagercontrollerdeprecated.class.php';
    $c = new modManagerControllerDeprecated($modx, array());
    $modx->controller = call_user_func_array(array($c, 'getInstance'), array($modx, 'modManagerControllerDeprecated', array()));
}

$modx->resource = ($modx->getObject('modResource', $scriptProperties['resource_id']));
$migx->working_context = $modx->resource->get('context_key');
//$_REQUEST['id']=$scriptProperties['resource_id'];

if (!isset($modx->smarty)) {
    $modx->getService('smarty', 'smarty.modSmarty', '', array('template_dir' => $modx->getOption('manager_path') . 'templates/' . $modx->getOption('manager_theme', null, 'default') . '/', ));
}
$modx->smarty->template_dir = $modx->getOption('manager_path') . 'templates/' . $modx->getOption('manager_theme', null, 'default') . '/';
$modx->smarty->assign('OnResourceTVFormPrerender', $onResourceTVFormPrerender);
$modx->smarty->assign('_config', $modx->config);

//get the MIGX-TV
$tv = $modx->getObject('modTemplateVar', array('name' => $scriptProperties['tv_name']));

$migx->source = $tv->getSource($migx->working_context, false);

$properties = $tv->get('input_properties');
$properties = isset($properties['formtabs']) ? $properties : $tv->getProperties();
$default_formtabs = '[{"caption":"Default", "fields": [{"field":"title","caption":"Title"}]}]';
$formtabs = $modx->fromJSON($modx->getOption('formtabs', $properties, $default_formtabs));
$formtabs = empty($properties['formtabs']) ? $modx->fromJSON($default_formtabs) : $formtabs;
$fieldid = 0;
$tabid = 0;
$allfields = array();
$formnames = array();

/*actual record */
$record = $modx->fromJSON($scriptProperties['record_json']);

$field = array();
$field['field'] = 'MIGX_id';
$field['tv_id'] = 'migxid';
$allfields[] = $field;
if ($scriptProperties['isnew'] == '1') {
    $migxid = $scriptProperties['autoinc'] + 1;
} else {
    $migxid = $record['MIGX_id'];
}
$modx->smarty->assign('migxid', $migxid);

//multiple different Forms
// Note: use same field-names and inputTVs in all forms
if (isset($formtabs[0]['formtabs'])) {
    $forms = $formtabs;
    $tabs = array();
    foreach ($forms as $form) {
        $formname = array();
        $formname['value'] = $form['formname'];
        $formname['text'] = $form['formname'];
        $formname['selected'] = 0;
        if ($form['formname'] == $record['MIGX_formname']) {
            $formname['selected'] = 1;
        }
        $formnames[] = $formname;
        foreach ($form['formtabs'] as $tab) {
            $tabs[$form['formname']][] = $tab;
        }
    }

    $modx->smarty->assign('formnames', $formnames);

    if (isset($record['MIGX_formname'])) {
        $formtabs = $tabs[$record['MIGX_formname']];
    } else {
        //if no formname requested use the first form
        $formtabs = $tabs[$formnames[0]['value']];
    }
    $field = array();
    $field['field'] = 'MIGX_formname';
    $field['tv_id'] = 'Formname';
    $allfields[] = $field;
}

$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH);
$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);

$basePath = $base_path . $properties['basePath'];

$categories = array();
$modx->migx->createForm($formtabs, $record, $allfields, $categories, $scriptProperties);

$modx->smarty->assign('fields', $modx->toJSON($allfields));
$modx->smarty->assign('categories', $categories);
$modx->smarty->assign('properties', $scriptProperties);
$modx->smarty->assign('id_update_window', 'modx-window-mi-grid-update');

if (!empty($_REQUEST['showCheckbox'])) {
    $modx->smarty->assign('showCheckbox', 1);
}
$miTVCorePath = $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/');
$modx->smarty->template_dir = $miTVCorePath . 'templates/';
return $modx->smarty->fetch('mgr/fields.tpl');
