<?php

$config = $modx->migx->customconfigs;
$emtpytext = $config['gridfilters'][$scriptProperties['searchname']]['emptytext'];
$emtpytext = empty($emtpytext) ? 'all' : $emtpytext;
$options = array();
$options[] = array('combo_name' => $emtpytext, 'combo_id' => 'all');
$options[] = array('combo_name' => '=', 'combo_id' => '=');
$options[] = array('combo_name' => '<=', 'combo_id' => '<=');
$options[] = array('combo_name' => '>=', 'combo_id' => '>=');

$count = count($options);
return $this->outputArray($options, $count);