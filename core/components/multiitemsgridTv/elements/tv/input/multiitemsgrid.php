<?php

/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */

$this->xpdo->lexicon->load('tv_widget');

$properties = $this->getProperties();

/* get input-tvs */
$formtabs = $modx->fromJSON($properties['formtabs']);
$inputTvs = array();
foreach ($formtabs as $tab) {
    if (isset($tab['fields'])) {
        foreach ($tab['fields'] as $field) {
            if (isset($field['inputTV'])) {
                $inputTvs[$field['field']] = $field['inputTV'];
            }
        }
    }
}

/* get base path based on either TV param or filemanager_path */
//$key = 1;
$modx->getService('fileHandler', 'modFileHandler', '', array('context' => $this->xpdo->context->get('key')));

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

/* get base path based on either TV param or filemanager_path */
$replacePaths = array(
    '[[++base_path]]' => $modx->getOption('base_path', null, MODX_BASE_PATH), 
    '[[++core_path]]' => $modx->getOption('core_path', null, MODX_CORE_PATH), 
    '[[++manager_path]]' => $modx->getOption('manager_path', null, MODX_MANAGER_PATH), 
    '[[++assets_path]]' => $modx->getOption('assets_path', null, MODX_ASSETS_PATH), 
    '[[++base_url]]' => $modx->getOption('base_url', null, MODX_BASE_URL),
    '[[++manager_url]]' => $modx->getOption('manager_url', null, MODX_MANAGER_URL), 
    '[[++assets_url]]' => $modx->getOption('assets_url', null, MODX_ASSETS_URL), );
$replaceKeys = array_keys($replacePaths);
$replaceValues = array_values($replacePaths);

/*
if (empty($params[$key]['basePath'])) {
    $params[$key]['basePath'] = $modx->fileHandler->getBasePath();
    $params[$key]['basePathRelative'] = $this->xpdo->getOption('filemanager_path_relative', null, true) ? 1 : 0;
} else {
    $params[$key]['basePathRelative'] = !isset($params[$key]['basePathRelative']) || in_array($params[$key]['basePathRelative'], array('true', 1, '1'));
}
if (empty($params[$key]['baseUrl'])) {
    $params[$key]['baseUrl'] = $modx->fileHandler->getBaseUrl();
    $params[$key]['baseUrlRelative'] = $this->xpdo->getOption('filemanager_url_relative', null, true) ? 1 : 0;
} else {
    $params[$key]['baseUrlRelative'] = !isset($params[$key]['baseUrlRelative']) || in_array($params[$key]['baseUrlRelative'], array('true', 1, '1'));
}
*/

$columns = $this->xpdo->fromJSON($properties['columns']);

if (count($columns) > 0) {
    foreach ($columns as $key=>$column) {
        $field['name'] = $column['dataIndex'];
        $field['mapping'] = $column['dataIndex'];
        $fields[] = $field;
        $col['dataIndex'] = $column['dataIndex'];
        $col['header'] = $column['header'];
        $col['sortable'] = $column['sortable'] == 'true' ? true : false;
        $col['width'] = $column['width'];
        $col['renderer'] = $column['renderer'];
        $cols[] = $col;
        $item[$field['name']] = isset($column['default']) ? $column['default'] : '';

        if (isset($inputTvs[$field['name']]) && $tv = $modx->getObject('modTemplateVar', array ('name'=>$inputTvs[$field['name']]))) {
            $params = $tv->get('input_properties');
            $params ['wctx'] = $wctx;
            if (empty( $params['basePath'])) {
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
            /*
            if (!empty($params['baseUrl']) && !empty($value)) {
                $relativeValue = $params['baseUrl'] . ltrim($value, '/');
            } else {
                $relativeValue = $value;
            }
            */
            $params['basePathRelative']=$params['basePathRelative']?1:0;
            $params['baseUrlRelative']=$params['baseUrlRelative']?1:0;
            $pathconfigs[$key]=$params;
            
        }
        else{
            $pathconfigs[$key]=array();    
        }
    }
}


$newitem[] = $item;
$this->xpdo->smarty->assign('pathconfigs', $this->xpdo->toJSON($pathconfigs));
$this->xpdo->smarty->assign('columns', $this->xpdo->toJSON($cols));
$this->xpdo->smarty->assign('fields', $this->xpdo->toJSON($fields));
$this->xpdo->smarty->assign('newitem', $this->xpdo->toJSON($newitem));
$this->xpdo->smarty->assign('base_url', $this->xpdo->getOption('base_url'));
//return $this->xpdo->smarty->fetch('element/tv/renders/input/multiitemsgrid.tpl');

$corePath = $this->xpdo->getOption('multiitemsgridTv.core_path', null, $this->xpdo->getOption('core_path') . 'components/multiitemsgridTv/');
return $modx->smarty->fetch($corePath . 'elements/tv/multiitemsgrid.tpl');
