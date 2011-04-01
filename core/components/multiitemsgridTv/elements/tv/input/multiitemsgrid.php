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
		$item[$field['name']]='';
		
	}
}

$newitem[]=$item;

$this->xpdo->smarty->assign('columns',$this->xpdo->toJSON($cols));
$this->xpdo->smarty->assign('fields',$this->xpdo->toJSON($fields));
$this->xpdo->smarty->assign('newitem',$this->xpdo->toJSON($newitem));
$this->xpdo->smarty->assign('base_url',$this->xpdo->getOption('base_url'));
return $this->xpdo->smarty->fetch('element/tv/renders/input/multiitemsgrid.tpl');