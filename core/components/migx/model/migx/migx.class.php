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

    public function getXpdoInstanceAndAddPackage($scriptProperties) {
        $modx = &$this->modx;

        $prefix = isset($scriptProperties['prefix']) ? $scriptProperties['prefix'] : '';
        $usecustomprefix = $modx->getOption('useCustomPrefix', $scriptProperties, '');
        $usecustomprefix = empty($usecustomprefix) ? $modx->getOption('usecustomprefix', $scriptProperties, '') : $usecustomprefix;
        $usecustomprefix = empty($usecustomprefix) ? $modx->getOption('use_custom_prefix', $scriptProperties, '') : $usecustomprefix;

        if (empty($prefix)) {
            $prefix = !empty($usecustomprefix) ? $prefix : null;
        }

        $packageName = $modx->getOption('packageName', $scriptProperties, '');

        if (!empty($packageName)) {
            $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
            $modelpath = $packagepath . 'model/';

            $xpdo_name = $packageName . '_xpdo';

            if (isset($this->modx->$xpdo_name)) {
                //create xpdo-instance for that package only once
                $xpdo = &$this->modx->$xpdo_name;
            } elseif (file_exists($packagepath . 'config/config.inc.php')) {
                include ($packagepath . 'config/config.inc.php');
                if (is_null($prefix) && isset($table_prefix)) {
                    $prefix = $table_prefix;
                }
                $charset = '';
                if (!empty($database_connection_charset)) {
                    $charset = ';charset=' . $database_connection_charset;
                }
                $dsn = $database_type . ':host=' . $database_server . ';dbname=' . $dbase . $charset;
                $xpdo = new xPDO($dsn, $database_user, $database_password);
                //echo $o=($xpdo->connect()) ? 'Connected' : 'Not Connected';

                $this->modx->$xpdo_name = &$xpdo;

            } else {
                $xpdo = &$this->modx;
            }

            $xpdo->addPackage($packageName, $modelpath, $prefix);
        } else {
            $xpdo = &$this->modx;
        }

        return $xpdo;
    }

    public function prepareQuery(&$xpdo, $scriptProperties) {
        $modx = &$this->modx;

        $limit = $modx->getOption('limit', $scriptProperties, '0');
        $offset = $modx->getOption('offset', $scriptProperties, 0);
        $totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');

        $where = $modx->getOption('where', $scriptProperties, array());
        $where = !empty($where) && !is_array($where) ? $modx->fromJSON($where) : $where;
        $queries = $modx->getOption('queries', $scriptProperties, array());
        $queries = !empty($queries) && !is_array($queries) ? $modx->fromJSON($queries) : $queries;
        $sortConfig = $modx->getOption('sortConfig', $scriptProperties, array());
        $sortConfig = !empty($sortConfig) && !is_array($sortConfig) ? $modx->fromJSON($sortConfig) : $sortConfig;
        $joins = $modx->getOption('joins', $scriptProperties, array());
        $joins = !empty($joins) && !is_array($joins) ? $modx->fromJSON($joins) : $joins;

        $selectfields = $modx->getOption('selectfields', $scriptProperties, '');
        $selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
        $classname = $scriptProperties['classname'];
        $groupby = $modx->getOption('groupby', $scriptProperties, '');

        $debug = $modx->getOption('debug', $scriptProperties, false);

        $c = $xpdo->newQuery($classname);

        $c->select($xpdo->getSelectColumns($classname, $classname, '', $selectfields));

        if (is_array($joins) && count($joins) > 0) {
            $this->prepareJoins($classname, $joins, $c);
        }

        if (!empty($where)) {
            foreach ($where as $key => $value) {
                if (strstr($key, 'MONTH') || strstr($key, 'YEAR') || strstr($key, 'DATE')) {
                    $c->where($key . " = " . $value, xPDOQuery::SQL_AND);
                    unset($where[$key]);
                }
            }
            $c->where($where);
        }

        if (!empty($queries)) {
            foreach ($queries as $key => $query) {
                $c->where($query, $key);
            }

        }

        if (!empty($groupby)) {
            $c->groupby($groupby);
        }

        //set "total" placeholder for getPage
        $total = $xpdo->getCount($classname, $c);
        $modx->setPlaceholder($totalVar, $total);

        if (is_array($sortConfig)) {
            foreach ($sortConfig as $sort) {
                $sortby = $sort['sortby'];
                $sortdir = isset($sort['sortdir']) ? $sort['sortdir'] : 'ASC';
                $c->sortby($sortby, $sortdir);
            }
        }

        //&limit, &offset
        if (!empty($limit)) {
            $c->limit($limit, $offset);
        }
        $c->prepare();
        if ($debug) {
            echo $c->toSql();
        }
        return $c;
    }

    public function getCollection($c) {
        $rows = array();
        if ($c->stmt->execute()) {
            if (!$rows = $c->stmt->fetchAll(PDO::FETCH_ASSOC)) {
                $rows = array();
            }
        }
        return $rows;
    }

    public function renderOutput($rows, $scriptProperties) {

        $modx = &$this->modx;

        $tpl = $modx->getOption('tpl', $scriptProperties, '');
        $wrapperTpl = $modx->getOption('wrapperTpl', $scriptProperties, '');
        $tplFirst = $modx->getOption('tplFirst', $scriptProperties, '');
        $tplLast = $modx->getOption('tplLast', $scriptProperties, '');

        $toSeparatePlaceholders = $modx->getOption('toSeparatePlaceholders', $scriptProperties, false);
        $toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
        $outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, '');
        //$placeholdersKeyField = $modx->getOption('placeholdersKeyField', $scriptProperties, 'MIGX_id');
        $placeholdersKeyField = $modx->getOption('placeholdersKeyField', $scriptProperties, 'id');
        $toJsonPlaceholder = $modx->getOption('toJsonPlaceholder', $scriptProperties, false);

        $addfields = $modx->getOption('addfields', $scriptProperties, '');
        $addfields = !empty($addfields) ? explode(',', $addfields) : null;

        $properties = array();
        foreach ($scriptProperties as $property => $value) {
            $properties['property.' . $property] = $value;
        }

        $idx = 0;
        $output = array();
        $template = array();
        $count = count($rows);
        if ($count > 0) {
            foreach ($rows as $fields) {

                if (!empty($addfields)) {
                    foreach ($addfields as $addfield) {
                        $addfield = explode(':', $addfield);
                        $addname = $addfield[0];
                        $adddefault = isset($addfield[1]) ? $addfield[1] : '';
                        $fields[$addname] = $adddefault;
                    }
                }

                if ($toJsonPlaceholder) {
                    $output[] = $fields;
                } else {
                    $fields['_alt'] = $idx % 2;
                    $idx++;
                    $fields['_first'] = $idx == 1 ? true : '';
                    $fields['_last'] = $idx == $count ? true : '';
                    $fields['idx'] = $idx;
                    $rowtpl = '';
                    //get changing tpls from field
                    if (substr($tpl, 0, 7) == "@FIELD:") {
                        $tplField = substr($tpl, 7);
                        $rowtpl = $fields[$tplField];
                    }

                    if ($fields['_first'] && !empty($tplFirst)) {
                        $rowtpl = $tplFirst;
                    }
                    if ($fields['_last'] && empty($rowtpl) && !empty($tplLast)) {
                        $rowtpl = $tplLast;
                    }
                    $tplidx = 'tpl_' . $idx;
                    if (empty($rowtpl) && !empty($scriptProperties[$tplidx])) {
                        $rowtpl = $scriptProperties[$tplidx];
                    }
                    if ($idx > 1 && empty($rowtpl)) {
                        $divisors = $this->getDivisors($idx);
                        if (!empty($divisors)) {
                            foreach ($divisors as $divisor) {
                                $tplnth = 'tpl_n' . $divisor;
                                if (!empty($scriptProperties[$tplnth])) {
                                    $rowtpl = $scriptProperties[$tplnth];
                                    if (!empty($rowtpl)) {
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    $fields = array_merge($fields, $properties);

                    //get changing tpls by running a snippet to determine the current tpl
                    if (substr($tpl, 0, 9) == "@SNIPPET:") {
                        $snippet = substr($tpl, 9);
                        $rowtpl = $modx->runSnippet($snippet, $fields);
                    }

                    if (!empty($rowtpl)) {
                        $template = $this->getTemplate($tpl, $template);
                        $fields['_tpl'] = $template[$tpl];
                    } else {
                        $rowtpl = $tpl;

                    }
                    $template = $this->getTemplate($rowtpl, $template);

                    if ($template[$rowtpl]) {
                        $chunk = $modx->newObject('modChunk');
                        $chunk->setCacheable(false);
                        $chunk->setContent($template[$rowtpl]);

                        if (!empty($placeholdersKeyField) && isset($fields[$placeholdersKeyField])) {
                            $output[$fields[$placeholdersKeyField]] = $chunk->process($fields);
                        } else {
                            $output[] = $chunk->process($fields);
                        }
                    } else {
                        if (!empty($placeholdersKeyField)) {
                            $output[$fields[$placeholdersKeyField]] = '<pre>' . print_r($fields, 1) . '</pre>';
                        } else {
                            $output[] = '<pre>' . print_r($fields, 1) . '</pre>';
                        }
                    }
                }
            }
        }

        if ($toJsonPlaceholder) {
            $modx->setPlaceholder($toJsonPlaceholder, $modx->toJson($output));
            return '';
        }

        if (!empty($toSeparatePlaceholders)) {
            $modx->toPlaceholders($output, $toSeparatePlaceholders);
            return '';
        }

        if (is_array($output)) {
            $o = implode($outputSeparator, $output);
        } else {
            $o = $output;
        }

        if (!empty($o) && !empty($wrapperTpl)) {
            $template = $this->getTemplate($wrapperTpl);
            if ($template[$wrapperTpl]) {
                $chunk = $modx->newObject('modChunk');
                $chunk->setCacheable(false);
                $chunk->setContent($template[$wrapperTpl]);
                $properties['output'] = $o;
                $o = $chunk->process($properties);
            }
        }

        if (!empty($toPlaceholder)) {
            $modx->setPlaceholder($toPlaceholder, $o);
            return '';
        }

        return $o;
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
        $multiple_formtabs_label = $this->modx->getOption('multiple_formtabs_label', $this->customconfigs, 'Formname');
        $multiple_formtabs_field = $this->modx->getOption('multiple_formtabs_field', $this->customconfigs, 'MIGX_formname');

        $controller->setPlaceholder('multiple_formtabs_label', $multiple_formtabs_label);

        if (!empty($multiple_formtabs)) {
            if (isset($_REQUEST['loadaction']) && $_REQUEST['loadaction'] == 'switchForm') {
                $data = $this->modx->fromJson($this->modx->getOption('record_json', $_REQUEST, ''));
                if (is_array($data) && isset($data[$multiple_formtabs_field])) {
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

                    $ext = $object->get('extended');

                    $text = $this->modx->getOption('multiple_formtabs_optionstext', $ext, '');
                    $value = $this->modx->getOption('multiple_formtabs_optionsvalue', $ext, '');

                    $formname = array();
                    $formname['value'] = !empty($value) ? $value : $object->get('name');
                    $formname['text'] = !empty($text) ? $text : $object->get('name');
                    $formname['selected'] = 0;
                    if ($idx == 0) {
                        $firstformtabs = $this->modx->fromJson($object->get('formtabs'));
                    }
                    if (isset($record[$multiple_formtabs_field]) && $record[$multiple_formtabs_field] == $formname['value']) {
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
                $field['field'] = $multiple_formtabs_field;
                $field['tv_id'] = 'Formname';
                $allfields[] = $field;
            }
        }
        return $formtabs;
    }

    function loadConfigs($grid = true, $other = true, $properties = array(), $sender = '') {
        $winbuttons = array();
        $gridactionbuttons = array();
        $gridcolumnbuttons = array();
        $gridcontextmenus = array();
        $gridfunctions = array();
        $winfunctions = array();
        $renderer = array();
        $editors = array();
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
            if ($sender == 'mgr/fields' && ($req_configs == 'migxcolumns' || $req_configs == 'migxdbfilters')) {
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
                        $this->prepareConfigsArray($objectarray, $gridactionbuttons, $gridcontextmenus, $gridcolumnbuttons, $winbuttons);
                    }

                    if ($cfObject) {

                        $objectarray = $cfObject->toArray();
                        $this->prepareConfigsArray($objectarray, $gridactionbuttons, $gridcontextmenus, $gridcolumnbuttons, $winbuttons);

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
        $this->customconfigs['gridfunctions'] = array_merge($gridfunctions, $renderer, $editors);
        $this->customconfigs['winfunctions'] = $winfunctions;
        $this->customconfigs['windowbuttons'] = $winbuttons;
        //$defaulttask = empty($this->customconfigs['join_alias']) ? 'default' : 'default_join';
        $defaulttask = 'default';
        $this->customconfigs['task'] = empty($this->customconfigs['task']) ? $defaulttask : $this->customconfigs['task'];

    }


    public function prepareConfigsArray($objectarray, &$gridactionbuttons, &$gridcontextmenus, &$gridcolumnbuttons, &$winbuttons) {

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

        if (isset($this->customconfigs['winbuttonslist'])) {
            $winbuttonslist = $this->customconfigs['winbuttonslist'];
            if (!empty($winbuttonslist)) {
                $winbuttonslist = explode('||', $winbuttonslist);
                foreach ($winbuttonslist as $button) {
                    $winbuttons[$button]['active'] = 1;
                }
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
                $maincaption = empty($this->customconfigs['cmpmaincaption']) ? $maincaption : "'" . $this->replaceLang($this->customconfigs['cmpmaincaption']) . "'";

                $controller->setPlaceholder('config', $this->config);
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

    public function loadLang($prefix = 'migx') {
        $lang = $this->modx->lexicon->fetch($prefix);

        if (is_array($lang)) {
            $this->migxlang = isset($this->migxlang) && is_array($this->migxlang) ? array_merge($this->migxlang, $lang) : $lang;
            //$this->migxi18n = array();
            foreach ($lang as $key => $value) {
                $this->addLangValue($key, $value);
            }
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

        if (isset($this->langSearch) && isset($this->langReplace)) {
            $value = str_replace($this->langSearch, $this->langReplace, $value);
        }
        return $value;
    }

    public function prepareGrid($properties, &$controller, &$tv, $columns = array()) {

        
        $this->loadConfigs(false);
        //$lang = $this->modx->lexicon->fetch();

        $resource = is_object($this->modx->resource) ? $this->modx->resource->toArray() : array();
        $this->config['resource_id'] = $this->modx->getOption('id', $resource, '');
        $this->config['connected_object_id'] = $this->modx->getOption('object_id', $_REQUEST, '');
        $this->config['req_configs'] = $this->modx->getOption('configs', $_REQUEST, '');
        $this->config['media_source_id'] = $this->source->id;

        if (is_object($tv)) {
            $win_id = $tv->get('id');
            $tv_type = $tv->get('type');
        } else {
            $tv_type = '';
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

        //winbuttons
        $winbuttons = '';
        if (isset($this->customconfigs['windowbuttons'])) {
            if (is_array($this->customconfigs['windowbuttons']) && count($this->customconfigs['windowbuttons']) > 0) {
                $buttons_a = array();
                foreach ($this->customconfigs['windowbuttons'] as $button) {
                    if (!empty($button['active'])) {
                        unset($button['active']);
                        if (isset($button['handler'])) {
                            $handlerarr = explode(',', $button['handler']);
                            foreach ($handlerarr as $handler) {
                                if (!in_array($handler, $handlers)) {
                                    $handlers[] = $handler;
                                }
                            }
                        }
                        $buttons_a[] = str_replace('"', '', $this->modx->toJson($button));
                    }
                }
                if (count($buttons_a) > 0) {
                    $winbuttons = ',buttons:[' . implode(',', $buttons_a) . ']';
                } else {
                    $winbuttons = ",buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: this.cancel
        },{
            text: config.saveBtnText || _('done')
            ,scope: this
            ,handler: this.submit
        }]";
                }


            }
        }
        $this->customconfigs['winbuttons'] = $winbuttons;

        $buttons = array();
        if (count($this->customconfigs['gridactionbuttons']) > 0) {
            foreach ($this->customconfigs['gridactionbuttons'] as $button) {
                if (!empty($button['active'])) {
                    unset($button['active']);
                    if (isset($button['handler'])) {
                        $handlerarr = explode(',', $button['handler']);
                        $button['handler'] = $handlerarr[0]; //can have only one handler, use the first one
                        //load one or multiple handlers
                        foreach ($handlerarr as $handler) {
                            if (!in_array($handler, $handlers)) {
                                $handlers[] = $handler;
                            }
                        }
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
                $filter['emptytext'] = empty($filter['emptytext']) ? 'migx.search' : $filter['emptytext'];
                $filter['emptytext'] = str_replace(array('[[%', ']]'), '', $this->replaceLang('[[%' . $filter['emptytext'] . ']]'));
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
            $perRow = $this->modx->getOption('actionbuttonsperrow', $this->customconfigs, '4');
            $tbaractions[] = "
          {
            xtype: 'buttongroup',
            title: '[[%migx.actions]]',
            columns: {$perRow},
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
            $perRow = $this->modx->getOption('filtersperrow', $this->customconfigs, '4');
            $tbarfilters[] = "
          {
            xtype: 'buttongroup',
            title: '[[%migx.filters]]',
            columns: {$perRow},
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
        
        if ($tv_type == 'migx' && empty($menues)){
            //default context-menues for migx
            $menues = "
        m.push({
            text: '[[%migx.edit]]'
            ,handler: this.migx_update
        });
        m.push({
            text: '[[%migx.duplicate]]'
            ,handler: this.migx_duplicate
        });        
        m.push('-');
        m.push({
            text: '[[%migx.remove]]'
            ,handler: this.migx_remove
        });
        m.push('-');
        m.push({
            text: '[[%migx.move_to_top]]'
            ,handler: this.moveToTop
        }); 
        m.push({
            text: '[[%migx.move_to_bottom]]'
            ,handler: this.moveToBottom
        });                  
            ";
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

        $inputTvs = $this->extractFieldsFromTabs($formtabs);

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

                $fieldconfig = $this->modx->getOption($field['name'], $inputTvs, '');

                if (!empty($column['show_in_grid'])) {
                    $col = array();
                    $col['dataIndex'] = $column['dataIndex'];
                    $col['header'] = htmlentities($this->replaceLang($column['header']), ENT_QUOTES, $this->modx->getOption('modx_charset'));
                    $col['sortable'] = isset($column['sortable']) && $column['sortable'] == 'true' ? true : false;
                    if (isset($column['width']) && !empty($column['width'])) {
                        $col['width'] = (int)$column['width'];
                    }

                    if (isset($column['renderer']) && !empty($column['renderer'])) {
                        $col['renderer'] = $column['renderer'];
                        $handlers[] = $column['renderer'];
                    }
                    if (isset($column['editor']) && !empty($column['editor'])) {
                        $col['editor'] = $column['editor'];
                        $handlers[] = $column['editor'];
                    }

                    $cols[] = $col;
                    $pathconfigs[$colidx] = isset($inputTvs[$field['name']]) ? $this->prepareSourceForGrid($inputTvs[$field['name']]) : array();
                    $colidx++;
                }

                $default = isset($fieldconfig['default']) ? (string )$fieldconfig['default'] : '';
                $item[$field['name']] = isset($column['default']) ? $column['default'] : $default;


            }
        }

        $newitem[] = $item;

        $gf = '';
        $wf = '';
        if (count($handlers) > 0) {
            $gridfunctions = array();
            $winfunctions = array();
            $collectedhandlers = array();
            foreach ($handlers as $handler) {
                if (!in_array($handler, $collectedhandlers) && isset($this->customconfigs['gridfunctions'][$handler])) {
                    $gridfunction = $this->customconfigs['gridfunctions'][$handler];
                    if (!empty($gridfunction)) {
                        $collectedhandlers[] = $handler;
                        $gridfunctions[] = $gridfunction;
                    }
                }
                if (!in_array($handler, $collectedhandlers) && isset($this->customconfigs['winfunctions'][$handler])) {
                    $winfunction = $this->customconfigs['winfunctions'][$handler];
                    if (!empty($winfunction)) {
                        $collectedhandlers[] = $handler;
                        $winfunctions[] = $winfunction;
                    }
                }
            }
            if (count($gridfunctions) > 0) {
                $gf = ',' . str_replace($search, $replace, implode(',', $gridfunctions));
                $gf = str_replace('[[+newitem]]', $this->modx->toJson($newitem), $gf);
            }
            if (count($winfunctions) > 0) {
                $wf = ',' . str_replace($search, $replace, implode(',', $winfunctions));
            }
        }

        $this->customconfigs['gridfunctions'] = $gf;
        $this->customconfigs['winfunctions'] = $wf;


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
                $options = $this->modx->fromJson($renderoptions);

                if ($getdefaultclickaction && !empty($column['clickaction'])) {
                    $option = array();
                    $defaultclickaction = $column['clickaction'];
                    $option['clickaction'] = $column['clickaction'];
                    $option['selectorconfig'] = $this->modx->getOption('selectorconfig', $column, '');
                    $defaultselectorconfig = $option['selectorconfig'];
                    $columnrenderoptions[$column['dataIndex']]['default_clickaction'] = $option;
                }

                if (is_array($options) && count($options) > 0) {
                    foreach ($options as $key => $option) {
                        $option['idx'] = $key;
                        $option['_renderer'] = $renderer;
                        $option['clickaction'] = empty($option['clickaction']) && !empty($defaultclickaction) ? $defaultclickaction : $option['clickaction'];
                        $option['selectorconfig'] = $this->modx->getOption('selectorconfig', $column, '');
                        $option['selectorconfig'] = empty($option['selectorconfig']) && !empty($defaultselectorconfig) ? $defaultselectorconfig : $option['selectorconfig'];
                        if (isset($option['use_as_fallback']) && !empty($option['use_as_fallback'])) {
                            $option['value'] = 'use_as_fallback';
                        }
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
        if (is_array($rows) && count($columnrenderoptions) > 0) {
            $outputrows = array();
            foreach ($rows as $row) {

                foreach ($columnrenderoptions as $column => $options) {
                    $value = $this->modx->getOption($column, $row, '');
                    $row[$column . '_ro'] = isset($options[$value]) ? $this->modx->toJson($options[$value]) : '';
                    if (empty($row[$column . '_ro']) && isset($options['use_as_fallback'])) {
                        $row[$column . '_ro'] = $this->modx->toJson($options['use_as_fallback']);
                    }
                    foreach ($options as $option) {
                        if ($option['_renderer'] == 'this.renderChunk') {
                            $row['_this.value'] = $value;
                            $properties = $row;
                            $properties['_request'] = $_REQUEST;
                            $renderchunktpl = $this->modx->getOption('_renderchunktpl', $option, '');
                            if (!empty($renderchunktpl)) {
                                $row[$column] = $this->renderChunk($renderchunktpl, $properties, false);
                            } else {
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
            $mediasource = $tv->getSource($this->working_context,false);
        }
        
        //try to get the context-default-media-source
        if (!$mediasource){
            $defaultSourceId = null;
            if ($contextSetting = $this->modx->getObject('modContextSetting',array('key'=>'default_media_source','context_key'=>$this->working_context))){
                $defaultSourceId = $contextSetting->get('value');
            }
            $mediasource = modMediaSource::getDefaultSource($this->modx,$defaultSourceId);
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
                    if (isset($field['restrictive_condition'])) {
                        $props = $record;
                        $rc = $this->renderChunk($field['restrictive_condition'], $props, false, false);
                        if (!empty($rc)) {
                            continue;
                        }
                    }

                    $fieldname = $this->modx->getOption('field', $field, '');
                    $useDefaultIfEmpty = $this->modx->getOption('useDefaultIfEmpty', $field, 0);


                    $fieldid++;
                    /*generate unique tvid, must be numeric*/
                    /*todo: find a better solution*/
                    $field['tv_id'] = $input_prefix . $scriptProperties['tv_id'] . '_' . $fieldid;
                    $params = array();
                    $tv = false;


                    if (isset($field['inputTV']) && $tv = $this->modx->getObject('modTemplateVar', array('name' => $field['inputTV']))) {
                        $params = $tv->get('input_properties');
                        $params['inputTVid'] = $tv->get('id');
                    }

                    if (!empty($field['inputTVtype'])) {
                        $tv = $this->modx->newObject('modTemplateVar');
                        $tv->set('type', $field['inputTVtype']);
                    }

                    if (!$tv) {
                        $tv = $this->modx->newObject('modTemplateVar');
                        $tv->set('type', 'text');
                    }

                    $o_type = $tv->get('type');
                    if ($tv->get('type') == 'richtext') {
                        $tv->set('type', 'migx' . str_replace(' ','_',strtolower($rte)));
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
                    if (isset($field['display'])) {
                        $tv->set('display', $field['display']);
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
                    if (isset($record[$fieldname])) {
                        $fieldvalue = $record[$fieldname];
                        if (is_array($fieldvalue)) {
                            $fieldvalue = is_array($fieldvalue[0]) ? $this->modx->toJson($fieldvalue) : implode('||', $fieldvalue);
                        }
                    }

                    $tv->set('value', $fieldvalue);
                    if (!empty($field['caption'])) {
                        $field['caption'] = htmlentities($this->replaceLang($field['caption']), ENT_QUOTES, $this->modx->getOption('modx_charset'));
                        $tv->set('caption', $field['caption']);
                    }


                    $desc = '';
                    if (!empty($field['description'])) {
                        $desc = $field['description'];
                        $field['description'] = htmlentities($this->replaceLang($field['description']), ENT_QUOTES, $this->modx->getOption('modx_charset'));
                        $tv->set('description', $field['description']);
                    }

                    $allfield = array();
                    $allfield['field'] = $fieldname;
                    $allfield['tv_id'] = $field['tv_id'];
                    $allfield['array_tv_id'] = $field['tv_id'] . '[]';
                    $allfields[] = $allfield;

                    $field['array_tv_id'] = $field['tv_id'] . '[]';
                    $mediasource = $this->getFieldSource($field, $tv);
                    
                    $tv->setSource($mediasource);
                    $tv->set('id', $field['tv_id']);

                    /*
                    $default = $tv->processBindings($tv->get('default_text'), $resourceId);
                    if (strpos($tv->get('default_text'), '@INHERIT') > -1 && (strcmp($default, $tv->get('value')) == 0 || $tv->get('value') == null)) {
                    $tv->set('inherited', true);
                    }
                    */

                    $isnew = $this->modx->getOption('isnew', $scriptProperties, 0);
                    $isduplicate = $this->modx->getOption('isduplicate', $scriptProperties, 0);


                    if (!empty($useDefaultIfEmpty)) {
                        //old behaviour minus use now default values for checkboxes, if new record
                        if ($tv->get('value') == null) {
                            $v = $tv->get('default_text');
                            if ($tv->get('type') == 'checkbox' && $tv->get('value') == '') {
                                if (!empty($isnew) && empty($isduplicate)) {
                                    $v = $tv->get('default_text');
                                } else {
                                    $v = '';
                                }
                            }
                            $tv->set('value', $v);
                        }
                    } else {
                        //set default value, only on new records
                        if (!empty($isnew) && empty($isduplicate)) {
                            $v = $tv->get('default_text');
                            $tv->set('value', $v);
                        }
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
                    if (isset($field['description_is_code']) && !empty($field['description_is_code'])) {
                        $props = $record;
                        unset($field['description']);
                        $tv_array = $tv->toArray();
                        unset($tv_array['description']);
                        // don't parse the value - set special placeholder, replace it later
                        $tv_array['value'] = '[+[+value]]';
                        $tempvalue = $tv->get('value');
                        $props['record_json'] = $this->modx->toJson($props);
                        $props['tv_json'] = $this->modx->toJson($tv_array);
                        $props['field_json'] = $this->modx->toJson($field);
                        // don't parse the rendered formElement - set special placeholder, replace it later
                        $props['tv_formElement'] = '[+[+tv_formElement]]';

                        $tv->set('formElement', str_replace(array('[+[+value]]', '[+[+tv_formElement]]'), array($tempvalue, $inputForm), $this->renderChunk($desc, $props, false, false)));
                        $tv->set('type', 'description_is_code');
                    } else {


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

    function extractFieldsFromTabs($formtabs, $onlyTvTypes = false) {
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
                            } elseif (!$onlyTvTypes) {
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

    function extractInputTvs($formtabs) {

        return $this->extractFieldsFromTabs($formtabs, true);

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
                            $filterGroup[] = "(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " . "OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " . ")";
                        } else {
                            $filterGroup = "(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " . "OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " . ")";
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
                        $subject = is_array($subject) ? $subject : explode(',', $subject);
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
                $type = $this->modx->getOption('type', $join, 'left');
                $joinclass = $this->modx->getOption('classname', $join, '');
                $selectfields = $this->modx->getOption('selectfields', $join, '');
                $on = $this->modx->getOption('on', $join, null);
                if (!empty($jalias)) {
                    if (empty($joinclass) && $fkMeta = $c->xpdo->getFKDefinition($classname, $jalias)) {
                        $joinclass = $fkMeta['class'];
                    }
                    if (!empty($joinclass)) {
                        /*
                        if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
                        $localkey = $joinFkMeta['local'];
                        }    
                        */
                        $selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
                        switch ($type) {
                            case 'left':
                                $c->leftjoin($joinclass, $jalias, $on);
                                break;
                            case 'right':
                                $c->rightjoin($joinclass, $jalias, $on);
                                break;
                            case 'inner':
                                $c->innerjoin($joinclass, $jalias, $on);
                                break;
                            default:
                                $c->leftjoin($joinclass, $jalias, $on);
                                break;
                        }

                        $c->select($c->xpdo->getSelectColumns($joinclass, $jalias, $jalias . '_', $selectfields));
                    }
                }
            }
        }
    }

    public function addRelatedLinkIds(&$object, &$record, $config) {
        $modx = &$this->modx;
        $xpdo = &$object->xpdo;

        $link_classname = $modx->getOption('link_classname', $config, '');
        $link_alias = $modx->getOption('link_alias', $config, '');
        $postfield = $modx->getOption('postfield', $config, '');
        $id_field = $modx->getOption('id_field', $config, '');
        $link_field = $modx->getOption('link_field', $config, '');

        $ids = array();
        if ($collection = $object->getMany($link_alias)) {
            foreach ($collection as $link_object) {
                $ids[] = $link_object->get($link_field);
                //print_r($object->toArray());
            }
        }

        $record[$postfield] = implode('||', $ids);
    }

    public function handleRelatedLinks(&$object, $postvalues, $config = array()) {
        $modx = &$this->modx;
        $xpdo = &$object->xpdo;

        $link_classname = $modx->getOption('link_classname', $config, '');
        $link_alias = $modx->getOption('link_alias', $config, '');
        $postfield = $modx->getOption('postfield', $config, '');
        $id_field = $modx->getOption('id_field', $config, '');
        $link_field = $modx->getOption('link_field', $config, '');

        $attributes = explode('||', $modx->getOption($postfield, $postvalues, ''));
        $old_attributes = array();

        if ($attr_collection = $object->getMany($link_alias)) {
            foreach ($attr_collection as $attr_o) {
                $old_attributes[$attr_o->get($link_field)] = $attr_o;
            }
        }

        foreach ($attributes as $attribute) {
            if (!empty($attribute)) {
                if (isset($old_attributes[$attribute])) {
                    unset($old_attributes[$attribute]);
                } else {
                    $attr_o = $xpdo->newObject($link_classname);
                    $attr_o->set($link_field, $attribute);
                    $attr_o->set($id_field, $object->get('id'));
                    $attr_o->save();
                }
            }
        }
        
        foreach ($old_attributes as $attr_o) {
            $attr_o->remove();
        }        
    }
    
    public function handleRelatedLinksFromMIGX(&$object, $postvalues, $config) {
        $modx = &$this->modx;
        $xpdo = &$object->xpdo;
        
        $link_classname = $modx->getOption('link_classname', $config, '');
        $link_alias = $modx->getOption('link_alias', $config, '');
        $postfield = $modx->getOption('postfield', $config, '');
        $id_field = $modx->getOption('id_field', $config, '');
        $link_field = $modx->getOption('link_field', $config, '');
        $pos_field = $modx->getOption('pos_field', $config, 'pos');
        $resave_object = $modx->getOption('resave_object', $config, 0);
        $extrafields = explode(',',$modx->getOption('extrafields', $config, ''));        
        
        $products = $modx->fromJson($modx->getOption($postfield, $postvalues, ''));
        $old_products = array();

        if ($product_collection = $object->getMany($link_alias)) {
            foreach ($product_collection as $product_o) {
                $old_products[$product_o->get('id')] = $product_o;
            }
        }

        $pos = 1;
        $new_products = array();
        foreach ($products as $product) {
            $product_id = $modx->getOption($link_field, $product, '');
            $migx_id = $modx->getOption('MIGX_id', $product, '');
            $id = $modx->getOption('id', $product, 'new');
            if (!empty($product_id)) {
                if (isset($old_products[$id])) {
                    $product_o = $old_products[$id];
                    unset($old_products[$id]);
                } else {
                    $product_o = $xpdo->newObject($link_classname);
                }
                $product_o->set($pos_field, $pos);
                foreach ($extrafields as $extrafield){
                    $value = $modx->getOption($extrafield, $product, '');
                    $product_o->set($extrafield, $value);
                }
                
                $product_o->set($link_field, $product_id);
                $product_o->set($id_field, $object->get('id'));
                $product_o->save();
                $new_product = $product_o->toArray();
                $new_product['MIGX_id'] = $migx_id;
                $new_products[] = $new_product;
                $pos++;
            }
        }

        //save cleaned json
        $object->set($postfield, $modx->toJson($new_products));
        if (!empty($resave_object)){
            $object->save();
        }

        foreach ($old_products as $product_o) {
            $product_o->remove();
        }
    }    
    
    public function handleTranslations(&$object, $postvalues, $config) {
        $modx = &$this->modx;
        $xpdo = &$object->xpdo;
		
        $link_classname = $modx->getOption('link_classname', $config, '');
        $link_alias = $modx->getOption('link_alias', $config, 'Translations');
        $postfield = $modx->getOption('postfield', $config, '');
        $id_field = $modx->getOption('id_field', $config, '');
        $link_field = $modx->getOption('link_field', $config, 'iso_code');
        $languages = $modx->getOption('languages', $config, array());		
		
        $old_translations = array();
        if ($trans_collection = $object->getMany($link_alias)) {
            foreach ($trans_collection as $trans_o) {
                $old_translations[$trans_o->get($link_field)] = $trans_o;
            }
        }

        foreach ($languages as $language) {
            $iso_code = $modx->getOption($link_field, $language, '');
            if (!empty($iso_code)) {
                if (isset($old_translations[$iso_code])) {
                    $trans_o = $old_translations[$iso_code];
                    unset($old_translations[$iso_code]);
                } else {
                    $trans_o = $xpdo->newObject($link_classname);
                    $trans_o->set($link_field, $iso_code);
                    $trans_o->set($id_field, $object->get('id'));
                }
                foreach ($postvalues as $field => $value) {
                    $fieldparts = explode('_', $field);
                    $fieldparts = array_reverse($fieldparts);
                    if ($fieldparts[0] == $iso_code) {
                        $fieldname = str_replace('_' . $iso_code, '', $field);
                        $trans_o->set($fieldname, $value);
                    }
                }
                $trans_o->save();

            }
        }

        foreach ($old_translations as $trans_o) {
            $trans_o->remove();
        }
    }	    

    public function getTemplate($rowtpl, $template = array()) {
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
        $excludekeys_ifarray = array(
            'getlistwhere',
            'hooksnippets',
            'joins',
            'configs');
        $array = $this->recursive_encode($array, $excludekeys_ifarray);
        return $array;

    }

    function recursive_encode($array, $excludekeys_ifarray = array()) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {

                if (!is_int($key) && is_array($value) && in_array($key, $excludekeys_ifarray)) {
                    $array[$key] = !empty($value) ? json_encode($value) : $value;
                    //$array[$key] = $this->recursive_encode($value, $excludekeys);
                } else {
                    $array[$key] = $this->recursive_encode($value, $excludekeys_ifarray);
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
