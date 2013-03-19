<?php
/**
 * @name migxResourceMediaPath
 * @description Dynamically calculates the upload path for a given resource
 * 
 * This Snippet is meant to dynamically calculate your baseBath attribute
 * for custom Media Sources.  This is useful if you wish to shepard uploaded
 * images to a folder dedicated to a given resource.  E.g. page 123 would 
 * have its own images that page 456 could not reference.
 *
 * USAGE:
 * [[migxResourceMediaPath? &pathTpl=`assets/businesses/{id}/`]]
 * [[migxResourceMediaPath? &pathTpl=`assets/test/{breadcrumb}`]]
 * [[migxResourceMediaPath? &pathTpl=`assets/test/{breadcrumb}` &breadcrumbdepth=`2`]]
 *
 * PARAMETERS
 * &pathTpl string formatting string specifying the file path. 
 *		Relative to MODX base_path
 *		Available placeholders: {id}, {pagetitle}, {parent}
 * &docid (optional) integer page id
 * &createfolder (optional) boolean whether or not to create
 */
$pathTpl = $modx->getOption('pathTpl', $scriptProperties, '');
$docid = $modx->getOption('docid', $scriptProperties, '');
$createfolder = $modx->getOption('createFolder', $scriptProperties, false);
$path = '';
$createpath = false;

if (empty($pathTpl)) {
    $modx->log(MODX_LOG_LEVEL_ERROR, '[migxResourceMediaPath]: pathTpl not specified.');
    return;
}

if (empty($docid) && $modx->getPlaceholder('docid')) {
    // placeholder was set by some script
    // warning: the parser may not render placeholders, e.g. &docid=`[[*parent]]` may fail
    $docid = $modx->getPlaceholder('docid');
}
if (empty($docid)) {

    //on frontend
    if (is_object($modx->resource)) {
        $docid = $modx->resource->get('id');
    }
    //on backend
    else {
        $createpath = $createfolder;
        // We do this to read the &id param from an Ajax request
        $parsedUrl = parse_url($_SERVER['HTTP_REFERER']);
        parse_str($parsedUrl['query'], $parsedQuery);

        if (isset($parsedQuery['amp;id'])) {
            $docid = (int)$parsedQuery['amp;id'];
        } elseif (isset($parsedQuery['id'])) {
            $docid = (int)$parsedQuery['id'];
        }
    }
}

if (empty($docid)) {
    $modx->log(MODX_LOG_LEVEL_ERROR, '[migxResourceMediaPath]: docid could not be determined.');
    return;
}

if ($resource = $modx->getObject('modResource', $docid)) {
    $path = $pathTpl;
    $ultimateParent = '';
    if (strstr($path, '{breadcrumb}') || strstr($path, '{ultimateparent}')) {
        $parentids = $modx->getParentIds($docid);
        $breadcrumbdepth = $modx->getOption('breadcrumbdepth', $scriptProperties, count($parentids));
        $breadcrumbdepth = $breadcrumbdepth > count($parentids) ? count($parentids) : $breadcrumbdepth;
        if (count($parentids) > 1) {
            $parentids = array_reverse($parentids);
            $parentids[] = $docid;
            $ultimateParent = $parentids[1];
        } else {
            $ultimateParent = $docid;
            $parentids = array();
            $parentids[] = $docid;
        }
    }

    if (strstr($path, '{breadcrumb}')) {
        $breadcrumbpath = '';
        for ($i = 1; $i <= $breadcrumbdepth; $i++) {
           $breadcrumbpath .= $parentids[$i].'/';                
        }
        $path = str_replace('{breadcrumb}', $breadcrumbpath, $path);

    } else {
        $path = str_replace('{id}', $docid, $path);
        $path = str_replace('{pagetitle}', $resource->get('pagetitle'), $path);
				$path = str_replace('{alias}', $resource->get('alias'), $path);
        $path = str_replace('{parent}', $resource->get('parent'), $path);
        $path = str_replace('{ultimateparent}', $ultimateParent, $path);
    }

    $fullpath = $modx->getOption('base_path') . $path;

    if ($createpath && !file_exists($fullpath)) {
        if (!@mkdir($fullpath, 0755, true)) {
            $modx->log(MODX_LOG_LEVEL_ERROR, sprintf('[migxResourceMediaPath]: could not create directory %s).', $fullpath));
        }
    }

    return $path;
} else {
    $modx->log(MODX_LOG_LEVEL_ERROR, sprintf('[migxResourceMediaPath]: resource not found (page id %s).', $docid));
    return;
}

/*EOF*/