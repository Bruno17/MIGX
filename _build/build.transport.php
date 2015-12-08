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
        /**
         *  *  *  *  *  * @var $modx modX */
        protected $modx;

        /**
         *  *  *  *  *  * @var $files array - array of files; created by dir_walk */
        protected $files = array();

        /**
         *  *  *  *  *  * @var $props array - properties array */
        protected $props = array();

        public function __construct(&$modx) {
            /* @var $modx modX */
            $this->modx = &$modx;
        }

        public function getProps($configPath) {
            $properties = @include $configPath;
            $this->props = $properties;
            return $properties;
        }

        public function sendLog($level, $message) {
            $msg = '';
            if ($level == modX::LOG_LEVEL_ERROR) {
                $msg .= $this->modx->lexicon('mc_error') . ' -- ';
            }
            $msg .= $message;
            $msg .= "\n";
            if (php_sapi_name() != 'cli') {
                $msg = nl2br($msg);
            }
            echo $msg;
        }

        /**
         * Recursively search directories for certain file types
         * Adapted from boen dot robot at gmail dot com's code on the scandir man page
         * @param $dir - dir to search (no trailing slash)
         * @param mixed $types - null for all files, or a comma-separated list of strings
         *                       the filename must include (e.g., '.php,.class')
         * @param bool $recursive - if false, only searches $dir, not it's descendants
         * @param string $baseDir - used internally -- do not send
         */
        public function dirWalk($dir, $types = null, $recursive = false, $baseDir = '') {

            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }
                    // $this->output .= "\n" , $dir;
                    //$this->output .= "\n", $file;
                    if (is_file($dir . '/' . $file)) {
                        if ($types !== null) {
                            $found = false;
                            $typeArray = explode(',', $types);
                            foreach ($typeArray as $type) {
                                if (strstr($file, $type)) {
                                    $found = true;
                                }
                            }
                            if (!$found)
                                continue;
                        }
                        // $this->{$callback}($dir, $file);
                        $this->addFile($dir, $file);
                    } elseif ($recursive && is_dir($dir . '/' . $file)) {
                        $this->dirWalk($dir . '/' . $file, $types, $recursive, $baseDir . '/' . $file);
                    }
                }
                closedir($dh);
            }
        }

        /**
         * Used by dirWalk() to add files to $this->files
         * @param $dir string - directory of file (no trailing slash)
         * @param $file string - filename of file
         */
        public function addFile($dir, $file) {
            $this->files[$file] = $dir;
        }

        /**
         * Empties $this->files prior to dirWalk
         */
        public function resetFiles() {
            $this->files = array();
        }

        /**
         * Get files found by dirWalk
         *
         * @return array
         */
        public function getFiles() {
            return $this->files;
        }

        /**
         * @param $minimizerFile string - Which minimizer: JSMinPlus or JSMin
         * @param $dir string - dir to search (no trailing slash)
         * @param bool $createJsAll - If true, create packageNameLower . '-all-min.js'
         */
        public function mc_minify($minimizerFile, $dir, $createJsAll = false) {
            $dir = rtrim($dir, '/');
            $this->resetFiles();
            $all = '';
            $this->dirWalk($dir, '.js', true);
            $usePlus = stripos($minimizerFile, 'plus') !== false;
            $minClass = $usePlus ? 'JSMinPlus' : 'JSMin';
            $files = $this->getFiles();
            require dirname(__file__) . '/utilities/' . $minimizerFile;

            $this->sendLog(modX::LOG_LEVEL_INFO, $this->modx->lexicon('mc_creating_js_min_files') . ' (' . $this->modx->lexicon('mc_using') . ' ' . $minClass . ')');

            foreach ($files as $fileName => $path) {
                /* don't minify minimized files */
                if (strpos($fileName, 'min.js') !== false) {
                    continue;
                }
                $code = file_get_contents($path . '/' . $fileName);
                $code = $usePlus ? $minClass::minify($code, $fileName) : $minClass::minify($code);
                if ($createJsAll) {
                    /* JSMin writes its own "\n" */
                    $jend = $usePlus ? "\n" : '';
                    /* Add filename in comment for debugging */
                    $all .= "\n/* " . $fileName . '*/' . $jend . $code;
                }

                $outFile = $path . '/' . str_ireplace('.js', '-min.js', $fileName);
                $fp = fopen($outFile, 'w');
                if ($fp) {
                    fwrite($fp, $code);
                    fclose($fp);
                    $this->sendLog(modX::LOG_LEVEL_INFO, $this->modx->lexicon('mc_updated') . ': ' . $outFile);
                } else {
                    $this->sendLog(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('mc_could_not_open') . ': ' . $outFile);
                }
            }
            if ($createJsAll) {
                $pnl = $this->modx->getOption('packageNameLower', $this->props, 'jsfile');
                $allFile = $pnl . '-all-min.js';
                $outFile = $dir . '/' . $allFile;
                $fp = fopen($outFile, 'w');
                if ($fp) {
                    fwrite($fp, $all);
                    $this->sendLog(modX::LOG_LEVEL_INFO, $this->modx->lexicon('mc_updated') . ': ' . $outFile);
                    fclose($fp);
                } else {
                    $this->sendLog(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('mc_could_not_open') . ': ' . $outFile);
                }
            }
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
$modx->lexicon->load('mycomponent:default');
$modx->setLogLevel(xPDO::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');

if (!defined('MODX_CORE_PATH')) {
    session_write_close();
    die('build.config.php is not correct');
}

/*
@include dirname(__file__) . '/config/current.project.php';

if (!$currentProject) {
session_write_close();
die('Could not get current project');
}
*/

$helper = new BuildHelper($modx);

$modx->lexicon->load('mycomponent:default');

//$props = $helper->getProps(dirname(__FILE__) . '/config/' . $currentProject . '.config.php');

$props = $modx->fromJson(file_get_contents(dirname(__file__) . '/config/config.json'));
$props['install.options'] = '1';
$props['packageName'] = $modx->getOption('package', $props);
$props['packageNameLower'] = strtolower($modx->getOption('package', $props));
//$props['targetRoot'] = $modx->getOption('assets_path') . 'mypackages/' . $props['packageNameLower'] . '/';
//$props = isset($configArray) ? $configArray : false;


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
    session_write_close();
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
    'subpackages' => $root . '_build/subpackages/',

    );
unset($root);

$packagepath = $sources['source_core'] . '/';
$categories = include $sources['build'] . 'config/categories.php';


if (empty($categories) || (!is_array($categories))) {
    //if no category was found, we create a category with the package-name
    $categories = array();
    $category = array('category' => PKG_NAME);
    $categories[] = $category;
    //session_write_close();
    //die($modx->lexicon('no_categories'));
}

$menuprops = isset($props['menus']) ? $modx->fromJson($props['menus']) : '';

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
$hasSetupOptions = is_dir($sources['install_options']);

/* HTML/PHP script to interact with user */
//$hasMenu = file_exists($sources['data'] . 'transport.menus.php');
$hasMenu = is_array($menuprops) && count($menuprops) > 0 ? true : false;
/* Add items to the MODx Top Menu */
$hasSettings = file_exists($sources['data'] . 'transport.settings.php');
/* Add new MODx System Settings */
$hasContextSettings = file_exists($sources['data'] . 'transport.contextsettings.php');
$hasSubPackages = is_dir($sources['subpackages']);
$minifyJS = $modx->getOption('minifyJS', $props, false);

$helper->sendLog(modX::LOG_LEVEL_INFO, "\n" . $modx->lexicon('mc_project') . ': ' . $currentProject);
$helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_action') . ': ' . $modx->lexicon('mc_build'));
$helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_created_package') . ': ' . PKG_NAME_LOWER . '-' . PKG_VERSION . '-' . PKG_RELEASE);
$helper->sendLog(modX::LOG_LEVEL_INFO, "\n" . $modx->lexicon('mc_created_namespace') . ': ' . PKG_NAME_LOWER);
/* load builder */
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$modx->loadClass('transport.modPackageBuilder', '', false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);

$assetsPath = $hasAssets ? '{assets_path}components/' . PKG_NAME_LOWER . '/' : '';
$builder->registerNamespace(PKG_NAME_LOWER, false, true, '{core_path}components/' . PKG_NAME_LOWER . '/', $assetsPath);
$modx->setLogLevel(modX::LOG_LEVEL_INFO);

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
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($contexts) . ' ' . $modx->lexicon('mc_new_contexts'));
        unset($contexts, $context, $attributes);
    }
}


/* Transport Resources */

if ($hasResources) {
    $resources = include $sources['data'] . 'transport.resources.php';
    if (!is_array($resources)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_resources_not_an_array'));
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
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($resources) . ' ' . $modx->lexicon('mc_resources'));
    }
    unset($resources, $resource, $attributes);
}

/* load new system settings */
if ($hasSettings) {
    $settings = include $sources['data'] . 'transport.settings.php';
    if (!is_array($settings)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_settings_not_an_array'));
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            );
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($settings) . ' ' . $modx->lexicon('mc_new_system_settings'));
        unset($settings, $setting, $attributes);
    }
}

/* load new context settings */
if ($hasContextSettings) {
    $settings = include $sources['data'] . 'transport.contextsettings.php';
    if (!is_array($settings)) {
        $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_context_settings_not_an_array'));
    } else {
        $attributes = array(
            xPDOTransport::UNIQUE_KEY => 'key',
            xPDOTransport::PRESERVE_KEYS => true,
            xPDOTransport::UPDATE_OBJECT => false,
            );
        foreach ($settings as $setting) {
            $vehicle = $builder->createVehicle($setting, $attributes);
            $builder->putVehicle($vehicle);
        }
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged') . ' ' . count($settings) . ' ' . $modx->lexicon('mc_context_settings'));
        unset($settings, $setting, $attributes);
    }
}

/* minify JS */

if ($minifyJS) {
    $helper->sendLog(modX::LOG_LEVEL_INFO, 'Creating js-min file(s)');

    $usePlus = $modx->getOption('useJSMinPlus', $props, false);
    $minimizer = $usePlus ? 'jsminplus.class.php' : 'jsmin.class.php';
    $dir = $sources['source_assets'] . '/js';
    $jsAll = $modx->getOption('createJSMinAll', $props, false);
    $helper->mc_minify($minimizer, $dir, $jsAll);
}

/* Create each Category and its Elements */

$i = 0;
$count = count($categories);

foreach ($categories as $k => $cat) {
    /* @var $categoryName string */
    $categoryName = isset($cat['category']) ? $cat['category'] : '';

    if (empty($categoryName)) {
        continue;
    }

    $categoryNameLower = strtolower($categoryName);

    /* See what we have based on the files */
    //$hasSnippets = file_exists($sources['data'] . $categoryNameLower . '/transport.snippets.php');
    $hasSnippets = is_array($cat['snippets']);
    //$hasChunks = file_exists($sources['data'] . $categoryNameLower . '/transport.chunks.php');
    $hasChunks = is_array($cat['chunks']);
    //$hasTemplates = file_exists($sources['data'] . $categoryNameLower . '/transport.templates.php');
    $hasTemplates = is_array($cat['templates']);
    //$hasTemplateVariables = file_exists($sources['data'] . $categoryNameLower . '/transport.tvs.php');
    //$hasPlugins = file_exists($sources['data'] . $categoryNameLower . '/transport.plugins.php');
    $hasPlugins = is_array($cat['plugins']);
    //$hasPropertySets = file_exists($sources['data'] . $categoryNameLower . '/transport.propertysets.php');

    /* @var $category modCategory */
    $category = $modx->newObject('modCategory');
    $i++;
    /* will be 1 for the first category */
    $category->set('id', $i);
    $category->set('category', $categoryName);
    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_creating_category') . ': ' . $categoryName);
    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_processing_elements_in_category') . ': ' . $categoryName);

    /* add snippets */
    if ($hasSnippets) {

        //$snippets = include $sources['data'] . $categoryNameLower  . '/transport.snippets.php';
        $snippets = array();
        $el_i = 1;
        foreach ($cat['snippets'] as $element) {
            $snippets[$el_i] = $modx->newObject('modSnippet');
            $snippets[$el_i]->fromArray(array(
                'id' => $el_i,
                'property_preprocess' => '',
                'name' => $modx->getOption('name', $element, ''),
                'description' => $modx->getOption('description', $element, ''),
                'properties' => $modx->getOption('properties', $element, ''),
                ), '', true, true);
            $snippets[$el_i]->setContent($modx->getOption('content', $element, ''));

            $el_i++;
        }

        /* note: Snippets' default properties are set in transport.snippets.php */
        if (is_array($snippets)) {
            if ($category->addMany($snippets, 'Snippets')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($snippets) . ' ' . $modx->lexicon('mc_snippets'));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_snippets_failed'));
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
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($propertySets) . ' ' . $modx->lexicon('mc_property_sets'));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_property_sets_failed'));
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.propertysets.php');
        }
    }
    if ($hasChunks) {
        /* add chunks  */
        $helper->sendLog(modX::LOG_LEVEL_INFO, 'Adding Chunks.');
        /* note: Chunks' default properties are set in transport.chunks.php */
        //$chunks = include $sources['data'] . $categoryNameLower . '/transport.chunks.php';

        $chunks = array();
        $el_i = 1;
        foreach ($cat['chunks'] as $element) {
            $chunks[$el_i] = $modx->newObject('modChunk');
            $chunks[$el_i]->fromArray(array(
                'id' => $el_i,
                'property_preprocess' => '',
                'name' => $modx->getOption('name', $element, ''),
                'description' => $modx->getOption('description', $element, ''),
                'properties' => $modx->getOption('properties', $element, ''),
                ), '', true, true);
            $chunks[$el_i]->setContent($modx->getOption('content', $element, ''));

            $el_i++;
        }

        if (is_array($chunks)) {
            if ($category->addMany($chunks, 'Chunks')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($chunks) . ' ' . $modx->lexicon('mc_chunks'));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_chunks_failed'));
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.chunks.php');
        }
    }


    if ($hasTemplates) {
        /* add templates  */
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_adding_templates'));
        /* note: Templates' default properties are set in transport.templates.php */
        //$templates = include $sources['data'] . $categoryNameLower . '/transport.templates.php';
        $templates = array();
        $el_i = 1;
        foreach ($cat['templates'] as $element) {
            $templates[$el_i] = $modx->newObject('modTemplate');
            $templates[$el_i]->fromArray(array(
                'id' => $el_i,
                'property_preprocess' => '',
                'templatename' => $modx->getOption('templatename', $element, ''),
                'description' => $modx->getOption('description', $element, ''),
                'icon' => $modx->getOption('icon', $element, ''),
                'template_type' => $modx->getOption('description', $element, ''),
                'properties' => $modx->getOption('template_type', $element, ''),
                ), '', true, true);
            $templates[$el_i]->setContent($modx->getOption('content', $element, ''));

            $el_i++;
        }


        if (is_array($templates)) {
            if ($category->addMany($templates, 'Templates')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($templates) . ' ' . $modx->lexicon('mc_templates'));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_templates_failed'));
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.templates.php');
        }
    }

    if ($hasTemplateVariables) {
        /* add template variables  */
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_adding_template_variables'));
        /* note: Template Variables' default properties are set in transport.tvs.php */
        $tvs = include $sources['data'] . $categoryNameLower . '/transport.tvs.php';
        if (is_array($tvs)) {
            if ($category->addMany($tvs, 'TemplateVars')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($tvs) . ' ' . $modx->lexicon('mc_tvs'));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_tvs_failed'));
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.tvs.php');
        }
    }

    $plugin_events = array();
    if ($hasPlugins) {
        /* Plugins' default properties are set in transport.plugins.php */
        //$plugins = include $sources['data'] . $categoryNameLower . '/transport.plugins.php';
        $plugins = array();
        $el_i = 1;

        foreach ($cat['plugins'] as $element) {
            $plugins[$el_i] = $modx->newObject('modPlugin');
            $plugins[$el_i]->fromArray(array(
                'id' => $el_i,
                'property_preprocess' => '',
                'name' => $modx->getOption('name', $element, ''),
                'description' => $modx->getOption('description', $element, ''),
                'disabled' => $modx->getOption('disabled', $element, ''),
                'properties' => $modx->getOption('properties', $element, ''),
                ), '', true, true);
            $plugins[$el_i]->setContent($modx->getOption('content', $element, ''));
            $events = $modx->getOption('plugin_events', $element, '');

            $plugin_events = is_array($events) ? array_merge($plugin_events, $events) : $plugin_events;


            $el_i++;
        }

        if (is_array($plugins)) {
            if ($category->addMany($plugins, 'Plugins')) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($plugins) . ' ' . $modx->lexicon('mc_plugins'));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_adding_plugins_failed'));
            }
        } else {
            $helper->sendLog(modX::LOG_LEVEL_FATAL, '    ' . $modx->lexicon('mc_non_array_in') . ' transport.plugins.php');
        }
    }

    $filename = $sources['resolvers'] . 'plugin.resolver.php';
    if (file_exists($filename)) {
        $plugin_events = $modx->toJson($plugin_events);
        $content = file_get_contents($filename);
        $content = str_replace('{events}', $plugin_events, $content);
        file_put_contents($filename, $content);
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

    /* Only add this on first pass if no subPackages */
    if ($hasValidators && ($i == 1) && (!$hasSubPackages)) {
        $attr[xPDOTransport::ABORT_INSTALL_ON_VEHICLE_FAIL] = true;
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
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_processing_validators'));
        $validators = empty($props['validators']) ? array() : $props['validators'];
        if (!empty($validators)) {
            foreach ($validators as $validator) {
                if ($validator == 'default') {
                    $validator = PKG_NAME_LOWER;
                }
                $file = $sources['validators'] . $validator . '.validator.php';
                if (file_exists($file)) {
                    $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaging') . ' ' . $validator . ' ' . $modx->lexicon('mc_validator'));
                    $vehicle->validate('php', array('source' => $file, ));
                } else {
                    $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_could_not_find_validator_file') . ': ' . $file);
                }
            }
        }
    }

    if ($hasCore && $i == 1) {
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged_core_files'));
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
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaged_assets_files'));
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
            'propertyset',
            'tables'), $resolvers);
        $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_processing_resolvers'));

        foreach ($resolvers as $resolver) {
            if ($resolver == 'default') {
                $resolver = PKG_NAME_LOWER;
            }

            $file = $sources['resolvers'] . $resolver . '.resolver.php';
            if (file_exists($file)) {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . $resolver . ' ' . $modx->lexicon('mc_resolver'));
                $vehicle->resolve('php', array('source' => $sources['resolvers'] . $resolver . '.resolver.php', ));
            } else {
                $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_no') . ' ' . $resolver . ' ' . $modx->lexicon('mc_resolver'));
            }
        }
    }

    /* Put the category vehicle (with all the stuff we added to the
    * category) into the package
    */
    $builder->putVehicle($vehicle);
}
/* Transport Menus */
if ($hasMenu) {
    /* load menu */
    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaging_menu'));
    $menus = include $sources['data'] . 'transport.menu-2.2.x.php';
    foreach ($menus as $menu) {
        if (empty($menu)) {
            $helper->sendLog(modX::LOG_LEVEL_ERROR, $modx->lexicon('mc_could_not_package_menu'));
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

            $modx->log(modX::LOG_LEVEL_INFO, 'Adding in PHP resolvers resolve.menues.php');
            $vehicle->resolve('php', array('source' => $sources['resolvers'] . 'resolve.menues.php', ));

            $builder->putVehicle($vehicle);
            unset($vehicle, $menu);
        }
    }
    $helper->sendLog(modX::LOG_LEVEL_INFO, '    ' . $modx->lexicon('mc_packaged') . ' ' . count($menus) . ' ' . $modx->lexicon('mc_menu_items'));
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
    $modx->log(modX::LOG_LEVEL_INFO, 'Adding in install_options user.input.php');
    $attr['setup-options'] = array('source' => $sources['install_options'] . 'user.input.php', );
} else {
    $attr['setup-options'] = array();
}
$builder->setPackageAttributes($attr);

/* Add subpackages */

if ($hasSubPackages) {
    $helper->sendLog(modX::LOG_LEVEL_INFO, $modx->lexicon('mc_packaging_subpackages'));
    include $sources['data'] . 'transport.subpackages.php';
}


/* Last step - zip up the package */
$builder->pack();

/* report how long it took */
$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$helper->sendLog(xPDO::LOG_LEVEL_INFO, $modx->lexicon('mc_package_built'));
$helper->sendLog(xPDO::LOG_LEVEL_INFO, $modx->lexicon('mc_execution_time') . ': ' . $totalTime);

return '';
