<?php

/**
 * @package migx
 * @subpackage elements.tv.input
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) .'/model/migx/migx.class.php';
$migx = new Migx($modx);


$path = 'components/migx/';
$corePath = $this->xpdo->getOption('migx.core_path', null, $this->xpdo->getOption('core_path') . $path);
$namespace = 'migx';
$this->xpdo->lexicon->load('tv_widget', $namespace . ':default');
$properties = isset($params['columns']) ? $params : $this->getProperties();

/* get input-tvs */
$default_formtabs = '[{"caption":"Default", "fields": [{"field":"title","caption":"Title"}]}]';
$default_columns = '[{"header": "Title", "width": "160", "sortable": "true", "dataIndex": "title"}]';

$formtabs = $modx->fromJSON($modx->getOption('formtabs', $properties, $default_formtabs));
$formtabs = empty($properties['formtabs']) ? $modx->fromJSON($default_formtabs) : $formtabs;

$resource = is_object($this->xpdo->resource) ? $this->xpdo->resource->toArray() : array();
//multiple different Forms
// Note: use same field-names and inputTVs in all forms

$inputTvs = $migx->extractInputTvs($formtabs);

/* get base path based on either TV param or filemanager_path */
$modx->getService('fileHandler', 'modFileHandler', '', array('context' => $this->xpdo->context->get('key')));

/* pasted from processors.element.tv.renders.mgr.input*/
/* get working context */
$wctx = isset($_GET['wctx']) && !empty($_GET['wctx']) ? $modx->sanitizeString($_GET['wctx']) : '';
if (!empty($wctx)) {
    $workingContext = $modx->getContext($wctx);
    if (!$workingContext) {
        return $modx->error->failure($modx->lexicon('permission_denied'));
    }
    $wctx = $workingContext->get('key');
} else {
    $wctx = $modx->context->get('key');
}

$migx->working_context = $wctx;
$migx->source = $this->tv->getSource($migx->working_context, false);

/* get base path based on either TV param or filemanager_path */

$replacePaths = array('[[++base_path]]' => $modx->getOption('base_path', null, MODX_BASE_PATH), '[[++core_path]]' => $modx->getOption('core_path', null, MODX_CORE_PATH), '[[++manager_path]]' => $modx->
    getOption('manager_path', null, MODX_MANAGER_PATH), '[[++assets_path]]' => $modx->getOption('assets_path', null, MODX_ASSETS_PATH), '[[++base_url]]' => $modx->getOption('base_url', null, MODX_BASE_URL),
    '[[++manager_url]]' => $modx->getOption('manager_url', null, MODX_MANAGER_URL), '[[++assets_url]]' => $modx->getOption('assets_url', null, MODX_ASSETS_URL), );
$replaceKeys = array_keys($replacePaths);
$replaceValues = array_values($replacePaths);

/* pasted end*/

$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH); 
$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);

$columns = $modx->fromJSON($modx->getOption('columns', $properties, $default_columns));
$columns = empty($properties['columns']) ? $modx->fromJSON($default_columns) : $columns;

if (is_array($columns) && count($columns) > 0) {
    foreach ($columns as $key => $column) {
        $field['name'] = $column['dataIndex'];
        $field['mapping'] = $column['dataIndex'];
        $fields[] = $field;
        $col['dataIndex'] = $column['dataIndex'];
        $col['header'] = htmlentities($column['header'], ENT_QUOTES, $modx->getOption('modx_charset'));
        $col['sortable'] = $column['sortable'] == 'true' ? true : false;
        $col['width'] = $column['width'];
        $col['renderer'] = $column['renderer'];
        $cols[] = $col;
        $item[$field['name']] = isset($column['default']) ? $column['default'] : '';

        if (isset($inputTvs[$field['name']]) && $tv = $modx->getObject('modTemplateVar', array('name' => $inputTvs[$field['name']]['inputTV']))) {
            
            $inputTV = $inputTvs[$field['name']];
            
            $params = $tv->get('input_properties');
            $params['wctx'] = $wctx;
            /* pasted from processors.element.tv.renders.mgr.input*/
            if (!empty($properties['basePath'])) {
                if ($properties['autoResourceFolders'] == 'true' && isset($resource['id'])) {
                    $params['basePath'] = $base_path.$properties['basePath'] . $resource['id'] . '/';
                } else {
                    $params['basePath'] = $base_path.$properties['basePath'];
                }
            }
            if (empty($params['basePath'])) {
                $params['basePath'] = $modx->fileHandler->getBasePath();
                $params['basePath'] = str_replace($replaceKeys, $replaceValues, $params['basePath']);
                $params['basePathRelative'] = $this->xpdo->getOption('filemanager_path_relative', null, true) ? 1 : 0;
            } else {
                $params['basePath'] = str_replace($replaceKeys, $replaceValues, $params['basePath']);
                $params['basePathRelative'] = !isset($params['basePathRelative']) || in_array($params['basePathRelative'], array('true', 1, '1'));
            }
            if (empty($params['baseUrl'])) {
                $params['baseUrl'] = $modx->fileHandler->getBaseUrl();
                $params['baseUrl'] = str_replace($replaceKeys, $replaceValues, $params['baseUrl']);
                $params['baseUrlRelative'] = $this->xpdo->getOption('filemanager_url_relative', null, true) ? 1 : 0;
            } else {
                $params['baseUrl'] = str_replace($replaceKeys, $replaceValues, $params['baseUrl']);
                $params['baseUrlRelative'] = !isset($params['baseUrlRelative']) || in_array($params['baseUrlRelative'], array('true', 1, '1'));
            }
            $modxBasePath = $modx->getOption('base_path', null, MODX_BASE_PATH);
            if ($params['basePathRelative'] && $modxBasePath != '/') {
                $params['basePath'] = ltrim(str_replace($modxBasePath, '', $params['basePath']), '/');
            }
            $modxBaseUrl = $modx->getOption('base_url', null, MODX_BASE_URL);
            if ($params['baseUrlRelative'] && $modxBaseUrl != '/') {
                $params['baseUrl'] = ltrim(str_replace($modxBaseUrl, '', $params['baseUrl']), '/');
            }

            $params['basePathRelative'] = $params['basePathRelative'] ? 1 : 0;
            $params['baseUrlRelative'] = $params['baseUrlRelative'] ? 1 : 0;
            /* pasted end*/
            
            $mediasource = $migx->getFieldSource($inputTV,$tv);
            $pathconfigs[$key] = '&source='.$mediasource->get('id');
            //$pathconfigs[$key] = '&basePath='.$params['basePath'].'&basePathRelative='.$params['basePathRelative'].'&baseUrl='.$params['baseUrl'].'&baseUrlRelative='.$params['baseUrlRelative'];

        } else {
            $pathconfigs[$key] = array();
        }
    }
}

$newitem[] = $item;
$lang = $this->xpdo->lexicon->fetch();
$lang['mig_add'] = !empty($properties['btntext']) ? $properties['btntext'] : $lang['mig_add'];
$modx->smarty->assign('i18n', $lang);
$this->xpdo->smarty->assign('properties', $properties);
$this->xpdo->smarty->assign('resource', $resource);
$this->xpdo->smarty->assign('pathconfigs', $this->xpdo->toJSON($pathconfigs));
$this->xpdo->smarty->assign('columns', $this->xpdo->toJSON($cols));
$this->xpdo->smarty->assign('fields', $this->xpdo->toJSON($fields));
$this->xpdo->smarty->assign('newitem', $this->xpdo->toJSON($newitem));
$this->xpdo->smarty->assign('base_url', $this->xpdo->getOption('base_url'));
$this->xpdo->smarty->assign('myctx', $wctx);

return $modx->smarty->fetch($corePath . 'elements/tv/migx.tpl');
