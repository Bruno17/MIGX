<?php
//[[migxSwitchDetailChunk? &detailChunk=`detailChunk` &listingChunk=`listingChunk`]]


$properties['migx_id'] = $modx->getOption('migx_id',$_GET,'');

if (!empty($properties['migx_id'])){
    $output = $modx->getChunk($detailChunk,$properties);
}
else{
    $output = $modx->getChunk($listingChunk);
}

return $output;