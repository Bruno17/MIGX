<?php

/**
 * Loads the TV panel for MIGX.
 *
 * Note: This page is not to be accessed directly.
 *
 * @package migx
 * @subpackage processors
 */

class migxFormProcessor extends modProcessor {

    public function process() {
        //require_once dirname(dirname(dirname(__file__))) . '/model/migx/migx.class.php';
        //$migx = new Migx($this->modx);
        $modx = &$this->modx;

        require_once dirname(dirname(dirname(dirname(__file__)))) . '/model/migx/migxformcontroller.class.php';
        $controller = new MigxFormController($this->modx);
        $this->modx->controller = &$controller;

        $this->modx->getService('smarty', 'smarty.modSmarty');
        $scriptProperties = $this->getProperties();

        // special actions, for example the selectFromGrid - action
        $tempParams = $this->modx->getOption('tempParams', $scriptProperties, '');
        $action = '';
        if (!empty($tempParams)) {
            $tempParams = $this->modx->fromJson($tempParams);
            if (is_array($tempParams) && array_key_exists('action',$tempParams) && !empty($tempParams['action'])) {
                $action = strtolower($tempParams['action']) ;
                if ($action == 'selectfromgrid'){
                    $scriptProperties['configs'] = !empty($tempParams['selectorconfig']) ? $tempParams['selectorconfig'] : $action;
                }
                $action = '_' . $action ;
            }

        }

        //$controller->loadControllersPath();

        // we will need a way to get a context-key, if in CMP-mode, from config, from dataset..... thoughts??
        // can be overridden in custom-processors for now, but whats with the preparegrid-method and working-context?
        // ok let's see when we need this.
        $this->modx->migx->working_context = 'web';

        if ($this->modx->resource = $this->modx->getObject('modResource', $scriptProperties['resource_id'])) {
            $this->modx->migx->working_context = $this->modx->resource->get('context_key');

            //$_REQUEST['id']=$scriptProperties['resource_id'];
        }

        $controller->loadTemplatesPath();
        $controller->setPlaceholder('_config', $this->modx->config);
        $task = $this->modx->migx->getTask();
        $filename = str_replace(array('.class', '.php'), '', basename(__file__)) . $action . '.php';
        $processorspath = dirname(dirname(__file__)) . '/';
        $filenames = array();
        if ($processor_file = $this->modx->migx->findProcessor($processorspath, $filename, $filenames)) {
            include_once ($processor_file);
        }


        //$object = $this->modx->getObject('Angebote',$scriptProperties['angebot']);
        //if (empty($object)) return $this->modx->error->failure($this->modx->lexicon('quip.thread_err_nf'));
        //if (!$thread->checkPolicy('view')) return $this->modx->error->failure($this->modx->lexicon('access_denied'));

        //return $this->modx->error->success('',$angebot);

        //echo '<pre>'.print_r($angebot->toArray(),1).'</pre>';

        $sender = isset($sender) ? $sender : '';

        $this->modx->migx->loadConfigs(true, true, $scriptProperties, $sender);
        $customconfigs = $this->modx->migx->customconfigs;
        $tabs = $this->modx->migx->getTabs();
        $fieldid = 0;
        $allfields[] = array();
        $categories = array();
        
        $tabs = $this->modx->migx->checkMultipleForms($tabs,$controller,$allfields,$record);
        
        $this->modx->migx->createForm($tabs, $record, $allfields, $categories, $scriptProperties);
        $formcaption = $this->modx->getOption('formcaption',$customconfigs,'');

        $controller->setPlaceholder('formcaption', $this->modx->migx->renderChunk($formcaption,$record,false,false));
        $controller->setPlaceholder('fields', $this->modx->toJSON($allfields));
        $controller->setPlaceholder('customconfigs', $customconfigs);
        $controller->setPlaceholder('object', $object);
        $controller->setPlaceholder('categories', $categories);
        //$controller->setPlaceholder('win_id', $scriptProperties['tv_id']);
        $controller->setPlaceholder('win_id', isset($customconfigs['win_id']) ? $customconfigs['win_id'] : $scriptProperties['tv_id']);
        //$c->setPlaceholder('id_update_window', 'modx-window-midb-grid-update');

        if (!empty($_REQUEST['showCheckbox'])) {
            $controller->setPlaceholder('showCheckbox', 1);
        }


        return $controller->process($scriptProperties);

    }
}
return 'migxFormProcessor';
