<?php

//if (!$modx->hasPermission('quip.thread_list')) return $modx->error->failure($modx->lexicon('access_denied'));

$config = $modx->migx->customconfigs;

$tvname = $config['gridfilters'][$scriptProperties['searchname']]['combotextfield'];
$rows = array();
if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {
    $options = $tv->parseInputOptions($tv->processBindings($tv->get('elements')));
    foreach ($options as $option){
        $option = explode('==',$option);
        $opt['combo_id'] = $option[0];
        $opt['combo_name'] = isset($option[1]) ? $option[1] : $option[0];
        $rows[] = $opt;
    }
}

$emtpytext = $config['gridfilters'][$scriptProperties['searchname']]['emptytext'];
$emtpytext = empty($emtpytext) ? 'all' : $emtpytext;

$rows = array_merge(array(array('combo_id' => 'all', 'combo_name' => $emtpytext)), $rows);

return $this->outputArray($rows, $count);
