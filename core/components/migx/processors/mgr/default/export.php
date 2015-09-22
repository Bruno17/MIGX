<?php

/**
 * Generatting CSV formatted string from an array.
 * By Sergey Gurevich.
 */
function array_to_csv($array, $header_row = true, $col_sep = ",", $row_sep = "\n", $qut = '"') {
    if (!is_array($array) or !is_array($array[0]))
        return false;

    //Header row.
    if ($header_row) {
        foreach ($array[0] as $key => $val) {
            //Escaping quotes.
            $key = str_replace($qut, "$qut$qut", $key);
            $output .= "$col_sep$qut$key$qut";
        }
        $output = substr($output, 1) . $row_sep;
    }
    //Data rows.
    foreach ($array as $key => $val) {
        $tmp = '';
        foreach ($val as $cell_key => $cell_val) {
            //Escaping quotes.
            $cell_val = str_replace($qut, "$qut$qut", $cell_val);
            $tmp .= "$col_sep$qut$cell_val$qut";
        }
        $output .= substr($tmp, 1) . $row_sep;
    }

    return $output;
}

$config = $modx->migx->customconfigs;

$hooksnippets = $modx->fromJson($modx->getOption('hooksnippets',$config,''));
if (is_array($hooksnippets)){
    $hooksnippet_getcustomconfigs = $modx->getOption('getcustomconfigs',$hooksnippets,'');
}

$snippetProperties = array();
$snippetProperties['scriptProperties'] = $scriptProperties;
$snippetProperties['processor'] = 'export';

if (!empty($hooksnippet_getcustomconfigs)){
    $customconfigs = $modx->runSnippet($hooksnippet_getcustomconfigs,$snippetProperties);
    $customconfigs = $modx->fromJson($customconfigs);
    if (is_array($customconfigs)){
        $config = array_merge($config,$customconfigs);    
    }
}

$output = '';
if (!($scriptProperties['download'])) {

    $prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
    if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';
    }

    if (!empty($config['packageName'])) {
        $packageNames = explode(',', $config['packageName']);

        if (count($packageNames) == '1') {
            //for now connecting also to foreign databases, only with one package by default possible
            $xpdo = $modx->migx->getXpdoInstanceAndAddPackage($config);
        } else {
            //all packages must have the same prefix for now!
            foreach ($packageNames as $packageName) {
                $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
                $modelpath = $packagepath . 'model/';
                if (is_dir($modelpath)) {
                    $modx->addPackage($packageName, $modelpath, $prefix);
                }

            }
            $xpdo = &$modx;
        }
    } else {
        $xpdo = &$modx;
    }
    $classname = $config['classname'];
    $joins = isset($config['joins']) && !empty($config['joins']) ? $modx->fromJson($config['joins']) : false;
    $checkdeleted = isset($config['gridactionbuttons']['toggletrash']['active']) && !empty($config['gridactionbuttons']['toggletrash']['active']) ? true : false;

    if ($this->modx->lexicon) {
        $this->modx->lexicon->load($packageName . ':default');
    }

    /* setup default properties */
    $sort = $modx->getOption('sort', $scriptProperties, 'id');
    $sort = $modx->getOption('sort', $scriptProperties, 'id');
    $dir = $modx->getOption('dir', $scriptProperties, 'ASC');
    $showtrash = $modx->getOption('showtrash', $scriptProperties, '');

    $where = !empty($config['getlistwhere']) ? $config['getlistwhere'] : '';
    $where = $modx->getOption('where', $scriptProperties, $where);

    $c = $xpdo->newQuery($classname);
    $c->select($xpdo->getSelectColumns($classname, $classname));

    if ($joins) {
        $modx->migx->prepareJoins($classname, $joins, $c);
    }

    if (isset($config['gridfilters']) && count($config['gridfilters']) > 0) {
        foreach ($config['gridfilters'] as $filter) {

            if (!empty($filter['getlistwhere'])) {

                $requestvalue = $modx->getOption($filter['name'], $scriptProperties, 'all');

                if (isset($scriptProperties[$filter['name']]) && $requestvalue != 'all') {

                    $chunk = $modx->newObject('modChunk');
                    $chunk->setCacheable(false);
                    $chunk->setContent($filter['getlistwhere']);
                    $fwhere = $chunk->process($scriptProperties);
                    $fwhere = strpos($fwhere, '{') === 0 ? $modx->fromJson($fwhere) : $fwhere;

                    $c->where($fwhere);
                }
            }
        }
    }

    if ($checkdeleted) {
        if (!empty($showtrash)) {
            $c->where(array($classname . '.deleted' => '1'));
        } else {
            $c->where(array($classname . '.deleted' => '0'));
        }
    }

    if (!empty($where)) {
        $c->where($modx->fromJson($where));
    }

    $count = $modx->getCount($classname, $c);

    $c->sortby($sort, $dir);

    //$c->prepare();
    //adie ($c->toSql());

    $collection = $xpdo->getCollection($classname, $c);

    $collectfieldnames = false;
    if (isset($config['exportfields']) && !empty($config['exportfields'])) {
        $exportFields = $config['exportfields'];
    } else {
        $collectfieldnames = true;
    }


    $excludeFields = $modx->getOption('excludeFields', $config);
    $excludeFields = explode(',', $excludeFields);

    $rows = array();
    $i = 0;
    foreach ($collection as $row) {
        $tempRow = $row->toArray();

        foreach ($tempRow as $tempfield => $tempvalue) {
            //get fieldnames from first record

            if ($collectfieldnames) {
                $exportFields[$tempfield] = $tempfield;
            }

            //extract json-fields to new fieldnames


            if (is_array($tempvalue)) {
                foreach ($tempvalue as $field => $value) {
                    $tempRow[$tempfield . '_' . $field] = $value;
                    if ($collectfieldnames) {
                        $exportFields[$tempfield . '_' . $field] = $field;
                    }
                }
                unset($tempRow[$tempfield]);
                unset($exportFields[$tempfield]);

            }


            if (in_array($tempfield, $excludeFields)) {
                unset($exportFields[$tempfield]);
                unset($tempRow[$tempfield]);
            }

            //print_r($tempRow);


        }


        /*
        $newRow = array();

        foreach ($exportFields as $key => $exportKey) {
        if (isset($tempRow[$key])) {
        $newRow[$exportKey] = $tempRow[$key];
        }
        }
        */
        $rows[] = $tempRow;
        $i++;
    }

    $temprows = $rows;
    $rows = array();
    foreach ($temprows as $row) {
        $newRow = array();
        foreach ($exportFields as $key => $exportKey) {
            if (isset($row[$key])) {
                $newRow[$exportKey] = $row[$key];
            } else {
                $newRow[$exportKey] = '';
            }
        }
        $rows[] = $newRow;

    }

    //die(print_r($rows, true));

    $output = array_to_csv($rows);

    $cacheName = md5(time());
    $cacheName = $modx->getOption('core_path') . 'export/' . $cacheName;

    $cacheManager = $modx->getCacheManager();
    $cacheManager->writeFile($cacheName, $output);
    $_SESSION['csv_filedownload'] = basename($cacheName);
    return $modx->error->success(basename($cacheName));
} else {
    $configs = $modx->getOption('configs', $scriptProperties, '');
    $cacheName = $scriptProperties['download'];
    $output = 'Export error: ' . $cacheName . ' unknown/no permission';
    if (isset($_SESSION['csv_filedownload']) && $cacheName == $_SESSION['csv_filedownload']) {
        $cacheName = $modx->getOption('core_path') . 'export/' . $cacheName;

        if (!is_file($cacheName)) {
            return 'Export error: Export ' . $cacheName . ' does not exist';
        } else {
            $output = file_get_contents($cacheName);
        }

        $filename = strftime('%Y-%m-%d') . '_' . $configs . '_report.csv';

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=' . $filename);
    }

    return $output;
}

?>