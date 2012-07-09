<?php

/**
 * Generatting CSV formatted string from an array.
 * By Sergey Gurevich.
 */
function array_to_csv($array, $header_row = true, $col_sep = ",", $row_sep = "\n", $qut = '"')
{
    if (!is_array($array) or !is_array($array[0])) return false;

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

$output = '';
if (!($scriptProperties['download'])) {

    $prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
    if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
        $prefix = isset($config['prefix']) ? $config['prefix'] : '';  
    }
    $packageName = $config['packageName'];
    $classname = $config['classname'];

    $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
    $modelpath = $packagepath . 'model/';

    $modx->addPackage($packageName, $modelpath, $prefix);

    if ($this->modx->lexicon) {
        $this->modx->lexicon->load($packageName . ':default');
    }

    /* setup default properties */
    $sort = $modx->getOption('sort', $scriptProperties, 'id');
    $sort = $modx->getOption('sort', $scriptProperties, 'id');
    $dir = $modx->getOption('dir', $scriptProperties, 'ASC');
    $showtrash = $modx->getOption('showtrash', $scriptProperties, '');

    $c = $modx->newQuery($classname);

    if (!empty($showtrash)) {
        $c->where(array($classname . '.deleted' => '1'));
    } else {
        $c->where(array($classname . '.deleted' => '0'));
    }
    $count = $modx->getCount($classname, $c);

    $c->select($modx->getSelectColumns($classname, $classname));

    $c->sortby($sort, $dir);

    //$c->prepare();
    //die ($c->toSql());

    $collection = $modx->getCollection($classname, $c);

    if (isset($config['exportfields']) && !empty($config['exportfields'])) {
        $exportFields = $config['exportfields'];
    } else {
        $fields = $modx->getFields($classname);
        foreach ($fields as $field => $value) {
            $exportFields[$field] = $field;
        }
    }

    $rows = array();
    foreach ($collection as $row) {
        $tempRow = $row->toArray();
        $newRow = array();

        foreach ($exportFields as $key => $exportKey) {
            if (isset($tempRow[$key])) {
                $newRow[$exportKey] = $tempRow[$key];
            }
        }
        $rows[] = $newRow;
    }

    //die(print_r($rows, true));

    $output = array_to_csv($rows);

    $cacheName = md5(time());
    $cacheName = $modx->getOption('core_path') . 'export/courses/' . $cacheName;

    $cacheManager = $modx->getCacheManager();
    $cacheManager->writeFile($cacheName, $output);

    return $modx->error->success(basename($cacheName));
} else {
    $configs = $modx->getOption('configs', $scriptProperties, '');
    $cacheName = $scriptProperties['download'];
    $cacheName = $modx->getOption('core_path') . 'export/courses/' . $cacheName;

    if (!is_file($cacheName)) {
        return 'Export error: Export ' . $cacheName . ' does not exist';
    } else {
        $output = file_get_contents($cacheName);
    }

    $filename = strftime('%Y-%m-%d') . '_' . $configs . '_report.csv';

    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename=' . $filename);

    return $output;
}

?>