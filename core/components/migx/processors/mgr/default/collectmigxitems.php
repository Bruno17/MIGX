<?php

$items = $modx->getOption('items', $scriptProperties, '');
$config = $modx->migx->customconfigs;

if (!empty($items)) {
    $items = $modx->fromJson($items);

    $rows = $modx->migx->checkRenderOptions($items);

    $hooksnippets = $modx->fromJson($modx->getOption('hooksnippets', $config, ''));
    if (is_array($hooksnippets)) {
        $hooksnippet_aftercollectmigxitems = $modx->getOption('aftercollectmigxitems', $hooksnippets, '');
    }
    
    
    if (!empty($hooksnippet_aftercollectmigxitems)) {
        $snippetProperties = array();
        $snippetProperties['rows'] = $rows;
        $snippetProperties['scriptProperties'] = $scriptProperties;

        $result = $modx->runSnippet($hooksnippet_aftercollectmigxitems, $snippetProperties);
        $result = $modx->fromJson($result);
        $error = $modx->getOption('error', $result, '');
        if (!empty($error)) {
            $updateerror = true;
            $errormsg = $error;
            return;
        } else {
            $rows = $result;
        }
    }
    

    //print_r($items);

    //$items = $modx->toJson($items);
}

return $modx->error->success('', $rows);
