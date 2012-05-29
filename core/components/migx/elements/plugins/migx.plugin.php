<?php
$corePath = $modx->getOption('migx.core_path',null,$modx->getOption('core_path').'components/migx/');
$assetsUrl = $modx->getOption('migx.assets_url', null, $modx->getOption('assets_url') . 'components/migx/');
switch ($modx->event->name) {
    case 'OnTVInputRenderList':
        $modx->event->output($corePath.'elements/tv/input/');
        break;
    case 'OnTVInputPropertiesList':
        $modx->event->output($corePath.'elements/tv/inputoptions/');
        break;

        case 'OnDocFormPrerender':
        $modx->controller->addCss($assetsUrl.'css/mgr.css');
        break; 
 
    /*          
    case 'OnTVOutputRenderList':
        $modx->event->output($corePath.'elements/tv/output/');
        break;
    case 'OnTVOutputRenderPropertiesList':
        $modx->event->output($corePath.'elements/tv/properties/');
        break;
    
    case 'OnDocFormPrerender':
        $assetsUrl = $modx->getOption('colorpicker.assets_url',null,$modx->getOption('assets_url').'components/colorpicker/'); 
        $modx->regClientStartupHTMLBlock('<script type="text/javascript">
        Ext.onReady(function() {
            
        });
        </script>');
        $modx->regClientStartupScript($assetsUrl.'sources/ColorPicker.js');
        $modx->regClientStartupScript($assetsUrl.'sources/ColorMenu.js');
        $modx->regClientStartupScript($assetsUrl.'sources/ColorPickerField.js');		
        $modx->regClientCSS($assetsUrl.'resources/css/colorpicker.css');
        break;
     */
}
return;