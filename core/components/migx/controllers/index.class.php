<?php

require_once dirname(dirname(__file__)) . '/model/migx/migx.class.php';
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
    public function loadCustomCssJs()
    {

        $useEditor = $this->modx->getOption('use_editor', null, false);
        $whichEditor = $this->modx->getOption('which_editor', null, '');

        $plugin = $this->modx->getObject('modPlugin', array('name' => $whichEditor));


        /* OnRichTextEditorInit */
        if ($useEditor && $whichEditor == 'TinyMCE')
        {
            
            $tinyproperties = $plugin->getProperties();
            //$tinyUrl = $this ->config['jsUrl'] . 'tinymce/';
            //require_once $xdbedit->config['modelPath'] . 'tinymce/tinymce.class.php';
            //$tiny = new TinyMCE($modx, $tinyproperties, $tinyUrl);
            require_once $this->modx->getOption('tiny.core_path', null, $this->modx->getOption('core_path') . 'components/tinymce/') . 'tinymce.class.php';
            
            
            $tiny = new TinyMCE($this->modx);
            
           
            
            /*
            if (isset($forfrontend))
            {
            $def = $modx->getOption('cultureKey', null, $modx->getOption('manager_language', null, 'en'));
            $tiny->properties['language'] = $modx->getOption('fe_editor_lang', array(), $def);
            $tiny->properties['frontend'] = true;
            unset($def);
            }
            */
            if (isset($forfrontend) || $this->modx->context->get('key') != 'mgr')
            {
                $def = $this->modx->getOption('cultureKey', null, $this->modx->getOption('manager_language', null, 'en'));
                $tiny->properties['language'] = $this->modx->getOption('fe_editor_lang', array(), $def);
                $tiny->properties['frontend'] = true;
                unset($def);
            }
            /* commenting these out as it causes problems with richtext tvs */
            //if (isset($scriptProperties['resource']) && !$resource->get('richtext')) return;
            //if (!isset($scriptProperties['resource']) && !$modx->getOption('richtext_default',null,false)) return;
            $tiny->setProperties($tinyproperties);
            $html = $tiny->initialize();

            //$modx->event->output($html);
            //unset($html);
        }
        /* OnRichTextBrowserInit */
        if ($useEditor && $whichEditor == 'TinyMCE')
        {
            //$modx->regClientStartupScript($tiny->config['assetsUrl'].'jscripts/tiny_mce/tiny_mce_popup.js');
            /*
            $modx->regClientStartupScript($tiny->config['assetsUrl'] . 'jscripts/tiny_mce/langs/' . $tiny->properties['language'] . '.js');
            $modx->regClientStartupScript($tiny->config['assetsUrl'] . 'tiny.browser.js');
            */
            //$modx->event->output('Tiny.browserCallback');
            $inRevo20 = (boolean)version_compare($this->modx->version['full_version'],'2.1.0-rc1','<');
            $this->modx->getVersionData();
            $source = $this->modx->getOption('default_media_source',null,1);
            
            $this->addHtml('<script type="text/javascript">var inRevo20 = '.($inRevo20 ? 1 : 0).';MODx.source = "'.$source.'";</script>');
            
            //$this->addLastJavascript($tiny->config['assetsUrl'].'jscripts/tiny_mce/tiny_mce_popup.js');
            if (file_exists($tiny->config['assetsPath'].'jscripts/tiny_mce/langs/'.$tiny->properties['language'].'.js')) {
                $this->addLastJavascript($tiny->config['assetsUrl'].'jscripts/tiny_mce/langs/'.$tiny->properties['language'].'.js');
            } else {
                $this->addLastJavascript($tiny->config['assetsUrl'].'jscripts/tiny_mce/langs/en.js');
            }
            $this->addLastJavascript($tiny->config['assetsUrl'].'tiny.browser.js');            
            
        }

        
        $this->addJavascript($this->modx->getOption('manager_url') . 'assets/modext/util/datetime.js');
        $this->addJavascript($this->modx->getOption('manager_url') . 'assets/modext/widgets/element/modx.panel.tv.renders.js');

        //$panelJs = $this->fetchTemplate($this->migx->config['templatesPath'].'mgr/formpanel.tpl');

        $this->addHtml('<script type="text/javascript">' . $this->panelJs . '</script>');
        $this->addLastJavascript($this->migx->config['jsUrl'] . 'mgr/sections/index.js');
        

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
