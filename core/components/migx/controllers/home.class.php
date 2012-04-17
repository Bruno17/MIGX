<?php
class MigxHomeManagerController extends MigxManagerController {
    public function process(array $scriptProperties = array()) {
        
        $tv = '';
        $this->migx->prepareGrid($params,$this,$tv);         
    
    }
    public function getPageTitle() { return $this->modx->lexicon('migx'); }
    public function loadCustomCssJs() {
        //$this->addJavascript($this->migx->config['jsUrl'].'mgr/widgets/doodles.grid.js');
        //$this->addJavascript($this->migx->config['jsUrl'].'mgr/widgets/home.panel.js');
        
        //$panelJs = $this->fetchTemplate($this->migx->config['templatesPath'].'mgr/formpanel.tpl');
        $panelJs = $this->fetchTemplate($this->migx->config['templatesPath'].'mgr/gridpanel.tpl');  
        $this->addHtml('<script type="text/javascript">'.$panelJs.'</script>');        
        $this->addLastJavascript($this->migx->config['jsUrl'].'mgr/sections/index.js');
    }
    public function getTemplateFile() { return $this->migx->config['templatesPath'].'mgr/home.tpl'; }
}