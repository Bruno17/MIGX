<?php
/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class modTemplateVarInputRenderTinymceWrapper extends modTemplateVarInputRender {
    public function process($value,array $params = array()) {
        $which_editor = $this->modx->getOption('which_editor',null,'');
        $this->setPlaceholder('which_editor',$which_editor);
        $tpl = $this->modx->getOption('tinymce_chunk',$params,'TinymceWrapperMIGX');
        $properties = array();
        $properties['tv_id'] = $this->tv->get('id');
        
        $this->setPlaceholder('tinymce_chunk',$this->modx->getChunk($tpl,$properties));
        
    }
    public function getTemplate() {
        $path = 'components/migx/';
        $corePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . $path);
        return $corePath . 'elements/tv/tinymcewrapper.tpl';  
    }
}
return 'modTemplateVarInputRenderTinymceWrapper';