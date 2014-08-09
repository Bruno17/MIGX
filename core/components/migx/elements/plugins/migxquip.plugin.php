<?php
$quipCorePath = $modx->getOption('quip.core_path', null, $modx->getOption('core_path') . 'components/quip/');
//$assetsUrl = $modx->getOption('migx.assets_url', null, $modx->getOption('assets_url') . 'components/migx/');
switch ($modx->event->name)
{

    case 'OnDocFormPrerender':

        
        require_once $quipCorePath . 'model/quip/quip.class.php';
        $modx->quip = new Quip($modx);

        $modx->lexicon->load('quip:default');
        $quipconfig = $modx->toJson($modx->quip->config);
        
        $js = "
        Quip.config = Ext.util.JSON.decode('{$quipconfig}');
        console.log(Quip);";

        //$modx->controller->addCss($assetsUrl . 'css/mgr.css');
        $modx->controller->addJavascript($modx->quip->config['jsUrl'].'quip.js');
        $modx->controller->addHtml('<script type="text/javascript">' . $js . '</script>');
        break;

}
return;