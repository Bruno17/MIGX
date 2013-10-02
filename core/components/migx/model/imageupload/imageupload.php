<?php

//$config = $modx->migx->customconfigs;
$resource_id = $modx->getOption('resource_id', $scriptProperties, '');
$tvname = $modx->getOption('tv_name', $scriptProperties, '');

//$uniqueFilenames = $modx->getOption('uniqueFilenames', $config, false);

if ($resource = $modx->getObject('modResource', $resource_id)) {
    $wctx = $resource->get('context_key');
}

if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {
    $source = $tv->getSource($wctx, false);
}

if ($source) {

} else {
    $sourceid = $modx->getOption('source', $_REQUEST, '');
    /**
     *  * @var modMediaSource $source */
    $modx->loadClass('sources.modMediaSource');
    $source = modMediaSource::getDefaultSource($modx, $sourceid);
    if (!$source->getWorkingContext()) {
        return $modx->lexicon('permission_denied');
    }
    $source->setRequestProperties($_REQUEST);
}

if (!($source instanceof modMediaSource)) {
    return 'mediasource couldn not be loaded';    
}    
    $modx->setPlaceholder('docid', $resource_id);
    $source->initialize();
    $sourceProperties = $source->getPropertyList();
    $dirTree = $modx->getOption('dirtree', $_REQUEST, '');
    
    $modx->setPlaceholder('debugSourceProperties', '<pre>' . print_r($sourceProperties, 1) . '</pre>');
    $modx->toPlaceholders($sourceProperties, 'sourceProperty');
    $basePath = $modx->getOption('basePath', $sourceProperties);
    $basePath = $basePath . $dirTree;
    $baseUrl = $modx->getOption('baseUrl', $sourceProperties);
    $baseUrl = $baseUrl . $dirTree;
    $allowedExtensions = $modx->getOption('allowedFileTypes', $sourceProperties, '');
    $allowedExtensions = empty($allowedExtensions) ? 'jpg,jpeg,png,gif' : $allowedExtensions;
    $maxFilesizeMb = $modx->getOption('maxFilesizeMb', $sourceProperties, '8');
    $maxFiles = $modx->getOption('maxFiles', $sourceProperties, '10');
    $thumbX = $modx->getOption('thumbX', $sourceProperties, '100');
    $thumbY = $modx->getOption('thumbY', $sourceProperties, '100');
    $thumbscontainer = $modx->getOption('thumbscontainer', $sourceProperties, 'thumbs/');
    $imageExtensions = $modx->getOption('imageExtensions', $sourceProperties, 'jpg,jpeg,png,gif');
    $imageExtensions = explode(',', $imageExtensions);
    
    



//$baseUrl = $modx->getOption('site_url') . $baseUrl;

define('AIU_BASE_PATH', $modx->getOption('core_path') . 'components/migx/model/imageupload/');
define('AIU_CACHE_PATH', $basePath);


include_once AIU_BASE_PATH . 'includes/fileuploader/fileuploader.class.php';


/***************************/
/* Set/Read Snippet Params */
/***************************/

// default: &language=`english` &allowedExtensions=`jpg,jpeg,png,gif` &maxFilesizeMb=`8` &uid=`site-specific` &maxFiles=`3` &thumbX=`100` &=`100` &mode=`form` &ajaxId=`0`

$lang = $modx->getOption('manager_language');
// comma separated list of valid extensions

$formUid = isset($uid) ? $uid : md5($modx->config['site_url']);
$ajaxId = isset($ajaxId) ? intval($ajaxId) : 0;
$ajaxUrl = isset($ajaxUrl) ? $ajaxUrl : '';
$addJquery = isset($addJquery) ? intval($addJquery) : 1;
$addJscript = isset($addJscript) ? intval($addJscript) : 1;
$addCss = isset($addCss) ? intval($addJquery) : 1;
$show_clearbutton = false;
$show_oldfiles_deletebutton = 1;

if (!function_exists('includeFile')) {
    function includeFile($name, $type = 'config', $defaultName = 'default', $fileType = '.inc.php') {
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


include (includeFile($lang, 'language', 'en'));
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
        $scriptSettings = file_get_contents(includeFile('script' . ucfirst($lang), 'template', 'script', '.html'));
        $placeholder = array();
        $placeholder['ajaxId'] = !empty($ajaxUrl) ? $ajaxUrl : $modx->makeUrl($ajaxId);
        $placeholder['dropArea'] = $language['dropArea'];
        $placeholder['uploadButton'] = $language['uploadButton'];
        $placeholder['clearButton'] = $show_clearbutton ? '<div class="qq-clear-button">' . $language['clearButton'] . '</div>' : '';
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
    $output = file_get_contents(includeFile('uploadSection' . ucfirst($lang), 'template', 'uploadSection', '.html'));
    //$imageTpl = file_get_contents(includeFile('image' . ucfirst($language), 'template', 'image', '.html'));
    $imageTpl = $modx->migx->config['corePath'] . '/model/imageupload/templates/image.template.html';
    $fileTpl = $modx->migx->config['corePath'] . '/model/imageupload/templates/file.template.html';
    $imageList = array();
    $fileList = array();
    $placeholder = array();
    $placeholder['thumbX'] = $thumbX;
    $placeholder['thumbY'] = $thumbY;
    $placeholder['deleteButton'] = $show_oldfiles_deletebutton ? '<div class="delete-button"><a>' . $language['deleteButton'] . '</a></div>' : '';

    $files = $source->getObjectsInContainer($dirTree);
    $i = 1;
    foreach ($files as $file) {
        if (isset($limit) && $i > $limit) {
            break;
        }
        $thumbpath = str_replace($basePath, $basePath . $thumbscontainer, $file['pathname']);

        if (file_exists($thumbpath)) {
            $file['fullRelativeUrl'] = str_replace($baseUrl, $baseUrl . $thumbscontainer, $file['fullRelativeUrl']);
        }

        //$imageElement = $imageTpl;
        $placeholder = array_merge($placeholder, $file);


        /*
        foreach ($placeholder as $key => $value) {
        $imageElement = str_replace('[+' . $key . '+]', $value, $imageElement);
        }
        */


        if (in_array($file['ext'], $imageExtensions)) {
            $imageList[] = $modx->migx->parseChunk($imageTpl, $placeholder);
        } else {
            $fileList[] = $modx->migx->parseChunk($fileTpl, $placeholder);
        }

        $i++;
    }

    //echo '<pre>' . print_r($files,1) .'</pre>';

    $output = str_replace('[+images+]', implode("\r\n", $imageList), $output);
    $output = str_replace('[+files+]', implode("\r\n", $fileList), $output);
    $output = str_replace('[+uid+]', $formUid, $output);
    return $output;
    break;
} else {
    return;
}

?>