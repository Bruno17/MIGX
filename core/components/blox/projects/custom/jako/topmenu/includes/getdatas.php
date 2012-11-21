<?php

$this->bloxconfig['parents'] = (string )intval($this->bloxconfig['startId']);
$this->bloxconfig['depth'] = (string )intval($this->bloxconfig['level']);
$this->bloxconfig['limit'] = '1000';
$this->bloxconfig['context'] = $modx->context->key;
$this->bloxconfig['where'] = $modx->fromJson('{"hidemenu:!=": "1"}');
$this->bloxconfig['activeid'] = $modx->getOption('activeid', $this->bloxconfig, $modx->resource->get('id'));

if (class_exists('bloxhelpers')) {
    // Initialize class
    $helper = new bloxhelpers($this);
} else {
    echo 'bloxhelpers class not found';
}

/*
$parentIds = $modx->getParentIds($this->bloxconfig['activeid'], 10, array('context' => $this->bloxconfig['context']));
$parentIds = array_merge($parentIds, array($this->bloxconfig['activeid']));

$resources = $modx->cacheManager->get('bloX.' . $this->bloxconfig['configs'] . '.resources');
if (!$resources) {
    $resources = $helper->getResources();
}
$modx->cacheManager->set('bloX.' . $this->bloxconfig['configs'] . '.resources', $resources, 7200);
*/
$resources = $helper->getResources();

$firstlevel = $modx->getChildIds($this->bloxconfig['parents'], 1, array('context' => $this->bloxconfig['context']));

$rows = $helper->buildMenu($firstlevel, $this->bloxconfig['depth'] - 1, $resources, $parentIds);
$template = $helper->buildMenuTemplate($firstlevel, $this->bloxconfig['depth'] - 1, $resources);

$bloxdatas['menu'] = $template;
$bloxdatas['level1'] = $rows;

//echo '<pre>' . print_r($this->bloxconfig, true) . '</pre>';
//echo '---------------------------------------';
//	die('<pre>' . print_r($bloxdatas, true) . '</pre>');


?>
