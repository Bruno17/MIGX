<?php

$tvname = $modx->getOption('tvname', $scriptProperties, '');

//[[getTvOptions? &tpl=`myChunk` &tvname=`myTv` &name=`cb1`]]

$opts = array();
$opts[]['name'] = 'all';

if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {
    $options = explode('||', $tv->get('elements'));

    foreach ($options as $option) {
        $opt = explode('==', $option);
        $value = count($opt) > 1 ? $opt[1] : $opt[0];
        $text = $opt[0];

        $opts[]['name'] = $text;
    }
}


$count = count($opts);
return $this->outputArray($opts, $count);
