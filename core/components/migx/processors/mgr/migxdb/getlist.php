<?php
$task=$modx->migx->getTask();
$getObject= dirname(dirname(__FILE__)) . '/' . $task . '/' . basename(__FILE__);
if (file_exists($getObject)) {
    $overridden= include_once ($getObject);
    if ($overridden !== false) {
       // return;
    }
}
return $this->outputArray($rows,$count);

