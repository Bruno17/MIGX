<?php

/**
 * bloX
 *
 * Copyright 2009-2012 by Bruno Perner <b.perner@gmx.de>
 *
 * bloX is free software; you can redistribute it and/or modify it under the 
 * terms of the GNU General Public License as published by the Free Software 
 * Foundation; either version 2 of the License, or (at your option) any 
 * later version.
 *
 * bloX is distributed in the hope that it will be useful, but WITHOUT ANY 
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * bloX; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package blox
 * @subpackage classfile
 */
class blox {

    // Declaring private variables
    var $bloxconfig;
    var $bloxtpl;

    // Class constructor
    function blox($bloxconfig) {
        $this->bloxID = $bloxconfig['id'];
        $this->bloxconfig = $bloxconfig;
        $this->bloxconfig['prefilter'] = '';
        $this->columnNames = array();
        $this->tvnames = array();
        $this->docColumnNames = array();
        $this->tvids = array();
        //$this->bloxconfig['parents'] = $this->cleanIDs($bloxconfig['parents']);
        //$this->bloxconfig['IDs'] = $this->cleanIDs($bloxconfig['IDs']);

        $this->tpls = array();
        $this->checktpls();

        $this->renderdepth = 0;
        $this->eventscount = array();
        $this->output = '';
        $this->date = xetadodb_mktime(0, 0, 0, $this->bloxconfig['month'], $this->bloxconfig['day'], $this->bloxconfig['year']);
    }

    function checktpls() {
        // example: &tpls=`bloxouter:myouter||row:contentonly`

        $this->tpls['bloxouter'] = "@FILE " . $this->bloxconfig['tplpath'] . "bloxouterTpl.html"; // [ path | chunkname | text ]
        if ($this->bloxconfig['tpls'] !== '') {
            $tpls = explode('||', $this->bloxconfig['tpls']);
            foreach ($tpls as $tpl) {
                $this->tpls[substr($tpl, 0, strpos($tpl, ':'))] = substr($tpl, strpos($tpl, ':') + 1);
                //Todo: check if chunk exists
            }
        }
    }

    function prepareQuery($scriptProperties = array(), &$total = 0, $forcounting = false) {
        global $modx;

        $limit = $modx->getOption('limit', $scriptProperties, '0');
        $offset = $modx->getOption('offset', $scriptProperties, 0);

        $selectfields = $modx->getOption('selectfields', $scriptProperties, '');
        $where = $modx->getOption('where', $scriptProperties, '');
        $where = !empty($where) ? $modx->fromJSON($where) : array();
        $queries = $modx->getOption('queries', $scriptProperties, '');
        $queries = !empty($queries) ? $modx->fromJSON($queries) : array();
        $sortConfig = $modx->getOption('sortConfig', $scriptProperties, '');
        $sortConfig = !empty($sortConfig) ? $modx->fromJSON($sortConfig) : array();
        $groupby = $modx->getOption('groupby', $scriptProperties, '');

        $debug = $modx->getOption('debug', $scriptProperties, false);
        $joins = $modx->getOption('joins', $scriptProperties, '');
        $joins = !empty($joins) ? $modx->fromJson($joins) : false;
        $classname = ($scriptProperties['classname'] != '') ? $scriptProperties['classname'] : 'modResource';
        $c = $modx->newQuery($classname);

        $selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
        if ($forcounting) {
            $c->select('1');
        } else {
            $c->select($modx->getSelectColumns($classname, $classname, '', $selectfields));
        }


        if ($joins) {
            $this->prepareJoins($classname, $joins, $c, $forcounting);
        }

        if (is_array($where)) {
            $c->where($where);
        }

        if (is_array($queries)) {
            $keys = array('AND' => xPDOQuery::SQL_AND, 'OR' => xPDOQuery::SQL_OR);
            foreach ($queries as $query) {
                $c->where($query['query'], $keys[$query['operator']]);
            }
        }
        
        if (!empty($groupby)){
            $c->groupby($groupby);
        }         
        
        if ($forcounting) {
            if ($debug) {
                $c->prepare();
                echo '<pre>Precount Query String:<br/>' . $c->toSql() . '</pre>';
            }
            if ($c->prepare() && $c->stmt->execute()) {
                $rows = $c->stmt->fetchAll(PDO::FETCH_COLUMN);
                $total = count($rows);
            }
            return $c;
        }

        $total = $modx->getCount($classname, $c);


        if (is_array($sortConfig)) {
            foreach ($sortConfig as $sort) {
                $sortby = $sort['sortby'];
                $sortdir = isset($sort['sortdir']) ? $sort['sortdir'] : 'ASC';
                $c->sortby($sortby, $sortdir);
            }
        }
        if (!empty($limit)) {
            $c->limit($limit, $offset);
        }

        if ($debug) {
            $c->prepare();

            echo '<pre>Query String:<br/>' . $c->toSql() . '</pre>';
        }

        return $c;
    }

    public function prepareJoins($classname, $joins, &$c, $forcounting = false) {
        global $modx;

        if (is_array($joins)) {
            foreach ($joins as $join) {
                $jalias = $modx->getOption('alias', $join, '');
                $joinclass = $modx->getOption('classname', $join, '');
                $on = $modx->getOption('on', $join, null);
                if (!empty($jalias)) {
                    if (empty($joinclass) && $fkMeta = $modx->getFKDefinition($classname, $jalias)) {
                        $joinclass = $fkMeta['class'];
                    }
                    if (!empty($joinclass)) {
                        $selectfields = $modx->getOption('selectfields', $join, '');

                        /*
                        if ($joinFkMeta = $modx->getFKDefinition($joinclass, 'Resource')){
                        $localkey = $joinFkMeta['local'];
                        }
                        */
                        $c->leftjoin($joinclass, $jalias, $on);
                        $selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
                        if ($forcounting) {

                        } else {
                            $c->select($modx->getSelectColumns($joinclass, $jalias, $jalias . '_', $selectfields));
                        }
                    }
                }
            }
        }
    }


    //////////////////////////////////////////////////
    //Display bloX
    /////////////////////////////////////////////////

    function displayblox() {
        $datas = $this->getdatas($this->date, $this->bloxconfig['includesfile']);
        if (isset($datas['bloxoutput'])){
            //direkt output
            return $datas['bloxoutput'];
        }
        return $this->displaydatas($datas);
    }

    //////////////////////////////////////////////////
    //displaydatas (bloxouterTpl)
    /////////////////////////////////////////////////

    function displaydatas($outerdata = array()) {
        global $modx;

        // $outerdata['innerrows']['row']='innerrows.row';

        $start = microtime(true);
        $cache = $modx->getOption('cacheaction', $outerdata, '');
        $cachename = $modx->getOption('cachename', $outerdata, '');
        if ($cache == '2') {
            return $outerdata['cacheoutput'];
        }


        $bloxouterTplData = array();
        $bloxinnerrows = array();
        $bloxinnercounts = array();

        $innerrows = $modx->getOption('innerrows', $outerdata, array());

        unset($outerdata['innerrows']);

        if (count($innerrows) > 0) {
            foreach ($innerrows as $key => $row) {
                $startsub = microtime(true);

                $daten = '';
                $innertpl = '';
                if (isset($this->tpls[$key])) {
                    $innertpl = $this->tpls[$key];
                } else {
                    $tplfile = $this->bloxconfig['tplpath'] . $key . "Tpl.html";
                    if (file_exists($modx->getOption('core_path') . $tplfile)) {
                        $innertpl = "@FILE " . $tplfile;
                    }
                }

                if ($innertpl !== '') {
                    $data = $this->renderdatarows($row, $innertpl, $key, $outerdata);
                    $bloxinnerrows[$key] = $data;
                    $bloxinnercounts[$key] = count($row);
                }
                $endsub = microtime(true);
                if ($this->bloxconfig['debug'] || $this->bloxconfig['debugTime']) {

                    echo '<pre>Time to render (' . $key . '): ' . ($endsub - $startsub) . ' seconds</pre>';
                }
            }
        }
        $outerdata['innerrows'] = $bloxinnerrows;
        $outerdata['innercounts'] = $bloxinnercounts;

        $bloxouterTplData['row'] = $outerdata;
        $bloxouterTplData['config'] = $this->bloxconfig;
        $outerdata['blox'] = $bloxouterTplData;

        $tpl = new bloxChunkie($this->tpls['bloxouter']);
        $tpl->placeholders = $outerdata;
        $daten = $tpl->Render();
        unset($tpl);
        if ($cache == '1') {
            $this->cache->writeCache($cachename, $daten);
        }

        $end = microtime(true);
        if ($this->bloxconfig['debug'] || $this->bloxconfig['debugTime']) {

            echo '<pre>Time to render all: ' . ($end - $start) . ' seconds</pre>';
        }
        return $daten;
    }

    //////////////////////////////////////////////////
    //renderdatarows
    /////////////////////////////////////////////////
    function renderdatarows($rows, $tpl, $rowkey = '', $outerdata = array()) {
        //$this->renderdepth++;//Todo

        $output = '';
        $out = array();
        if (is_array($rows)) {
            $iteration = 0;
            $rowscount = count($rows);

            foreach ($rows as $row) {
                $iteration++;
                $out[] = $this->renderdatarow($row, $tpl, $rowkey, $outerdata, $rowscount, $iteration);
            }
        }
        $output = implode($this->bloxconfig['outputSeparator'], $out);
        return $output;
    }

    //////////////////////////////////////////////////
    //renderdatarow and custom-innerrows (bloxouterTpl)
    /////////////////////////////////////////////////
    function renderdatarow($row, $rowTpl = 'default', $rowkey = '', $outerdata = array(), $rowscount = 0, $iteration = 0) {
        global $modx;

        $date = $this->date;

        if (isset($row['tpl'])) {
            $tplfilename = $this->bloxconfig['tplpath'] . $row['tpl'];
            if (($row['tpl'] !== '') && (file_exists($modx->getOption('core_path') . $tplfilename))) {
                $rowTpl = "@FILE " . $tplfilename;
            }
            $tplfilename = $this->bloxconfig['tplpath'] . $row['tpl'] . 'Tpl.html';
            if (($row['tpl'] !== '') && (file_exists($modx->getOption('core_path') . $tplfilename))) {
                $rowTpl = "@FILE " . $tplfilename;
            }
        }

        if (substr($rowTpl, 0, 7) == '@FIELD:') {
            $rowTpl = ($row[substr($rowTpl, 7)]);
        }

        $datarowTplData = array();
        $bloxinnerrows = array();
        $bloxinnercounts = array();
        $innerrows = $modx->getOption('innerrows', $row, '');
        unset($row['innerrows']);


        if (is_array($innerrows)) {
            foreach ($innerrows as $key => $innerrow) {
                $innertpl = '';
                if (isset($this->tpls[$key])) {
                    $innertpl = $this->tpls[$key];
                } else {
                    $tplfile = $this->bloxconfig['tplpath'] . $key . "Tpl.html";
                    if (file_exists($modx->getOption('core_path') . $tplfile)) {
                        $innertpl = "@FILE " . $tplfile;
                    }
                }
                if (isset($this->templates[$innertpl]) || $innertpl !== '') {
                    $data = $this->renderdatarows($innerrow, $innertpl, $key, $row);
                    $datarowTplData['innerrows'][$key] = $data;
                    $bloxinnerrows[$key] = $data;
                    $bloxinnercounts[$key] = count($innerrow);
                }
            }
        }

        if (count($bloxinnerrows) > 0) {
            $row['innerrows'] = $bloxinnerrows;
            $row['innercounts'] = $bloxinnercounts;
        }

        $datarowTplData['parent'] = $outerdata;
        $datarowTplData['event'] = $row;
        $datarowTplData['date'] = $date;
        $datarowTplData['row'] = $row;
        $datarowTplData['rowscount'] = $rowscount;
        $datarowTplData['iteration'] = $iteration;

        $datarowTplData['config'] = $this->bloxconfig;
        $datarowTplData['userID'] = $this->bloxconfig['userID'];
        $row['_idx'] = $iteration;
        $row['_alt'] = ($iteration - 1) % 2;
        $row['_first'] = $iteration == 1 ? true : '';
        $row['_last'] = $iteration == $rowscount ? true : '';
        $row['blox'] = $datarowTplData;
        $tpl = new bloxChunkie($rowTpl);
        $tpl->placeholders = $row;
        $output = $tpl->Render();

        unset($tpl, $row);

        return $output;
    }

    //////////////////////////////////////////////////////
    //Daten-array holen
    //////////////////////////////////////////////////////
    function getdatas($date, $file) {
        global $modx;
        $file = $modx->getOption('core_path') . $file;
        $classfile = str_replace('.php', '.class.php', $file);
        $class = $this->bloxconfig['includesclass'];
        if ($date == 'dayisempty') {
            $bloxdatas = array();
        } else {
            if (file_exists($file)) {
                include $file;
            }
            if (file_exists($classfile)) {
                if (!class_exists($class)) {
                    include ($classfile);
                }

                $gd = new $class($this);
                $bloxdatas = $gd->getdatas();
            } 
        }

        return $bloxdatas;
    }

}

?>