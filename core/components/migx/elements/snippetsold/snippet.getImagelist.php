<?php

/**
 * getImageList
 *
 * Copyright 2009-2011 by Bruno Perner <b.perner@gmx.de>
 *
 * getImageList is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * getImageList is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * getImageList; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package migx
 */
/**
 * getImageList
 *
 * display Items from outputvalue of TV with custom-TV-input-type MIGX or from other JSON-string for MODx Revolution 
 *
 * @version 1.4
 * @author Bruno Perner <b.perner@gmx.de>
 * @copyright Copyright &copy; 2009-2011
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License
 * version 2 or (at your option) any later version.
 * @package migx
 */

/*example: <ul>[[!getImageList? &tvname=`myTV`&tpl=`@CODE:<li>[[+idx]]<img src="[[+imageURL]]"/><p>[[+imageAlt]]</p></li>`]]</ul>*/
/* get default properties */


$tvname = $modx->getOption('tvname', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, '0');
$offset = $modx->getOption('offset', $scriptProperties, 0);
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');
$randomize = $modx->getOption('randomize', $scriptProperties, false);
$preselectLimit = $modx->getOption('preselectLimit', $scriptProperties, 0); // when random preselect important images
$where = $modx->getOption('where', $scriptProperties, '');
$where = !empty($where) ? $modx->fromJSON($where) : array();
$sort = $modx->getOption('sort', $scriptProperties, '');
$sort = !empty($sort) ? $modx->fromJSON($sort) : array();
$toSeparatePlaceholders = $modx->getOption('toSeparatePlaceholders', $scriptProperties, false);
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, '');
$placeholdersKeyField = $modx->getOption('placeholdersKeyField', $scriptProperties, 'MIGX_id');
$toJsonPlaceholder = $modx->getOption('toJsonPlaceholder', $scriptProperties, false);
$jsonVarKey = $modx->getOption('jsonVarKey', $scriptProperties, 'migx_outputvalue');
$outputvalue = $modx->getOption('value', $scriptProperties, '');
$outputvalue = isset($_REQUEST[$jsonVarKey]) ? $_REQUEST[$jsonVarKey] : $outputvalue;
$docidVarKey = $modx->getOption('docidVarKey', $scriptProperties, 'migx_docid');
$docid = $modx->getOption('docid', $scriptProperties, (isset($modx->resource) ? $modx->resource->get('id') : 1));
$docid = isset($_REQUEST[$docidVarKey]) ? $_REQUEST[$docidVarKey] : $docid;
$processTVs = $modx->getOption('processTVs', $scriptProperties, '1');
$reverse = $modx->getOption('reverse', $scriptProperties, '0');

$base_path = $modx->getOption('base_path', null, MODX_BASE_PATH);
$base_url = $modx->getOption('base_url', null, MODX_BASE_URL);

$migx = $modx->getService('migx', 'Migx', $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/migx/', $scriptProperties);
if (!($migx instanceof Migx))
    return '';
$migx->working_context = isset($modx->resource) ? $modx->resource->get('context_key') : 'web';


if (!empty($tvname)) {
    if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {

        /*
        *   get inputProperties
        */


        $properties = $tv->get('input_properties');
        $properties = isset($properties['formtabs']) ? $properties : $tv->getProperties();

        $migx->config['configs'] = $modx->getOption('configs', $properties, '');
        if (!empty($migx->config['configs'])) {
            $migx->loadConfigs();
            // get tabs from file or migx-config-table
            $formtabs = $migx->getTabs();
        }
        if (empty($formtabs) && isset($properties['formtabs'])) {
            //try to get formtabs and its fields from properties
            $formtabs = $modx->fromJSON($properties['formtabs']);
        }

        if (!empty($properties['basePath'])) {
            if ($properties['autoResourceFolders'] == 'true') {
                $scriptProperties['base_path'] = $base_path . $properties['basePath'] . $docid . '/';
                $scriptProperties['base_url'] = $base_url . $properties['basePath'] . $docid . '/';
            } else {
                $scriptProperties['base_path'] = $base_path . $properties['base_path'];
                $scriptProperties['base_url'] = $base_url . $properties['basePath'];
            }
        }
        if ($jsonVarKey == 'migx_outputvalue' && !empty($properties['jsonvarkey'])) {
            $jsonVarKey = $properties['jsonvarkey'];
            $outputvalue = isset($_REQUEST[$jsonVarKey]) ? $_REQUEST[$jsonVarKey] : $outputvalue;
        }
        $outputvalue = empty($outputvalue) ? $tv->renderOutput($docid) : $outputvalue;
        /*
        *   get inputTvs 
        */
        $inputTvs = array();
        if (is_array($formtabs)) {

            //multiple different Forms
            // Note: use same field-names and inputTVs in all forms
            $inputTvs = $migx->extractInputTvs($formtabs);
        }

    }
    $migx->source = $tv->getSource($migx->working_context, false);
}

if (empty($outputvalue)) {
    return '';
}

//echo $outputvalue.'<br/><br/>';


$items = $modx->fromJSON($outputvalue);

// where filter
if (is_array($where) && count($where) > 0) {
    $items = $migx->filterItems($where, $items);
}
$modx->setPlaceholder($totalVar, count($items));


if (!empty($reverse)) {
    $items = array_reverse($items);
}


// sort items
if (is_array($sort) && count($sort) > 0) {
    $items = $migx->sortDbResult($items, $sort);
}


if (count($items) > 0) {
    $items = $offset > 0 ? array_slice($items, $offset) : $items;
    $count = count($items);
    $limit = $limit == 0 || $limit > $count ? $count : $limit;
    $preselectLimit = $preselectLimit > $count ? $count : $preselectLimit;
    //preselect important items
    $preitems = array();
    if ($randomize && $preselectLimit > 0) {
        for ($i = 0; $i < $preselectLimit; $i++) {
            $preitems[] = $items[$i];
            unset($items[$i]);
        }
        $limit = $limit - count($preitems);
    }

    //shuffle items
    if ($randomize) {
        shuffle($items);
    }

    //limit items
    $tempitems = array();
    for ($i = 0; $i < $limit; $i++) {
        $tempitems[] = $items[$i];
    }
    $items = $tempitems;

    //add preselected items and schuffle again
    if ($randomize && $preselectLimit > 0) {
        $items = array_merge($preitems, $items);
        shuffle($items);
    }

    $properties = array();
    foreach ($scriptProperties as $property => $value) {
        $properties['property.' . $property] = $value;
    }

    $idx = 0;
    $output = array();
    $template = array();
    $count = count($items);
    foreach ($items as $key => $item) {
        $formname = isset($item['MIGX_formname']) ? $item['MIGX_formname'] . '_' : '';
        $fields = array();
        foreach ($item as $field => $value) {
            $value = is_array($value) ? implode('||', $value) : $value; //handle arrays (checkboxes, multiselects)
            $inputTVkey = $formname . $field;
            if ($processTVs && isset($inputTvs[$inputTVkey])) {
                if ($tv = $modx->getObject('modTemplateVar', array('name' => $inputTvs[$inputTVkey]['inputTV']))) {

                } else {
                    $tv = $modx->newObject('modTemplateVar');
                    $tv->set('type', $inputTvs[$inputTVkey]['inputTVtype']);
                }
                $inputTV = $inputTvs[$inputTVkey];

                $mTypes = $modx->getOption('manipulatable_url_tv_output_types', null, 'image,file');
                //don't manipulate any urls here
                $modx->setOption('manipulatable_url_tv_output_types', '');
                $tv->set('default_text', $value);
                $value = $tv->renderOutput($docid);
                //set option back
                $modx->setOption('manipulatable_url_tv_output_types', $mTypes);
                //now manipulate urls
                if ($mediasource = $migx->getFieldSource($inputTV, $tv)) {
                    $mTypes = explode(',', $mTypes);
                    if (!empty($value) && in_array($tv->get('type'), $mTypes)) {
                        //$value = $mediasource->prepareOutputUrl($value);
                        $value = str_replace('/./', '/', $mediasource->prepareOutputUrl($value));
                    }
                }

            }
            $fields[$field] = $value;

        }
        if ($toJsonPlaceholder) {
            $output[] = $fields;
        } else {
            $fields['_alt'] = $idx % 2;
            $idx++;
            $fields['_first'] = $idx == 1 ? true : '';
            $fields['_last'] = $idx == $limit ? true : '';
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
            if (empty($rowtpl) && !empty($$tplidx)) {
                $rowtpl = $$tplidx;
            }
            if ($idx > 1 && empty($rowtpl)) {
                $divisors = $migx->getDivisors($idx);
                if (!empty($divisors)) {
                    foreach ($divisors as $divisor) {
                        $tplnth = 'tpl_n' . $divisor;
                        if (!empty($$tplnth)) {
                            $rowtpl = $$tplnth;
                            if (!empty($rowtpl)) {
                                break;
                            }
                        }
                    }
                }
            }
            
            if ($count == 1 && isset($tpl_oneresult)){
                $rowtpl = $tpl_oneresult;
            }

            $fields = array_merge($fields, $properties);

            if (!empty($rowtpl)) {
                $template = $migx->getTemplate($tpl, $template);
                $fields['_tpl'] = $template[$tpl]; 
            } else {
                $rowtpl = $tpl;

            }
            $template = $migx->getTemplate($rowtpl, $template);
            
            
            
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
/*
if (!empty($outerTpl))
$o = parseTpl($outerTpl, array('output'=>implode($outputSeparator, $output)));
else 
*/
if (is_array($output)) {
    $o = implode($outputSeparator, $output);
} else {
    $o = $output;
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $o);
    return '';
}

return $o;
