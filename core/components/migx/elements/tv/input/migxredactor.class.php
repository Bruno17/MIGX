<?php
/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class modTemplateVarInputRenderRedactor extends modTemplateVarInputRender {
    public function process($value,array $params = array()) {
        $which_editor = $this->modx->getOption('which_editor',null,'');
        $this->setPlaceholder('which_editor',$which_editor);

        // Get Redactor class
        $corePath = $this->modx->getOption('redactor.core_path', null, $this->modx->getOption('core_path').'components/redactor/');
        $redactor = $this->modx->getService('redactor', 'Redactor', $corePath . 'model/redactor/');
        if (!($redactor instanceof Redactor)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[Redactor/MIGX] Error loading Redactor for use in MIGX from ' . $corePath);
            return;
        }

        // Get Redactor HTML and assign it to a placeholder to load in the MIGX window.
        $this->setPlaceholder('redactor_html', $redactor->getHtml());
    }
    public function getTemplate() {
        $corePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . 'components/migx/');
        return $corePath . 'elements/tv/redactor.tpl';  
    }
}
return 'modTemplateVarInputRenderRedactor';