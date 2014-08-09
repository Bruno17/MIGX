<?php
/**
 * getDayliMIGXrecord
 *
 * Copyright 2009-2011 by Bruno Perner <b.perner@gmx.de>
 *
 * getDayliMIGXrecord is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * getDayliMIGXrecord is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * getDayliMIGXrecord; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package migx
 */
/**
 * getDayliMIGXrecord
 *
 * display Items from outputvalue of TV with custom-TV-input-type MIGX or from other JSON-string for MODx Revolution 
 *
 * @version 1.0
 * @author Bruno Perner <b.perner@gmx.de>
 * @copyright Copyright &copy; 2012
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License
 * version 2 or (at your option) any later version.
 * @package migx
 */

/*example: [[!getDayliMIGXrecord? &tvname=`myTV`&tpl=`@CODE:<img src="[[+image]]"/>` &randomize=`1`]]*/
/* get default properties */


$tvname = $modx->getOption('tvname', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$randomize = $modx->getOption('randomize', $scriptProperties, false);
$where = $modx->getOption('where', $scriptProperties, '');
$where = !empty($where) ? $modx->fromJSON($where) : array();
$sort = $modx->getOption('sort', $scriptProperties, '');
$sort = !empty($sort) ? $modx->fromJSON($sort) : array();
$toPlaceholder = $modx->getOption('toPlaceholder', $scriptProperties, false);
$docid = $modx->getOption('docid', $scriptProperties, (isset($modx->resource) ? $modx->resource->get('id') : 1));
$processTVs = $modx->getOption('processTVs', $scriptProperties, '1');

$migx = $modx->getService('migx', 'Migx', $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/migx/', $scriptProperties);
if (!($migx instanceof Migx))
    return '';
$migx->working_context = $modx->resource->get('context_key');

if (!empty($tvname)) {
    if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {

        /*
        *   get inputProperties
        */


        $properties = $tv->get('input_properties');
        $properties = isset($properties['formtabs']) ? $properties : $tv->getProperties();

        $migx->config['configs'] = $properties['configs'];
        $migx->loadConfigs();
        // get tabs from file or migx-config-table
        $formtabs = $migx->getTabs();
        if (empty($formtabs)) {
            //try to get formtabs and its fields from properties
            $formtabs = $modx->fromJSON($properties['formtabs']);
        }

        //$tv->setCacheable(false);
        //$outputvalue = $tv->renderOutput($docid);
        
        $tvresource = $modx->getObject('modTemplateVarResource', array(
            'tmplvarid' => $tv->get('id'),
            'contentid' => $docid,
            ));


        $outputvalue = $tvresource->get('value');
        
        /*
        *   get inputTvs 
        */
        $inputTvs = array();
        if (is_array($formtabs)) {

            //multiple different Forms
            // Note: use same field-names and inputTVs in all forms
            $inputTvs = $migx->extractInputTvs($formtabs);
        }
        $migx->source = $tv->getSource($migx->working_context, false);

        if (empty($outputvalue)) {
            return '';
        }

        $items = $modx->fromJSON($outputvalue);


        //is there an active item for the current date?
        $activedate = $modx->getOption('activedate', $scriptProperties, strftime('%Y/%m/%d'));
        //$activedate = $modx->getOption('activedate', $_GET, strftime('%Y/%m/%d'));
        $activewhere = array();
        $activewhere['activedate'] = $activedate;
        $activewhere['activated'] = '1';
        $activeitems = $migx->filterItems($activewhere, $items);

        if (count($activeitems) == 0) {

            $activeitems = array();
            // where filter
            if (is_array($where) && count($where) > 0) {
                $items = $migx->filterItems($where, $items);
            }

            $tempitems = array();
            $count = count($items);
            $emptycount = 0;
            $latestdate = $activedate;
            $nextdate = strtotime($latestdate);
            foreach ($items as $item) {
                //empty all dates and active-states which are older than today
                if (!empty($item['activedate']) && $item['activedate'] < $activedate) {
                    $item['activated'] = '0';
                    $item['activedate'] = '';
                }
                if (empty($item['activedate'])) {
                    $emptycount++;
                }
                if ($item['activedate'] > $latestdate) {
                    $latestdate = $item['activedate'];
                    $nextdate = strtotime($latestdate) + (24 * 60 * 60);
                }
                if ($item['activedate'] == $activedate) {
                    $item['activated'] = '1';
                    $activeitems[] = $item;
                }
                $tempitems[] = $item;
            }

            //echo '<pre>' . print_r($tempitems, 1) . '</pre>';

            $items = $tempitems;


            //are there more than half of all items with empty activedates

            if ($emptycount >= $count / 2) {

                // sort items
                if (is_array($sort) && count($sort) > 0) {
                    $items = $migx->sortDbResult($items, $sort);
                }
                if (count($items) > 0) {
                    //shuffle items
                    if ($randomize) {
                        shuffle($items);
                    }
                }

                $tempitems = array();
                foreach ($items as $item) {
                    if (empty($item['activedate'])) {
                        $item['activedate'] = strftime('%Y/%m/%d', $nextdate);
                        $nextdate = $nextdate + (24 * 60 * 60);
                        if ($item['activedate'] == $activedate) {
                            $item['activated'] = '1';
                            $activeitems[] = $item;
                        }
                    }

                    $tempitems[] = $item;
                }

                $items = $tempitems;
            }

            //$resource = $modx->getObject('modResource', $docid);
            //echo $modx->toJson($items);
            $sort = '[{"sortby":"activedate"}]';
            $items = $migx->sortDbResult($items, $modx->fromJson($sort));

            //echo '<pre>' . print_r($items, 1) . '</pre>';

            $tv->setValue($docid, $modx->toJson($items));
            $tv->save();

        }
    }

}


$properties = array();
foreach ($scriptProperties as $property => $value) {
    $properties['property.' . $property] = $value;
}

$output = '';

foreach ($activeitems as $key => $item) {

    $fields = array();
    foreach ($item as $field => $value) {
        $value = is_array($value) ? implode('||', $value) : $value; //handle arrays (checkboxes, multiselects)
        if ($processTVs && isset($inputTvs[$field])) {
            if ($tv = $modx->getObject('modTemplateVar', array('name' => $inputTvs[$field]['inputTV']))) {

            } else {
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
        $output .= $chunk->process($fields);

    } else {
        $output .= '<pre>' . print_r($fields, 1) . '</pre>';

    }


}


if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
    return '';
}

return $output;