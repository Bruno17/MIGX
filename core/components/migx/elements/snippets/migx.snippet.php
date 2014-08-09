<?php
$tvname = $modx->getOption('tvname', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, '0');
$offset = $modx->getOption('offset', $scriptProperties, 0);
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');
$randomize = $modx->getOption('randomize', $scriptProperties, false);
$preselectLimit = $modx->getOption('preselectLimit', $scriptProperties, 0); // when random preselect important images
$where = $modx->getOption('where', $scriptProperties, '');
$where = !empty($where) ? $modx->fromJSON($where) : array();
$sortConfig = $modx->getOption('sortConfig', $scriptProperties, '');
$sortConfig = !empty($sortConfig) ? $modx->fromJSON($sortConfig) : array();
$configs = $modx->getOption('configs', $scriptProperties, '');
$configs = !empty($configs) ? explode(',',$configs):array();
$toSeparatePlaceholders = $modx->getOption('toSeparatePlaceholders', $scriptProperties, false);
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, '');
//$placeholdersKeyField = $modx->getOption('placeholdersKeyField', $scriptProperties, 'MIGX_id');
$placeholdersKeyField = $modx->getOption('placeholdersKeyField', $scriptProperties, 'id');
$toJsonPlaceholder = $modx->getOption('toJsonPlaceholder', $scriptProperties, false);
$jsonVarKey = $modx->getOption('jsonVarKey', $scriptProperties, 'migx_outputvalue');
$outputvalue = $modx->getOption('value', $scriptProperties, '');
$outputvalue = isset($_REQUEST[$jsonVarKey]) ? $_REQUEST[$jsonVarKey] : $outputvalue;
$docidVarKey = $modx->getOption('docidVarKey', $scriptProperties, 'migx_docid');
$docid = $modx->getOption('docid', $scriptProperties, (isset($modx->resource) ? $modx->resource->get('id') : 1));
$docid = isset($_REQUEST[$docidVarKey]) ? $_REQUEST[$docidVarKey] : $docid;
$processTVs = $modx->getOption('processTVs', $scriptProperties, '1');

$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH);
$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);

$migx = $modx->getService('migx', 'Migx', $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/migx/', $scriptProperties);
if (!($migx instanceof Migx))
    return '';
//$modx->migx = &$migx;
$defaultcontext = 'web';
$migx->working_context = isset($modx->resource) ? $modx->resource->get('context_key') : $defaultcontext;

if (!empty($tvname))
{
    if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname)))
    {

        /*
        *   get inputProperties
        */


        $properties = $tv->get('input_properties');
        $properties = isset($properties['configs']) ? $properties : $tv->getProperties();
        $cfgs = $modx->getOption('configs',$properties,'');
        if (!empty($cfgs)){
            $cfgs = explode(',',$cfgs);
            $configs = array_merge($configs,$cfgs);
           
        }
        
    }
}



//$migx->config['configs'] = implode(',',$configs);
$migx->loadConfigs(false,true,array('configs'=>implode(',',$configs)));
$migx->customconfigs = array_merge($migx->customconfigs,$scriptProperties);



// get tabs from file or migx-config-table
$formtabs = $migx->getTabs();
if (empty($formtabs))
{
    //try to get formtabs and its fields from properties
    $formtabs = $modx->fromJSON($properties['formtabs']);
}

if ($jsonVarKey == 'migx_outputvalue' && !empty($properties['jsonvarkey']))
{
    $jsonVarKey = $properties['jsonvarkey'];
    $outputvalue = isset($_REQUEST[$jsonVarKey]) ? $_REQUEST[$jsonVarKey] : $outputvalue;
}

$outputvalue = $tv && empty($outputvalue) ? $tv->renderOutput($docid) : $outputvalue;
/*
*   get inputTvs 
*/
$inputTvs = array();
if (is_array($formtabs))
{

    //multiple different Forms
    // Note: use same field-names and inputTVs in all forms
    $inputTvs = $migx->extractInputTvs($formtabs);
}

if ($tv)
{
    $migx->source = $tv->getSource($migx->working_context, false);
}

//$task = $modx->migx->getTask();
$filename = 'getlist.php';
$processorspath = $migx->config['processorsPath'] . 'mgr/';
$filenames = array();
$scriptProperties['start'] = $modx->getOption('offset', $scriptProperties, 0);
if ($processor_file = $migx->findProcessor($processorspath, $filename, $filenames))
{
    include ($processor_file);
    //todo: add getlist-processor for default-MIGX-TV
}

$items = isset($rows) && is_array($rows) ? $rows : array();
$modx->setPlaceholder($totalVar, isset($count) ? $count : 0);

$properties = array();
foreach ($scriptProperties as $property => $value)
{
    $properties['property.' . $property] = $value;
}

$idx = 0;
$output = array();
foreach ($items as $key => $item)
{

    $fields = array();
    foreach ($item as $field => $value)
    {
        $value = is_array($value) ? implode('||', $value) : $value; //handle arrays (checkboxes, multiselects)
        if ($processTVs && isset($inputTvs[$field]))
        {
            if ($tv = $modx->getObject('modTemplateVar', array('name' => $inputTvs[$field]['inputTV'])))
            {

            } else
            {
                $tv = $modx->newObject('modTemplateVar');
                $tv->set('type', $inputTvs[$field]['inputTVtype']);
            }
            $inputTV = $inputTvs[$field];

            $mTypes = $modx->getOption('manipulatable_url_tv_output_types', null, 'image,file');
            //don't manipulate any urls here
            $modx->setOption('manipulatable_url_tv_output_types', '');
            $tv->set('default_text', $value);
            $value = $tv->renderOutput($docid);
            //set option back
            $modx->setOption('manipulatable_url_tv_output_types', $mTypes);
            //now manipulate urls
            if ($mediasource = $migx->getFieldSource($inputTV, $tv))
            {
                $mTypes = explode(',', $mTypes);
                if (!empty($value) && in_array($tv->get('type'), $mTypes))
                {
                    //$value = $mediasource->prepareOutputUrl($value);
                    $value = str_replace('/./', '/', $mediasource->prepareOutputUrl($value));
                }
            }

        }
        $fields[$field] = $value;

    }
    if ($toJsonPlaceholder)
    {
        $output[] = $fields;
    } else
    {
        $fields['_alt'] = $idx % 2;
        $idx++;
        $fields['_first'] = $idx == 1 ? true : '';
        $fields['_last'] = $idx == $limit ? true : '';
        $fields['idx'] = $idx;
        $rowtpl = $tpl;
        //get changing tpls from field
        if (substr($tpl, 0, 7) == "@FIELD:")
        {
            $tplField = substr($tpl, 7);
            $rowtpl = $fields[$tplField];
        }

        if (!isset($template[$rowtpl]))
        {
            if (substr($rowtpl, 0, 6) == "@FILE:")
            {
                $template[$rowtpl] = file_get_contents($modx->config['base_path'] . substr($rowtpl, 6));
            } elseif (substr($rowtpl, 0, 6) == "@CODE:")
            {
                $template[$rowtpl] = substr($tpl, 6);
            } elseif ($chunk = $modx->getObject('modChunk', array('name' => $rowtpl), true))
            {
                $template[$rowtpl] = $chunk->getContent();
            } else
            {
                $template[$rowtpl] = false;
            }
        }

        $fields = array_merge($fields, $properties);

        if ($template[$rowtpl])
        {
            $chunk = $modx->newObject('modChunk');
            $chunk->setCacheable(false);
            $chunk->setContent($template[$rowtpl]);
            if (!empty($placeholdersKeyField) && isset($fields[$placeholdersKeyField]))
            {
                $output[$fields[$placeholdersKeyField]] = $chunk->process($fields);
            } else
            {
                $output[] = $chunk->process($fields);
            }
        } else
        {
            if (!empty($placeholdersKeyField))
            {
                $output[$fields[$placeholdersKeyField]] = '<pre>' . print_r($fields, 1) . '</pre>';
            } else
            {
                $output[] = '<pre>' . print_r($fields, 1) . '</pre>';
            }
        }
    }


}


if ($toJsonPlaceholder)
{
    $modx->setPlaceholder($toJsonPlaceholder, $modx->toJson($output));
    return '';
}

if (!empty($toSeparatePlaceholders))
{
    $modx->toPlaceholders($output, $toSeparatePlaceholders);
    return '';
}
/*
if (!empty($outerTpl))
$o = parseTpl($outerTpl, array('output'=>implode($outputSeparator, $output)));
else 
*/
if (is_array($output))
{
    $o = implode($outputSeparator, $output);
} else
{
    $o = $output;
}

if (!empty($toPlaceholder))
{
    $modx->setPlaceholder($toPlaceholder, $o);
    return '';
}

return $o;