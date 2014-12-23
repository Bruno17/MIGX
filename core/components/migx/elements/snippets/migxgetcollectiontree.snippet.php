<?php
/**
 * migxGetCollectionTree
 *
 * Copyright 2014 by Bruno Perner <b.perner@gmx.de>
 *
 * migxGetCollectionTree is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * migxGetCollectionTree is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * migxGetCollectionTree; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package migx
 */
/**
 * migxGetCollectionTree
 *
 *          display nested items from different objects. The tree-schema is defined by a json-property. 
 *
 * @version 1.0.0
 * @author Bruno Perner <b.perner@gmx.de>
 * @copyright Copyright &copy; 2014
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License
 * version 2 or (at your option) any later version.
 * @package migx
 */

$treeSchema = $modx->getOption('treeSchema', $scriptProperties, '');
$treeSchema = $modx->fromJson($treeSchema);

$scriptProperties['current'] = $modx->getOption('current', $scriptProperties, '');
$scriptProperties['currentClassname'] = $modx->getOption('currentClassname', $scriptProperties, '');
$scriptProperties['currentKeyField'] = $modx->getOption('currentKeyField', $scriptProperties, 'id');
$return = $modx->getOption('return', $scriptProperties, 'parsed'); //parsed,json,arrayprint

/*
Examples:

Get Resource-Tree, 4 levels deep

[[!migxGetCollectionTree?
&current=`57`
&currentClassname=`modResource`
&treeSchema=`
{
"classname": "modResource",
"debug": "1",
"tpl": "mgctResourceTree",
"wrapperTpl": "@CODE:<ul>[[+output]]</ul>",
"selectfields": "id,pagetitle",
"where": {
"parent": "0",
"published": "1",
"deleted": "0"
},
"_branches": [{
"alias": "children",
"classname": "modResource",
"local": "parent",
"foreign": "id",
"tpl": "mgctResourceTree",
"debug": "1",
"selectfields": "id,pagetitle,parent",
"_branches": [{
"alias": "children",
"classname": "modResource",
"local": "parent",
"foreign": "id",
"tpl": "mgctResourceTree",
"debug": "1",
"selectfields": "id,pagetitle,parent",
"where": {
"published": "1",
"deleted": "0"
},
"_branches": [{
"alias": "children",
"classname": "modResource",
"local": "parent",
"foreign": "id",
"tpl": "mgctResourceTree",
"debug": "1",
"selectfields": "id,pagetitle,parent",
"where": {
"published": "1",
"deleted": "0"
}
}]
}]
}]
}
`]]

the chunk mgctResourceTree:
<li class="[[+_activelabel]] [[+_currentlabel]]" ><a href="[[~[[+id]]]]">[[+pagetitle]]([[+id]])</a></li>
[[+innercounts.children:gt=`0`:then=`
<ul>[[+innerrows.children]]</ul>
`:else=``]]

get all Templates and its Resources:

[[!migxGetCollectionTree?
&treeSchema=`
{
"classname": "modTemplate",
"debug": "1",
"tpl": "@CODE:<h3>[[+templatename]]</h3><ul>[[+innerrows.resource]]</ul>",
"selectfields": "id,templatename",
"_branches": [{
"alias": "resource",
"classname": "modResource",
"local": "template",
"foreign": "id",
"tpl": "@CODE:<li>[[+pagetitle]]([[+id]])</li>",
"debug": "1",
"selectfields": "id,pagetitle,template"
}]
}
`]]
*/

if (!class_exists('MigxGetCollectionTree')) {
    class MigxGetCollectionTree
    {
        function __construct(modX & $modx, array $config = array())
        {
            $this->modx = &$modx;
            $this->config = $config;
        }

        function getBranch($branch, $foreigns = array(), $level = 1)
        {

            $rows = array();

            if (count($foreigns) > 0) {
                $modx = &$this->modx;

                $local = $modx->getOption('local', $branch, '');
                $where = $modx->getOption('where', $branch, array());
                $where = !empty($where) && !is_array($where) ? $modx->fromJSON($where) : $where;
                $where[] = array($local . ':IN' => $foreigns);

                $branch['where'] = $modx->toJson($where);

                $level++;
                /*
                if ($levelFromCurrent > 0){
                $levelFromCurrent++;    
                }
                */

                $rows = $this->getRows($branch, $level);
            }

            return $rows;
        }

        function getRows($scriptProperties, $level)
        {
            $migx = &$this->migx;
            $modx = &$this->modx;

            $current = $modx->getOption('current', $this->config, '');
            $currentKeyField = $modx->getOption('currentKeyField', $this->config, 'id');
            $currentlabel = $modx->getOption('currentlabel', $this->config, 'current');
            $classname = $modx->getOption('classname', $scriptProperties, '');
            $currentClassname = !empty($this->config['currentClassname']) ? $this->config['currentClassname'] : $classname;

            $activelabel = $modx->getOption('activelabel', $this->config, 'active');
            $return = $modx->getOption('return', $this->config, 'parsed');

            $xpdo = $migx->getXpdoInstanceAndAddPackage($scriptProperties);
            $c = $migx->prepareQuery($xpdo, $scriptProperties);
            $rows = $migx->getCollection($c);

            $branches = $modx->getOption('_branches', $scriptProperties, array());

            $collectedSubrows = array();
            foreach ($branches as $branch) {
                $foreign = $modx->getOption('foreign', $branch, '');
                $local = $modx->getOption('local', $branch, '');
                $alias = $modx->getOption('alias', $branch, '');
                //$activeonly = $modx->getOption('activeonly', $branch, '');
                $foreigns = array();
                foreach ($rows as $row) {
                    $foreigns[] = $row[$foreign];
                }

                $subrows = $this->getBranch($branch, $foreigns, $level);
                foreach ($subrows as $subrow) {

                    $collectedSubrows[$subrow[$local]][] = $subrow;
                    $subrow['_active'] = $modx->getOption('_active', $subrow, '0');
                    /*
                    if (!empty($activeonly) && $subrow['_active'] != '1') {
                    $output = '';
                    } else {
                    $collectedSubrows[$subrow[$local]][] = $subrow;
                    }
                    */
                    if ($subrow['_active'] == '1') {
                        //echo 'active subrow:<pre>' . print_r($subrow,1) . '</pre>';
                        $activesubrow[$subrow[$local]] = true;
                    }
                    if ($subrow['_current'] == '1') {
                        //echo 'active subrow:<pre>' . print_r($subrow,1) . '</pre>';
                        $currentsubrow[$subrow[$local]] = true;
                    }


                }
                //insert subrows
                $temprows = $rows;
                $rows = array();
                foreach ($temprows as $row) {
                    if (isset($collectedSubrows[$row[$foreign]])) {
                        $row['_active'] = '0';
                        $row['_currentparent'] = '0';
                        if (isset($activesubrow[$row[$foreign]]) && $activesubrow[$row[$foreign]]) {
                            $row['_active'] = '1';
                            //echo 'active row:<pre>' . print_r($row,1) . '</pre>';
                        }
                        if (isset($currentsubrow[$row[$foreign]]) && $currentsubrow[$row[$foreign]]) {
                            $row['_currentparent'] = '1';
                            //echo 'active row:<pre>' . print_r($row,1) . '</pre>';
                        }

                        //render innerrows
                        //$output = $migx->renderOutput($collectedSubrows[$row[$foreign]],$scriptProperties);
                        //$output = $collectedSubrows[$row[$foreign]];

                        $row['innercounts.' . $alias] = count($collectedSubrows[$row[$foreign]]);
                        $row['_scriptProperties'][$alias] = $branch;
                        /*
                        switch ($return) {
                        case 'parsed':
                        $output = $migx->renderOutput($collectedSubrows[$row[$foreign]], $branch);
                        //$subbranches = $modx->getOption('_branches', $branch, array());
                        //if there are any placeholders left with the same alias from subbranch, remove them
                        $output = str_replace('[[+innerrows.' . $alias . ']]', '', $output);
                        break;
                        case 'json':
                        case 'arrayprint':
                        $output = $collectedSubrows[$row[$foreign]];
                        break;
                        }
                        */
                        $output = $collectedSubrows[$row[$foreign]];

                        $row['innerrows.' . $alias] = $output;

                    }
                    $rows[] = $row;
                }

            }

            $temprows = $rows;
            $rows = array();
            foreach ($temprows as $row) {
                //add additional placeholders
                $row['_level'] = $level;
                $row['_active'] = $modx->getOption('_active', $row, '0');
                if ($currentClassname == $classname && $row[$currentKeyField] == $current) {
                    $row['_current'] = '1';
                    $row['_currentlabel'] = $currentlabel;
                    $row['_active'] = '1';
                } else {
                    $row['_current'] = '0';
                    $row['_currentlabel'] = '';
                }
                if ($row['_active'] == '1') {
                    $row['_activelabel'] = $activelabel;
                } else {
                    $row['_activelabel'] = '';
                }
                $rows[] = $row;
            }

            return $rows;
        }

        function renderRow($row, $levelFromCurrent = 0)
        {
            $migx = &$this->migx;
            $modx = &$this->modx;
            $return = $modx->getOption('return', $this->config, 'parsed');
            $branchProperties = $modx->getOption('_scriptProperties', $row, array());
            $current = $modx->getOption('_current', $row, '0');
            $currentparent = $modx->getOption('_currentparent', $row, '0');
            $levelFromCurrent = $current == '1' ? 1 : $levelFromCurrent;
            $row['_levelFromCurrent'] = $levelFromCurrent;
            foreach ($branchProperties as $alias => $properties) {
                $innerrows = $modx->getOption('innerrows.' . $alias, $row, array());
                $subrows = $this->renderRows($innerrows, $properties, $levelFromCurrent, $currentparent);
                if ($return == 'parsed') {
                    $subrows = $migx->renderOutput($subrows, $properties);
                }
                $row['innerrows.' . $alias] = $subrows;
            }

            return $row;
        }

        function renderRows($rows, $scriptProperties, $levelFromCurrent = 0, $siblingOfCurrent = '0')
        {

            $modx = &$this->modx;
            $temprows = $rows;
            $rows = array();
            if ($levelFromCurrent > 0) {
                $levelFromCurrent++;
            }
            foreach ($temprows as $row) {
                $row['_siblingOfCurrent'] = $siblingOfCurrent;
                $row = $this->renderRow($row, $levelFromCurrent);
                $rows[] = $row;
            }
            return $rows;
        }
    }
}

$instance = new MigxGetCollectionTree($modx, $scriptProperties);

if (is_array($treeSchema)) {
    $scriptProperties = $treeSchema;

    $migx = $modx->getService('migx', 'Migx', $modx->getOption('migx.core_path', null, $modx->getOption('core_path') . 'components/migx/') . 'model/migx/', $scriptProperties);
    if (!($migx instanceof Migx))
        return '';

    $defaultcontext = 'web';
    $migx->working_context = isset($modx->resource) ? $modx->resource->get('context_key') : $defaultcontext;
    $instance->migx = &$migx;

    $level = 1;
    $scriptProperties['alias'] = 'row';
    $rows = $instance->getRows($scriptProperties, $level);
    $row = array();
    $row['innercounts.row'] = count($rows);
    $row['innerrows.row'] = $rows;
    $row['_scriptProperties']['row'] = $scriptProperties;

    $rows = $instance->renderRow($row);

    $output = '';
    switch ($return) {
        case 'parsed':
            $output = $modx->getOption('innerrows.row', $rows, '');
            break;
        case 'json':
            $output = $modx->toJson($rows);
            break;
        case 'arrayprint':
            $output = '<pre>' . print_r($rows, 1) . '</pre>';
            break;
    }

    return $output;

}