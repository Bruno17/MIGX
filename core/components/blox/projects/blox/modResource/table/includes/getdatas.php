<?php
$rows = $this->getResources($this->bloxconfig);

$i = 0;
foreach ($rows as &$row) {
	if (!$i) {
		$this->columnNames = array_keys($row);
	}
	if (is_array($row) && count($row > 0)) {
		$colums = array();
		foreach ($row as $fieldname => $value) {
			$colums[] = array('value' => $value, 'fieldname' => $fieldname);
		}
		$row['innerrows']['rowvalue'] = $colums;
	}
	$i++;
}

$numRows = count($rows);
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

if ($this->bloxconfig['debug']) {
	//echo '<pre>' . print_r($bloxdatas, true) . '</pre>';
	//echo '---------------------------------------';
	//echo '<pre>' . print_r($rows, true) . '</pre>';
}
?>