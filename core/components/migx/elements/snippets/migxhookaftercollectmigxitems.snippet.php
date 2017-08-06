<?php
$configs = $modx->getOption('configs', $_REQUEST, '');

$rows = $modx->getOption('rows', $scriptProperties, array());
$newrows = array();


if (is_array($rows)) {
    $max_id = 0;
    $dbfields = array();
    $existing_dbfields = array();
    foreach ($rows as $key => $row) {
        if (isset($row['MIGX_id']) && $row['MIGX_id'] > $max_id) {
            $max_id = $row['MIGX_id'];
        }
        if (isset($row['selected_dbfields']) && isset($row['existing_dbfields'])) {
            $dbfields = is_array($row['selected_dbfields']) ? $row['selected_dbfields'] : array($row['selected_dbfields']);
            
            $existing_dbfields = explode('||', $row['existing_dbfields']);
            //echo '<pre>' . print_r($existing_dbfields,1) . '</pre>';die();

        } else {
            $newrows[] = $row;
        }

    }

    foreach ($dbfields as $dbfield) {
        if (!empty($dbfield) && !in_array($dbfield, $existing_dbfields)) {
            $max_id++;
            $newrow = array();
            $newrow['MIGX_id'] = $max_id;

            switch ($configs) {
                case 'migxformtabfields':
                    $newrow['field'] = $dbfield;
                    $newrow['caption'] = $dbfield;
                    break;
                case 'migxcolumns':
                    $newrow['dataIndex'] = $dbfield;
                    $newrow['header'] = $dbfield;
                    break;                    
            }


            $newrows[] = $newrow;
        }
    }


}


return json_encode($newrows);