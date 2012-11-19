<?php

class Blox_modTable_table {

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	array	initialization parameters
	 */
	public function __construct(&$blox) {
        $this->blox = & $blox;
        $this->bloxconfig = & $blox->bloxconfig; 
    }

    function getdatas() {
        global $modx;
        
        if (!empty($this->bloxconfig['packagename'])) {
            $modx->addPackage($this->bloxconfig['packagename'], $modx->getOption('core_path') . 'components/' . $this->bloxconfig['packagename'] . '/model/');
        }


        $query = $this->blox->prepareQuery($this->bloxconfig, $this->totalCount);
        $collection = $modx->getCollection($this->bloxconfig['classname'], $query);

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
        $p = new Pagination(array(
            'per_page' => $this->bloxconfig['limit'],
            'num_links' => $this->bloxconfig['numLinks'],
            'cur_page' => $this->bloxconfig['page'],
            'total_rows' => $numRows,
            'page_query_string' => $this->bloxconfig['pageVarKey'],
            'use_page_numbers' => true));

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
        return $bloxdatas;

    }
}

return 'Blox_modTable_table';