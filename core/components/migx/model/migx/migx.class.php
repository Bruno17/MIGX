<?php

/**
 * migx
 *
 * @author Bruno Perner
 *
 *
 * @package migx
 */
/**
 * @package migx
 * @subpackage migx
 */
class Migx {
    /**
     * @access public
     * @var modX A reference to the modX object.
     */
    public $modx = null;
    /**
     * @access public
     * @var array A collection of properties to adjust MIGX behaviour.
     */
    public $config = array();
    /**
     * @access public
     * @var source, the source of this MIGX-TV
     */
    public $source = false;
    /**
     * @access public
     * @var working_context, the working context
     */
    public $working_context = null;

    /**
     * The MIGX Constructor.
     *
     * This method is used to create a new MIGX object.
     *
     * @param modX &$modx A reference to the modX object.
     * @param array $config A collection of properties that modify MIGX
     * behaviour.
     * @return MIGX A unique MIGX instance.
     */
    function __construct(modX & $modx, array $config = array()) {
        $this->modx = &$modx;

        $packageName = 'migx';
        $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
        $modelpath = $packagepath . 'model/';
        $prefix = null;
        $this->modx->addPackage($packageName, $modelpath, $prefix);


        /* allows you to set paths in different environments
        * this allows for easier SVN management of files
        */
        $corePath = $this->modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/');
        $assetsPath = $this->modx->getOption('migx.assets_path', null, $modx->getOption('assets_path') . 'components/migx/');
        $assetsUrl = $this->modx->getOption('migx.assets_url', null, $modx->getOption('assets_url') . 'components/migx/');

        $defaultconfig['debugUser'] = '';
        $defaultconfig['corePath'] = $corePath;
        $defaultconfig['modelPath'] = $corePath . 'model/';
        $defaultconfig['processorsPath'] = $corePath . 'processors/';
        $defaultconfig['templatesPath'] = $corePath . 'templates/';
        $defaultconfig['controllersPath'] = $corePath . 'controllers/';
        $defaultconfig['chunksPath'] = $corePath . 'elements/chunks/';
        $defaultconfig['snippetsPath'] = $corePath . 'elements/snippets/';
        $defaultconfig['auto_create_tables'] = true;
        $defaultconfig['baseUrl'] = $assetsUrl;
        $defaultconfig['cssUrl'] = $assetsUrl . 'css/';
        $defaultconfig['jsUrl'] = $assetsUrl . 'js/';
        $defaultconfig['jsPath'] = $assetsPath . 'js/';
        $defaultconfig['connectorUrl'] = $assetsUrl . 'connector.php';
        $defaultconfig['request'] = $_REQUEST;

        $this->config = array_merge($defaultconfig, $config);

        /* load debugging settings */
        if ($this->modx->getOption('debug', $this->config, false)) {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
            $this->modx->setLogTarget('HTML');
            $this->modx->setLogLevel(modX::LOG_LEVEL_ERROR);

            $debugUser = $this->config['debugUser'] == '' ? $this->modx->user->get('username') : 'anonymous';
            $user = $this->modx->getObject('modUser', array('username' => $debugUser));
            if ($user == null) {
                $this->modx->user->set('id', $this->modx->getOption('debugUserId', $this->config, 1));
                $this->modx->user->set('username', $debugUser);
            } else {
                $this->modx->user = $user;
            }
        }
    }

    function findProcessor($processorspath, $filename, &$filenames) {
        return $this->findCustomFile($processorspath, $filename, $filenames);
    }

    function findGrid($processorspath, $filename, &$filenames) {
        return $this->findCustomFile($processorspath, $filename, $filenames, 'grids');
    }

    function findCustomFile($defaultpath, $filename, &$filenames, $type = 'processors') {
        $config = $this->customconfigs;
        $packageName = $this->modx->getOption('packageName', $config);
        $task = $this->getTask();
        if (!empty($packageName)) {
            $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
            switch ($type) {
                case 'processors':
                    $path = $packagepath . 'processors/mgr/';
                    if (!empty($task)) {
                        $filepath = $path . $task . '/' . $filename;
                        $filenames[] = $filepath;
                        if (file_exists($filepath)) {
                            return $filepath;
                        }
                    }

                    $filepath = $path . 'default/' . $filename;
                    $filenames[] = $filepath;
                    if (file_exists($filepath)) {
                        return $filepath;
                    }
                    break;
                case 'grids':
                    $path = $packagepath . 'migxtemplates/mgr/grids/';
                    $filepath = $path . '/' . $filename;
                    $filenames[] = $filepath;
                    if (file_exists($filepath)) {
                        return $filepath;
                    }

                    break;
            }

        }
        switch ($type) {
            case 'processors':
                if (!empty($task)) {
                    $filepath = $defaultpath . $task . '/' . $filename;
                    $filenames[] = $filepath;
                    $found = false;
                    if (file_exists($filepath)) {
                        return $filepath;
                    }
                }

                $filepath = $defaultpath . 'default/' . $filename;
                $filenames[] = $filepath;
                if (file_exists($filepath)) {
                    return $filepath;
                }
                break;
            case 'grids':
            default:
                $filepath = $defaultpath . $filename;
                $filenames[] = $filepath;
                if (file_exists($filepath)) {
                    return $filepath;
                }
                break;

        }
        return false;
    }

    function checkMultipleForms($formtabs, &$controller, &$allfields, &$record) {
        $multiple_formtabs = $this->modx->getOption('multiple_formtabs', $this->customconfigs, '');
        if (!empty($multiple_formtabs)) {
            if (isset($_REQUEST['loadaction']) && $_REQUEST['loadaction'] == 'switchForm') {
                $data = $this->modx->fromJson($this->modx->getOption('record_json', $_REQUEST, ''));
                if (is_array($data) && isset($data['MIGX_formname'])) {
                    $record = array_merge($record, $data);
                }
            }
            $mf_configs = explode('||', $multiple_formtabs);
            $classname = 'migxConfig';
            $c = $this->modx->newQuery($classname);
            $c->select($this->modx->getSelectColumns($classname, $classname));
            $c->where(array('id:IN' => $mf_configs));
            $c->sortby('name');
            if ($collection = $this->modx->getCollection($classname, $c)) {
                $idx = 0;
                $formtabs = false;
                foreach ($collection as $object) {
                    $formname = array();
                    $formname['value'] = $object->get('name');
                    $formname['text'] = $object->get('name');
                    $formname['selected'] = 0;
                    if ($idx == 0) {
                        $firstformtabs = $this->modx->fromJson($object->get('formtabs'));
                    }
                    if (isset($record['MIGX_formname']) && $record['MIGX_formname'] == $object->get('name')) {
                        $formname['selected'] = 1;
                        $formtabs = $this->modx->fromJson($object->get('formtabs'));
                    }
                    $formnames[] = $formname;
                    $idx++;
                    /*
                    foreach ($form['formtabs'] as $tab) {
                    $tabs[$form['formname']][] = $tab;
                    }
                    */
                }

                $formtabs = $formtabs ? $formtabs : $firstformtabs;

                $controller->setPlaceholder('formnames', $formnames);

                $field = array();
                $field['field'] = 'MIGX_formname';
                $field['tv_id'] = 'Formname';
                $allfields[] = $field;
            }
        }
        return $formtabs;
    }

    function loadConfigs($grid = true, $other = true, $properties = array(), $sender = '') {
        $gridactionbuttons = array();
        $gridcolumnbuttons = array();
        $gridcontextmenus = array();
        $gridfunctions = array();
        $renderer = array();
        $gridfilters = array();
        $configs = array('migx_default');
        //$configs = array();

        if (isset($properties['configs']) && !empty($properties['configs'])) {
            $configs = explode(',', $properties['configs']);
        } elseif (isset($this->config['configs']) && !empty($this->config['configs'])) {
            $configs = explode(',', $this->config['configs']);
        }

        if (!empty($configs)) {
            //$configs = (isset($this->config['configs'])) ? explode(',', $this->config['configs']) : array();
            //$configs = array_merge( array ('master'), $configs);

            if ($grid) {
                $configFile = $this->config['corePath'] . 'configs/grid/grid.config.inc.php'; // [ file ]
                if (file_exists($configFile)) {
                    include ($configFile);
                }
                //custom collection of grid-functions...... - deprecated
                $configFile = $this->config['corePath'] . 'configs/grid/grid.custom.config.inc.php'; // [ file ]
                if (file_exists($configFile)) {
                    include ($configFile);
                }
            }

            //get migxconfig-specific grid-configs
            $req_configs = $this->modx->getOption('configs', $_REQUEST, '');

            $preloadGridConfigs = false;
            if ($sender == 'mgr/fields' && $req_configs == 'migxcolumns') {
                $preloadGridConfigs = true;
                $configs_id = $this->modx->getOption('co_id', $_REQUEST, '');
                $this->configsObject = $this->modx->getObject('migxConfig', $configs_id);
            }

            if ($sender == 'migxconfigs/fields') {
                $preloadGridConfigs = true;
            }

            if ($preloadGridConfigs && is_Object($this->configsObject)) {

                $config = $this->configsObject->get('name');
                $configFile = $this->config['corePath'] . 'configs/grid/grid.' . $config . '.config.inc.php'; // [ file ]
                if (file_exists($configFile)) {
                    include ($configFile);
                }
                //package-specific
                $extended = $this->configsObject->get('extended');
                $packageName = $this->modx->getOption('packageName', $extended, '');
                if (!empty($packageName)) {
                    $configFile = $this->modx->getOption('core_path') . 'components/' . $packageName . '/migxconfigs/grid/grid.' . $config . '.config.inc.php'; // [ file ]
                    if (file_exists($configFile)) {
                        include ($configFile);
                    }
                    $configFile = $this->modx->getOption('core_path') . 'components/' . $packageName . '/migxconfigs/grid/grid.config.inc.php'; // [ file ]
                    if (file_exists($configFile)) {
                        include ($configFile);
                    }
                }

            }

            foreach ($configs as $config) {
                $parts = explode(':', $config);
                $cfObject = false;
                if (isset($parts[1])) {
                    $config = $parts[0];
                    $packageName = $parts[1];
                } elseif ($cfObject = $this->modx->getObject('migxConfig', array('name' => $config, 'deleted' => '0'))) {

                    $extended = $cfObject->get('extended');
                    $packageName = $this->modx->getOption('packageName', $extended, '');
                }
                if (isset($packageName)) {
                    $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
                    $configpath = $packagepath . 'migxconfigs/';
                }


                if ($grid) {
                    //first try to find custom-grid-configurations (buttons,context-menus,functions)
                    $configFile = $this->config['corePath'] . 'configs/grid/grid.' . $config . '.config.inc.php'; // [ file ]
                    if (file_exists($configFile)) {
                        include ($configFile);
                    }
                    if (!empty($packageName)) {
                        $configFile = $configpath . 'grid/grid.' . $config . '.config.inc.php'; // [ file ]
                        if (file_exists($configFile)) {
                            include ($configFile);
                        }
                        $configFile = $configpath . 'grid/grid.config.inc.php'; // [ file ]
                        if (file_exists($configFile)) {
                            include ($configFile);
                        }
                    }
                }

                if ($other) {
                    //second try to find config-object

                    if (isset($configpath) && !$cfObject && file_exists($configpath . $config . '.config.js')) {
                        $filecontent = @file_get_contents($configpath . $config . '.config.js');
                        $objectarray = $this->importconfig($this->modx->fromJson($filecontent));
                        $this->prepareConfigsArray($objectarray, $gridactionbuttons, $gridcontextmenus, $gridcolumnbuttons);
                    }

                    if ($cfObject) {

                        $objectarray = $cfObject->toArray();
                        $this->prepareConfigsArray($objectarray, $gridactionbuttons, $gridcontextmenus, $gridcolumnbuttons);

                    }
                    //third add configs from file, if exists
                    $configFile = $this->config['corePath'] . 'configs/' . $config . '.config.inc.php'; // [ file ]
                    if (file_exists($configFile)) {
                        include ($configFile);
                    }
                    if (!empty($packageName)) {
                        $configFile = $configpath . $config . '.config.inc.php'; // [ file ]
                        if (file_exists($configFile)) {
                            include ($configFile);
                        }
                    }
                }
            }
        }


        if (isset($this->customconfigs['filters']) && is_array($this->customconfigs['filters']) && count($this->customconfigs['filters']) > 0) {
            foreach ($this->customconfigs['filters'] as $filter) {
                if (isset($gridfilters[$filter['type']]) && is_array($gridfilters[$filter['type']])) {
                    $this->customconfigs['gridfilters'][$filter['name']] = array_merge($filter, $gridfilters[$filter['type']]);
                }
            }
        }

        $this->customconfigs['gridactionbuttons'] = $gridactionbuttons;
        $this->customconfigs['gridcontextmenus'] = $gridcontextmenus;
        $this->customconfigs['gridcolumnbuttons'] = $gridcolumnbuttons;
        $this->customconfigs['gridfunctions'] = array_merge($gridfunctions, $renderer);
        //$defaulttask = empty($this->customconfigs['join_alias']) ? 'default' : 'default_join';
        $defaulttask = 'default';
        $this->customconfigs['task'] = empty($this->customconfigs['task']) ? $defaulttask : $this->customconfigs['task'];

    }


    public function prepareConfigsArray($objectarray, &$gridactionbuttons, &$gridcontextmenus, &$gridcolumnbuttons) {

        if (is_array($objectarray['extended'])) {
            foreach ($objectarray['extended'] as $key => $value) {
                if (!empty($value)) {
                    $this->customconfigs[$key] = $value;
                }
            }
        }

        unset($objectarray['extended']);

        if (isset($this->customconfigs)) {
            $this->customconfigs = is_array($this->customconfigs) ? array_merge($this->customconfigs, $objectarray) : $objectarray;
            $this->customconfigs['tabs'] = $this->modx->fromJson($objectarray['formtabs']);
            $this->customconfigs['filters'] = $this->modx->fromJson($objectarray['filters']);
            //$this->customconfigs['tabs'] =  stripslashes($cfObject->get('formtabs'));
            //$this->customconfigs['columns'] = $this->modx->fromJson(stripslashes($cfObject->get('columns')));
            $this->customconfigs['columns'] = $this->modx->fromJson($objectarray['columns']);
        }

        $menus = $objectarray['contextmenus'];

        if (!empty($menus)) {
            $menus = explode('||', $menus);
            foreach ($menus as $menu) {
                $gridcontextmenus[$menu]['active'] = 1;
            }
        }
        $columnbuttons = $objectarray['columnbuttons'];

        if (!empty($columnbuttons)) {
            $columnbuttons = explode('||', $columnbuttons);
            foreach ($columnbuttons as $button) {
                if (isset($gridcontextmenus[$button])) {
                    $gridcolumnbuttons[$button] = $gridcontextmenus[$button];
                    $gridcolumnbuttons[$button]['active'] = 1;
                }

            }
        }

        $actionbuttons = $objectarray['actionbuttons'];
        if (!empty($actionbuttons)) {
            $actionbuttons = explode('||', $actionbuttons);
            foreach ($actionbuttons as $button) {
                $gridactionbuttons[$button]['active'] = 1;
            }
        }
    }

    function loadPackageManager() {

        include_once ($this->config['modelPath'] . 'migx/migxpackagemanager.class.php');
        return new MigxPackageManager($this->modx);
    }

    public function getTask() {
        return isset($this->customconfigs['task']) ? $this->customconfigs['task'] : '';
    }
    public function getTabs() {
        return isset($this->customconfigs['tabs']) ? $this->customconfigs['tabs'] : '';
    }
    public function getColumns() {
        return isset($this->customconfigs['columns']) ? $this->customconfigs['columns'] : '';
    }
    public function getGrid() {
        return !empty($this->customconfigs['grid']) ? $this->customconfigs['grid'] : 'default';
    }

    public function prepareCmpTabs($properties, &$controller, &$tv) {
        $cmptabs = (isset($this->config['cmptabs'])) ? explode('||', $this->config['cmptabs']) : array();
        $cmptabsout = array();
        $grids = '';
        $updatewindows = '';
        $iframewindows = '';
        $customHandlers = array();

        $maincaption = "_('migx.management')";

        if (count($cmptabs) > 0) {
            foreach ($cmptabs as $tab_idx => $tab) {
                $this->customconfigs = array();
                $this->config['configs'] = $tab;
                $properties['tv_id'] = $tab_idx + 1;
                $this->prepareGrid($properties, $controller, $tv);
                $tabcaption = empty($this->customconfigs['cmptabcaption']) ? 'undefined' : $this->customconfigs['cmptabcaption'];
                $tabdescription = empty($this->customconfigs['cmptabdescription']) ? 'undefined' : $this->customconfigs['cmptabdescription'];
                $maincaption = empty($this->customconfigs['cmpmaincaption']) ? $maincaption : "'" . $this->customconfigs['cmpmaincaption'] . "'";

                $controller->setPlaceholder('cmptabcaption', $tabcaption);
                $controller->setPlaceholder('cmptabdescription', $tabdescription);

                $cmptabfile = $this->config['templatesPath'] . 'mgr/cmptab.tpl';
                if (!empty($this->customconfigs['cmptabcontroller'])) {
                    $controllerfile = $this->config['controllersPath'] . 'custom/' . $this->customconfigs['cmptabcontroller'] . '.php';
                    if (file_exists($controllerfile)) {
                        $tabTemplate = '';
                        include ($controllerfile);
                        if (!empty($tabTemplate) && file_exists($tabTemplate)) {
                            $cmptabfile = $tabTemplate;
                        }
                    }
                }

                $cmptabsout[] = $this->replaceLang($controller->fetchTemplate($cmptabfile));
                $grid = $this->getGrid();

                $filenames = array();
                $defaultpath = $this->config['templatesPath'] . '/mgr/grids/';
                $filename = $grid . '.grid.tpl';
                if ($gridfile = $this->findGrid($defaultpath, $filename, $filenames)) {
                    $grids .= $this->replaceLang($controller->fetchTemplate($gridfile));
                }
                //$gridfile = $this->config['templatesPath'] . '/mgr/grids/' . $grid . '.grid.tpl';
                //$windowfile = $this->config['templatesPath'] . 'mgr/updatewindow.tpl';
                //$updatewindows .= $this->replaceLang($controller->fetchTemplate($windowfile));

                $filenames = array();
                $defaultpath = $this->config['templatesPath'] . 'mgr/';
                $filename = 'updatewindow.tpl';
                if ($gridfile = $this->findGrid($defaultpath, $filename, $filenames)) {
                    $updatewindows .= $this->replaceLang($controller->fetchTemplate($gridfile));
                }

                $filenames = array();
                $filename = 'iframewindow.tpl';
                if ($windowfile = $this->findGrid($defaultpath, $filename, $filenames)) {
                    $iframewindows .= $this->replaceLang($controller->fetchTemplate($windowfile));
                }
            }
        }
        if (count($customHandlers) > 0) {
            $customHandlers = implode(',', $customHandlers);
            $controller->setPlaceholder('customHandlers', $customHandlers);
        }

        $controller->setPlaceholder('maincaption', $maincaption);
        $controller->setPlaceholder('grids', $grids);
        $controller->setPlaceholder('updatewindows', $updatewindows);
        $controller->setPlaceholder('iframewindows', $iframewindows);
        $controller->setPlaceholder('cmptabs', implode(',', $cmptabsout));
        return $controller->fetchTemplate($this->config['templatesPath'] . 'mgr/gridpanel.tpl');

    }

    public function loadLang() {
        //$lang = $this->modx->lexicon->fetch();
        $this->migxlang = $this->modx->lexicon->fetch('migx');

        //$this->migxi18n = array();
        foreach ($this->migxlang as $key => $value) {
            $this->addLangValue($key, $value);
        }
    }

    public function addLangValue($key, $value) {
        //$key = str_replace('migx.', 'migx_', $key);
        //$this->migxi18n[$key] = $value;
        $this->langSearch[$key] = '[[%' . $key . ']]';
        $this->langReplace[$key] = $value;
    }

    public function replaceLang($value, $debug = false) {
        if ($debug) {
            echo str_replace($this->langSearch, $this->langReplace, $value);
        }
        return str_replace($this->langSearch, $this->langReplace, $value);
    }

    public function prepareGrid($properties, &$controller, &$tv, $columns = array()) {

        $this->loadConfigs(false);
        //$lang = $this->modx->lexicon->fetch();

        $resource = is_object($this->modx->resource) ? $this->modx->resource->toArray() : array();
        $this->config['resource_id'] = $this->modx->getOption('id', $resource, '');
        $this->config['connected_object_id'] = $this->modx->getOption('object_id', $_REQUEST, '');
        $this->config['req_configs'] = $this->modx->getOption('configs', $_REQUEST, '');

        if (is_object($tv)) {
            $win_id = $tv->get('id');
        } else {
            $win_id = 'migxdb';
            $tv = $this->modx->newObject('modTemplateVar');
            $controller->setPlaceholder('tv', $tv);
        }
        
        $this->customconfigs['win_id'] = !empty($this->customconfigs['win_id']) ? $this->customconfigs['win_id'] : $win_id;
        

        $tv_id = $tv->get('id');
        $tv_id = empty($tv_id) && isset($properties['tv_id']) ? $properties['tv_id'] : $tv_id;

        $this->config['tv_id'] = $tv_id;

        foreach ($this->config as $key => $value) {
            if (!is_array($value)) {
                $replace['config_' . $key] = $value;
                $search['config_' . $key] = '[[+config.' . $key . ']]';
            }

        }

        foreach ($this->customconfigs as $key => $value) {
            if (!is_array($value)) {
                $replace['config_' . $key] = $value;
                $search['config_' . $key] = '[[+config.' . $key . ']]';
            }
        }

        $l['migx.add'] = !empty($this->customconfigs['migx_add']) ? $this->customconfigs['migx_add'] : $this->migxlang['migx.add'];
        $l['migx.add'] = str_replace("'", "\'", $l['migx.add']);

        $this->addLangValue('migx.add', $l['migx.add']);

        $this->loadConfigs();

        $handlers = array();
        if (isset($this->customconfigs['extrahandlers'])) {
            $extrahandlers = explode('||', $this->customconfigs['extrahandlers']);
            foreach ($extrahandlers as $handler) {
                $handlers[] = $handler;
            }
        }

        $buttons = array();
        if (count($this->customconfigs['gridactionbuttons']) > 0) {
            foreach ($this->customconfigs['gridactionbuttons'] as $button) {
                if (!empty($button['active'])) {
                    unset($button['active']);
                    if (isset($button['handler']) && !in_array($button['handler'], $handlers)) {
                        $handlers[] = $button['handler'];
                    }
                    if (isset($button['menu']) && is_array($button['menu'])) {
                        foreach ($button['menu'] as $menu) {
                            if (!in_array($menu['handler'], $handlers)) {
                                $handlers[] = $menu['handler'];
                            }
                        }

                    }
                    //$button['text'] = $this->replaceLang($button['text']);
                    $standalone = $this->modx->getOption('standalone', $button, '');
                    if (!empty($standalone)) {
                        $gridbuttons[] = str_replace('"', '', $this->modx->toJson($button));
                    } else {
                        $buttons[] = str_replace('"', '', $this->modx->toJson($button));
                    }


                }

            }
        }

        $filters = array();
        $filterDefaults = array();
        if (isset($this->customconfigs['gridfilters']) && count($this->customconfigs['gridfilters']) > 0) {
            foreach ($this->customconfigs['gridfilters'] as $filter) {
                if (isset($filter['comboparent']) && !empty($filter['comboparent'])) {
                    $combochilds[$filter['comboparent']][$filter['name']] = $filter['name'];
                }
            }

            foreach ($this->customconfigs['gridfilters'] as $filter) {
                $filter['emptytext'] = empty($filter['emptytext']) ? 'search...' : $filter['emptytext'];
                $filter['combochilds'] = '[]';
                if (isset($combochilds[$filter['name']])) {
                    $filter['combochilds'] = $this->modx->toJson(array_values($combochilds[$filter['name']]));
                    //print_r($filter);
                }
                foreach ($filter as $key => $value) {
                    $replace[$key] = $value;
                    $search[$key] = '[[+' . $key . ']]';
                }
                $filtername = $filter['handler'] . '_' . $filter['name'];
                if (isset($this->customconfigs['gridfunctions'][$filter['handler']])) {
                    $this->customconfigs['gridfunctions'][$filtername] = str_replace($search, $replace, $this->customconfigs['gridfunctions'][$filter['handler']]);
                }
                $filters[] = str_replace($search, $replace, $filter['code']);
                if (!in_array($filtername, $handlers)) {
                    $handlers[] = $filtername;
                }
                $default = array();
                $default['name'] = $filter['name'];
                $default['default'] = isset($filter['default']) ? $filter['default'] : '';
                $filterDefaults[] = $default;
            }
        }

        $this->customconfigs['tbar'] = '';
        $tbaritems = array();

        $tbaractions = array();

        if (isset($gridbuttons) && count($gridbuttons) > 0) {
            $gridbuttons = implode(',', $gridbuttons);
            $tbaractions[] = $gridbuttons;
        }


        if (isset($buttons) && count($buttons) > 0) {
            $gridactionbuttons = implode(',', $buttons);
            $tbaractions[] = "
          {
            xtype: 'buttongroup',
            title: '[[%migx.actions]]',
            columns: 4,
            defaults: {
                scale: 'large'
            },
            items: [{$gridactionbuttons}]
    	  }       
          ";
        }


        if (count($tbaractions) > 0) {
            $tbaritems[] = implode(',', $tbaractions);
        }

        $tbarfilters = array();
        if (count($filters) > 0) {
            $gridfilters = implode(',', $filters);
            $tbarfilters[] = "
          {
            xtype: 'buttongroup',
            title: '[[%migx.filters]]',
            columns: 4,
            defaults: {
                scale: 'large'
            },
            items: [{$gridfilters}]
    	  }       
          ";
        }

        if (count($tbarfilters) > 0) {
            $tbaritems[] = implode(',', $tbarfilters);
        }

        if (count($tbaritems) > 0) {
            $this->customconfigs['tbar'] = implode(',', $tbaritems);
        }

        $menues = '';
        if (count($this->customconfigs['gridcontextmenus']) > 0) {
            foreach ($this->customconfigs['gridcontextmenus'] as $menue) {
                if (!empty($menue['active'])) {
                    unset($menue['active']);
                    if (!empty($menue['handler'])) {
                        $handlerarr = explode(',', $menue['handler']);
                        foreach ($handlerarr as $handler) {
                            if (!in_array($handler, $handlers)) {
                                $handlers[] = $handler;
                            }
                        }

                    }
                    //$menues .= $this->replaceLang($menue['code']);
                    $menues .= $menue['code'];
                }

            }
        }
        $this->customconfigs['gridcontextmenus'] = $menues;

        $columnbuttons = '';

        if (count($this->customconfigs['gridcolumnbuttons']) > 0) {
            foreach ($this->customconfigs['gridcolumnbuttons'] as $button) {
                if (!empty($button['active'])) {
                    unset($button['active']);
                    if (!empty($button['handler'])) {
                        $handlerarr = explode(',', $button['handler']);
                        foreach ($handlerarr as $handler) {
                            if (!in_array($handler, $handlers)) {
                                $handlers[] = $handler;
                            }
                        }

                    }
                    //$menues .= $this->replaceLang($menue['code']);
                    $columnbuttons .= $button['code'];
                }

            }
        }
        $this->customconfigs['gridcolumnbuttons'] = $columnbuttons;

        $gridfunctions = array();

        $default_formtabs = '[{"caption":"Default", "fields": [{"field":"title","caption":"Title"}]}]';
        $default_columns = '[{"header": "Title", "width": "160", "sortable": "true", "dataIndex": "title"}]';

        $formtabs = $this->getTabs();

        if (empty($formtabs)) {
            // get them from input-properties
            $formtabs = $this->modx->fromJSON($this->modx->getOption('formtabs', $properties, $default_formtabs));
            $formtabs = empty($properties['formtabs']) ? $this->modx->fromJSON($default_formtabs) : $formtabs;
        }

        //$this->migx->debug('resource',$resource);

        //multiple different Forms
        // Note: use same field-names and inputTVs in all forms

        $inputTvs = $this->extractInputTvs($formtabs);

        /* get base path based on either TV param or filemanager_path */
        $this->modx->getService('fileHandler', 'modFileHandler', '', array('context' => $this->modx->context->get('key')));

        /* pasted from processors.element.tv.renders.mgr.input*/
        /* get working context */
        $wctx = isset($_GET['wctx']) && !empty($_GET['wctx']) ? $this->modx->sanitizeString($_GET['wctx']) : '';
        if (!empty($wctx)) {
            $workingContext = $this->modx->getContext($wctx);
            if (!$workingContext) {
                return $modx->error->failure($this->modx->lexicon('permission_denied'));
            }
            $wctx = $workingContext->get('key');
        } else {
            $wctx = $this->modx->context->get('key');
        }

        $this->working_context = $wctx;

        if (is_object($tv)) {
            $this->source = $tv->getSource($this->working_context, false);
        }


        /* pasted end*/

        //$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH);
        //$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);

        //$columns = $this->modx->fromJSON($this->modx->getOption('columns', $properties, $default_columns));
        //$columns = empty($properties['columns']) ? $this->modx->fromJSON($default_columns) : $columns;

        $columns = empty($columns) ? $this->getColumns() : $columns;

        $item = array();
        $pathconfigs = array();
        $cols = array();
        $fields = array();
        $colidx = 0;

        if (is_array($columns) && count($columns) > 0) {
            foreach ($columns as $key => $column) {
                $field = array();
                if (isset($column['type'])) {
                    $field['type'] = $column['type'];
                }
                $field['name'] = $column['dataIndex'];
                $field['mapping'] = $column['dataIndex'];
                $fields[] = $field;
                $column['show_in_grid'] = isset($column['show_in_grid']) ? (int)$column['show_in_grid'] : 1;

                if (!empty($column['show_in_grid'])) {
                    $col = array();
                    $col['dataIndex'] = $column['dataIndex'];
                    $col['header'] = htmlentities($column['header'], ENT_QUOTES, $this->modx->getOption('modx_charset'));
                    $col['sortable'] = isset($column['sortable']) && $column['sortable'] == 'true' ? true : false;
                    if (isset($column['width']) && !empty($column['width'])) {
                        $col['width'] = (int)$column['width'];
                    }

                    if (isset($column['renderer']) && !empty($column['renderer'])) {
                        $col['renderer'] = $column['renderer'];

                        $handlers[] = $column['renderer'];
                    }
                    $cols[] = $col;
                    $pathconfigs[$colidx] = isset($inputTvs[$field['name']]) ? $this->prepareSourceForGrid($inputTvs[$field['name']]) : array();
                    $colidx++;
                }

                $item[$field['name']] = isset($column['default']) ? $column['default'] : '';


            }
        }

        $gf = '';
        if (count($handlers) > 0) {
            $gridfunctions = array();
            $collectedhandlers = array();
            foreach ($handlers as $handler) {
                if (!in_array($handler, $collectedhandlers) && isset($this->customconfigs['gridfunctions'][$handler])) {
                    $gridfunction = $this->customconfigs['gridfunctions'][$handler];
                    if (!empty($gridfunction)) {
                        $collectedhandlers[] = $handler;
                        $gridfunctions[] = $gridfunction;
                    }
                }
            }
            if (count($gridfunctions) > 0) {
                $gf = ',' . str_replace($search, $replace, implode(',', $gridfunctions));
            }
        }

        $this->customconfigs['gridfunctions'] = $gf;

        $newitem[] = $item;

        //print_r(array_keys($this->customconfigs));

        //$controller->setPlaceholder('i18n', $this->migxi18n);

        $controller->setPlaceholder('filterDefaults', $this->modx->toJSON($filterDefaults));
        $controller->setPlaceholder('tv_id', $tv_id);
        $controller->setPlaceholder('migx_lang', $this->modx->toJSON($this->migxlang));
        $controller->setPlaceholder('properties', $properties);
        $controller->setPlaceholder('resource', $resource);
        $controller->setPlaceholder('configs', $this->config['configs']);
        $controller->setPlaceholder('reqConfigs', $this->modx->getOption('configs', $_REQUEST, ''));
        $controller->setPlaceholder('object_id', $this->modx->getOption('object_id', $_REQUEST, ''));
        $controller->setPlaceholder('reqTempParams', $this->modx->getOption('tempParams', $_REQUEST, ''));
        $controller->setPlaceholder('connected_object_id', $this->modx->getOption('object_id', $_REQUEST, ''));
        $controller->setPlaceholder('pathconfigs', $this->modx->toJSON($pathconfigs));
        $controller->setPlaceholder('columns', $this->modx->toJSON($cols));
        $controller->setPlaceholder('fields', $this->modx->toJSON($fields));
        $controller->setPlaceholder('newitem', $this->modx->toJSON($newitem));
        $controller->setPlaceholder('base_url', $this->modx->getOption('base_url'));
        $controller->setPlaceholder('myctx', $wctx);
        $controller->setPlaceholder('auth', $_SESSION["modx.{$this->modx->context->get('key')}.user.token"]);
        $controller->setPlaceholder('customconfigs', $this->customconfigs);
        $controller->setPlaceholder('win_id', $this->customconfigs['win_id']);
        $controller->setPlaceholder('update_win_title', !empty($this->customconfigs['update_win_title']) ? $this->customconfigs['update_win_title'] : 'MIGX');

    }

    function getColumnRenderOptions($col = '*', $indexfield = 'idx', $format = 'json', $getdefaultclickaction = false) {
        $columns = $this->getColumns();
        $columnrenderoptions = array();
        $optionscolumns = array();
        if (is_array($columns)) {
            foreach ($columns as $column) {
                $defaultclickaction = '';
                
                $renderer = $this->modx->getOption('renderer', $column, '');
                $renderoptions = $this->modx->getOption('renderoptions', $column, '');
                $renderchunktpl = $this->modx->getOption('renderchunktpl', $column, '');
                $options = $this->modx->fromJson($column['renderoptions']);
                
                if ($getdefaultclickaction && !empty($column['clickaction'])) {
                    $option = array();
                    $defaultclickaction = $column['clickaction'];
                    $option['clickaction'] = $column['clickaction'];
                    $option['selectorconfig'] = $this->modx->getOption('selectorconfig', $column, '');
                    $defaultselectorconfig = $option['selectorconfig'];
                    $columnrenderoptions[$column['dataIndex']]['default_clickaction'] = $option;
                }

                if (is_array($options) && count($options)>0) {
                    foreach ($options as $key => $option) {
                        $option['idx'] = $key;
                        $option['_renderer'] = $renderer;
                        $option['clickaction'] = empty($option['clickaction']) && !empty($defaultclickaction) ? $defaultclickaction : $option['clickaction'];
                        $option['selectorconfig'] = $this->modx->getOption('selectorconfig', $column, '');
                        $option['selectorconfig'] = empty($option['selectorconfig']) && !empty($defaultselectorconfig) ? $defaultselectorconfig : $option['selectorconfig'];
                        $columnrenderoptions[$column['dataIndex']][$option[$indexfield]] = $format == 'json' ? $this->modx->toJson($option) : $option;
                    }
                } elseif (!empty($renderer) && $renderer == 'this.renderChunk') {
                    $option['idx'] = 0;
                    $option['_renderer'] = $renderer;
                    $option['_renderchunktpl'] = $renderchunktpl;
                    $columnrenderoptions[$column['dataIndex']][$option[$indexfield]] = $format == 'json' ? $this->modx->toJson($option) : $option;
                }
            }
        }

        return $col == '*' ? $columnrenderoptions : $columnrenderoptions[$col];
    }

    function renderChunk($tpl, $properties = array(), $getChunk = true, $printIfemty = true) {

        $value = $this->parseChunk($tpl, $properties, $getChunk, $printIfemty);

        $this->modx->getParser();
        /*parse all non-cacheable tags and remove unprocessed tags, if you want to parse only cacheable tags set param 3 as false*/
        $this->modx->parser->processElementTags('', $value, true, true, '[[', ']]', array());

        return $value;
    }

    function checkRenderOptions($rows) {
        $columnrenderoptions = $this->getColumnRenderOptions('*', 'value', 'array');
        //print_r($columnrenderoptions);
        $outputrows = is_array($rows) ? $rows : array();
        if (count($columnrenderoptions) > 0) {
            $outputrows = array();
            foreach ($rows as $row) {
                
                foreach ($columnrenderoptions as $column => $options) {
                    $value = $this->modx->getOption($column,$row,'');
                    
                    $row[$column . '_ro'] = isset($options[$value]) ? $this->modx->toJson($options[$value]) : '';
                    foreach ($options as $option) {
                        if ($option['_renderer'] == 'this.renderChunk') {
                            $row['_this.value'] = $value;
                            $properties = $row;
                            $properties['_request'] = $_REQUEST;
                            $renderchunktpl = $this->modx->getOption('_renderchunktpl', $option, '');
                            if (!empty($renderchunktpl)){
                                $row[$column] = $this->renderChunk($renderchunktpl, $properties,false);    
                            }
                            else{
                                $row[$column] = $this->renderChunk($option['name'], $properties);
                            }
                            
                        }
                        break;
                    }
                }
                $outputrows[] = $row;
            }
        }
        return $outputrows;

    }

    function prepareSourceForGrid($inputTv) {
        if (!empty($inputTv['inputTV']) && $tv = $this->modx->getObject('modTemplateVar', array('name' => $inputTv['inputTV']))) {

        } else {
            $tv = $this->modx->newObject('modTemplateVar');
        }

        $mediasource = $this->getFieldSource($inputTv, $tv);
        return '&source=' . $mediasource->get('id');

    }

    function getFieldSource($field, &$tv) {
        //source from config

        $sourcefrom = isset($field['sourceFrom']) && !empty($field['sourceFrom']) ? $field['sourceFrom'] : 'config';

        if ($sourcefrom == 'config' && isset($field['sources'])) {
            if (is_array($field['sources'])) {
                foreach ($field['sources'] as $context => $sourceid) {
                    $sources[$context] = $sourceid;
                }
            } else {
                $fsources = $this->modx->fromJson($field['sources']);
                if (is_array($fsources)) {
                    foreach ($fsources as $source) {
                        if (isset($source['context']) && isset($source['sourceid'])) {
                            $sources[$source['context']] = $source['sourceid'];
                        }
                    }
                }
            }

        }

        if (isset($sources[$this->working_context]) && !empty($sources[$this->working_context])) {
            //try using field-specific mediasource from config
            if ($mediasource = $this->modx->getObject('sources.modMediaSource', $sources[$this->working_context])) {
                return $mediasource;
            }
        }

        if ($this->source && $sourcefrom == 'migx') {
            //use global MIGX-mediasource for all TVs
            $tv->setSource($this->source);
            $mediasource = $this->source;
        } else {
            //useTV-specific mediasource
            $mediasource = $tv->getSource($this->working_context);
        }

        return $mediasource;
    }

    function generateTvTab($tvnames) {
        $tvnames = !empty($tvnames) ? explode(',', $tvnames) : array();
        $fields = array();
        foreach ($tvnames as $tvname) {
            $field['field'] = $tvname;
            $field['inputTV'] = $tvname;
            $fields[] = $field;
        }
        return $fields;
    }

    function checkForConnectedResource($resource_id = false, &$config) {
        if ($resource_id) {
            $check_resid = $this->modx->getOption('check_resid', $config);
            if ($check_resid == '@TV' && $resource = $this->modx->getObject('modResource', $resource_id)) {
                if ($check = $resource->getTvValue($config['check_resid_TV'])) {
                    $check_resid = $check;
                }
            }
            if (!empty($check_resid)) {
                //$c->where("CONCAT('||',resource_ids,'||') LIKE '%||{$resource_id}||%'", xPDOQuery::SQL_AND);
                return true;
            }
        }
        return false;
    }


    function createForm(&$tabs, &$record, &$allfields, &$categories, $scriptProperties) {
        $fieldid = 0;

        $input_prefix = $this->modx->getOption('input_prefix', $scriptProperties, '');
        $input_prefix = !empty($input_prefix) ? $input_prefix . '_' : '';
        $rte = isset($scriptProperties['which_editor']) ? $scriptProperties['which_editor'] : $this->modx->getOption('which_editor', '', $this->modx->_userConfig);

        foreach ($tabs as $tabid => $tab) {
            $tvs = array();
            $fields = $this->modx->getOption('fields', $tab, array());
            $fields = is_array($fields) ? $fields : $this->modx->fromJson($fields);
            if (is_array($fields) && count($fields) > 0) {

                foreach ($fields as &$field) {
                    $fieldid++;
                    /*generate unique tvid, must be numeric*/
                    /*todo: find a better solution*/
                    $field['tv_id'] = $input_prefix . $scriptProperties['tv_id'] . '_' . $fieldid;

                    if (isset($field['description_is_code']) && !empty($field['description_is_code'])) {
                        $tv = $this->modx->newObject('modTemplateVar');
                        $tv->set('description', $this->renderChunk($field['description'], $record, false, false));
                        $tv->set('type', 'description_is_code');
                        //we change the phptype, that way we can use any id, not only integers (issues on windows-systems with big integers!)
                        $tv->_fieldMeta['id']['phptype'] = 'string';
                        $tv->set('id', $field['tv_id']);
                    } else {

                        if (isset($field['inputTV']) && $tv = $this->modx->getObject('modTemplateVar', array('name' => $field['inputTV']))) {
                            $params = $tv->get('input_properties');
                        } else {
                            $tv = $this->modx->newObject('modTemplateVar');
                            $tv->set('type', !empty($field['inputTVtype']) ? $field['inputTVtype'] : 'text');
                        }
                        $o_type = $tv->get('type');
                        if ($tv->get('type') == 'richtext') {
                            $tv->set('type', 'migx' . strtolower($rte));
                        }

                        //we change the phptype, that way we can use any id, not only integers (issues on windows-systems with big integers!)
                        $tv->_fieldMeta['id']['phptype'] = 'string';

                        /*
                        $tv->set('id','skdjflskjd');
                        echo 'id:'. $tv->get('id');
                        $tv->_fieldMeta['id']['phptype'] = 'string';
                        echo($tv->_fieldMeta['id']['phptype']); 
                        $tv->set('id','skdjflskjd');
                        echo 'id:'. $tv->get('id');                                               
                        */

                        if (!empty($field['inputOptionValues'])) {
                            $tv->set('elements', $field['inputOptionValues']);
                        }
                        if (!empty($field['default'])) {
                            $tv->set('default_text', $tv->processBindings($field['default']));
                        }
                        if (!empty($field['configs'])) {
                            $cfg = $this->modx->fromJson($field['configs']);
                            if (is_array($cfg)) {
                                $params = array_merge($params, $cfg);
                            } else {
                                $params['configs'] = $field['configs'];
                            }
                        }

                        /*insert actual value from requested record, convert arrays to ||-delimeted string */
                        $fieldvalue = '';
                        if (isset($record[$field['field']])) {
                            $fieldvalue = $record[$field['field']];
                            if (is_array($fieldvalue)) {
                                $fieldvalue = is_array($fieldvalue[0]) ? $this->modx->toJson($fieldvalue) : implode('||', $fieldvalue);
                            }
                        }


                        $tv->set('value', $fieldvalue);
                        if (!empty($field['caption'])) {
                            $field['caption'] = htmlentities($field['caption'], ENT_QUOTES, $this->modx->getOption('modx_charset'));
                            $tv->set('caption', $field['caption']);
                        }

                        if (!empty($field['description'])) {
                            $field['description'] = htmlentities($field['description'], ENT_QUOTES, $this->modx->getOption('modx_charset'));
                            $tv->set('description', $field['description']);
                        }

                        $field['array_tv_id'] = $field['tv_id'] . '[]';

                        $allfield = array();
                        $allfield['field'] = $field['field'];
                        $allfield['tv_id'] = $field['tv_id'];
                        $allfield['array_tv_id'] = $field['array_tv_id'];
                        $allfields[] = $allfield;

                        $mediasource = $this->getFieldSource($field, $tv);
                        $tv->setSource($mediasource);
                        $tv->set('id', $field['tv_id']);

                        /*
                        $default = $tv->processBindings($tv->get('default_text'), $resourceId);
                        if (strpos($tv->get('default_text'), '@INHERIT') > -1 && (strcmp($default, $tv->get('value')) == 0 || $tv->get('value') == null)) {
                        $tv->set('inherited', true);
                        }
                        */

                        if ($tv->get('value') == null) {
                            $v = $tv->get('default_text');
                            if ($tv->get('type') == 'checkbox' && $tv->get('value') == '') {
                                $v = '';
                            }
                            $tv->set('value', $v);
                        }


                        $this->modx->smarty->assign('tv', $tv);


                        /* move this part into a plugin onMediaSourceGetProperties and create a mediaSource - property 'autoCreateFolder'
                        * may be performancewise its better todo that here?
                        
                        if (!empty($properties['basePath'])) {
                        if ($properties['autoResourceFolders'] == 'true') {
                        $params['basePath'] = $basePath . $scriptProperties['resource_id'] . '/';
                        $targetDir = $params['basePath'];

                        $cacheManager = $this->modx->getCacheManager();
                        // if directory doesnt exist, create it 
                        if (!file_exists($targetDir) || !is_dir($targetDir)) {
                        if (!$cacheManager->writeTree($targetDir)) {
                        $this->modx->log(modX::LOG_LEVEL_ERROR, '[MIGX] Could not create directory: ' . $targetDir);
                        return $this->modx->error->failure('Could not create directory: ' . $targetDir);
                        }
                        }
                        // make sure directory is readable/writable 
                        if (!is_readable($targetDir) || !is_writable($targetDir)) {
                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[MIGX] Could not write to directory: ' . $targetDir);
                        return $this->modx->error->failure('Could not write to directory: ' . $targetDir);
                        }
                        } else {
                        $params['basePath'] = $basePath;
                        }
                        }
                        */

                        if (!isset($params['allowBlank']))
                            $params['allowBlank'] = 1;

                        $value = $tv->get('value');
                        if ($value === null) {
                            $value = $tv->get('default_text');
                        }
                        $this->modx->smarty->assign('params', $params);
                        /* find the correct renderer for the TV, if not one, render a textbox */
                        $inputRenderPaths = $tv->getRenderDirectories('OnTVInputRenderList', 'input');

                        if ($o_type == 'richtext') {
                            $fallback = true;
                            foreach ($inputRenderPaths as $path) {
                                $renderFile = $path . $tv->get('type') . '.class.php';
                                if (file_exists($renderFile)) {
                                    $fallback = false;
                                    break;
                                }
                            }
                            if ($fallback) {
                                $tv->set('type', 'textarea');
                            }
                        }


                        $inputForm = $tv->getRender($params, $value, $inputRenderPaths, 'input', null, $tv->get('type'));

                        if (empty($inputForm))
                            continue;

                        $tv->set('formElement', $inputForm);
                    }


                    $tvs[] = $tv;
                }
            }

            $cat = array();
            $cat['category'] = $this->modx->getOption('caption', $tab, 'undefined');
            $cat['print_before_tabs'] = isset($tab['print_before_tabs']) && !empty($tab['print_before_tabs']) ? true : false;
            $cat['id'] = $tabid;
            $cat['tvs'] = $tvs;
            $categories[] = $cat;

        }

    }

    function extractInputTvs($formtabs) {
        //multiple different Forms
        // Note: use same field-names and inputTVs in all forms
        if (is_array($formtabs) && isset($formtabs[0]['formtabs'])) {
            $forms = $formtabs;
            $formtabs = array();
            foreach ($forms as $form) {
                foreach ($form['formtabs'] as $tab) {
                    $tab['formname'] = $form['formname'];
                    $formtabs[] = $tab;
                }
            }
        }


        $inputTvs = array();
        if (is_array($formtabs)) {
            foreach ($formtabs as $tabidx => $tab) {
                $formname = isset($tab['formname']) && !empty($tab['formname']) ? $tab['formname'] . '_' : '';
                if (isset($tab['fields'])) {
                    $fields = is_array($tab['fields']) ? $tab['fields'] : $this->modx->fromJson($tab['fields']);
                    if (is_array($fields)) {
                        foreach ($fields as $field) {
                            //$fieldkey = $formname.$field['field'];
                            if (isset($field['inputTV']) && !empty($field['inputTV'])) {
                                $inputTvs[$field['field']] = $field;
                                //for different inputTvs, for example with different mediasources, in multiple forms, currently not used for the grid
                                $inputTvs[$formname . $field['field']] = $field;
                            } elseif (isset($field['inputTVtype']) && !empty($field['inputTVtype'])) {
                                $inputTvs[$field['field']] = $field;
                                $inputTvs[$formname . $field['field']] = $field;
                            }
                        }
                    }
                }

            }
        }
        return $inputTvs;
    }

    function parseChunk($tpl, $fields = array(), $getChunk = true, $printIfemty = true) {

        $output = '';

        if ($getChunk) {
            if ($chunk = $this->modx->getObject('modChunk', array('name' => $tpl), true)) {
                $tpl = $chunk->getContent();
            } elseif (file_exists($tpl)) {
                $tpl = file_get_contents($tpl);
            } elseif (file_exists($this->modx->getOption('base_path') . $tpl)) {
                $tpl = file_get_contents($this->modx->getOption('base_path') . $tpl);
            } else {
                $tpl = false;
            }
        }

        if ($tpl) {
            $chunk = $this->modx->newObject('modChunk');
            $chunk->setCacheable(false);
            $chunk->setContent($tpl);

            $output = $chunk->process($fields);

        } elseif ($printIfemty) {
            $output = '<pre>' . print_r($fields, 1) . '</pre>';
        }

        return $output;

    }


    function sortTV($sort, &$c, $dir = 'ASC', $sortbyTVType = '') {
        $c->leftJoin('modTemplateVar', 'tvDefault', array("tvDefault.name" => $sort));
        $c->leftJoin('modTemplateVarResource', 'tvSort', array("tvSort.contentid = modResource.id", "tvSort.tmplvarid = tvDefault.id"));
        if (empty($sortbyTVType))
            $sortbyTVType = 'string';
        if ($this->modx->getOption('dbtype') === 'mysql') {
            switch ($sortbyTVType) {
                case 'integer':
                    $c->select("CAST(IFNULL(tvSort.value, tvDefault.default_text) AS SIGNED INTEGER) AS sortTV");
                    break;
                case 'decimal':
                    $c->select("CAST(IFNULL(tvSort.value, tvDefault.default_text) AS DECIMAL) AS sortTV");
                    break;
                case 'datetime':
                    $c->select("CAST(IFNULL(tvSort.value, tvDefault.default_text) AS DATETIME) AS sortTV");
                    break;
                case 'string':
                default:
                    $c->select("IFNULL(tvSort.value, tvDefault.default_text) AS sortTV");
                    break;
            }
        }
        $c->sortby("sortTV", $dir);

        return true;
    }

    function tvFilters($tvFilters = '', &$criteria) {

        //tvFilter::categories=inArray=[[+category]]


        $tvFilters = !empty($tvFilters) ? explode('||', $tvFilters) : array();
        if (!empty($tvFilters)) {
            $tmplVarTbl = $this->modx->getTableName('modTemplateVar');
            $tmplVarResourceTbl = $this->modx->getTableName('modTemplateVarResource');
            $conditions = array();
            $operators = array(
                '<=>' => '<=>',
                '===' => '=',
                '!==' => '!=',
                '<>' => '<>',
                '==' => 'LIKE',
                '!=' => 'NOT LIKE',
                '<<' => '<',
                '<=' => '<=',
                '=<' => '=<',
                '>>' => '>',
                '>=' => '>=',
                '=>' => '=>',
                '=inArray=' => '=inArray=');
            foreach ($tvFilters as $fGroup => $tvFilter) {
                $filterGroup = array();
                $filters = explode(',', $tvFilter);
                $multiple = count($filters) > 0;
                foreach ($filters as $filter) {
                    $operator = '==';
                    $sqlOperator = 'LIKE';
                    foreach ($operators as $op => $opSymbol) {
                        if (strpos($filter, $op, 1) !== false) {
                            $operator = $op;
                            $sqlOperator = $opSymbol;
                            break;
                        }
                    }
                    $tvValueField = 'tvr.value';
                    $tvDefaultField = 'tv.default_text';
                    $f = explode($operator, $filter);
                    if (count($f) == 2) {
                        $tvName = $this->modx->quote($f[0]);
                        if (is_numeric($f[1]) && !in_array($sqlOperator, array('LIKE', 'NOT LIKE'))) {
                            $tvValue = $f[1];
                            if ($f[1] == (integer)$f[1]) {
                                $tvValueField = "CAST({$tvValueField} AS SIGNED INTEGER)";
                                $tvDefaultField = "CAST({$tvDefaultField} AS SIGNED INTEGER)";
                            } else {
                                $tvValueField = "CAST({$tvValueField} AS DECIMAL)";
                                $tvDefaultField = "CAST({$tvDefaultField} AS DECIMAL)";
                            }
                        } elseif ($sqlOperator == '=inArray=') {
                            $sqlOperator = 'LIKE';
                            $tvValueField = "CONCAT('||',{$tvValueField},'||')";
                            $tvDefaultField = "CONCAT('||',{$tvDefaultField},'||')";
                            $tvValue = $this->modx->quote('%||' . $f[1] . '||%');
                        } else {
                            $tvValue = $this->modx->quote($f[1]);
                        }
                        if ($multiple) {
                            $filterGroup[] = "(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
                                "OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " .
                                ")";
                        } else {
                            $filterGroup = "(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
                                "OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " .
                                ")";
                        }
                    } elseif (count($f) == 1) {
                        $tvValue = $this->modx->quote($f[0]);
                        if ($multiple) {
                            $filterGroup[] = "EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id)";
                        } else {
                            $filterGroup = "EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id)";
                        }
                    }
                }
                $conditions[] = $filterGroup;
            }
            if (!empty($conditions)) {
                $firstGroup = true;
                foreach ($conditions as $cGroup => $c) {
                    if (is_array($c)) {
                        $first = true;
                        foreach ($c as $cond) {
                            if ($first && !$firstGroup) {
                                $criteria->condition($criteria->query['where'][0][1], $cond, xPDOQuery::SQL_OR, null, $cGroup);
                            } else {
                                $criteria->condition($criteria->query['where'][0][1], $cond, xPDOQuery::SQL_AND, null, $cGroup);
                            }
                            $first = false;
                        }
                    } else {
                        $criteria->condition($criteria->query['where'][0][1], $c, $firstGroup ? xPDOQuery::SQL_AND : xPDOQuery::SQL_OR, null, $cGroup);
                    }
                    $firstGroup = false;
                }
            }

            return true;

        }

    }

    public function debug($key, $value, $reset = false) {

        $debug[$key] = $value;
        $chunk = $this->modx->getObject('modChunk', array('name' => 'debug'));
        $oldContent = $reset ? '' : $chunk->getContent();
        $chunk->setContent($oldContent . print_r($debug, 1));
        $chunk->save();
    }

    function filterItems($where, $items) {

        $tempitems = array();
        foreach ($items as $item) {
            $include = true;
            foreach ($where as $key => $operand) {
                $key = explode(':', $key);
                $field = $key[0];
                $then = $include;
                $else = false;
                $subject = $item[$field];

                $operator = isset($key[1]) ? $key[1] : '=';
                $params = isset($key[2]) ? $key[2] : '';
                $operator = strtolower($operator);
                switch ($operator) {
                    case '!=':
                    case 'neq':
                    case 'not':
                    case 'isnot':
                    case 'isnt':
                    case 'unequal':
                    case 'notequal':
                        $output = (($subject != $operand) ? $then : (isset($else) ? $else : ''));
                        break;
                    case '<':
                    case 'lt':
                    case 'less':
                    case 'lessthan':
                        $output = (($subject < $operand) ? $then : (isset($else) ? $else : ''));
                        break;
                    case '>':
                    case 'gt':
                    case 'greater':
                    case 'greaterthan':
                        $output = (($subject > $operand) ? $then : (isset($else) ? $else : ''));
                        break;
                    case '<=':
                    case 'lte':
                    case 'lessthanequals':
                    case 'lessthanorequalto':
                        $output = (($subject <= $operand) ? $then : (isset($else) ? $else : ''));
                        break;
                    case '>=':
                    case 'gte':
                    case 'greaterthanequals':
                    case 'greaterthanequalto':
                        $output = (($subject >= $operand) ? $then : (isset($else) ? $else : ''));
                        break;
                    case 'isempty':
                    case 'empty':
                        $output = empty($subject) ? $then : (isset($else) ? $else : '');
                        break;
                    case '!empty':
                    case 'notempty':
                    case 'isnotempty':
                        $output = !empty($subject) && $subject != '' ? $then : (isset($else) ? $else : '');
                        break;
                    case 'isnull':
                    case 'null':
                        $output = $subject == null || strtolower($subject) == 'null' ? $then : (isset($else) ? $else : '');
                        break;
                    case 'inarray':
                    case 'in_array':
                    case 'ia':
                    case 'in':
                        $operand = is_array($operand) ? $operand : explode(',', $operand);
                        $output = in_array($subject, $operand) ? $then : (isset($else) ? $else : '');
                        break;
                    case 'find':
                    case 'find_in_set':
                        $subject = explode(',', $subject);
                        $output = in_array($operand, $subject) ? $then : (isset($else) ? $else : '');
                        break;
                    case 'find_pd':
                    case 'find_in_pipesdelimited_set':
                        $subject = explode('||', $subject);
                        $output = in_array($operand, $subject) ? $then : (isset($else) ? $else : '');
                        break;
                    case 'contains':
                        $output = strpos($subject, $operand) !== false ? $then : (isset($else) ? $else : '');
                        break;
                    case 'snippet':
                        $result = $this->modx->runSnippet($params, array('subject' => $subject, 'operand' => $operand));
                        $output = !empty($result) ? $then : (isset($else) ? $else : '');
                        break;
                    case '==':
                    case '=':
                    case 'eq':
                    case 'is':
                    case 'equal':
                    case 'equals':
                    case 'equalto':
                    default:
                        $output = (($subject == $operand) ? $then : (isset($else) ? $else : ''));
                        break;
                }

                $include = $output ? $output : false;

            }
            if ($include) {
                $tempitems[] = $item;
            }

        }
        return $tempitems;


    }

    /**
     * Sort DB result
     *
     * @param array $data Result of sql query as associative array
     * 
     * @param array $options Sortoptions as array 
     * 
     *
     * <code>
     *
     * // You can sort data by several columns e.g.
     * $data = array();
     * for ($i = 1; $i <= 10; $i++) {
     *     $data[] = array( 'id' => $i,
     *                      'first_name' => sprintf('first_name_%s', rand(1, 9)),
     *                      'last_name' => sprintf('last_name_%s', rand(1, 9)),
     *                      'date' => date('Y-m-d', rand(0, time()))
     *                  );
     * }
     * 
     * $options = array(array('sortby'=>'date','sortdir'=>'DESC','sortmode'=>'numeric'));
     * $data = sortDbResult($data, $options);
     * printf('<pre>%s</pre>', print_r($data, true));
     * 
     * $options = array(array('sortby'=>'last_name','sortdir'=>'ASC','sortmode'=>'string'),array('sortby'=>'first_name','sortdir'=>'ASC','sortmode'=>'string'));
     * $data = sortDbResult($data, $options);
     * printf('<pre>%s</pre>', print_r($data, true));
     *
     * </code>
     *
     * @return array $data - Sorted data
     */

    function sortDbResult($_data, $options = array()) {


        $sortmodes = array();
        $sortmodes['numeric'] = SORT_NUMERIC;
        $sortmodes['string'] = SORT_STRING;
        $sortmodes['regular'] = SORT_REGULAR;

        $sortdirs = array();
        $sortdirs['ASC'] = SORT_ASC;
        $sortdirs['DESC'] = SORT_DESC;


        $_rules = array();
        if (count($options) > 0) {
            foreach ($options as $option) {
                $rule['name'] = isset($option['sortby']) ? (string )$option['sortby'] : '';
                if (empty($rule['name']) || (is_array(current($_data)) && !in_array($rule['name'], array_keys(current($_data))))) {
                    continue;
                }
                $rule['order'] = isset($option['sortdir']) && isset($sortdirs[$option['sortdir']]) ? $sortdirs[$option['sortdir']] : $sortdirs['ASC'];
                $rule['mode'] = isset($option['sortmode']) && isset($sortmodes[$option['sortmode']]) ? $sortmodes[$option['sortmode']] : $sortmodes['regular'];
                $_rules[] = $rule;
            }

        }

        $_cols = array();
        foreach ($_data as $_k => $_row) {
            foreach ($_rules as $_rule) {
                if (!isset($_cols[$_rule['name']])) {
                    $_cols[$_rule['name']] = array();
                    $_params[] = &$_cols[$_rule['name']];
                    $_params[] = $_rule['order'];
                    $_params[] = $_rule['mode'];
                }
                $_cols[$_rule['name']][$_k] = $_row[$_rule['name']];
            }
        }
        $_params[] = &$_data;
        call_user_func_array('array_multisort', $_params);
        return $_data;


    }


    public function prepareJoins($classname, $joins, &$c) {
        if (is_array($joins)) {
            foreach ($joins as $join) {
                $jalias = $this->modx->getOption('alias', $join, '');
                $joinclass = $this->modx->getOption('classname', $join, '');
                $selectfields = $this->modx->getOption('selectfields', $join, '');
                $on = $this->modx->getOption('on', $join, null);
                if (!empty($jalias)) {
                    if (empty($joinclass) && $fkMeta = $this->modx->getFKDefinition($classname, $jalias)) {
                        $joinclass = $fkMeta['class'];
                    }
                    if (!empty($joinclass)) {
                        /*
                        if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
                        $localkey = $joinFkMeta['local'];
                        }    
                        */
                        $selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
                        $c->leftjoin($joinclass, $jalias, $on);
                        $c->select($this->modx->getSelectColumns($joinclass, $jalias, $jalias . '_', $selectfields));
                    }
                }
            }
        }
    }

    public function getTemplate($rowtpl, $template) {
        if (!isset($template[$rowtpl])) {
            if (substr($rowtpl, 0, 6) == "@FILE:") {
                $template[$rowtpl] = file_get_contents($this->modx->config['base_path'] . substr($rowtpl, 6));
            } elseif (substr($rowtpl, 0, 6) == "@CODE:") {
                $template[$rowtpl] = substr($rowtpl, 6);
            } elseif ($chunk = $this->modx->getObject('modChunk', array('name' => $rowtpl), true)) {
                $template[$rowtpl] = $chunk->getContent();
            } else {
                $template[$rowtpl] = false;
            }
        }
        return $template;
    }

    public function addConnectorParams($properties, $unset = '') {
        global $modx;
        $properties['connectorUrl'] = $this->config['connectorUrl'];
        $params = array();
        $unset = explode(',', $unset);
        $req = $_REQUEST;
        foreach ($unset as $param) {
            unset($req[$param]);
        }
        foreach ($req as $key => $value) {
            $params[] = $key . '=' . $value;
        }
        $properties['urlparams'] = implode('&', $params);
        return $properties;
    }

    public function getDivisors($integer) {
        $divisors = array();
        for ($i = $integer; $i > 1; $i--) {
            if (($integer % $i) === 0) {
                $divisors[] = $i;
            }
        }
        return $divisors;
    }

    function importconfig($array) {
        $excludekeys = array('getlistwhere', 'joins', 'configs');
        return $this->recursive_encode($array, $excludekeys);
    }

    function recursive_encode($array, $excludekeys = array()) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {

                if (!is_int($key) && in_array($key, $excludekeys)) {
                    $array[$key] = !empty($value) ? json_encode($value) : $value;
                    //$array[$key] = $this->recursive_encode($value, $excludekeys);
                } else {
                    $array[$key] = $this->recursive_encode($value, $excludekeys);
                }
            }
            if (!$this->is_assoc($array)) {
                $array = json_encode($array);
            }
        }
        return $array;
    }

    function is_assoc($array) {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    function recursive_decode($array) {
        foreach ($array as $key => $value) {
            if (is_string($value) && $decoded = json_decode($value, true)) {
                $array[$key] = $this->recursive_decode($decoded);
            } else {
                $array[$key] = $this->recursive_decode($value);
            }
        }
        return $array;
    }

    /**
     * Indents a flat JSON string to make it more human-readable.
     * Source: http://recursive-design.com/blog/2008/03/11/format-json-with-php/
     *
     * @param string $json The original JSON string to process.
     *
     * @return string Indented version of the original JSON string.
     */
    function indent($json) {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '  ';
        $newLine = "\n";
        $prevChar = '';
        $outOfQuotes = true;

        for ($i = 0; $i <= $strLen; $i++) {
            // Grab the next character in the string.
            $char = substr($json, $i, 1);
            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
                // If this character is the end of an element,
                // output a new line and indent the next line.
            } else
                if (($char == '}' || $char == ']') && $outOfQuotes) {
                    $result .= $newLine;
                    $pos--;
                    for ($j = 0; $j < $pos; $j++) {
                        $result .= $indentStr;
                    }
                }
            // Add the character to the result string.
            $result .= $char;
            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }
        return $result;
    }


}
