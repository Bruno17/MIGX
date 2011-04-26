<?php

/**
 * getImageList
 *
 * Copyright 2009-2010 by Bruno Perner <b.perner@gmx.de>
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
 * FormIt; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package migx
 */
/**
 * getImageList
 *
 * get Images from TV with custom-input-type imageList for MODx Revolution 2.0.
 *
 * @version 1.1
 * @author Bruno Perner <b.perner@gmx.de>
 * @copyright Copyright &copy; 2009-2010
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License
 * version 2 or (at your option) any later version.
 * @package migx
 */

/*example: <ul>[[!getImageList? &tvname=`myTV`&tpl=`@CODE:<li>[[+idx]]<img src="[[+imageURL]]"/><p>[[+imageAlt]]</p></li>`]]</ul>*/
/* get default properties */


$tvname = $modx->getOption('tvname', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$docid = $modx->getOption('docid', $scriptProperties, $modx->resource->get('id'));
$outputvalue = $modx->getOption('value', $scriptProperties, '');
$limit = $modx->getOption('limit', $scriptProperties, '999999');
$offset = $modx->getOption('offset', $scriptProperties, 0);
$totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');

if (empty($tpl)) {
    return 'empty property: &tpl';
}

if (!empty($tvname)) {
    if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {
        $outputvalue = $tv->renderOutput($docid);
        /*
        *   get inputTvs 
        */
        $properties = $tv->get('input_properties');
        $formtabs = $modx->fromJSON($properties['formtabs']);
        $inputTvs = array();
        if (is_array($formtabs)) {
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

$items = $modx->fromJSON($outputvalue);
$output = '';
if (substr($tpl, 0, 6) == "@FILE:") {
    $template = file_get_contents($modx->config['base_path'] . substr($tpl, 6));
} else
    if (substr($tpl, 0, 6) == "@CODE:") {
        $template = substr($tpl, 6);
    } else
        if ($chunk = $modx->getObject('modChunk', array('name' => $tpl), true)) {
            $template = $chunk->getContent();
        } else {
            $template = false;
        }

        if ($template) {
            if (count($items) > 0) {
                $idx = 0;
                foreach ($items as $key => $item) {
                    $fields = array();
                    foreach ($item as $field => $value) {
                        if (isset($inputTvs[$field])) {
                            if ($tv = $modx->getObject('modTemplateVar', array('name' => $inputTvs[$field]))) {
                                $tv->set('default_text', $value);
                                $fields[$field] = $tv->renderOutput($docid);
                            }
                        } else {
                            $fields[$field] = $value;
                        }

                    }

                    if ($key >= $offset && $idx < $limit) {
                        $fields['idx'] = $idx;
                        $ct = count($items);
                        $fields['_alt'] = $idx % 2;
                        if ($idx == 0) $fields['_first'] = true;
                        if ($idx == $ct-1) $fields['_last'] = true;
                        $chunk = $modx->newObject('modChunk');
                        $chunk->setCacheable(false);
                        $chunk->setContent($template);
                        $output .= $chunk->process($fields);
                        $idx++;
                    }

                }
            }
        }

$modx->setPlaceholder($totalVar, count($items));
return $output;
