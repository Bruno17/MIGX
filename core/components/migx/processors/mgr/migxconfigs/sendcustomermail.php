<?php

//$cm = $modx->getService('couponman','CouponMan',$modx->getOption('couponman.core_path',null,$modx->getOption('core_path').'components/couponman/').'model/couponman/',$scriptProperties);

if (empty($scriptProperties['object_id'])){

	return $modx->error->failure($modx->lexicon('quip.thread_err_ns'));

} 


include_once $modx->getOption('fakturax.core_path',null,$modx->getOption('core_path').'components/fakturax/model/fakturax/').'fakturax.class.php';
$properties = array();
$fx = new FakturaX($modx,$scriptProperties);

if (!($fx instanceof FakturaX)) return '';

$params = $modx->fromJson($modx->runSnippet('fxGetSettings'));
$params['status']='berechnet';
$params['type']='Rechnung';
$params['mode']=$scriptProperties['mode'];
$fx->sendStatusEmail(array('id'=>$scriptProperties['object_id']), true, $params); 

return $modx->error->success();   
