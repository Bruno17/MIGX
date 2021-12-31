<?php
class MigxFormController extends modManagerController {

    public function process(array $scriptProperties = array()) {
    
        $this->prepareLanguage(); 
        $tpl = $this->getTemplateFile();
        if ($this->isFailure) {
            $this->setPlaceholder('_e', $this->modx->error->failure($this->failureMessage));
            $content = $this->fetchTemplate('error.tpl');
        } else if (!empty($tpl)) {
            $content = $this->fetchTemplate($tpl);
        }
        
        $this->modx->migx->loadLang();
       
        return $this->modx->migx->replaceLang($content);        
        
    }
  
    public function loadCustomCssJs() {}
  
    public function checkPermissions() { return true;}
    
    public function getPageTitle() { return ''; }

    public function getTemplateFile() { 
        
        $miTVCorePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . 'components/migx/');
        return $miTVCorePath . 'templates/mgr/fields.tpl';

    }
}