<?php

$config = $modx->migx->customconfigs;
$emtpytext = $config['gridfilters'][$scriptProperties['searchname']]['emptytext'];
$emtpytext = empty($emtpytext) ? 'all' : $emtpytext;
$options = array();
$options[] = array('combo_name' => $emtpytext, 'combo_id' => 'all');
$options[] = array('combo_name' => 'yes', 'combo_id' => '1');
$options[] = array('combo_name' => 'no', 'combo_id' => '0');

$count = count($options);
return $this->outputArray($options, $count);