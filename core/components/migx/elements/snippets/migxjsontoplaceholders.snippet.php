<?php
$value = $modx->getOption('value',$scriptProperties,'');
$prefix = $modx->getOption('prefix',$scriptProperties,'');

//$modx->setPlaceholders($modx->fromJson($value),$prefix,'',true);

$values = $modx->fromJson($value);
if (is_array($values)){
    foreach ($values as $key => $value){
        $value = $value == null ? '' : $value;
        $modx->setPlaceholder($prefix . $key, $value);
    }
}