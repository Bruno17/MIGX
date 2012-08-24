<?php
$query = $this->prepareQuery($this->bloxconfig, $this->totalCount);
$collection = $modx->getCollection('modResource', $query);

//echo '<pre>' . print_r($this->bloxconfig, true) . '</pre>';
//$query->prepare();
//die($query->toSql());


$rows = array();
$i = 0;
foreach ($collection as $object) {
	$row = $object->toArray();
	if (!$i) {
		$this->columnNames = array_keys($row);
	}
	if (is_array($row) && count($row > 0)) {
		$colums = array();
		foreach ($row as $fieldname => $value) {
			$colums[] = array('value' => $value, 'fieldname' => $fieldname);
		}
		$row['innerrows']['rowvalue'] = $colums;
		$rows[] = $row;
	}
	$i++;
}

$numRows = $this->totalCount;
require_once ($this->bloxconfig['absolutepath'] . 'inc/Pagination.php');
$p = new Pagination(array('per_page' => $perPage,
			'num_links' => $this->bloxconfig['numLinks'],
			'cur_item' => $this->bloxconfig['pageStart'],
			'total_rows' => $numRows));

$fieldnames = array();
if (count($this->columnNames) > 0) {
	foreach ($this->columnNames as $col) {
		$col = array('fieldname' => $col);
		$fieldnames[] = $col;
	}
}
$bloxdatas['innerrows']['fieldnames'] = $fieldnames;
$bloxdatas['pagination'] = $p->create_links();
$bloxdatas['innerrows']['row'] = $rows;

//echo '<pre>' . print_r($bloxdatas, true) . '</pre>';
//echo '---------------------------------------';
//echo '<pre>' . print_r($rows, true) . '</pre>';
?>