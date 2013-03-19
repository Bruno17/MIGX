<?php

$items = $modx->getOption('items', $scriptProperties, '');

if (!empty($items)){
    $items = $modx->fromJson($items);

$rows = $modx->migx->checkRenderOptions($items);

//print_r($items);
    
    //$items = $modx->toJson($items);
}

return $modx->error->success('',$rows);