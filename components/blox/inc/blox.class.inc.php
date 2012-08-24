<?php

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
        $this->bloxconfig['parents'] = $this->cleanIDs($bloxconfig['parents']);
        $this->bloxconfig['IDs'] = $this->cleanIDs($bloxconfig['IDs']);

        $this->tpls = array();
        $this->checktpls();

        $this->renderdepth = 0;
        $this->eventscount = array();
        $this->output = '';
        $this->date = xetadodb_mktime(0, 0, 0, $this->bloxconfig['month'], $this->bloxconfig['day'], $this->bloxconfig['year']);
    }

    function checktpls() {
        // example: &tpls=`bloxouter:myouter||row:contentonly`

        $this->tpls['bloxouter'] = "@FILE:" . $this->bloxconfig['tplpath'] . "/bloxouterTpl.html"; // [ path | chunkname | text ]
        if ($this->bloxconfig['tpls'] !== '') {
            $tpls = explode('||', $this->bloxconfig['tpls']);
            foreach ($tpls as $tpl) {
                $this->tpls[substr($tpl, 0, strpos($tpl, ':'))] = substr($tpl, strpos($tpl, ':') + 1);
                //Todo: check if chunk exists
            }
        }
    }

    function checkfilter() {
        if ($this->bloxconfig['resourceclass'] == 'modDocument') {
            if ($this->bloxconfig['showdeleted'] == '0' || $this->bloxconfig['showdeleted'] == '0') {
                $filter = 'deleted|' . $this->bloxconfig['showdeleted'] . '|=';
                $this->bloxconfig['prefilter'] = !empty($this->bloxconfig['prefilter']) ? $filter . '++' . $this->bloxconfig['prefilter'] : $filter;
            }

            if ($this->bloxconfig['showunpublished'] == '0' || $this->bloxconfig['showunpublished'] == '2') {

                $filter = 'published|' . (($this->bloxconfig['showunpublished'] == '0') ? '1' : '0') . '|=';
                $this->bloxconfig['prefilter'] = !empty($this->bloxconfig['prefilter']) ? $filter . '++' . $this->bloxconfig['prefilter'] : $filter;
            }
        }
    }

    function checkparents() {
        global $modx;

        if (!empty($this->bloxconfig['IDs']) || $this->bloxconfig['resourceclass'] !== 'modDocument') {
            return;
        }

        $this->bloxconfig['parents'] = ($this->bloxconfig['parents'] !== '') ? $this->bloxconfig['parents'] : $modx->resource->get('id');

        $parents = explode(',', $this->bloxconfig['parents']);
        $depth = $this->bloxconfig['depth'];

        if ($this->bloxconfig['bloxfolder'] !== '') {
            $depth = '1';
            $parents = $this->getBloxfolder($parents);
        }

        $parents = $this->getChildParents($parents, $depth);
        $parents = (is_array($parents)) ? $parents : array('9999999');

        //$filter = 'id|'.implode(',', $ids).'|IN';
        $filter = 'parent|' . implode(',', $parents) . '|IN';
        $this->bloxconfig['prefilter'] = !empty($this->bloxconfig['prefilter']) ? $filter . '++' . $this->bloxconfig['prefilter'] : $filter;
    }

    function checkIDs() {
        if (!empty($this->bloxconfig['IDs'])) {
            $ids = $this->bloxconfig['IDs'];
            $filter = $this->bloxconfig['keyField'] . '|' . $ids . '|IN';
            $this->bloxconfig['prefilter'] = !empty($this->bloxconfig['prefilter']) ? $filter . '++' . $this->bloxconfig['prefilter'] : $filter;
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

        $debug = $modx->getOption('debug', $scriptProperties, false);
        $joins = $modx->getOption('joins', $scriptProperties, '');
        $joins = !empty($joins) ? $modx->fromJson($joins) : false;
        $classname = ($scriptProperties['classname'] != '') ? $scriptProperties['classname'] : 'modResource';
        $c = $modx->newQuery($classname);

        $selectfields = !empty($selectfields) ? explode(',', $selectfields) : null;
        if ($forcounting) {
            $c->select('count(1)');
        } else {
            $c->select($modx->getSelectColumns($classname, $classname, '', $selectfields));
        }


        if ($joins) {
            $this->prepareJoins($classname, $joins, $c, $forcounting);
        }

        if (!empty($where)) {
            $c->where($where);
        }

        if (!empty($queries)) {
            $keys = array('AND' => xPDOQuery::SQL_AND, 'OR' => xPDOQuery::SQL_OR);
            foreach ($queries as $query) {
                $c->where($query['query'], $keys[$query['operator']]);
            }

        }
        if ($forcounting) {
            if ($debug) {
                $c->prepare();
                echo '<pre>Precount Query String:<br/>' . $c->toSql() . '</pre>';
                //echo $c->toSql();
            }
            if ($c->prepare() && $c->stmt->execute()) {
                $rows = $c->stmt->fetchAll(PDO::FETCH_COLUMN);
                $total = (integer)reset($rows);

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

    function getTvNames($template = 'all') {
        global $modx;
        $t_tv = $modx->getFullTableName('site_tmplvars');
        if ($template !== 'all') {
            $table2 = $modx->getFullTableName('site_tmplvar_templates') . ' stt ';
            $tablenames = $t_tv . ',' . $table2;
            $query = 'SELECT id,name FROM ' . $tablenames . 'WHERE templateid=' . $template . ' and stt.tmplvarid=st.id';
            $result = $modx->db->query($query);
        } else {
            $result = $modx->db->select('*', $t_tv, '');
        }
        $tv_arrays = $modx->db->makeArray($result);
        $tvnames = array();
        $tvids = array();
        foreach ($tv_arrays as $tv_array) {
            $tvid = $tv_array['id'];
            $tvnames[$tvid] = $tv_array['name'];
            $tvids[$tv_array['name']] = $tvid;
        }
        $this->tvnames = $tvnames;
        $this->tvids = $tvids;
        return;
    }


    //////////////////////////////////////////////////
    //Display bloX
    /////////////////////////////////////////////////

    function displayblox() {
        $datas = $this->getdatas($this->date, $this->bloxconfig['includesfile']);
        return $this->displaydatas($datas);

    }

    //////////////////////////////////////////////////
    //displaydatas (bloxouterTpl)
    /////////////////////////////////////////////////

    function displaydatas($outerdata = array()) {
        global $modx;

        // $outerdata['innerrows']['row']='innerrows.row';

        $start = microtime(TRUE);
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

                $daten = '';
                $innertpl = '';
                if (isset($this->tpls[$key])) {
                    $innertpl = $this->tpls[$key];
                } else {
                    $tplfile = $this->bloxconfig['tplpath'] . "/" . $key . "Tpl.html";
                    if (file_exists($modx->getOption('core_path') . $tplfile)) {
                        $innertpl = "@FILE:" . $tplfile;
                    }
                }

                if ($innertpl !== '') {
                    $data = $this->renderdatarows($row, $innertpl, $key, $outerdata);
                    $bloxinnerrows[$key] = $data;
                    $bloxinnercounts[$key] = count($row);
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

        $end = microtime(TRUE);
        if ($this->bloxconfig['debug']) {

            echo '<pre>Time to render: ' . ($end - $start) . ' seconds</pre>';
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
            $tplfilename = $this->bloxconfig['tplpath'] . "/" . $row['tpl'];
            if (($row['tpl'] !== '') && (file_exists($modx->getOption('core_path') . $tplfilename))) {
                $rowTpl = "@FILE:" . $tplfilename;
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
                    $tplfile = $this->bloxconfig['tplpath'] . "/" . $key . "Tpl.html";
                    if (file_exists($modx->getOption('core_path') . $tplfile)) {
                        $innertpl = "@FILE:" . $tplfile;
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
        $scriptProperties = $this->bloxconfig['scriptProperties'];
        $file = $modx->getOption('core_path') . $file;
        if ($date == 'dayisempty') {
            $bloxdatas = array();
        } else {

            if (file_exists($file)) {
                include ($file);
            } else {
                if ($this->bloxconfig['debug']) {

                    echo "includes-File " . $file . " nicht gefunden3";
                }
            }
        }

        return $bloxdatas;

    }


    //////////////////////////////////////////////////////////////////////////
    //Member Check
    //////////////////////////////////////////////////////////////////////////
    function isMemberOf($groups) {
        global $modx;
        if ($groups == 'all') {
            return true;
        } else {
            $webgroups = explode(',', $groups);
            if ($modx->user->isMember($webgroups)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /////////////////////////////////////////////////////////////////////////////
    //function to check for permission
    /////////////////////////////////////////////////////////////////////////////

    function checkpermission($permission) {
        $groupnames = $this->getwebusergroupnames();
        $perms = '';
        foreach ($groupnames as $groupname) {
            $perms .= $this->bloxconfig['permissions'][$groupname] . ',';
        }
        $perms = explode(',', $perms);
        return in_array($permission, $perms);
    }

    /////////////////////////////////////////////////////////////////////////////
    //function to get the groupnames of the webuser
    /////////////////////////////////////////////////////////////////////////////

    function getwebusergroupnames() {
        global $modx;
        $userid = $modx->getLoginUserID();
        $rows = array();
        $names = array();
        if (!$userid) {
            return $names;
        } else {
            $tablename1 = $modx->getFullTableName('web_groups') . ' wg';
            $tablename2 = $modx->getFullTableName('webgroup_names') . ' wn';
            $query = "SELECT distinct name
                       FROM " . $tablename1 . ", " . $tablename2 . " 
                       where webuser=" . $userid . " and wn.id = wg.webgroup";
            $result = $modx->db->query($query);
            $rows = $modx->db->makeArray($result);
            foreach ($rows as $row) {
                $names[] = $row['name'];

            }

        }
        return $names;
    }


    function getrows() {
        $result = array();
        switch ($this->bloxconfig['resourceclass']) {
            case 'modDocument':
                $result = $this->getdocs();
                break;
            case 'modTable':
                $result = $this->gettablerows();
                break;
            default:
                break;

        }

        $result = $this->checkXCCbuttons($result);
        $result = $this->checkDocSort($result);

        return $result;
    }

    /**
     * Sort DB result
     *
     * @param array $data Result of sql query as associative array
     *
     * Rest of parameters are optional
     * [, string $name  [, mixed $name or $order  [, mixed $name or $mode]]]
     * $name string - column name i database table
     * $order integer - sorting direction ascending (SORT_ASC) or descending (SORT_DESC)
     * $mode integer - sorting mode (SORT_REGULAR, SORT_STRING, SORT_NUMERIC)
     *
     * <code>
     *
     * // You can sort data by several columns e.g.
     * $data = array();
     * for ($i = 1; $i <= 10; $i++) {
     *     $data[] = array( 'id' => $i,
     *                      'first_name' => sprintf('first_name_%s', rand(1, 9)),
     *                      'last_name' => sprintf('last_name_%s', rand(1, 9)),
     *                      'date' => date('Y-m-d', rand(0, time()))
     *                  );
     * }
     * $data = sortDbResult($data, 'date', SORT_DESC, SORT_NUMERIC, 'id');
     * printf('<pre>%s</pre>', print_r($data, true));
     * $data = sortDbResult($data, 'last_name', SORT_ASC, SORT_STRING, 'first_name', SORT_ASC, SORT_STRING);
     * printf('<pre>%s</pre>', print_r($data, true));
     *
     * </code>
     *
     * @return array $data - Sorted data
     */

    function sortDbResult($_data) {


        $_argList = func_get_args();
        $_data = array_shift($_argList);
        if (empty($_data)) {
            return $_data;
        }
        $_max = count($_argList);
        $_params = array();
        $_cols = array();
        $_rules = array();
        for ($_i = 0; $_i < $_max; $_i += 3) {
            $_name = (string )$_argList[$_i];
            if (!in_array($_name, array_keys(current($_data)))) {
                continue;
            }
            if (!isset($_argList[($_i + 1)]) || is_string($_argList[($_i + 1)])) {
                $_order = SORT_ASC;
                $_mode = SORT_REGULAR;
                $_i -= 2;
            } else
                if (3 > $_argList[($_i + 1)]) {
                    $_order = SORT_ASC;
                    $_mode = $_argList[($_i + 1)];
                    $_i--;
                } else {
                    $_order = $_argList[($_i + 1)] == SORT_ASC ? SORT_ASC : SORT_DESC;
                    if (!isset($_argList[($_i + 2)]) || is_string($_argList[($_i + 2)])) {
                        $_mode = SORT_REGULAR;
                        $_i--;
                    } else {
                        $_mode = $_argList[($_i + 2)];
                    }
                }
                $_mode = $_mode != SORT_NUMERIC ? $_argList[($_i + 2)] != SORT_STRING ? SORT_REGULAR : SORT_STRING : SORT_NUMERIC;
            $_rules[] = array(
                'name' => $_name,
                'order' => $_order,
                'mode' => $_mode);
        }
        foreach ($_data as $_k => $_row) {
            foreach ($_rules as $_rule) {
                if (!isset($_cols[$_rule['name']])) {
                    $_cols[$_rule['name']] = array();
                    $_params[] = &$_cols[$_rule['name']];
                    $_params[] = $_rule['order'];
                    $_params[] = $_rule['mode'];
                }
                $_cols[$_rule['name']][$_k] = $_row[$_rule['name']];
            }
        }
        $_params[] = &$_data;
        call_user_func_array('array_multisort', $_params);
        return $_data;
    }

    /*
    * $link['page'] = 3;
    * $link['aname'] = 'avalue';
    * $link['another'] = 'one';
    * echo smartModxUrl($modx->documentObject["id"],NULL, $link);
    */

    function smartModxUrl($docid, $docalias, $array_values, $removearray = array()) {
        global $modx;
        $array_url = $_GET;
        $urlstring = array();

        unset($array_url["id"]);
        unset($array_url["q"]);

        $array_url = array_merge($array_url, $array_values);

        foreach ($array_url as $name => $value) {
            if (!is_null($value) && !in_array($name, $removearray)) {
                $urlstring[] = $name . '=' . urlencode($value);
            }
        }

        return $modx->makeUrl($docid, $docalias, join('&', $urlstring));
    }

    // ---------------------------------------------------
    // Function: getChildIDs
    // Get the IDs ready to be processed
    // Similar to the modx version by the same name but much faster
    // ---------------------------------------------------

    function getChildParents($IDs, $depth) {
        global $modx;
        $depth = intval($depth);
        $kids = array();
        $parents = array();
        $docIDs = array();

        if ($depth == 0 && $IDs[0] == 0 && count($IDs) == 1) {
            $parents['0'] = 0;

            foreach ($modx->documentMap as $null => $document) {
                foreach ($document as $parent => $id) {
                    //$kids[] = $id;
                    $parents[$parent] = $parent;
                }
            }
            return $parents;
        } else
            if ($depth == 0) {
                $depth = 10000;
                // Impliment unlimited depth...
            }

        foreach ($modx->documentMap as $null => $document) {
            foreach ($document as $parent => $id) {
                $kids[$parent][] = $id;
            }
        }

        foreach ($IDs as $seed) {
            if (!empty($kids[intval($seed)])) {
                $docIDs = array_merge($docIDs, $kids[intval($seed)]);
                $parents[intval($seed)] = intval($seed);
                unset($kids[intval($seed)]);
            }
        }
        $depth--;

        while ($depth != 0) {
            $valid = $docIDs;
            foreach ($docIDs as $id) {
                if (!empty($kids[intval($id)])) {
                    $docIDs = array_merge($docIDs, $kids[intval($id)]);
                    $parents[intval($id)] = intval($id);
                    unset($kids[intval($id)]);
                }
            }
            $depth--;
            if ($valid == $docIDs)
                $depth = 0;
        }

        return $parents;
    }


    //////////////////////////////////////////////////////////////////////
    // Ditto - Functions
    // Author:
    // 		Mark Kaplan for MODx CMF
    //////////////////////////////////////////////////////////////////////

    // ---------------------------------------------------
    // Function: cleanIDs
    // Clean the IDs of any dangerous characters
    // ---------------------------------------------------

    function cleanIDs($IDs) {
        //Define the pattern to search for
        $pattern = array(
            '`(,)+`', //Multiple commas
            '`^(,)`', //Comma on first position
            '`(,)$`' //Comma on last position
                );

        //Define replacement parameters
        $replace = array(
            ',',
            '',
            '');

        //Clean startID (all chars except commas and numbers are removed)
        $IDs = preg_replace($pattern, $replace, $IDs);

        return $IDs;
    }

    // ---------------------------------------------------
    // Function: getChildIDs
    // Get the IDs ready to be processed
    // Similar to the modx version by the same name but much faster
    // ---------------------------------------------------

    function getChildIDs($IDs, $depth) {
        global $modx;
        $depth = intval($depth);
        $kids = array();
        $docIDs = array();

        if ($depth == 0 && $IDs[0] == 0 && count($IDs) == 1) {
            foreach ($modx->documentMap as $null => $document) {
                foreach ($document as $parent => $id) {
                    $kids[] = $id;
                }
            }
            return $kids;
        } else
            if ($depth == 0) {
                $depth = 10000;
                // Impliment unlimited depth...
            }

        foreach ($modx->documentMap as $null => $document) {
            foreach ($document as $parent => $id) {
                $kids[$parent][] = $id;
            }
        }

        foreach ($IDs as $seed) {
            if (!empty($kids[intval($seed)])) {
                $docIDs = array_merge($docIDs, $kids[intval($seed)]);
                unset($kids[intval($seed)]);
            }
        }
        $depth--;

        while ($depth != 0) {
            $valid = $docIDs;
            foreach ($docIDs as $id) {
                if (!empty($kids[intval($id)])) {
                    $docIDs = array_merge($docIDs, $kids[intval($id)]);
                    unset($kids[intval($id)]);
                }
            }
            $depth--;
            if ($valid == $docIDs)
                $depth = 0;
        }

        return array_unique($docIDs);
    }

    function getSiteMap($items, $level = 0) {
        /* $start = array (array ('id'=>0));
        * $map = getSiteMap($start);
        * print_r($map);
        */

        global $modx;
        $pages = array();
        foreach ($items as $item) {
            $page = array();
            $page['id'] = $item['id'];
            $page['level'] = $level;
            $page['pagetitle'] = $item['pagetitle'];
            //$page['URL'] = $modx->makeUrl($item['id']);
            $children = $modx->getAllChildren($item['id'], 'menuindex ASC, pagetitle', 'ASC', 'id,isfolder,pagetitle,description,parent,alias,longtitle,published,deleted,hidemenu');
            if (count($children) > 0) {
                $children = $this->getSiteMap($children, $level + 1);
                $page['innerrows']['level_' . $level + 1] = $children;
            }
            $pages[] = $page;
        }

        return $pages;
    }

}

?>