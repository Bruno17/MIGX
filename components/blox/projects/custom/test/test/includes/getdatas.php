<?php

$collection = $modx->getCollection('modResource');

foreach ($collection as $object){
    $bloxdatas['innerrows']['row'][]=$object->toArray();
}

