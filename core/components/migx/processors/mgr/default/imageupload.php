<?php

$cachepath = $modx->getOption('base_path') . 'assets/cache/AjaxImageUpload/';

$config = $modx->migx->customconfigs;
$resource_id = $modx->getOption('resource_id', $scriptProperties, '');
$tvname = $modx->getOption('tv_name', $scriptProperties, '');

$uniqueFilenames = $modx->getOption('uniqueFilenames', $config, false);

if ($resource = $modx->getObject('modResource', $resource_id)) {
    $wctx = $resource->get('context_key');
}

if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {
    if ($source = $tv->getSource($wctx, false)) {
        $modx->setPlaceholder('docid', $resource_id);
        $source->initialize();
        $cachepath = str_replace('/./', '/', $source->getBasePath());
        $baseUrl = $modx->getOption('site_url') . $source->getBaseUrl();
    }
}


define('AIU_BASE_PATH', $modx->getOption('core_path') . 'components/AjaxImageUpload/');
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
$maxFiles = isset($maxFiles) ? $maxFiles : '20';
$thumbX = isset($thumbX) ? $thumbX : '100';
$thumbY = isset($thumbY) ? $thumbY : '100';
$thumbscontainer = 'thumbs/';


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


if (!file_exists(AIU_CACHE_PATH)) {
    mkdir(AIU_CACHE_PATH, 0755);
}
if (!file_exists(AIU_CACHE_PATH . 'thumbs/')) {
    mkdir(AIU_CACHE_PATH . 'thumbs/', 0755);
}


include (includeFile($language, 'language', 'english'));
$allowedExtensions = explode(',', $allowedExtensions);
$sizeLimit = intval($maxFilesizeMb) * 1024 * 1024;

include_once AIU_BASE_PATH . 'includes/PhpThumbFactory/ThumbLib.inc.php';
// delete uploaded images
if (isset($_GET['delete'])) {
    $result = array();
    $formUid = (isset($_GET['uid'])) ? htmlentities(trim($_GET['uid']), ENT_NOQUOTES) : $formUid;
    if (strtolower($_GET['delete']) == 'all') {
        // delete all uploaded files/thumbs & clean session
        if (is_array($_SESSION['AjaxImageUpload'][$formUid])) {
            foreach ($_SESSION['AjaxImageUpload'][$formUid] as $key => $file) {
                unlink($file['path'] . $file['uniqueName']);
                unlink($file['path'] . $file['thumbName']);
            }
        }
        $_SESSION['AjaxImageUpload'][$formUid] = array();
        $result['success'] = true;
        $result['session'] = $_SESSION['AjaxImageUpload'][$formUid];
    } else {
        // delete one uploaded file/thumb & remove session entry
        $file = $_GET['delete'];
        $success = $source->removeObject($file);

        if (empty($success)) {
            //file could not be removed, try to remove the thumb, if that succeeds set success to true
            $success = $source->removeObject($thumbscontainer . $file);
            if (empty($success)) {
                $errors = $source->getErrors();
                $error = array();
                foreach ($errors as $k => $msg) {
                    $error[] = $k . ':' . $msg;
                }
                $result['error'] = implode('', $error);
            } else {
                $result['success'] = true;
            }
        } else {
            $source->removeObject($thumbscontainer . $file);
            $result['success'] = true;
        }

        /*
        if (isset($_SESSION['AjaxImageUpload'][$formUid][$fileId])) {
        $file = $_SESSION['AjaxImageUpload'][$formUid][$fileId];
        unlink($file['path'] . $file['uniqueName']);
        unset($_SESSION['AjaxImageUpload'][$formUid][$fileId]);
        $result['success'] = true;
        $result['session'] = $_SESSION['AjaxImageUpload'][$formUid];
        } else {
        $result['error'] = sprintf($language['notFound'], $maxFiles);
        }
        */
    }
} else {
    // upload the image(s)
    $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
    $formUid = (isset($_GET['uid'])) ? htmlentities(trim($_GET['uid']), ENT_NOQUOTES) : $formUid;
    // to pass data through iframe you will need to encode all html tags
    $result = $uploader->handleUpload(AIU_CACHE_PATH, true, $language);
    // file successful uploaded
    if ($result['success']) {
        $originalName = $uploader->filename . '.' . $uploader->extension;
        $path = $uploader->path;
        // check if count of uploaded files are below max file count
        $files = $source->getObjectsInContainer('');
        if (count($files) < $maxFiles) {
            // create unique filename and unique thumbname

            if ($uniqueFilenames) {
                $uniqueName = md5($uploader->filename . time()) . '.' . $uploader->extension;
                //$thumbName = md5($uploader->filename . time() . '.thumb') . '.' . $uploader->extension;
            } else {
                $uniqueName = $uploader->filename . '.' . $uploader->extension;
                //$thumbName = $uploader->filename . '.thumb.' . $uploader->extension;
            }

            // generate thumbname
            $thumb = PhpThumbFactory::create($path . $originalName);
            $thumb->adaptiveResize($thumbX, $thumbY);
            $thumb->save($path . 'thumbs/' . $uniqueName);
            rename($path . $originalName, $path . $uniqueName);
            // fill session
            /*
            $session['originalName'] = $originalName;
            $session['uniqueName'] = $uniqueName;
            $session['thumbName'] = $thumbName;
            $session['path'] = $path;
            $session['base_url'] = $modx->getOption('assets_url') . $cachepath;

            $_SESSION['AjaxImageUpload'][$formUid][] = $session;
            */
            // prepare returned values (filename & fileid)
            $result['filename'] = $baseUrl . 'thumbs/' . $uniqueName;
            $result['fileid'] = end(array_keys($_SESSION['AjaxImageUpload'][$formUid]));
            $result['url'] = $uniqueName;
            
        } else {
            unset($result['success']);
            // error message
            $result['error'] = sprintf($language['maxFiles'], $maxFiles);
            // delete uploaded file
            unlink($path . $originalName);
        }
    }
}
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
exit;
