<?php

/**
 * @var modX $this->modx
 * @var modTemplateVar $this
 * @var array $params
 *
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */

global $modx; 
$path = 'components/migx/';
$migxPath = $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . $path);
include_once ($migxPath . 'model/migx/migxinputrender.class.php');

class modTemplateVarInputRenderMigx extends migxInputRender {
    public function process($value, array $params = array()) {
        
        parent::process($value, $params);

    }


    public function getTemplate() {
        $path = 'components/migx/';
        $corePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . $path);
        return $corePath . 'elements/tv/migx.tpl';
    }
}
return 'modTemplateVarInputRenderMigx';
