<?php
/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class modTemplateVarInputRenderHiddenFix extends modTemplateVarInputRender {

    public function getTemplate() {
        $path = 'components/migx/';
        $corePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . $path);
        return $corePath . 'elements/tv/hiddenfix.tpl';  
    }
}
return 'modTemplateVarInputRenderHiddenFix';