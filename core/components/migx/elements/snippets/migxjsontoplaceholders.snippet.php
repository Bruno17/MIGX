<?php
$value = $modx->getOption('value',$scriptProperties,'');
$prefix = $modx->getOption('prefix',$scriptProperties,'');

//$modx->setPlaceholders($modx->fromJson($value),$prefix,'',true);

$values = json_decode($value, true);

$it = new RecursiveIteratorIterator(new RecursiveArrayIterator($values));

if (is_array($values)){
    foreach ($it as $key => $value){
        $value = $value == null ? '' : $value;
        $modx->setPlaceholder($prefix . $key, $value);
    }
}