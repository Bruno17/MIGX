<?php

$config = $modx->migx->customconfigs;

//todo: make this configurable
$tpl = 'ajaxupload.html';

$tpl = $modx->migx->config['templatesPath'].'mgr/iframechunks/'.$tpl;

$modx->toPlaceholders($modx->migx->config,'migx_config');
$modx->toPlaceholders($_REQUEST,'request');

return $modx->migx->parseChunk($tpl);