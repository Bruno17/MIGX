<?php

require_once dirname(dirname(dirname(__file__))) . '/model/migx/migx.class.php';
abstract class MigxManagerController extends modExtraManagerController {
    /**
     @var MIGX $migx */
    public $migx;
    public function initialize() {

        $this->migx = new MIGX($this->modx);
        $this->migx->config['cmptabs'] = $_REQUEST['configs'];

        $this->modx->migx = &$this->migx;

        $this->addCss($this->migx->config['cssUrl'] . 'mgr.css');
        $this->addJavascript($this->migx->config['jsUrl'] . 'mgr/migx.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Migx.config = ' . $this->modx->toJSON($this->migx->config) . ';
        });
        </script>');
        return parent::initialize();
    }
    public function getLanguageTopics() {
        return array('migx:default');
    }
    public function checkPermissions() {
        return true;
    }
}
class MigxIndexManagerController extends MigxManagerController {
    
    public function process(array $scriptProperties = array())
    {

        $tv = '';
        $this->migx->loadLang();
        $params = array();
        $this->panelJs = $this->migx->prepareCmpTabs($params, $this, $tv);

    }
    public function getPageTitle()
    {
        return $this->modx->lexicon('migx');
    }
    public function loadCustomCssJs() {

        $this->addJavascript($this->modx->getOption('manager_url') . 'assets/modext/util/datetime.js');
        $this->addJavascript($this->modx->getOption('manager_url') . 'assets/modext/widgets/element/modx.panel.tv.renders.js');

        //$panelJs = $this->fetchTemplate($this->migx->config['templatesPath'].'mgr/formpanel.tpl');

        $this->addHtml('<script type="text/javascript">' . $this->panelJs . '</script>');
        $this->addLastJavascript($this->migx->config['jsUrl'] . 'mgr/sections/index.js');
        
        $onRichTextEditorInit = $this->loadRichTextEditor();
        $this->addHtml($onRichTextEditorInit);        
    }
    
    /**
     * Initialize a RichText Editor, if set
     *
     * @return void
     */
    public function loadRichTextEditor() {
        /* register JS scripts */

        $rte = isset($this->scriptProperties['which_editor']) ? $this->scriptProperties['which_editor'] : $this->modx->getOption('which_editor', '', $this->modx->_userConfig);
        $this->setPlaceholder('which_editor', $rte);

        /* Set which RTE if not core */
        if ($this->modx->getOption('use_editor', false, $this->modx->_userConfig) && !empty($rte)) {
            /* invoke OnRichTextEditorRegister event */
            $textEditors = $this->modx->invokeEvent('OnRichTextEditorRegister');
            $this->setPlaceholder('text_editors', $textEditors);

            $this->rteFields = array('ta');
            $this->setPlaceholder('replace_richtexteditor', $this->rteFields);

            /* invoke OnRichTextEditorInit event */
            //$resourceId = $this->resource->get('id');
            $onRichTextEditorInit = $this->modx->invokeEvent('OnRichTextEditorInit', array(
                'editor' => $rte,
                'elements' => $this->rteFields,
                //'id' => $resourceId,
                //'resource' => &$this->resource,
                //'mode' => !empty($resourceId) ? modSystemEvent::MODE_UPD : modSystemEvent::MODE_NEW,
                ));
                
            if (is_array($onRichTextEditorInit)) {
                $onRichTextEditorInit = implode('', $onRichTextEditorInit);
                
                $this->setPlaceholder('onRichTextEditorInit', $onRichTextEditorInit);
                return $onRichTextEditorInit;
            }
        }
    }    
    
    
    public function getTemplateFile()
    {
        return $this->migx->config['templatesPath'] . 'mgr/home.tpl';
    }
    
    /*
    public static function getDefaultController() {
        echo 'home';
        return 'home';
    }
    */
}
