<?php

//print_r($scriptProperties);

$config = $modx->migx->customconfigs;

$hooksnippets = $modx->fromJson($modx->getOption('hooksnippets',$config,''));
if (is_array($hooksnippets)){
    $hooksnippet_getcustomconfigs = $modx->getOption('getcustomconfigs',$hooksnippets,'');
}

$snippetProperties = array();
$snippetProperties['scriptProperties'] = $scriptProperties;
$snippetProperties['processor'] = 'importcsvmigx';

if (!empty($hooksnippet_getcustomconfigs)){
    $customconfigs = $modx->runSnippet($hooksnippet_getcustomconfigs,$snippetProperties);
    $customconfigs = $modx->fromJson($customconfigs);
    if (is_array($customconfigs)){
        $config = array_merge($config,$customconfigs);    
    }
}

$reference_field = $modx->getOption('reference_field', $scriptProperties, 'id');
$items = $modx->getOption('items', $scriptProperties, '');
$pathname = $modx->getOption('pathname', $scriptProperties, '');
$items = !empty($items) ? $this->modx->fromJson($items) : array();


if (!function_exists('parse_csv_file')) {
    function parse_csv_file($file, $config ) {
        $columnheadings = true;
        $delimiter = isset($config['delimiter']) ? $config['delimiter'] : ',';
        $enclosure = isset($config['enclosure']) ? $config['enclosure'] : "\"";
        $row = 1;
        $rows = array();
        $handle = fopen($file, 'r');

        while (($data = fgetcsv($handle, 1000, $delimiter, $enclosure)) !== false) {

            if (!($columnheadings == false) && ($row == 1)) {
                $fieldnames = $data;
            } elseif (!($columnheadings == false)) {
                foreach ($data as $key => $value) {
                    unset($data[$key]);
                    $data[$fieldnames[$key]] = $value;
                }
                $rows[] = $data;
            } else {
                $rows[] = $data;
            }
            $row++;
        }

        fclose($handle);

        $result['fieldnames'] = $fieldnames;
        $result['rows'] = $rows;


        return $result;
    }
}

$result = parse_csv_file($pathname, $config);
//print_r($result);

$newitems = array();
$has_referencefield = false;
if (isset($result['rows'])) {
    foreach ($result['rows'] as $item) {
        if (isset($item[$reference_field])) {
            $newitems[$item[$reference_field]] = $item;
            $has_referencefield = true;
        } else {
            $newitems[] = $item;
        }
    }
}

$maxID = 0;
$outputitems = array();
if ($has_referencefield) {
    foreach ($items as $item) {
        if (isset($item[$reference_field]) && isset($newitems[$item[$reference_field]])) {
            if (isset($item['MIGX_id']) && $item['MIGX_id'] > $maxID) {
                $maxID = $item['MIGX_id'];
            }
            $outputitems[] = array_merge($item, $newitems[$item[$reference_field]]);
            unset($newitems[$item[$reference_field]]);
        }
    }
}

foreach ($newitems as $item) {
    $maxID++;
    $item['MIGX_id'] = (string )$maxID;
    $outputitems[] = $item;
}


return $modx->error->success('', $outputitems);
