<?php
/**
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class modTemplateVarInputRenderRedactor extends modTemplateVarInputRender {
    public function process($value,array $params = array()) {
        
        $inputTVid = $this->modx->getOption('inputTVid',$params,0);
        $which_editor = $this->modx->getOption('which_editor',null,'');
        $this->setPlaceholder('which_editor',$which_editor);
        
        // Get Redactor class
        $corePath = $this->modx->getOption('redactor.core_path', null, $this->modx->getOption('core_path').'components/redactor/');
        $redactor = $this->modx->getService('redactor', 'Redactor', $corePath . 'model/redactor/');
        if (!($redactor instanceof Redactor)) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, '[Redactor/MIGX] Error loading Redactor for use in MIGX from ' . $corePath);
            return;
        }

        if (!empty($params['buttons']) && !is_array($params['buttons'])  ){
            $params['buttons'] = explode(',',$params['buttons']);
        }

        /**
         * Get the Redactor configuration on system level, and cleverly merge it with
         * the TV configuration for inheritance and default values.
         */
        $systemOptions = $redactor->getGlobalOptions();
        foreach ($params as $key => $value) {
            if (($value == 'inherit' || $value == '') && isset($systemOptions[$key])) {
                $params[$key] = $systemOptions[$key];
            } 

            $systemValue = (isset($systemOptions[$key])) ? $systemOptions[$key] : null;
            $params[$key] = $this->_fixValueType($params[$key], $systemValue);
        }
        $params = array_merge($systemOptions, $params);
        $params['imageGetJson'] = $params['imageGetJson'].'&tv=' . $inputTVid;
        $params['fileGetJson'] = $params['fileGetJson'].'&tv=' . $inputTVid;
        $params['imageUpload'] = $params['imageUpload'].'&tv=' . $inputTVid;
        if(isset($params['clipboardUploadUrl'])) $params['clipboardUploadUrl'] = $params['clipboardUploadUrl'].'&tv=' . $inputTVid;
        $params['fileUpload'] = $params['fileUpload'].'&tv=' . $inputTVid;
        $params['plugins'] = array(); // wipe it clean and readd
        if(isset($params['buttonFullScreen'])) $params['plugins'][] = "fullscreen";
		if(isset($params['clipsJson']) && !empty($params['clipsJson'])) $params['plugins'][] = "clips";
		if(isset($params['stylesJson']) && !empty($params['stylesJson'])) $params['plugins'][] = "styles";
        /**
         * Set placeholders and register CSS/JS files.
         */
        if (!empty($params['lang']) && ($params['lang'] != 'en')) {
            $this->setPlaceholder('langFile', '<script type="text/javascript" src="' . $redactor->config['assetsUrl'] . 'lang/' . $params['lang'] . '.js"></script>');
        }

        $this->setPlaceholder('assetsUrl', $redactor->config['assetsUrl']);
        $this->setPlaceholder('params', $params);
        $this->setPlaceholder('params_json', $this->modx->toJSON($params));
        //$this->registerStuff();

        //return parent::render($value, $params);
    }
    public function getTemplate() {
        $corePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . 'components/migx/');
        return $corePath . 'elements/tv/redactor.tpl';  
    }
    
    /**
     * Makes sure boolean values are boolean, and that array values are exploded properly.
     *
     * @param $value
     * @param $systemValue
     *
     * @return array|bool
     */
    protected function _fixValueType($value, $systemValue) {
        switch (gettype($systemValue)) {
            case 'boolean':
                $value = (bool)$value;
                break;
            case 'array':
                if (!is_array($value)) {
                    $value = $this->redactor->explode($value);
                }
                break;
        }
        return $value;
    }    
    
}
return 'modTemplateVarInputRenderRedactor';