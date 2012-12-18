<?php



// special actions, for example the selectFromGrid - action
$tempParams = $modx->getOption('reqTempParams', $scriptProperties, '');
$action = '';
if (!empty($tempParams)) {
    $tempParams = $this->modx->fromJson($tempParams);
    if (isset($tempParams['action']) && !empty($tempParams['action'])) {
        $action = strtolower($tempParams['action']);
        if ($action == 'selectfromgrid') {
            //$scriptProperties['configs'] = $action;
        }
        $action = '_' . $action;
    }

}


$task = $modx->migx->getTask();
//$filename = basename(__file__);
$filename = str_replace(array('.class', '.php'), '', basename(__file__)) . $action . '.php';
$processorspath = dirname(dirname(__file__)) . '/';
$filenames = array();
if ($processor_file = $modx->migx->findProcessor($processorspath, $filename, $filenames)) {
    include_once ($processor_file);
}


$rows = is_array($rows) ? $rows : array();

return $this->outputArray($rows, $count);
