<?php

class Blox_modResource_Div {

    /**
     * Constructor
     *
     * @access	public
     * @param	array	initialization parameters
     */
    public function __construct(&$blox) {
        $this->blox = &$blox;
        $this->bloxconfig = &$blox->bloxconfig;
    }

    function getdatas() {
        global $modx;

        if (class_exists('bloxhelpers')) {
            // Initialize class
            $helper = new bloxhelpers($this->blox);
        } else {
            echo 'bloxhelpers class not found';
        }

        $rows = $helper->getResources($this->bloxconfig);

        $numRows = $modx->getPlaceholder($this->bloxconfig['totalVar']);

        require_once ($this->bloxconfig['absolutepath'] . 'inc/Pagination.php');
        $p = new Pagination(array(
            'per_page' => $this->bloxconfig['limit'],
            'num_links' => $this->bloxconfig['numLinks'],
            'cur_page' => $this->bloxconfig['page'],
            'total_rows' => $numRows,
            'page_query_string' => $this->bloxconfig['pageVarKey'],
            'use_page_numbers' => true));

        $bloxdatas['pagination'] = $p->create_links();
        $bloxdatas['innerrows']['row'] = $rows;

        if ($this->bloxconfig['debug']) {
            //echo '<pre>' . print_r($bloxdatas, true) . '</pre>';
            //echo '---------------------------------------';
            //echo '<pre>' . print_r($rows, true) . '</pre>';
        }
        return $bloxdatas;

    }
}


?>