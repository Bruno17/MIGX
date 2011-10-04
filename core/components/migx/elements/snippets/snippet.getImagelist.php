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
$toSeparatePlaceholders = $modx->getOption('toSeparatePlaceholders', $scriptProperties, false);
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
$outputSeparator = $modx->getOption('outputSeparator', $scriptProperties, '');
$placeholdersKeyField = $modx->getOption('placeholdersKeyField', $scriptProperties, false);
$toJsonPlaceholder = $modx->getOption('toJsonPlaceholder', $scriptProperties, false);
$jsonVarKey = $modx->getOption('jsonVarKey', $scriptProperties, 'migx_outputvalue');
$outputvalue = $modx->getOption('value', $scriptProperties, '');
$outputvalue = isset($_REQUEST[$jsonVarKey]) ? $_REQUEST[$jsonVarKey] : $outputvalue;
$docidVarKey = $modx->getOption('docidVarKey', $scriptProperties, 'migx_docid');
$docid = $modx->getOption('docid', $scriptProperties, (isset($modx->resource) ? $modx->resource->get('id') : 1));
$docid = isset($_REQUEST[$docidVarKey]) ? $_REQUEST[$docidVarKey] : $docid;
$processTVs = $modx->getOption('processTVs', $scriptProperties, '1');

if (!empty($tvname)) {
    if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {

        /*
        *   get inputProperties
        */
        $properties = $tv->get('input_properties');
        $properties = isset($properties['formtabs']) ? $properties : $tv->getProperties();
        $formtabs = $modx->fromJSON($properties['formtabs']);
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
            if (isset($formtabs[0]['formtabs'])) {
                $forms = $formtabs;
                $formtabs = array();
                foreach ($forms as $form) {
                    foreach ($form['formtabs'] as $tab) {
                        $formtabs[] = $tab;
                    }
                }
            }

            foreach ($formtabs as $tab) {
                if (isset($tab['fields'])) {
                    foreach ($tab['fields'] as $field) {
                        if (isset($field['inputTV'])) {
                            $inputTvs[$field['field']] = $field['inputTV'];
                        }
                    }
                }
            }
        }

    }
}

if (empty($outputvalue)) {
    return '';
}

//echo $outputvalue.'<br/><br/>';


$items = $modx->fromJSON($outputvalue);
$modx->setPlaceholder($totalVar, count($items));

// where filter
if (is_array($where) && count($where) > 0) {
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
    $items = $tempitems;
}

if (count($items) > 0) {
    $items = $offset > 0 ? array_slice($items, $offset) : $items;
    $count = count($items);
    $limit = $limit == 0 || $limit > $count ? $count : $limit;
    $preselectLimit = $preselectLimit > $count ? $count : $preselectLimit;
    //preselect important items
    if ($randomize && $preselectLimit > 0) {
        $tempitems = array();
        for ($i = 0; $i < $preselectLimit; $i++) {
            $tempitems[] = $items[$i];
            unset($items[$i]);
        }
        shuffle($items);
        $items = array_merge($tempitems, $items);
    }

    $tempitems = array();
    for ($i = 0; $i < $limit; $i++) {
        $tempitems[] = $items[$i];
    }
    $items = $tempitems;
    if ($randomize) {
        shuffle($items);
    }

    $properties = array();
    foreach ($scriptProperties as $property => $value) {
        $properties['property.' . $property] = $value;
    }

    $idx = 0;
    $output = array();
    foreach ($items as $key => $item) {

        $fields = array();
        foreach ($item as $field => $value) {
            $value = is_array($value) ? implode('||', $value) : $value; //handle arrays (checkboxes, multiselects)
            if ($processTVs && isset($inputTvs[$field])) {
                if ($tv = $modx->getObject('modTemplateVar', array('name' => $inputTvs[$field]))) {
                    $tv->set('default_text', $value);
                    $fields[$field] = $tv->renderOutput($docid);
                }
            } else {
                $fields[$field] = $value;
            }
        }
        if ($toJsonPlaceholder) {
            $output[] = $fields;
        } else {
            $fields['_alt'] = $idx % 2;
            $idx++;
            $fields['_first'] = $idx == 1 ? true : '';
            $fields['_last'] = $idx == $limit ? true : '';
            $fields['idx'] = $idx;
            $rowtpl = $tpl;
            //get changing tpls from field
            if (substr($tpl, 0, 7) == "@FIELD:") {
                $tplField = substr($tpl, 7);
                $rowtpl = $fields[$tplField];
            }

            if (!isset($template[$rowtpl])) {
                if (substr($rowtpl, 0, 6) == "@FILE:") {
                    $template[$rowtpl] = file_get_contents($modx->config['base_path'] . substr($rowtpl, 6));
                } elseif (substr($rowtpl, 0, 6) == "@CODE:") {
                    $template[$rowtpl] = substr($tpl, 6);
                } elseif ($chunk = $modx->getObject('modChunk', array('name' => $rowtpl), true)) {
                    $template[$rowtpl] = $chunk->getContent();
                } else {
                    $template[$rowtpl] = false;
                }
            }

            $fields = array_merge($fields, $properties);

            if ($template[$rowtpl]) {
                $chunk = $modx->newObject('modChunk');
                $chunk->setCacheable(false);
                $chunk->setContent($template[$rowtpl]);
                if (!empty($placeholdersKeyField)) {
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
$o = implode($outputSeparator, $output);

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $o);
    return '';
}

return $o;
