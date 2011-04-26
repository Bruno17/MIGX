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

$modx->getService('smarty', 'smarty.modSmarty');

if (!isset($modx->smarty)) {
    $modx->getService('smarty', 'smarty.modSmarty', '', array('template_dir' => $modx->
        getOption('manager_path') . 'templates/' . $modx->getOption('manager_theme', null,
        'default') . '/', ));
}
$modx->smarty->template_dir = $modx->getOption('manager_path') . 'templates/' .
$modx->getOption('manager_theme', null, 'default') . '/';
$modx->smarty->assign('OnResourceTVFormPrerender', $onResourceTVFormPrerender);
$modx->smarty->assign('_config', $modx->config);

$tv = $modx->getObject('modTemplateVar', array('name' => $scriptProperties['tv_name']));

$properties = $tv->get('input_properties');
$properties = isset($properties['columns']) ? $properties : $tv->getProperties();
$default_formtabs = '[{"caption":"Default", "fields": [{"field":"title","caption":"Title"}]}]';
$formtabs = $modx->fromJSON($modx->getOption('formtabs',$properties,$default_formtabs));
$formtabs = empty($properties['formtabs'])?$modx->fromJSON($default_formtabs):$formtabs;
$fieldid = 0;
$tabid = 0;
$allfields = array();

/*actual record */
$record = $modx->fromJSON($scriptProperties['record_json']);

foreach ($formtabs as $tabid => $tab) {
    /*virtual categories for tabs*/
    $emptycat = $modx->newObject('modCategory');
    $emptycat->set('category',  $tab['caption']);
    $emptycat->id = $tabid;
    $categories[$tabid] = $emptycat;
    $fields = $tab['fields'];
    foreach ($fields as & $field) {
        $fieldid++;
        if ($tv = $modx->getObject('modTemplateVar', array('name' => $field['inputTV']))) {

        } else {
            $tv = $modx->newObject('modTemplateVar');
            $tv->set('type','text');
        }

        /*insert actual value from requested record, convert arrays to ||-delimeted string */
        $fieldvalue = is_array($record[$field['field']]) ? implode('||', $record[$field['field']]) : $record[$field['field']];

        $tv->set('value', $fieldvalue);
        $tv->set('caption', htmlentities($field['caption'], ENT_QUOTES));
 
        /*generate unique tvid, must be numeric*/
        /*todo: find a better solution*/
        $field['tv_id'] = $scriptProperties['tv_id'] * 10000000 + $fieldid;
        $field['array_tv_id'] = $field['tv_id'] . '[]';
        $allfields[] = $field;

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
        if (!isset($params['allowBlank'])) $params['allowBlank'] = 1;

        $value = $tv->get('value');
        if ($value === null) {
            $value = $tv->get('default_text');
        }
        $modx->smarty->assign('params',$params);
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
