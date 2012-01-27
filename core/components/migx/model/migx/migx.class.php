<?php

/**
 * xdbedit
 *
 * @author Bruno Perner
 *
 *
 * @package migxdb
 */
/**
 * @package migxdb
 * @subpackage xdbedit
 */
class Migx
{
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
    function __construct(modX & $modx, array $config = array())
    {
        $this->modx = &$modx;

        /* allows you to set paths in different environments
        * this allows for easier SVN management of files
        */
        $corePath = $this->modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/');
        $assetsPath = $this->modx->getOption('migx.assets_path', null, $modx->getOption('assets_path') . 'components/migx/');
        $assetsUrl = $this->modx->getOption('migx.assets_url', null, $modx->getOption('assets_url') . 'components/migx/');

        $defaultconfig['corePath'] = $corePath;
        $defaultconfig['modelPath'] = $corePath . 'model/';
        $defaultconfig['processorsPath'] = $corePath . 'processors/';
        $defaultconfig['controllersPath'] = $corePath . 'controllers/';
        $defaultconfig['chunksPath'] = $corePath . 'elements/chunks/';
        $defaultconfig['snippetsPath'] = $corePath . 'elements/snippets/';
        $defaultconfig['auto_create_tables'] = true;
        $defaultconfig['baseUrl'] = $assetsUrl;
        $defaultconfig['cssUrl'] = $assetsUrl . 'css/';
        $defaultconfig['jsUrl'] = $assetsUrl . 'js/';
        $defaultconfig['jsPath'] = $assetsPath . 'js/';
        $defaultconfig['connectorUrl'] = $assetsUrl . 'connector.php';

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

    function loadConfigs()
    {

        $configs = (isset($this->config['configs'])) ? explode(',', $this->config['configs']) : array();
        //$configs = array_merge( array ('master'), $configs);
        foreach ($configs as $config) {
            $configFile = $this->config['corePath'] . 'configs/' . $config . '.config.inc.php'; // [ file ]

            if (file_exists($configFile)) {
                include ($configFile);
            }
        }
    }

    public function getTask()
    {
        return $this->customconfigs['task'];
    }
    public function getTabs()
    {
        return $this->customconfigs['tabs'];
    }
    public function getColumns()
    {
        return $this->customconfigs['columns'];
    }
    function getFieldSource($field, &$tv)
    {
        //set media_source for this TV before changing the id

        if (isset($field['sources']) && is_array($field['sources'])) {
            foreach ($field['sources'] as $context => $sourceid) {
                $sources[$context] = $sourceid;
            }
        }

        $findSource = true;
        if (isset($sources[$this->working_context])) {
            //try using field-specific mediasource
            if ($mediasource = $this->modx->getObject('sources.modMediaSource', $sources[$this->working_context])) {
                $findSource = false;

            }
        }
        if ($findSource) {
            if ($this->source && $field['sourceFrom'] == 'migx') {
                //use global MIGX-mediasource for all TVs
                $tv->setSource($this->source);
                $mediasource = $this->source;
            } else {
                //useTV-specific mediasource
                $mediasource = $tv->getSource($this->working_context);
            }
        }
        return $mediasource;
    }

    function generateTvTab($tvnames){
        $tvnames = !empty($tvnames) ? explode(',',$tvnames): array();
        $fields = array();
        foreach ($tvnames as $tvname){
            $field['field'] = $tvname;
            $field['inputTV'] = $tvname;
            $fields[] = $field;
        }
        return $fields;       
    }


    function createForm(&$tabs, &$record, &$allfields, &$categories, $scriptProperties)
    {

        foreach ($tabs as $tabid => $tab) {
            $emptycat = $this->modx->newObject('modCategory');
            $emptycat->set('category', $tab['caption']);
            $emptycat->id = $tabid;
            $categories[$tabid] = $emptycat;

            $fields = $tab['fields'];
            foreach ($fields as & $field) {
                $fieldid++;
                if ($tv = $this->modx->getObject('modTemplateVar', array('name' => $field['inputTV']))) {

                } else {
                    $tv = $this->modx->newObject('modTemplateVar');
                    $tv->set('type', 'text');
                }

                /*insert actual value from requested record, convert arrays to ||-delimeted string */
                $fieldvalue = is_array($record[$field['field']]) ? implode('||', $record[$field['field']]) : $record[$field['field']];

                $tv->set('value', $fieldvalue);
                if (!empty($field['caption'])){
                    $tv->set('caption', htmlentities($field['caption'], ENT_QUOTES, $this->modx->getOption('modx_charset')));
                }
                
                if (!empty($field['description'])) {
                    $tv->set('description', htmlentities($field['description'], ENT_QUOTES, $this->modx->getOption('modx_charset')));
                }
                /*generate unique tvid, must be numeric*/
                /*todo: find a better solution*/
                $field['tv_id'] = $scriptProperties['tv_id'] * 10000000 + $fieldid;
                $field['array_tv_id'] = $field['tv_id'] . '[]';
                $allfields[] = $field;

                $mediasource = $this->getFieldSource($field, $tv);


                //$this->modx->setOption('default_media_source',$mediasource->get('id'));
                //mediasource über formtabs steuerbar machen?
                //{"mediasources":[{"web":"1"}]}

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
                $params = $tv->get('input_properties');
                if (!empty($properties['basePath'])) {
                    if ($properties['autoResourceFolders'] == 'true') {
                        $params['basePath'] = $basePath . $scriptProperties['resource_id'] . '/';
                        $targetDir = $params['basePath'];

                        $cacheManager = $this->modx->getCacheManager();
                        /* if directory doesnt exist, create it */
                        if (!file_exists($targetDir) || !is_dir($targetDir)) {
                            if (!$cacheManager->writeTree($targetDir)) {
                                $this->modx->log(modX::LOG_LEVEL_ERROR, '[MIGX] Could not create directory: ' . $targetDir);
                                return $this->modx->error->failure('Could not create directory: ' . $targetDir);
                            }
                        }
                        /* make sure directory is readable/writable */
                        if (!is_readable($targetDir) || !is_writable($targetDir)) {
                            $this->modx->log(xPDO::LOG_LEVEL_ERROR, '[MIGX] Could not write to directory: ' . $targetDir);
                            return $this->modx->error->failure('Could not write to directory: ' . $targetDir);
                        }
                    } else {
                        $params['basePath'] = $basePath;
                    }
                }

                if (!isset($params['allowBlank'])) $params['allowBlank'] = 1;

                $value = $tv->get('value');
                if ($value === null) {
                    $value = $tv->get('default_text');
                }
                $this->modx->smarty->assign('params', $params);
                /* find the correct renderer for the TV, if not one, render a textbox */
                $inputRenderPaths = $tv->getRenderDirectories('OnTVInputRenderList', 'input');
                $inputForm = $tv->getRender($params, $value, $inputRenderPaths, 'input', $resourceId, $tv->get('type'));

                if (empty($inputForm)) continue;

                $tv->set('formElement', $inputForm);

                if (!is_array($categories[$tabid]->tvs)) {
                    $categories[$tabid]->tvs = array();
                }
                $categories[$tabid]->tvs[] = $tv;

            }
        }
    }

    function extractInputTvs($formtabs)
    {
        //multiple different Forms
        // Note: use same field-names and inputTVs in all forms
        if (isset($formtabs[0]['formtabs'])) {
            $forms = $formtabs;
            $formtabs = array();
            foreach ($forms as $form) {
                foreach ($form['formtabs'] as $tab) {
                    $formtabs[] = $tab;
                }
            }
        }


        $inputTvs = array();
        if (is_array($formtabs)) {
            foreach ($formtabs as $tab) {
                if (isset($tab['fields'])) {
                    foreach ($tab['fields'] as $field) {
                        if (isset($field['inputTV'])) {
                            $inputTvs[$field['field']] = $field;
                        }
                    }
                }
            }
        }
        return $inputTvs;
    }

    function sortTV($sort, & $c , $dir='ASC' , $sortbyTVType='')
    {
        $c->leftJoin('modTemplateVar', 'tvDefault', array("tvDefault.name" => $sort));
        $c->leftJoin('modTemplateVarResource', 'tvSort', array("tvSort.contentid = modResource.id", "tvSort.tmplvarid = tvDefault.id"));
        if (empty($sortbyTVType)) $sortbyTVType = 'string';
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

    function tvFilters($tvFilters = '', &$criteria)
    {
        $tvFilters = !empty($tvFilters) ? explode('||', $tvFilters) : array();
        if (!empty($tvFilters)) {
            $tmplVarTbl = $this->modx->getTableName('modTemplateVar');
            $tmplVarResourceTbl = $this->modx->getTableName('modTemplateVarResource');
            $conditions = array();
            $operators = array('<=>' => '<=>', '===' => '=', '!==' => '!=', '<>' => '<>', '==' => 'LIKE', '!=' => 'NOT LIKE', '<<' => '<', '<=' => '<=', '=<' => '=<', '>>' => '>', '>=' => '>=', '=>' => '=>');
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

    function filterItems($items)
    {

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
                        $output = empty($subject) ? $then:
                        (isset($else) ? $else : '');
                        break;
                    case '!empty':
                    case 'notempty':
                    case 'isnotempty':
                        $output = !empty($subject) && $subject != '' ? $then:
                        (isset($else) ? $else : '');
                        break;
                    case 'isnull':
                    case 'null':
                        $output = $subject == null || strtolower($subject) == 'null' ? $then:
                        (isset($else) ? $else : '');
                        break;
                    case 'inarray':
                    case 'in_array':
                    case 'ia':
                    case 'in':
                        $operand = is_array($operand) ? $operand:
                        explode(',', $operand);
                        $output = in_array($subject, $operand) ? $then:
                        (isset($else) ? $else : '');
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

}
