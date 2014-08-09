<?php
/**
 * exportMIGX2db
 *
 * Copyright 2014 by Bruno Perner <b.perner@gmx.de>
 * 
 * Sponsored by Simon Wurster <info@wurster-medien.de>
 *
 * exportMIGX2db is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * exportMIGX2db is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * exportMIGX2db; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package migx
 */
/**
 * exportMIGX2db
 *
 * export Items from outputvalue of TV with custom-TV-input-type MIGX or from other JSON-string to db-table 
 *
 * @version 1.0
 * @author Bruno Perner <b.perner@gmx.de>
 * @copyright Copyright &copy; 2014
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License
 * version 2 or (at your option) any later version.
 * @package migx
 */

/*
[[!exportMIGX2db? 
&tvname=`references` 
&resources=`25` 
&packageName=`projekte`
&classname=`Projekt` 
&migx_id_field=`migx_id` 
&renamed_fields=`{"Firmen-URL":"Firmen_url","Projekt-URL":"Projekt_URL","main-image":"main_image"}`
]]
*/


$tvname = $modx->getOption('tvname', $scriptProperties, '');
$resources = $modx->getOption('resources', $scriptProperties, (isset($modx->resource) ? $modx->resource->get('id') : ''));
$resources = explode(',', $resources);
$prefix = isset($scriptProperties['prefix']) ? $scriptProperties['prefix'] : null;
$packageName = $modx->getOption('packageName', $scriptProperties, '');
$classname = $modx->getOption('classname', $scriptProperties, '');
$value = $modx->getOption('value', $scriptProperties, '');
$migx_id_field = $modx->getOption('migx_id_field', $scriptProperties, '');
$pos_field = $modx->getOption('pos_field', $scriptProperties, '');
$renamed_fields = $modx->getOption('renamed_fields', $scriptProperties, '');

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName .
    '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$added = 0;
$modified = 0;

foreach ($resources as $docid) {
    
    $outputvalue = '';
    if (count($resources)==1){
        $outputvalue = $value;    
    }
    
    if (!empty($tvname)) {
        if ($tv = $modx->getObject('modTemplateVar', array('name' => $tvname))) {

            $outputvalue = empty($outputvalue) ? $tv->renderOutput($docid) : $outputvalue;
        }
    }

    if (!empty($outputvalue)) {
        $renamed = !empty($renamed_fields) ? $modx->fromJson($renamed_fields) : array();

        $items = $modx->fromJSON($outputvalue);
        $pos = 1;
        $searchfields = array();
        if (is_array($items)) {
            foreach ($items as $fields) {
                $search = array();
                if (!empty($migx_id_field)) {
                    $search[$migx_id_field] = $fields['MIGX_id'];
                }
                if (!empty($resource_id_field)) {
                    $search[$resource_id_field] = $docid;
                }
                if (!empty($migx_id_field) && $object = $modx->getObject($classname, $search)) {
                    $mode = 'mod';
                } else {
                    $object = $modx->newObject($classname);
                    $object->fromArray($search);
                    $mode = 'add';
                }
                foreach ($fields as $field => $value) {
                    $fieldname = array_key_exists($field, $renamed) ? $renamed[$field] : $field;
                    $object->set($fieldname, $value);
                }
                if (!empty($pos_field)) {
                    $object->set($pos_field,$pos) ;
                }                
                if ($object->save()) {
                    if ($mode == 'add') {
                        $added++;
                    } else {
                        $modified++;
                    }
                }
                $pos++;
            }
            
        }
    }
}


return $added . ' rows added to db, ' . $modified . ' existing rows actualized';