<?php

if (!empty($_REQUEST['tempParams'])) {

    //&& $_REQUEST['tempParams'] == 'selectDbFields'
    $tempParams = json_decode($_REQUEST['tempParams'], 1);

    $formtabs = isset($tempParams['formtabs']) ? json_decode($tempParams['formtabs'], 1) : '';
    $fields = isset($tempParams['fields']) ? json_decode($tempParams['fields'], 1) : '';
    $existing_fields = array();
    if (is_array($fields)) {
        foreach ($fields as $field) {
            if (isset($field['field'])) {
                $existing_fields[$field['field']] = $field['field'];
            } elseif (isset($field['dataIndex'])) {
                $existing_fields[$field['dataIndex']] = $field['dataIndex'];
            }
        }
    }

    if (is_array($formtabs)) {
        foreach ($formtabs as $formtab) {
            $fields = isset($formtab['fields']) ? $formtab['fields'] : '';
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($field['field'])) {
                        $existing_fields[$field['field']] = $field['field'];
                    }
                }
            }
        }
    }

    $selectcolumns = array();
    //echo '<pre>' . print_r($_REQUEST,1) . '</pre>';
    $object_id = isset($_REQUEST['object_id']) ? $_REQUEST['object_id'] : '';
    if (!empty($object_id) && $config_o = $this->modx->getObject('migxConfig', $object_id)) {
        $configs = $config_o->toArray();
        //echo '<pre>' . print_r($configs,1) . '</pre>';
        $extended = isset($configs['extended']) ? $configs['extended'] : array();
        $packageName = isset($extended['packageName']) ? $extended['packageName'] : array();
        $classname = isset($extended['classname']) ? $extended['classname'] : array();
        $joins = isset($extended['joins']) ? $extended['joins'] : array();
        $joins = is_array($joins) ? $joins : json_decode($joins, true);
        //$extended['joins'] = $joins;
        $xpdo = &$this->getXpdoInstanceAndAddPackage($extended);
        $c = $xpdo->newQuery($classname);
        $selectcolumns = $this->prepareJoins($classname, $joins, $c);
        if ($object = $xpdo->newObject($classname, $c)) {
            $selectcolumns = array_merge($object->toArray(), $selectcolumns);
            $selectcolumns = array_keys($selectcolumns);

        }

    }

    $tabs = '
[
{"caption":"[[%migx.select_dbfields]]", "fields": [
{"field":"selected_dbfields","caption":"[[%migx.select_dbfields]]","description":"[[%migx.select_dbfields_desc]]","inputTVtype":"listbox-multiple","default":"' . implode('||', $existing_fields) . '","inputOptionValues":"' . implode('||', $selectcolumns) . '"},
{"field":"existing_dbfields","inputTVtype":"hidden","default":"' . implode('||', $existing_fields) . '"}
]}
] 
';
}

$this->customconfigs['hooksnippets'] = '{"aftercollectmigxitems":"migxHookAftercollectmigxitems"}';

$gridactionbuttons['selectDbFields']['active'] = 1;
$gridactionbuttons['selectDbFields']['text'] = "'[[%migx.select_dbfields]]'";
$gridactionbuttons['selectDbFields']['handler'] = 'this.selectDbFields,this.addItem,this.addNewItem';
$gridactionbuttons['selectDbFields']['scope'] = 'this';
$gridactionbuttons['selectDbFields']['standalone'] = '1';

$tv_id = isset($_REQUEST['tv_id']) ? 'tv' . $_REQUEST['tv_id'] : '';
