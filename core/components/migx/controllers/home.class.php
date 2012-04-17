<?php
class MigxHomeManagerController extends MigxManagerController {
    public function process(array $scriptProperties = array()) {
        
        $tv = '';
        $this->panelJs = $this->migx->prepareCmpTabs($params,$this,$tv);  
    
    }
    public function getPageTitle() { return $this->modx->lexicon('migx'); }
    public function loadCustomCssJs() {
        
        $this->addJavascript($this->modx->getOption('manager_url').'assets/modext/util/datetime.js');
        $this->addJavascript($this->modx->getOption('manager_url').'assets/modext/widgets/element/modx.panel.tv.renders.js');        
        
        //$panelJs = $this->fetchTemplate($this->migx->config['templatesPath'].'mgr/formpanel.tpl');
         
        $this->addHtml('<script type="text/javascript">'.$this->panelJs.'</script>');        
        $this->addLastJavascript($this->migx->config['jsUrl'].'mgr/sections/index.js');
    }
    public function getTemplateFile() { return $this->migx->config['templatesPath'].'mgr/home.tpl'; }
}