<?php

/**
 * menus transport file for MIGX extra
 *
 * Copyright 2013 by Bruno Perner b.perner@gmx.de
 * Created on 04-17-2014
 *
 * @package migx
 * @subpackage build
 */

if (!function_exists('stripPhpTags')) {
    function stripPhpTags($filename)
    {
        $o = file_get_contents($filename);
        $o = str_replace('<' . '?' . 'php', '', $o);
        $o = str_replace('?>', '', $o);
        $o = trim($o);
        return $o;
    }
}
/* @var $modx modX */
/* @var $sources array */
/* @var xPDOObject[] $menus */

$menus = array();

if (is_array($menuprops)) {
    $i = 1;
    foreach ($menuprops as $m_props) {
        $action = $modx->newObject('modAction');
        $action->fromArray(array(
            'namespace' => !empty($m_props['action.namespace']) ? $m_props['action.namespace'] : 'migx',
            'controller' => !empty($m_props['action.controller']) ? $m_props['action.controller'] : 'index',
            'haslayout' => !empty($m_props['action.haslayout']) ? $m_props['action.haslayout'] : 0,
            'lang_topics' => !empty($m_props['action.lang_topics']) ? $m_props['action.lang_topics'] : 'example:default',
            'assets' => !empty($m_props['action.assets']) ? $m_props['action.assets'] : '',
            'help_url' => !empty($m_props['action.help_url']) ? $m_props['action.help_url'] : '',
            'id' => $i,
            ), '', true, true);


        $menus[$i] = $modx->newObject('modMenu');
        $menus[$i]->fromArray(array(
            'text' => !empty($m_props['text']) ? $m_props['text'] : '',
            'parent' => !empty($m_props['parent']) ? $m_props['parent'] : '',
            'description' => !empty($m_props['description']) ? $m_props['description'] : '',
            'icon' => !empty($m_props['icon']) ? $m_props['icon'] : '',
            'menuindex' => !empty($m_props['menuindex']) ? $m_props['menuindex'] : 0,
            'params' => !empty($m_props['params']) ? $m_props['params'] : '',
            'handler' => !empty($m_props['handler']) ? $m_props['handler'] : '',
            'permissions' => !empty($m_props['permissions']) ? $m_props['permissions'] : '',
            ), '', true, true);

        $menus[$i]->addOne($action);
        $i++;
    }

}


return $menus;
