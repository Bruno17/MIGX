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
        $defaultconfig = array();

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

    function extractInputTvs($formtabs)
    {
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
