<?php

/**
 * @var modX $this->modx
 * @var modTemplateVar $this
 * @var array $params
 *
 * @package modx
 * @subpackage processors.element.tv.renders.mgr.input
 */
class migxInputRender extends modTemplateVarInputRender {
    
    public function process($value, array $params = array()) {
        
        $namespace = 'migx';
        $this->modx->lexicon->load('tv_widget', $namespace . ':default');
        //$properties = isset($params['columns']) ? $params : $this->getProperties();
        $properties = $params;

        require_once dirname(dirname(dirname(__file__)))  . '/model/migx/migx.class.php';
        $this->migx = new Migx($this->modx, $properties);
        /* get input-tvs */
        $this->migx->loadConfigs();

        //$default_formtabs = '[{"caption":"Default", "fields": [{"field":"title","caption":"Title"}]}]';
        $default_columns = '[{"header": "Title", "width": "160", "sortable": "true", "dataIndex": "title"}]';

        // get tabs from file or migx-config-table
        //$formtabs = $this->migx->getTabs();
        /*
        if (empty($formtabs)) {
        // get them from input-properties
        $formtabs = $this->modx->fromJSON($this->modx->getOption('formtabs', $properties, $default_formtabs));
        $formtabs = empty($properties['formtabs']) ? $this->modx->fromJSON($default_formtabs) : $formtabs;
        }
        */
        //$inputTvs = $this->migx->extractInputTvs($formtabs);

        /* get base path based on either TV param or filemanager_path */
        $this->modx->getService('fileHandler', 'modFileHandler', '', array('context' => $this->modx->context->get('key')));

        $resource_array = array();
        $resource_id = 0;
        //are we on a edit- or create-resource - managerpage?
        if (is_object($this->modx->resource)) {
            $resource_array = $this->modx->resource->toArray();
            $resource_id = $this->modx->resource->get('id');
            $wctx = $this->modx->resource->get('context_key');
        } else {
            //try to get a context from REQUEST
            $wctx = isset($_REQUEST['wctx']) && !empty($_REQUEST['wctx']) ? $this->modx->sanitizeString($_REQUEST['wctx']) : '';
        }


        if (!empty($wctx)) {
            $this->migx->working_context = $wctx;
            $this->migx->source = $this->tv->getSource($wctx, false);
            //$workingContext = $this->modx->getContext($wctx);
            /*
            if (!$workingContext) {
            return $modx->error->failure($this->modx->lexicon('permission_denied'));
            }
            $wctx = $workingContext->get('key');
            */
        } else {
            //$wctx = $this->modx->context->get('key');
        }

        /* from image-TV do we need this somehow here?
        $source->setRequestProperties($_REQUEST);
        $source->initialize();
        $this->modx->controller->setPlaceholder('source',$source->get('id'));     
        */

        //$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH);
        //$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);

        $columns = $this->migx->getColumns();

        if (empty($columns)) {
            $columns = $this->modx->fromJSON($this->modx->getOption('columns', $properties, $default_columns));
            $columns = empty($properties['columns']) ? $this->modx->fromJSON($default_columns) : $columns;
        }

        $this->migx->loadLang();
        $lang = $this->modx->lexicon->fetch();
        if (!empty($properties['btntext'])){
            $lang['migx_add'] = $properties['btntext'];
        }else{
            $lang['migx_add'] = isset($lang['migx.add']) ? $lang['migx.add'] : 'Add Item';
        }
        $lang['migx_add'] = str_replace("'", "\'", $lang['migx_add']);
        $this->migx->addLangValue('migx.add', $lang['migx_add']);
        $this->migx->migxlang['migx.add'] = $lang['migx_add'];

        $this->migx->prepareGrid($params, $this, $this->tv, $columns);
        $grid = $this->migx->getGrid();

        $filenames = array();
        $defaultpath = $this->migx->config['templatesPath'] . 'mgr/';
        $filename = 'iframewindow.tpl';
        if ($windowfile = $this->migx->findGrid($defaultpath, $filename, $filenames)) {
            $this->setPlaceholder('iframewindow', $this->migx->replaceLang($this->modx->controller->fetchTemplate($windowfile)));
        }

        //$newitem[] = $item;
        $tv_value = $this->tv->processBindings($this->tv->get('value'));
        if ($temp = $this->modx->getObject('modTemplateVar', $this->tv->get('id'))) {
            $default_value = $this->tv->processBindings($temp->get('default_text'), $resource_id);
        } else {
            $default_value = $this->tv->processBindings($this->tv->get('default_text'), $resource_id);
        }

        if (empty($tv_value) && !empty($default_value)) {
            $tv_value = $default_value;
        }

        $options = $this->getInputOptions();
        
        $disable_add_item = $this->modx->getOption('disable_add_item',$this->migx->customconfigs,false);

        if (is_array($options) && !empty($options)) {
            $allow_customrecords = $disable_add_item ? false : true;
            $optrows = array();
            foreach ($options as $key => $row) {
                $row['MIGX_id'] = isset($row['MIGX_id']) ? $row['MIGX_id'] : 0;
                //if no MIGX_id, but id, use id, else use the key
                if (empty($row['MIGX_id'])) {
                    $row['MIGX_id'] = isset($row['id']) ? $row['id'] : $key;
                }
                $optrows[$row['MIGX_id']] = $row;
            }


            $tv_value = $this->modx->fromJson($tv_value);
            $rows = array();
            if (is_array($tv_value)) {
                foreach ($tv_value as $row) {

                    //only add records, which exists also in the input-options
                    if (isset($optrows[$row['MIGX_id']])) {
                        //use input-option-fields and values
                        $rowfields = $optrows[$row['MIGX_id']];

                        $editablefields = $this->modx->getOption('editablefields', $rowfields, '');
                        $editablefields = explode(',', $editablefields);

                        //add additional field/values from MIGX
                        foreach ($row as $field => $value) {
                            //only, if not in options or editable
                            if (in_array($field, $editablefields) || !array_key_exists($field, $rowfields)) {
                                $rowfields[$field] = $value;
                            }
                        }
                        $rows[] = $rowfields;
                        unset($optrows[$row['MIGX_id']]);
                    } elseif ($allow_customrecords) {
                        //customrecord, not in input-options
                        $rows[] = $row;
                    }
                }
            }

            //add not allready existing input-options
            foreach ($optrows as $row) {
                $rows[] = $row;
            }

            $rows = $this->migx->checkRenderOptions($rows);
        } else {
            $rows = $this->migx->checkRenderOptions($this->modx->fromJson($tv_value));
        }

        $tv_value = $this->modx->toJson($rows);

        $this->setPlaceholder('tv_type', 'migx');
        $this->setPlaceholder('tv_value', $tv_value);
        $this->setPlaceholder('i18n', $lang);
        $this->setPlaceholder('properties', $properties);
        $this->setPlaceholder('resource', $resource_array);
        $this->setPlaceholder('request', $_REQUEST);
        $this->setPlaceholder('connected_object_id', $this->modx->getOption('object_id', $_REQUEST, ''));
        $this->setPlaceholder('base_url', $this->modx->getOption('base_url'));
        $this->setPlaceholder('myctx', $wctx);
        $this->setPlaceholder('config', $this->migx->config);
        
        $grid = $grid == 'default' ? 'migx' : $grid;
        
        $filenames = array();
        $defaultpath = $this->migx->config['templatesPath'] . '/mgr/grids/';
        $filename = $grid . '.grid.tpl';
        if ($gridfile = $this->migx->findGrid($defaultpath, $filename, $filenames)) {
            $this->setPlaceholder('grid', $this->migx->replaceLang($this->modx->controller->fetchTemplate($gridfile)));
        }        
    }

    /**
     * Return the input options parsed for the TV
     * @return mixed
     */
    public function getInputOptions() {
        if (is_object($this->modx->resource)) {
            $options = $this->parseInputOptions($this->tv->processBindings($this->tv->get('elements'), $this->modx->resource->get('id')));
        } else {
            $options = $this->parseInputOptions($this->tv->processBindings($this->tv->get('elements')));
        }

        return $options;
    }

    /**
     * Parses input options sent through postback.
     *
     * @access public
     * @param mixed $v The options to parse, either a recordset, PDOStatement, array or string.
     * @return mixed The parsed options.
     */
    public function parseInputOptions($v) {

        $a = array();
        if (is_array($v))
            return $v;
        else
            if (is_resource($v)) {
                while ($cols = mysql_fetch_row($v))
                    $a[] = $cols;
            } else
                if (is_object($v)) {
                    $a = $v->fetchAll(PDO::FETCH_ASSOC);
                } else
                    $a = $this->modx->fromJson($v);
        return $a;
    }

    public function getTemplate() {
        $path = 'components/migx/';
        $corePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . $path);
        return $corePath . 'elements/tv/migx.tpl';
    }
}
