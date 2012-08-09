<?php

$cachepath = $modx->getOption('base_path') . 'assets/cache/AjaxImageUpload/';

//$config = $modx->migx->customconfigs;
$resource_id = $modx->getOption('resource_id', $scriptProperties, '');
$tvname = $modx->getOption('tv_name', $scriptProperties, '');

//$uniqueFilenames = $modx->getOption('uniqueFilenames', $config, false);

if ($resource = $modx->getObject('modResource', $resource_id)) {
    $wctx = $resource->get('context_key');
}

if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {
    if ($source = $tv->getSource($wctx, false)) {
        $modx->setPlaceholder('docid', $resource_id);
        $cachepath = str_replace('/./', '/', $source->prepareOutputUrl(''));
    }
}

$baseUrl = $modx->getOption('site_url') . str_replace($modx->getOption('base_path'), '', $cachepath);

define('AIU_BASE_PATH', $modx->getOption('core_path') . 'components/migx/model/imageupload/');
define('AIU_CACHE_PATH', $cachepath);


include_once AIU_BASE_PATH . 'includes/fileuploader/fileuploader.class.php';


/***************************/
/* Set/Read Snippet Params */
/***************************/

// default: &language=`english` &allowedExtensions=`jpg,jpeg,png,gif` &maxFilesizeMb=`8` &uid=`site-specific` &maxFiles=`3` &thumbX=`100` &=`100` &mode=`form` &ajaxId=`0`

$language = isset($language) ? $language : 'english';
// comma separated list of valid extensions
$allowedExtensions = isset($allowedExtensions) ? $allowedExtensions : 'jpg,jpeg,png,gif';
$maxFilesizeMb = isset($maxFilesizeMb) ? $maxFilesizeMb : '8';
$formUid = isset($uid) ? $uid : md5($modx->config['site_url']);
$maxFiles = isset($maxFiles) ? $maxFiles : '3';
$thumbX = isset($thumbX) ? $thumbX : '100';
$thumbY = isset($thumbY) ? $thumbY : '100';
$mode = isset($mode) ? $mode : 'form';
$ajaxId = isset($ajaxId) ? intval($ajaxId) : 0;
$ajaxUrl = isset($ajaxUrl) ? $ajaxUrl : '';
$addJquery = isset($addJquery) ? intval($addJquery) : 1;
$addJscript = isset($addJscript) ? intval($addJscript) : 1;
$addCss = isset($addCss) ? intval($addJquery) : 1;

if (!function_exists('includeFile')) {
    function includeFile($name, $type = 'config', $defaultName = 'default', $fileType = '.inc.php')
    {
        $folder = (substr($type, -1) != 'y') ? $type . 's/' : substr($folder, 0, -1) . 'ies/';
        $allowedConfigs = glob(AIU_BASE_PATH . $folder . '*.' . $type . $fileType);
        foreach ($allowedConfigs as $config) {
            $configs[] = preg_replace('=.*/' . $folder . '([^.]*).' . $type . $fileType . '=', '$1', $config);
        }
        if (in_array($name, $configs)) {
            return AIU_BASE_PATH . $folder . $name . '.' . $type . $fileType;
        } else {
            if (file_exists(AIU_BASE_PATH . $folder . $defaultName . '.' . $type . $fileType)) {
                return AIU_BASE_PATH . $folder . $defaultName . '.' . $type . $fileType;
            } else {
                $modx->messageQuit('Default AjaxImageUpload ' . $type . ' file "' . AIU_BASE_PATH . $folder . $defaultName . '.' . $type . '.inc.php" not found. Did you upload all snippet files?');
            }
        }
    }
}


include (includeFile($language, 'language', 'english'));
$allowedExtensions = explode(',', $allowedExtensions);
$sizeLimit = intval($maxFilesizeMb) * 1024 * 1024;


if ($ajaxId || $ajaxUrl) {
    if ($addJquery) {
        //$modx->regClientStartupScript('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
    }
    if ($addCss) {
        //$modx->regClientCSS('assets/components/AjaxImageUpload/AjaxImageUpload.css');
    }
    if ($addJscript) {
        $modx->regClientStartupScript('assets/components/AjaxImageUpload/fileuploader.js');
        $scriptSettings = file_get_contents(includeFile('script' . ucfirst($language), 'template', 'script', '.html'));
        $placeholder = array();
        $placeholder['ajaxId'] = !empty($ajaxUrl) ? $ajaxUrl : $modx->makeUrl($ajaxId);
        $placeholder['dropArea'] = $language['dropArea'];
        $placeholder['uploadButton'] = $language['uploadButton'];
        $placeholder['clearButton'] = $language['clearButton'];
        $placeholder['cancel'] = $language['cancel'];
        $placeholder['failed'] = $language['failed'];
        $placeholder['thumbX'] = $thumbX;
        $placeholder['thumbY'] = $thumbY;
        $placeholder['allowedExtensions'] = (count($allowedExtensions)) ? "'" . implode("', '", $allowedExtensions) . "'" : '[]';
        $placeholder['sizeLimit'] = $sizeLimit;
        $placeholder['uid'] = $formUid;
        $placeholder['typeError'] = $language['typeError'];
        $placeholder['sizeError'] = $language['sizeError'];
        $placeholder['minSizeError'] = $language['minSizeError'];
        $placeholder['emptyError'] = $language['emptyError'];
        $placeholder['onLeave'] = $language['onLeave'];
        $placeholder['deleteButton'] = '<div class="delete-button"><a>' . $language['deleteButton'] . '</a></div>';
        foreach ($placeholder as $key => $value) {
            $scriptSettings = str_replace('[+' . $key . '+]', $value, $scriptSettings);
        }

        $modx->setPlaceholder('scriptSettings', $scriptSettings);

        //$modx->regClientStartupScript($scriptSettings);
        //$modx->regClientStartupScript('assets/components/AjaxImageUpload/AjaxImageUpload.js');
    }
    $output = file_get_contents(includeFile('uploadSection' . ucfirst($language), 'template', 'uploadSection', '.html'));
    //$imageTpl = file_get_contents(includeFile('image' . ucfirst($language), 'template', 'image', '.html'));
    $tpl = $modx->migx->config['corePath'].'/model/imageupload/templates/image.template.html';
    $imageList = array();
    $placeholder = array();
    $placeholder['thumbX'] = $thumbX;
    $placeholder['thumbY'] = $thumbY;
    $placeholder['deleteButton'] = '';

    $source->initialize();
    $files = $source->getObjectsInContainer('thumbs');
    $i = 1;
    foreach ($files as $file) {
        if (isset($limit) && $i > $limit) {
            break;
        }
        //$imageElement = $imageTpl;
        $placeholder = array_merge($placeholder,$file);
        /*        
        foreach ($placeholder as $key => $value) {
            $imageElement = str_replace('[+' . $key . '+]', $value, $imageElement);
        }
        */
        $imageList[] = $modx->migx->parseChunk($tpl,$placeholder);
        $i++;
    }


    $output = str_replace('[+images+]', implode("\r\n", $imageList), $output);
    $output = str_replace('[+uid+]', $formUid, $output);
    return $output;
    break;
} else {
    return;
}

?>