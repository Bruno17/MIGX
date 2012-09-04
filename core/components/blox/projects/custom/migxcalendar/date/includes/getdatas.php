<?php
$modx->addPackage($this->bloxconfig['packagename'], $modx->getOption('core_path') . 'components/' . $this->bloxconfig['packagename'] . '/model/');

$queries = array();
if (isset($_REQUEST['p1'])) {
	$queries[] = array('operator'=> 'AND', 'query' => 'YEAR(`'.$this->bloxconfig['classname'].'`.`start`) = "' . intval($_REQUEST['p1']) . '"');
}
if (isset($_REQUEST['p2'])) {
	$queries[] = array('operator'=> 'AND', 'query' => 'MONTH(`'.$this->bloxconfig['classname'].'`.`start`) = "' . intval($_REQUEST['p2']) . '"');
}
if (isset($_REQUEST['p3'])) {
	$queries[] = array('operator'=> 'AND', 'query' => 'DAY(`'.$this->bloxconfig['classname'].'`.`start`) = "' . intval($_REQUEST['p3']) . '"');
}
if (isset($_REQUEST['p4'])) {
	$queries[] = array('operator'=> 'AND', 'query' => '`'.$this->bloxconfig['classname'].'`.`alias` = "' . $_REQUEST['p4'] . '"');
}

$this->bloxconfig['queries'] = json_encode($queries);
$query = $this->prepareQuery($this->bloxconfig, $this->totalCount);

//$query->prepare(); echo($query->toSql());

$collection = $modx->getCollection($this->bloxconfig['classname'], $query);

$rows = array();
foreach ($collection as $object) {
	$rows[0] = $object->toArray();
}

$modx->setPlaceholder ('blox.title', $rows[0]['title']);
$modx->setPlaceholder ('blox.longtitle', $rows[0]['longtitle']);

$bloxdatas['innerrows']['row'] = $rows;

//echo '<pre>' . print_r($bloxdatas, true) . '</pre>';
//echo '---------------------------------------';
//echo '<pre>' . print_r($rows, true) . '</pre>';
?>
