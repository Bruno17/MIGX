<?php
/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class modTemplateVarInputRenderRedactor extends modTemplateVarInputRender {
    public function process($value,array $params = array()) {
    }
    public function getTemplate() {
        $corePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . 'components/migx/');
        return $corePath . 'elements/tv/redactor.tpl';  
    }
}
return 'modTemplateVarInputRenderRedactor';
