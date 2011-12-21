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

foreach ($formtabs as $tabid => $tab) {
    /*virtual categories for tabs*/
    $emptycat = $modx->newObject('modCategory');
    $emptycat->set('category', $tab['caption']);
    $emptycat->id = $tabid;
    $categories[$tabid] = $emptycat;
    $fields = $tab['fields'];
    foreach ($fields as & $field) {
        $fieldid++;
        if ($tv = $modx->getObject('modTemplateVar', array('name' => $field['inputTV']))) {

        } else {
            $tv = $modx->newObject('modTemplateVar');
            $tv->set('type', 'text');
        }

        /*insert actual value from requested record, convert arrays to ||-delimeted string */
        $fieldvalue = is_array($record[$field['field']]) ? implode('||', $record[$field['field']]) : $record[$field['field']];

        $tv->set('value', $fieldvalue);
        $tv->set('caption', htmlentities($field['caption'], ENT_QUOTES, $modx->getOption('modx_charset')));
        if (!empty($field['description'])) {
            $tv->set('description', htmlentities($field['description'], ENT_QUOTES, $modx->getOption('modx_charset')));
        }
        /*generate unique tvid, must be numeric*/
        /*todo: find a better solution*/
        $field['tv_id'] = $scriptProperties['tv_id'] * 10000000 + $fieldid;
        $field['array_tv_id'] = $field['tv_id'] . '[]';
        $allfields[] = $field;

        $mediasource = $migx->getFieldSource($field,$tv);


        //$modx->setOption('default_media_source',$mediasource->get('id'));
        //mediasource über formtabs steuerbar machen?
        //{"mediasources":[{"web":"1"}]}

        $tv->set('id', $field['tv_id']);

        /*
        $default = $tv->processBindings($tv->get('default_text'), $resourceId);
        if (strpos($tv->get('default_text'), '@INHERIT') > -1 && (strcmp($default, $tv->get('value')) == 0 || $tv->get('value') == null)) {
        $tv->set('inherited', true);
        }
        */

        if ($tv->get('value') == null) {
            $v = $tv->get('default_text');
            if ($tv->get('type') == 'checkbox' && $tv->get('value') == '') {
                $v = '';
            }
            $tv->set('value', $v);
        }


        $modx->smarty->assign('tv', $tv);
        $params = $tv->get('input_properties');
        if (!empty($properties['basePath'])) {
            if ($properties['autoResourceFolders'] == 'true') {
                $params['basePath'] = $basePath . $scriptProperties['resource_id'] . '/';
                $targetDir = $params['basePath'];

                $cacheManager = $modx->getCacheManager();
                /* if directory doesnt exist, create it */
                if (!file_exists($targetDir) || !is_dir($targetDir)) {
                    if (!$cacheManager->writeTree($targetDir)) {
                        $modx->log(modX::LOG_LEVEL_ERROR, '[MIGX] Could not create directory: ' . $targetDir);
                        return $modx->error->failure('Could not create directory: ' . $targetDir);
                    }
                }
                /* make sure directory is readable/writable */
                if (!is_readable($targetDir) || !is_writable($targetDir)) {
                    $modx->log(xPDO::LOG_LEVEL_ERROR, '[MIGX] Could not write to directory: ' . $targetDir);
                    return $modx->error->failure('Could not write to directory: ' . $targetDir);
                }
            } else {
                $params['basePath'] = $basePath;
            }
        }

        if (!isset($params['allowBlank'])) $params['allowBlank'] = 1;

        $value = $tv->get('value');
        if ($value === null) {
            $value = $tv->get('default_text');
        }
        $modx->smarty->assign('params', $params);
        /* find the correct renderer for the TV, if not one, render a textbox */
        $inputRenderPaths = $tv->getRenderDirectories('OnTVInputRenderList', 'input');
        $inputForm = $tv->getRender($params, $value, $inputRenderPaths, 'input', $resourceId, $tv->get('type'));

        if (empty($inputForm)) continue;

        $tv->set('formElement', $inputForm);

        if (!is_array($categories[$tabid]->tvs)) {
            $categories[$tabid]->tvs = array();
        }
        $categories[$tabid]->tvs[] = $tv;

    }
}

$modx->smarty->assign('fields', $modx->toJSON($allfields));
$modx->smarty->assign('categories', $categories);
$modx->smarty->assign('properties', $scriptProperties);

if (!empty($_REQUEST['showCheckbox'])) {
    $modx->smarty->assign('showCheckbox', 1);
}
$miTVCorePath = $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/');
$modx->smarty->template_dir = $miTVCorePath . 'templates/';
return $modx->smarty->fetch('mgr/fields.tpl');
