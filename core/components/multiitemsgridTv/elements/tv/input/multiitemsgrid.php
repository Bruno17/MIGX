<?php
/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */


$this->xpdo->lexicon->load('tv_widget');

$properties=$this->getProperties();
$columns=$this->xpdo->fromJSON($properties['columns']);


if (count($columns)>0){
	foreach ($columns as $column){
		$field['name']=$column['dataIndex'];
		$field['mapping']=$column['dataIndex'];
		$fields[]=$field;
		$col['dataIndex']=$column['dataIndex'];
		$col['header']=$column['header'];
		$col['sortable']=$column['sortable']=='true'?true:false;
		$col['width']=$column['width'];
		$col['renderer']=$column['renderer'];
		$cols[]=$col;
		$item[$field['name']]=isset($column['default'])?$column['default']:'';
		
	}
}
/* get base path based on either TV param or filemanager_path */
$modx->getService('fileHandler','modFileHandler', '', array('context' => $this->xpdo->context->get('key')));
if (empty($params['basePath'])) {
    $params['basePath'] = $modx->fileHandler->getBasePath();
    $params['basePathRelative'] = $this->xpdo->getOption('filemanager_path_relative',null,true) ? 1 : 0;
} else {
    $params['basePathRelative'] = !isset($params['basePathRelative']) || in_array($params['basePathRelative'],array('true',1,'1'));
}
if (empty($params['baseUrl'])) {
    $params['baseUrl'] = $modx->fileHandler->getBaseUrl();
    $params['baseUrlRelative'] = $this->xpdo->getOption('filemanager_url_relative',null,true) ? 1 : 0;
} else {
    $params['baseUrlRelative'] = !isset($params['baseUrlRelative']) || in_array($params['baseUrlRelative'],array('true',1,'1'));
}

$newitem[]=$item;
$this->xpdo->smarty->assign('params',$params);
$this->xpdo->smarty->assign('columns',$this->xpdo->toJSON($cols));
$this->xpdo->smarty->assign('fields',$this->xpdo->toJSON($fields));
$this->xpdo->smarty->assign('newitem',$this->xpdo->toJSON($newitem));
$this->xpdo->smarty->assign('base_url',$this->xpdo->getOption('base_url'));
//return $this->xpdo->smarty->fetch('element/tv/renders/input/multiitemsgrid.tpl');

$corePath = $this->xpdo->getOption('multiitemsgridTv.core_path',null,$this->xpdo->getOption('core_path').'components/multiitemsgridTv/');
return $modx->smarty->fetch($corePath.'elements/tv/multiitemsgrid.tpl');