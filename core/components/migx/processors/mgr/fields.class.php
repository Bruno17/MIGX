<?php

/**
 * Loads the TV panel for MIGX.
 *
 * Note: This page is not to be accessed directly.
 *
 * @package migx
 * @subpackage processors
 */

class migxFormProcessor extends modProcessor
{

    public function process()
    {
        //require_once dirname(dirname(dirname(__file__))) . '/model/migx/migx.class.php';
        //$migx = new Migx($this->modx);
        $sender = 'mgr/fields';

        require_once dirname(dirname(dirname(__file__))) . '/model/migx/migxformcontroller.class.php';
        $controller = new MigxFormController($this->modx);
        $this->modx->controller = &$controller;

        $this->modx->getService('smarty', 'smarty.modSmarty');
        $scriptProperties = $this->getProperties();

        $this->modx->migx->working_context = 'web';

        if ($this->modx->resource = $this->modx->getObject('modResource', $scriptProperties['resource_id'])) {
            $this->modx->migx->working_context = $this->modx->resource->get('context_key');

            //$_REQUEST['id']=$scriptProperties['resource_id'];
        }

        /*
        if (!isset($this->modx->smarty)) {
        $this->modx->getService('smarty', 'smarty.modSmarty', '', array('template_dir' => $this->modx->getOption('manager_path') . 'templates/' . $this->modx->getOption('manager_theme', null, 'default') . '/', ));
        }
        */
        //$this->loadControllersPath();
        $controller->loadTemplatesPath();

        //$this->modx->smarty->template_dir = $this->modx->getOption('manager_path') . 'templates/' . $this->modx->getOption('manager_theme', null, 'default') . '/';
        //$this->modx->smarty->assign('OnResourceTVFormPrerender', $onResourceTVFormPrerender);
        $controller->setPlaceholder('_config', $this->modx->config);

        //get the MIGX-TV
        $properties = array();
        
        if ($tv = $this->modx->getObject('modTemplateVar', array('name' => $scriptProperties['tv_name']))) {
            $this->modx->migx->source = $tv->getSource($this->modx->migx->working_context, false);
            $properties = $tv->get('input_properties');
            //$properties = isset($properties['formtabs']) ? $properties : $tv->getProperties();
        }

        $configs = !empty($this->modx->migx->config['configs']) ? $this->modx->migx->config['configs'] : '';
        $configs = isset($properties['configs']) && !empty($properties['configs']) ? $properties['configs'] : $configs;

        if (!empty($configs)) {
            $this->modx->migx->config['configs'] = $configs;
            $this->modx->migx->loadConfigs(true,true,$scriptProperties,$sender);
            
            

        }
        
        $formtabs = $this->modx->migx->getTabs();
        $fieldid = 0;
        /*actual record */
        $record = $this->modx->fromJSON($scriptProperties['record_json']);

        $allfields = array();
        $formnames = array();

        $field = array();
        $field['field'] = 'MIGX_id';
        $field['tv_id'] = 'migxid';
        $allfields[] = $field;
        if ($scriptProperties['isnew'] == '1') {
            $migxid = $scriptProperties['autoinc'] + 1;
        } else {
            $migxid = $record['MIGX_id'];
        }
        $controller->setPlaceholder('migxid', $migxid);
        
        $formtabs = $this->modx->migx->checkMultipleForms($formtabs,$controller,$allfields,$record);

        if (empty($formtabs)) {

            //old stuff
            $default_formtabs = '[{"caption":"Default", "fields": [{"field":"title","caption":"Title"}]}]';
            $formtabs = $this->modx->fromJSON($this->modx->getOption('formtabs', $properties,
                $default_formtabs));
            $formtabs = empty($properties['formtabs']) ? $this->modx->fromJSON($default_formtabs) :
                $formtabs;
            $fieldid = 0;
            $tabid = 0;

            //multiple different Forms
            // Note: use same field-names and inputTVs in all forms
            if (isset($formtabs[0]['formtabs'])) {
                $forms = $formtabs;
                $tabs = array();
                foreach ($forms as $form) {
                    $formname = array();
                    $formname['value'] = $form['formname'];
                    $formname['text'] = $form['formname'];
                    $formname['selected'] = 0;
                    if (isset($record['MIGX_formname']) && $form['formname'] == $record['MIGX_formname']) {
                        $formname['selected'] = 1;
                    }
                    $formnames[] = $formname;
                    foreach ($form['formtabs'] as $tab) {
                        $tabs[$form['formname']][] = $tab;
                    }
                }

                $controller->setPlaceholder('formnames', $formnames);

                if (isset($record['MIGX_formname'])) {
                    $formtabs = $tabs[$record['MIGX_formname']];
                } else {
                    //if no formname requested use the first form
                    $formtabs = $tabs[$formnames[0]['value']];
                }
                $field = array();
                $field['field'] = 'MIGX_formname';
                $field['tv_id'] = 'Formname';
                $allfields[] = $field;
            }

        }


        $categories = array();
        $this->modx->migx->createForm($formtabs, $record, $allfields, $categories, $scriptProperties);
        $formcaption = $this->modx->getOption('formcaption' , $this->modx->migx->customconfigs,'');
        $formcaption = !empty($formcaption) ? $this->modx->migx->renderChunk($formcaption,$record,false,false) : '';
        $controller->setPlaceholder('formcaption', $formcaption);        
        $controller->setPlaceholder('fields', $this->modx->toJSON($allfields));
        $controller->setPlaceholder('customconfigs', $this->modx->migx->customconfigs);
        $controller->setPlaceholder('categories', $categories);
        $controller->setPlaceholder('properties', $scriptProperties);
        $controller->setPlaceholder('win_id', $scriptProperties['tv_id']);

        if (!empty($_REQUEST['showCheckbox'])) {
            $controller->setPlaceholder('showCheckbox', 1);
        }
        /*
        $miTVCorePath = $this->modx->getOption('migx.core_path', null, $this->modx->getOption('core_path') . 'components/migx/');
        $this->modx->smarty->template_dir = $miTVCorePath . 'templates/';
        return $this->modx->smarty->fetch('mgr/fields.tpl');        
        */

        return $controller->process($scriptProperties);

    }
}
return 'migxFormProcessor';
