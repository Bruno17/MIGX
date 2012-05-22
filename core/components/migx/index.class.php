<?php
require_once dirname(__FILE__) . '/model/migx/migx.class.php';
abstract class MigxManagerController extends modExtraManagerController {
    /** @var MIGX $migx */
    public $migx;
    public function initialize() {
        $this->migx = new MIGX($this->modx);
        $this->migx->config['cmptabs'] = $_REQUEST['configs'];
 
        $this->modx->migx = & $this->migx;
 
        $this->addCss($this->migx->config['cssUrl'].'mgr.css');
        $this->addJavascript($this->migx->config['jsUrl'].'mgr/migx.js');
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            Migx.config = '.$this->modx->toJSON($this->migx->config).';
        });
        </script>');
        return parent::initialize();
    }
    public function getLanguageTopics() {
        return array('migx:default');
    }
    public function checkPermissions() { return true;}
}
class IndexManagerController extends MigxManagerController {
    public static function getDefaultController() { return 'home'; }
}