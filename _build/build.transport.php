<?php

/**
 * Important: You should almost never need to edit this file,
 * except to add components that it won't handle (e.g., permissions,
 * users, policies, policy templates, ACL entries, and Form
 * Customization rules), and most of those might better be handled
 * in a script resolver, which you can add without editing this file.
 *
 * Important note: MyComponent never updates this file, so any changes
 * you make will be permanent.
 *
 * Build Script for MyComponent extra
 *
 * Copyright 2012-2013 by Bob Ray <http://bobsguides.com>
 * Created on 10-23-2012
 *
 * MyComponent is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MyComponent is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MyComponent; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 * @subpackage build
 */

/**
 * This is the template for the build script, which creates the
 * transport.zip file for your extra.
 *

 */
/* See the tutorial at http://http://bobsguides.com/mycomponent-tutorial.html
* for more detailed information about using the package.
*/

/* config file must be retrieved in a class */
if (!class_exists('BuildHelper')) {
    class BuildHelper {

        public function __construct(&$modx) {
            /* @var $modx modX */
            $this->modx = &$modx;

        }

        public function getProps($configPath) {
            $properties = @include $configPath;
            return $properties;
        }

        public function sendLog($level, $message) {

            $msg = '';
            if ($level == MODX::LOG_LEVEL_ERROR) {
                $msg .= $this->modx->lexicon('mc_error') . ' -- ';
            }
            $msg .= $message;
            $msg .= "\n";
            if (php_sapi_name() != 'cli') {
                $msg = nl2br($msg);
            }
            echo $msg;
        }
    }
}

/* set start time */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);


/* Instantiate MODx -- if this require fails, check your
* _build/build.config.php file
*/
require_once dirname(dirname(__file__)) . '/_build/build.config.php';

if ((!isset($modx)) || (!$modx instanceof modX)) {
    require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
    $modx = new modX();
    $modx->initialize('mgr');
    $modx->getService('error', 'error.modError', '', '');
}

$modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

if (!defined('MODX_CORE_PATH')) {
    session_write_close();
    die('build.config.php is not correct');
}

@include dirname(__file__) . '/config/current.project.php';

if (!$currentProject) {
    session_write_close();
    die('Could not get current project');
}

$helper = new BuildHelper($modx);

$modx->lexicon->load('mycomponent:default');

$props = $helper->getProps(dirname(__file__) . '/config/' . $currentProject . '.config.php');

if (!is_array($props)) {
    session_write_close();
    die($modx->lexicon('mc_no_config_file'));
}

$criticalSettings = array(
    'packageNameLower',
    'packageName',
    'version',
    'release');

foreach ($criticalSettings as $setting) {
    if (!isset($setting)) {
        session_write_close();
        die($modx->lexicon('mc_critical_setting_not_set') . ': ' . $setting);
    }
}


if (strpos($props['packageNameLower'], '-') || strpos($props['packageNameLower'], ' ')) {
    die($modx->lexicon("mc_space_hyphen_warning"));
}
/* Set package info. These are initially set from the the the project config
* but feel free to hard-code them for future versions */

define('PKG_NAME', $props['packageName']);
define('PKG_NAME_LOWER', $props['packageNameLower']);
define('PKG_VERSION', $props['version']);
define('PKG_RELEASE', $props['release']);


/* define sources */
$root = dirname(dirname(__file__)) . '/';
$sources = array(
    'root' => $root,
    'build' => $root . '_build/',
    'config' => $root . '_build/config/',
    'utilities' => $root . '_build/utilities/',
    /* note that the next two must not have a trailing slash */
    'source_core' => $root . 'core/components/' . PKG_NAME_LOWER,
    'source_assets' => $root . 'assets/components/' . PKG_NAME_LOWER,
    'resolvers' => $root . '_build/resolvers/',
    'validators' => $root . '_build/validators/',
    'data' => $root . '_build/data/',
    'docs' => $root . 'core/components/' . PKG_NAME_LOWER . '/docs/',
    'install_options' => $root . '_build/install.options/',
    'packages' => $root . 'core/packages',
    /* no trailing slash */

    );
unset($root);


$categories = require_once $sources['build'] . 'config/categories.php';

if (empty($categories)) {
    die($modx->lexicon('no_categories'));
}

/* Set package options - you can set these manually, but it's
* recommended to let them be generated automatically
*/

$hasAssets = is_dir($sources['source_assets']);
/* Transfer the files in the assets dir. */
$hasCore = is_dir($sources['source_core']);
/* Transfer the files in the core dir. */

$hasContexts = file_exists($sources['data'] . 'transport.contexts.php');
$hasResources = file_exists($sources['data'] . 'transport.resources.php');
$hasValidators = is_dir($sources['build'] . 'validators');
/* Run a validators before installing anything */
$hasResolvers = is_dir($sources['build'] . 'resolvers');
$hasSetupOptions = is_dir($sources['data'] . 'install.options');
/* HTML/PHP script to interact with user */
//$hasMenu = file_exists($sources['data'] . 'transport.menus.php'); /* Add items to the MODx Top Menu */
$hasMenu = true;
$hasSettings = file_exists($sources['data'] . 'transport.settings.php');
/* Add new MODx System Settings */
$hasContextSettings = file_exists($sources['data'] . 'transport.contextsettings.php');
$hasSubPackages = is_dir($sources['data'] . 'subpackages');
$minifyJS = $modx->getOption('minifyJS', $props, false);

$helper->sendLog(MODX::LOG_LEVEL_INFO, "\n" . $modx->lexicon('mc_project') . ': ' . $currentProject);
$helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_action') . ': ' . $modx->lexicon('mc_build') . "\n");
$helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_created_package') . ': ' . PKG_NAME_LOWER);
$helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_created_namespace') . ': ' . PKG_NAME_LOWER);
/* load builder */
$modx->setLogLevel(MODX::LOG_LEVEL_ERROR);
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);

$assetsPath = $hasAssets ? '{assets_path}components/' . PKG_NAME_LOWER . '/' : '';
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/', $assetsPath);
$modx->setLogLevel(MODX::LOG_LEVEL_INFO);

/* Transport Contexts */

if ($hasContexts) {
    $contexts = include $sources['data'] . 'transport.contexts.php';
    if (!is_array($contexts)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_contexts_not_an_array'));
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            );
        foreach ($contexts as $context) {
            $vehicle = $builder->createVehicle($context, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($contexts) . ' ' . $modx->lexicon('mc_new_contexts') . '.');
        unset($contexts, $context, $attributes);
    }
}


/* Transport Resources */

if ($hasResources) {
    $resources = include $sources['data'] . 'transport.resources.php';
    if (!is_array($resources)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_resources_not_an_array') . '.');
    } else {
        $attributes = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'pagetitle',
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array('ContentType' => array(
                    xPDOTransport::PRESERVE_KEYS => false,
                    xPDOTransport::UPDATE_OBJECT => true,
                    xPDOTransport::UNIQUE_KEY => 'name',
                    ), ),
            );
        foreach ($resources as $resource) {
            $vehicle = $builder->createVehicle($resource, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($resources) . ' ' . $modx->lexicon('mc_resources') . '.');
    }
    unset($resources, $resource, $attributes);
}

/* load new system settings */
if ($hasSettings) {
    $settings = include $sources['data'] . 'transport.settings.php';
    if (!is_array($settings)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_settings_not_an_array') . '.');
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => true,
            );
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($settings) . ' ' . $modx->lexicon('mc_new_system_settings') . '.');
        unset($settings, $setting, $attributes);
    }
}

/* load new context settings */
if ($hasContextSettings) {
    $settings = include $sources['data'] . 'transport.contextsettings.php';
    if (!is_array($settings)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_context_settings_not_an_array') . '.');
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => true,
            );
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($settings) . ' ' . $modx->lexicon('mc_context_settings') . '.');
        unset($settings, $setting, $attributes);
    }
}

/* minify JS */

if ($minifyJS) {
    $helper->sendLog(modX::LOG_LEVEL_INFO, 'Creating js-min file(s)');
    // require $sources['build'] . 'utilities/jsmin.class.php';
    require $sources['utilities'] . 'jsmin.class.php';

    $jsDir = $sources['source_assets'] . '/js';

    if (is_dir($jsDir)) {
        $files = scandir($jsDir);
        foreach ($files as $file) {
            /* skip non-js and already minified files */
            if ((!stristr($file, '.js') || strstr($file, 'min'))) {
                continue;
            }

            $jsmin = JSMin::minify(file_get_contents($sources['source_assets'] . '/js/' . $file));
            if (!empty($jsmin)) {
                $outFile = $jsDir . '/' . str_ireplace('.js', '-min.js', $file);
                $fp = fopen($outFile, 'w');
                if ($fp) {
                    fwrite($fp, $jsmin);
                    fclose($fp);
                    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_created') . ': ' . $outFile);
                } else {
                    $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_could_not_open') . ': ' . $outFile);
                }
            }
        }

    } else {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_could_not_open_js_directory') . '.');
    }
}

/* Create each Category and its Elements */

$i = 0;
$count = count($categories);

foreach ($categories as $k => $categoryName) {
    /* @var $categoryName string */
    $categoryNameLower = strtolower($categoryName);

    /* See what we have based on the files */
    $hasSnippets = file_exists($sources['data'] . $categoryNameLower . '/transport.snippets.php');
    $hasChunks = file_exists($sources['data'] . $categoryNameLower . '/transport.chunks.php');
    $hasTemplates = file_exists($sources['data'] . $categoryNameLower . '/transport.templates.php');
    $hasTemplateVariables = file_exists($sources['data'] . $categoryNameLower . '/transport.tvs.php');
    $hasPlugins = file_exists($sources['data'] . $categoryNameLower . '/transport.plugins.php');
    $hasPropertySets = file_exists($sources['data'] . $categoryNameLower . '/transport.propertysets.php');

    /* @var $category modCategory */
    $category = $modx->newObject('modCategory');
    $i++;
    /* will be 1 for the first category */
    $category->set('id', $i);
    $category->set('category', $categoryName);
    $helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_creating_category') . ': ' . $categoryName);
    $helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_processing_elements_in_category') . ': ' . $categoryName);

    /* add snippets */
    if ($hasSnippets) {

        $snippets = include $sources['data'] . $categoryNameLower . '/transport.snippets.php';

        /* note: Snippets' default properties are set in transport.snippets.php */
        if (is_array($snippets)) {
            if ($category->addMany($snippets, 'Snippets')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($snippets) . ' ' . $modx->lexicon('mc_snippets') . '.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_snippets_failed') . '.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.snippets.php');
        }
    }

    if ($hasPropertySets) {
        $propertySets = include $sources['data'] . $categoryNameLower . '/transport.propertysets.php';
        //  note: property set' properties are set in transport.propertysets.php
        if (is_array($propertySets)) {
            if ($category->addMany($propertySets, 'PropertySets')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($propertySets) . ' ' . $modx->lexicon('mc_property_sets') . '.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_property_sets_failed~~Adding Property Sets
                failed') . '.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.propertysets.php');
        }
    }
    if ($hasChunks) {
        /* add chunks  */
        $helper->sendLog(modX::LOG_LEVEL_INFO, 'Adding Chunks.');
        /* note: Chunks' default properties are set in transport.chunks.php */
        $chunks = include $sources['data'] . $categoryNameLower . '/transport.chunks.php';
        if (is_array($chunks)) {
            if ($category->addMany($chunks, 'Chunks')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($chunks) . ' ' . $modx->lexicon('mc_chunks') . '.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_chunks_failed') . '.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.chunks.php');
        }
    }


    if ($hasTemplates) {
        /* add templates  */
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_adding_templates') . '.');
        /* note: Templates' default properties are set in transport.templates.php */
        $templates = include $sources['data'] . $categoryNameLower . '/transport.templates.php';
        if (is_array($templates)) {
            if ($category->addMany($templates, 'Templates')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($templates) . ' ' . $modx->lexicon('mc_templates') . '.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_templates_failed') . '.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.templates.php');
        }
    }

    if ($hasTemplateVariables) {
        /* add template variables  */
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_adding_template_variables') . '.');
        /* note: Template Variables' default properties are set in transport.tvs.php */
        $tvs = include $sources['data'] . $categoryNameLower . '/transport.tvs.php';
        if (is_array($tvs)) {
            if ($category->addMany($tvs, 'TemplateVars')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($tvs) . ' ' . $modx->lexicon('mc_tvs') . '.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_tvs_failed') . '.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.tvs.php');
        }
    }


    if ($hasPlugins) {
        /* Plugins' default properties are set in transport.plugins.php */
        $plugins = include $sources['data'] . $categoryNameLower . '/transport.plugins.php';
        if (is_array($plugins)) {
            if ($category->addMany($plugins, 'Plugins')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($plugins) . ' ' . $modx->lexicon('mc_plugins') . '.');
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_plugins_failed') . '.');
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.plugins.php');
        }
    }

    /* Create Category attributes array dynamically
    * based on which elements are present
    */

    $attr = array(
        xPDOTransport::UNIQUE_KEY => 'category',
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::RELATED_OBJECTS => true,
        );

    if ($hasValidators && $i == 1) {
        /* only install these on first pass */
        //$attr[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
    }

    if ($hasSnippets) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Snippets'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    if ($hasPropertySets) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['PropertySets'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    if ($hasChunks) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Chunks'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    if ($hasPlugins) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Plugins'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    if ($hasTemplates) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['Templates'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'templatename',
            );
    }

    if ($hasTemplateVariables) {
        $attr[xPDOTransport::RELATED_OBJECT_ATTRIBUTES]['TemplateVars'] = array(
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::UNIQUE_KEY => 'name',
            );
    }

    /* create a vehicle for the category and all the things
    * we've added to it.
    */
    $vehicle = $builder->createVehicle($category, $attr);

    if ($hasValidators && $i == 1) {
        /* only install these on first pass */
        $helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_processing_validators'));
        $validators = empty($props['validators']) ? array() : $props['validators'];
        if (!empty($validators)) {
            foreach ($validators as $validator) {
                if ($validator == 'default') {
                    $validator = PKG_NAME_LOWER;
                }
                $file = $sources['validators'] . $validator . '.validator.php';
                if (file_exists($file)) {
                    $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaging') . ' ' . $validator . ' ' . $modx->lexicon('mc_validator') . '.');
                    $vehicle->validate('php', array('source' => $file, ));
                } else {
                    $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_could_not_find_validator_file~~Could not find Validator
                    file') . ': ' . $file);
                }
            }
        }
    }

    if ($hasCore && $i == 1) {
        $helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged_core_files'));
        $vehicle->resolve('file', array(
            'source' => $sources['source_core'],
            'target' => "return MODX_CORE_PATH . 'components/';",
            ));
    }

    /* This section transfers every file in the local
    mycomponents/mycomponent/assets directory to the
    target site's assets/mycomponent directory on install.
    */

    if ($hasAssets && $i == 1) {
        $helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged_assets_files'));
        $vehicle->resolve('file', array(
            'source' => $sources['source_assets'],
            'target' => "return MODX_ASSETS_PATH . 'components/';",
            ));
    }


    /* Package script resolvers, if any */
    if (($i == $count) && $hasResolvers) {
        /* add resolvers to last category only */
        $resolvers = empty($props['resolvers']) ? array() : $props['resolvers'];
        $resolvers = array_merge(array(
            'category',
            'plugin',
            'tv',
            'resource',
            'propertyset'), $resolvers);
        $helper->sendLog(MODX::LOG_LEVEL_INFO, $modx->lexicon('mc_processing_resolvers'));

        foreach ($resolvers as $resolver) {
            if ($resolver == 'default') {
                $resolver = PKG_NAME_LOWER;
            }

            $file = $sources['resolvers'] . $resolver . '.resolver.php';
            if (file_exists($file)) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . $resolver . ' ' . $modx->lexicon('mc_resolver') . '.');
                $vehicle->resolve('php', array('source' => $sources['resolvers'] . $resolver . '.resolver.php', ));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_no') . ' ' . $resolver . ' ' . $modx->lexicon('mc_resolver') . '.');
            }
        }
    }

    /* Add subpackages */
    /* The transport.zip files will be copied to core/packages
    * but will have to be installed manually with "Add New Package and
    *  "Search Locally for Packages" in Package Manager
    */

    if ($hasSubPackages && $i == 1) {
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaging_subpackages') . '.');
        $vehicle->resolve('file', array(
            'source' => $sources['packages'],
            'target' => "return MODX_CORE_PATH;",
            ));
    }

    /* Put the category vehicle (with all the stuff we added to the
    * category) into the package
    */
    $builder->putVehicle($vehicle);
}
/* Transport Menus */
if ($hasMenu) {
    /* load menu */
    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('Packaging in menu 2.2.x') . '.');
    $menus = include $sources['data'] . 'transport.menu-2.2.x.php';
    foreach ($menus as $menu) {
        if (empty($menu)) {
            $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_could_not_package_menu') . '.');
        } else {
            $vehicle = $builder->createVehicle($menu, array(
                xPDOTransport::PRESERVE_KEYS => true,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'text',
                xPDOTransport::RELATED_OBJECTS => true,
                xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array('Action' => array(
                        xPDOTransport::PRESERVE_KEYS => false,
                        xPDOTransport::UPDATE_OBJECT => true,
                        xPDOTransport::UNIQUE_KEY => array('namespace', 'controller'),
                        ), ),
                ));
            $modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP validators...');
            $vehicle->validate('php', array('source' => $sources['validators'] . 'modx-2.2.php', ));
            $builder->putVehicle($vehicle);
           
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($menus) . ' ' . $modx->lexicon('mc_menu_items') . '.');
        unset($vehicle, $menus);
    }

    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('Packaging in menu 2.3.x') . '.');
    $menus = include $sources['data'] . 'transport.menu-2.3.x.php';
    foreach ($menus as $menu) {
        if (empty($menu)) {
            $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_could_not_package_menu') . '.');
        } else {
            $vehicle = $builder->createVehicle($menu, array(
                xPDOTransport::PRESERVE_KEYS => true,
                xPDOTransport::UPDATE_OBJECT => true,
                xPDOTransport::UNIQUE_KEY => 'text',
                ));
            $modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP validators...');
            $vehicle->validate('php', array('source' => $sources['validators'] . 'modx-2.3.php', ));
            $builder->putVehicle($vehicle);
                        
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($menus) . ' ' . $modx->lexicon('mc_menu_items') . '.');
        unset($vehicle, $menus);
    }
}

/* Next-to-last step - pack in the license file, readme.txt, changelog,
* and setup options 
*/
$attr = array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    );

if ($hasSetupOptions && !empty($props['install.options'])) {
    $attr['setup-options'] = array('source' => $sources['install_options'] . 'user.input.php', );
} else {
    $attr['setup-options'] = array();
}
$builder->setPackageAttributes($attr);

/* Last step - zip up the package */
$builder->pack();

/* report how long it took */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$helper->sendLog(xPDO::LOG_LEVEL_INFO, $modx->lexicon('mc_package_built') . '.');
$helper->sendLog(xPDO::LOG_LEVEL_INFO, $modx->lexicon('mc_execution_time') . ': ' . $totalTime);

return '';
