<?php

$pathTpl = $modx->getOption('pathTpl', $scriptProperties, '');
$docid = $modx->getOption('docid', $scriptProperties, '');
$createfolder = $modx->getOption('createFolder', $scriptProperties, false);
$path = '';
$createpath = false;

if (empty($docid) && $modx->getPlaceholder('docid')) {
    // placeholder was set by some script
    $docid = $modx->getPlaceholder('docid');
}
if (empty($docid)) {

    if (is_Object($modx->resource)) {
        //on frontend
        $docid = $modx->resource->get('id');
    } else {
        //on backend
        $createpath = $createfolder;
        $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($parsedUrl['query'], $parsedQuery);

        if (isset($parsedQuery['amp;id'])) {
            $docid = $parsedQuery['amp;id'];
        } elseif (isset($parsedQuery['id'])) {
            $docid = $parsedQuery['id'];
        }
    }
}

if ($resource = $modx->getObject('modResource', $docid)) {
    $path = str_replace('{id}', $docid, $pathTpl);
    $path = str_replace('{pagetitle}', $resource->get('pagetitle'), $pathTpl);
    
    $fullpath = $modx->getOption('base_path') . $path;

    if ($createpath && !file_exists($fullpath)) {
        mkdir($fullpath, 0755, true);
    }

    return $path;
}
