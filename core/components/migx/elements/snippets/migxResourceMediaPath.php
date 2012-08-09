<?php

$pathTpl = $modx->getOption('pathTpl', $scriptProperties, '');
$docid = $modx->getOption('docid', $scriptProperties, '');

if (empty($docid) && $modx->getPlaceholder('docid')) {
    $docid = $modx->getPlaceholder('docid');
}
if (empty($docid)) {

    if (is_Object($modx->resource)) {
        $docid = $modx->resource->get('id');
    } else {

        $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($parsedUrl['query'], $parsedQuery);

        if (isset($parsedQuery['amp;id'])) {
            $docid = $parsedQuery['amp;id'];
        } elseif (isset($parsedQuery['id'])) {
            $docid = $parsedQuery['id'];
        }
    }
}


$path = str_replace('{id}', $docid, $pathTpl);
$fullpath = $modx->getOption('base_path') . $path;

if (!file_exists($fullpath)) {
    mkdir($fullpath, 0755, true);
}


return $path;