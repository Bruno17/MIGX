<?php
/**
 * migxResizeOnUpload Plugin
 *
 * Events: OnFileManagerUpload
 * Author: Bruno Perner <b.perner@gmx.de>
 * Modified to read multiple configs from mediasource-property
 * 
 * First Author: Vasiliy Naumkin <bezumkin@yandex.ru>
 * Required: PhpThumbOf snippet for resizing images
 * 
 * Example: mediasource - property 'resizeConfig':
 * [{"alias":"origin","w":"500","h":"500","far":1},{"alias":"thumb","w":"150","h":"150","far":1}]
 */

if ($modx->event->name != 'OnFileManagerUpload') {
    return;
}


$file = $modx->event->params['files']['file'];
$directory = $modx->event->params['directory'];

if ($file['error'] != 0) {
    return;
}

$name = $file['name'];
//$extensions = explode(',', $modx->getOption('upload_images'));

$source = $modx->event->params['source'];

if ($source instanceof modMediaSource) {
    //$dirTree = $modx->getOption('dirtree', $_REQUEST, '');
    //$modx->setPlaceholder('docid', $resource_id);
    $source->initialize();
    $basePath = str_replace('/./', '/', $source->getBasePath());
    //$cachepath = $cachepath . $dirTree;
    $baseUrl = $modx->getOption('site_url') . $source->getBaseUrl();
    //$baseUrl = $baseUrl . $dirTree;
    $sourceProperties = $source->getPropertyList();

    //echo '<pre>' . print_r($sourceProperties, 1) . '</pre>';
    //$allowedExtensions = $modx->getOption('allowedFileTypes', $sourceProperties, '');
    //$allowedExtensions = empty($allowedExtensions) ? 'jpg,jpeg,png,gif' : $allowedExtensions;
    //$maxFilesizeMb = $modx->getOption('maxFilesizeMb', $sourceProperties, '8');
    //$maxFiles = $modx->getOption('maxFiles', $sourceProperties, '0');
    //$thumbX = $modx->getOption('thumbX', $sourceProperties, '100');
    //$thumbY = $modx->getOption('thumbY', $sourceProperties, '100');
    $resizeConfigs = $modx->getOption('resizeConfigs', $sourceProperties, '');
    $resizeConfigs = $modx->fromJson($resizeConfigs);
    $thumbscontainer = $modx->getOption('thumbscontainer', $sourceProperties, 'thumbs/');
    $imageExtensions = $modx->getOption('imageExtensions', $sourceProperties, 'jpg,jpeg,png,gif,JPG');
    $imageExtensions = explode(',', $imageExtensions);
    //$uniqueFilenames = $modx->getOption('uniqueFilenames', $sourceProperties, false);
    //$onImageUpload = $modx->getOption('onImageUpload', $sourceProperties, '');
    //$onImageRemove = $modx->getOption('onImageRemove', $sourceProperties, '');
    $cleanalias = $modx->getOption('cleanFilename', $sourceProperties, false);

}

if (is_array($resizeConfigs) && count($resizeConfigs) > 0) {
    foreach ($resizeConfigs as $rc) {
        if (isset($rc['alias'])) {
            $filePath = $basePath . $directory;
            $filePath = str_replace('//','/',$filePath);
            if ($rc['alias'] == 'origin') {
                $thumbPath = $filePath;
            } else {
                $thumbPath = $filePath . $rc['alias'] . '/';
                $permissions = octdec('0' . (int)($modx->getOption('new_folder_permissions', null, '755', true)));
                if (!@mkdir($thumbPath, $permissions, true)) {
                    $modx->log(MODX_LOG_LEVEL_ERROR, sprintf('[migxResourceMediaPath]: could not create directory %s).', $thumbPath));
                } else {
                    chmod($thumbPath, $permissions);
                }

            }


            $filename = $filePath . $name;
            $thumbname = $thumbPath . $name;
            $ext = substr(strrchr($name, '.'), 1);
            if (in_array($ext, $imageExtensions)) {
                $sizes = getimagesize($filename);
                echo $sizes[0]; 
                //$format = substr($sizes['mime'], 6);
                if ($sizes[0] > $rc['w'] || $sizes[1] > $rc['h']) {
                    if ($sizes[0] < $rc['w']) {
                        $rc['w'] = $sizes[0];
                    }
                    if ($sizes[1] < $rc['h']) {
                        $rc['h'] = $sizes[1];
                    }
                    $type = $sizes[0] > $sizes[1] ? 'landscape' : 'portrait';
                    if (isset($rc['far']) && $rc['far'] == '1' && isset($rc['w']) && isset($rc['h'])) {
                        if ($type = 'landscape') {
                            unset($rc['h']);
                        }else {
                            unset($rc['w']);
                        }
                    }

                    $options = '';
                    foreach ($rc as $k => $v) {
                        if ($k != 'alias') {
                            $options .= '&' . $k . '=' . $v;
                        }
                    }
                    $resized = $modx->runSnippet('phpthumbof', array('input' => $filePath . $name, 'options' => $options));
                    rename(MODX_BASE_PATH . substr($resized, 1), $thumbname);
                }
            }


        }
    }
}