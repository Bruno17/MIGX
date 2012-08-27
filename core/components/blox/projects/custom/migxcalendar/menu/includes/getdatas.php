<?php
$modx->addPackage($this->bloxconfig['packagename'], $modx->getOption('core_path') . 'components/' . $this->bloxconfig['packagename'] . '/model/');

$query = $this->prepareQuery($this->bloxconfig, $this->totalCount);
$collection = $modx->getCollection($this->bloxconfig['classname'], $query);

$next = array();
foreach ($collection as $object) {
	$next[] = $object->toArray();
}

$bloxdatas['innerrows']['next'] = $next;

$this->bloxconfig['joins'] = '[{"alias":"Resources","selectfields":"resource_id"}]';
$this->bloxconfig['where'] ='{"Resources.resource_id":"1"}';
$query = $this->prepareQuery($this->bloxconfig, $this->totalCount);
$collection = $modx->getCollection($this->bloxconfig['classname'], $query);

$important = array();
foreach ($collection as $object) {
	$important[] = $object->toArray();
}

$bloxdatas['innerrows']['important'] = $important;

//echo ('<pre>' . print_r($bloxdatas, true) . '</pre>');
//echo ('---------------------------------------');
//die ('<pre>' . print_r($important, true) . '</pre>');
?>
