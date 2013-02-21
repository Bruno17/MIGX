<?php

class bloxhelpers {

    public function __construct(&$blox) {
        $this->blox = &$blox;
        $this->bloxconfig = &$blox->bloxconfig;
    }

    function getSiteMap($config) {
        global $modx;

        $config['parents'] = $modx->getOption('startid', $config, 0);
        $config['depth'] = $modx->getOption('depth', $config, 10);
        $config['level'] = $modx->getOption('startingLevel', $config, 0);
        $config['ignoreHidden'] = $modx->getOption('ignoreHidden', $config, 0);
        $config['hideSubmenuesStartlevel'] = $modx->getOption('hideSubmenuesStartlevel', $config, $config['depth']);
        $config['excludedocs'] = $modx->getOption('excludedocs', $config, '');
        $config['excludechildrenofdocs'] = $modx->getOption('excludechildrenofdocs', $config, '');
        $config['activeid'] = $modx->getOption('activeid', $config, $modx->resource->get('id'));
        $config['selectfields'] = $modx->getOption('selectfields', $config, '');
        $config['sortby'] = $modx->getOption('sortby', $config, 'menuindex');
        $config['sortdir'] = $modx->getOption('sortdir', $config, 'ASC');
        $config['classname'] = $modx->getOption('classname', $config, 'modResource');
        $config['context'] = $modx->getOption('context', $config, $modx->context->key);
        $wheres = !empty($config['where']) ? $modx->fromJson($config['where']) : array();
        if ($config['classname'] == 'modResource') {
            if (empty($config['ignoreHidden'])) {
                $wheres[] = array('hidemenu' => '0');
            }
            $config['activeparents'] = $modx->getParentIds($config['activeid']);
            $config['where'] = $modx->toJson($wheres);
            $config['docinfo'] = $this->getResources($config);
            $firstlevel = $modx->getChildIds($config['parents'], 1, array('context' => $config['context']));
            //print_r($items);
            $output = $this->getSiteMapChilds($firstlevel, $config);

        } else {
            $config['activeparents'] = $this->getParentIds($config);
            $config = $this->getChilds($config);
            $firstlevel = $config['levelids'];
            $output = $this->getSiteMapChilds($firstlevel, $config);
        }

        return $output;
    }


    function getSiteMapChilds($items, $config) {
        /* $start = array (array ('id'=>0));
        * $map = getSiteMap($start);
        * print_r($map);
        */

        global $modx;

        $config['startid'] = $modx->getOption('startid', $config, 0);
        $config['depth'] = $modx->getOption('depth', $config, 10);
        $config['level'] = $modx->getOption('level', $config, 0);
        $config['ignoreHidden'] = $modx->getOption('ignoreHidden', $config, 0);
        $config['hideSubmenuesStartlevel'] = $modx->getOption('hideSubmenuesStartlevel', $config, $config['depth']);
        $config['excludedocs'] = $modx->getOption('excludedocs', $config, '');
        $config['excludechildrenofdocs'] = $modx->getOption('excludechildrenofdocs', $config, '');
        $config['activeid'] = $modx->getOption('activeid', $config, 0);
        $config['selectfields'] = $modx->getOption('selectfields', $config, '');
        $config['activeparents'] = $modx->getOption('activeparents', $config, array());
        $config['sortby'] = $modx->getOption('sortby', $config, 'menuindex');
        $config['sortdir'] = $modx->getOption('sortdir', $config, 'ASC');
        $config['classname'] = $modx->getOption('classname', $config, 'modResource');

        $level = $config['level'];

        $excludedocs = !empty($config['excludedocs']) ? explode(',', $config['excludedocs']) : '';
        $excludechildrenofdocs = !empty($config['excludechildrenofdocs']) ? explode(',', $config['excludechildrenofdocs']) : '';
        $pages = array();
        $docinfo = $config['docinfo'];
        foreach ($items as $item) {
            $page = $docinfo[$item];
            $page['level'] = $level;
            $page['_haschildren'] = '0';
            $page['_active'] = $config['activeid'] == $page['id'] || in_array($page['id'], $config['activeparents']) ? '1' : '0';
            $page['_here'] = $config['activeid'] == $page['id'] ? '1' : '0';
            //$page['URL'] = $modx->makeUrl($item['id']);
            //$children = $modx->getAllChildren($item['id'], 'menuindex ASC, pagetitle', 'ASC', 'id,isfolder,pagetitle,description,parent,alias,longtitle,published,deleted,hidemenu');

            if ($config['classname'] == 'modResource') {
                $childs = $modx->getChildIds($page['id'], 1, array('context' => $config['context']));
            } else {
                $config['startid']=$page['id'];
                $config = $this->getChilds($config);
                $childs = $config['levelids'];
            }
            
            $count = count($childs);
            if ($count > 0) {
                $page['_haschildren'] = '1';
                if (!in_array($page['id'], $excludechildrenofdocs) && $level < $config['depth']) {
                    if ($page['_active'] == '1' || $level < $config['hideSubmenuesStartlevel']) {
                        $config['level'] = $level + 1;
                        $children = $this->getSiteMapChilds($childs, $config);
                        $page['innerrows']['level_' . $config['level']] = $children;
                        $page['innerrows']['children'] = $children;

                    }
                }
            }


            $pages[] = $page;
        }

        return $pages;
    }

   function getParentIds($config,$parentids=array()){
       global $modx;

       if($object = $modx->getObject($config['classname'], array('id' => $config['activeid'] ))){
           $parent = $object->get('parent');
           $config['activeid']=$parent;
           $parentids = array_merge(array($parent),$this->getParentIds($config,$parentids)); 
       }
       return $parentids; 
   }


    function getChilds($config) {
        global $modx;
        
        $c = $modx->newQuery($config['classname'], array('parent' => $config['startid'], ));
        if (!empty($config['selectfields'])) {
            $c->select($modx->getSelectColumns($config['classname'], $config['classname'], '', explode(',', $config['selectfields'])));
        }
        //$c->where(array('published' => '1'));

        $tablefields = array_keys($modx->getFields('modResource'));
        if (in_array('deleted', $tablefields)) {
            $c->where(array('deleted' => '0'));
        }
        $c->sortby($config['sortby'], $config['sortdir']);
        $items = array();
        /*
        $c->prepare();
        echo $c->toSql();
        */
        $levelids = array();
        if ($collection = $modx->getCollection($config['classname'], $c)) {
            foreach ($collection as $object) {
                $items[$object->get('id')] = $object->toArray('', false, true);
                $levelids[] = $object->get('id');
            }
        }
        $config['docinfo'] = $items;
        $config['levelids'] = $levelids;
        return $config;
    }


    function buildMenu($docs, $depth, $docInfo = array(), $parentIds = array(), $level = 0) {
        global $modx;
        $level++;

        if ($depth >= 0) {
            $depth--;
            $levelDocs = array();
            $idx = 0;
            $docs = array_intersect($docs, array_keys($docInfo));
            $levelCount = count($docs);
            foreach ($docs as $docId) {
                if (isset($docInfo[$docId])) {
                    $levelDocs[$docId] = $docInfo[$docId];
                    $levelDocs[$docId]['title'] = ($docInfo[$docId]['menutitle'] != '') ? $docInfo[$docId]['menutitle'] : $docInfo[$docId]['pagetitle'];
                    $levelDocs[$docId]['idx'] = $idx;
                    $levelDocs[$docId]['levelcount'] = $levelCount;
                    $levelDocs[$docId]['classnames'] = '';
                    if ($idx == 0) {
                        $levelDocs[$docId]['classnames'] .= ' first';
                    }
                    if (in_array($docId, $parentIds)) {
                        $levelDocs[$docId]['classnames'] .= ' current';
                    }
                    if ($docId == $modx->resource->get('id')) {
                        $levelDocs[$docId]['classnames'] .= ' here';
                    }
                    if ($idx == $levelCount - 1) {
                        $levelDocs[$docId]['classnames'] .= ' last';
                    }
                    $levelDocs[$docId]['classnames'] = trim($levelDocs[$docId]['classnames']);
                    $children = $modx->getChildIds($docId, 1, array('context' => $this->bloxconfig['context'], 'where' => $this->bloxconfig['where']));
                    if ($children) {
                        $childMenu = $this->buildMenu($children, $depth, $docInfo, $parentIds, $level);
                        if (count($childMenu)) {
                            $levelDocs[$docId]['level' . ($level + 1)] = $childMenu;
                        }
                    }
                    $idx++;
                }
            }
            return $levelDocs;
        }
    }

    function buildMenuTemplate($docs, $depth, $docInfo = array(), $level = 0) {
        global $modx;

        $level++;

        if ($depth >= 0) {
            $depth--;
            $levelDocTemplate = '';
            foreach ($docs as $docId) {
                if (isset($docInfo[$docId])) {
                    if (file_exists(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'level' . $level . 'Tpl.html')) {
                        $template = file_get_contents(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'level' . $level . 'Tpl.html');
                    } else {
                        $template = file_get_contents(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'levelTpl.html');
                    }
                    if (file_exists(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'doc' . $docId . 'OuterTpl.html')) {
                        $childMenu = file_get_contents(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'doc' . $docId . 'OuterTpl.html');
                        $levelDocTemplate .= str_replace('[[+', '[[+level' . ($level) . '.' . ($docId) . '.', $childMenu) . "\r\n";
                    } else {
                        $children = $modx->getChildIds($docId, 1);
                        $childMenu = ($children && $depth >= 0) ? $this->buildMenuTemplate($children, $depth, $docInfo, $level) : '';
                        if ($childMenu) {
                            if (file_exists(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'level' . ($level + 1) . 'OuterTpl.html')) {
                                $outerTemplate = file_get_contents(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'level' . ($level + 1) . 'OuterTpl.html');
                            } else {
                                $outerTemplate = file_get_contents(MODX_CORE_PATH . $this->bloxconfig['tplpath'] . 'levelOuterTpl.html');
                            }
                            $childMenu = str_replace('[[#wrapper]]', $childMenu, $outerTemplate);
                            $childMenu = str_replace('[[+', '[[+level' . ($level) . '.' . ($docId) . '.', $childMenu);
                        }
                        $levelDocTemplate .= str_replace('[[+', '[[+level' . ($level) . '.' . ($docId) . '.', $template) . "\r\n";
                        $levelDocTemplate = str_replace('[[#wrapper]]', $childMenu, $levelDocTemplate);
                    }
                }
            }
            return $levelDocTemplate;
        } else {
            return '';
        }
    }    


    function getResources($scriptProperties = null) {
        global $modx;


        if (!$scriptProperties) {
            $scriptProperties = $this->bloxconfig;
        }

        // lot of code from getresources snippet
        $includeContent = !empty($scriptProperties['includeContent']) ? true : false;
        $includeTVs = !empty($scriptProperties['includeTVs']) ? true : false;
        $includeTVList = !empty($scriptProperties['includeTVList']) ? explode(',', $scriptProperties['includeTVList']) : array();
        $processTVs = !empty($scriptProperties['processTVs']) ? true : false;
        $processTVList = !empty($scriptProperties['processTVList']) ? explode(',', $scriptProperties['processTVList']) : array();
        $prepareTVs = !empty($scriptProperties['prepareTVs']) ? true : false;
        $prepareTVList = !empty($scriptProperties['prepareTVList']) ? explode(',', $scriptProperties['prepareTVList']) : array();
        $tvPrefix = isset($scriptProperties['tvPrefix']) ? $scriptProperties['tvPrefix'] : 'tv.';
        $parents = (!empty($scriptProperties['parents']) || $scriptProperties['parents'] === '0') ? explode(',', $scriptProperties['parents']) : array($modx->resource->get('id'));
        array_walk($parents, 'trim');
        $parents = array_unique($parents);
        $depth = isset($scriptProperties['depth']) ? (integer)$scriptProperties['depth'] : 10;
        $resources = isset($scriptProperties['resources']) ? $scriptProperties['resources'] : '';
        $context = isset($scriptProperties['context']) ? $scriptProperties['context'] : '';

        $tvFiltersOrDelimiter = isset($scriptProperties['tvFiltersOrDelimiter']) ? $scriptProperties['tvFiltersOrDelimiter'] : '||';
        $tvFiltersAndDelimiter = isset($scriptProperties['tvFiltersAndDelimiter']) ? $scriptProperties['tvFiltersAndDelimiter'] : ',';
        $tvFilters = !empty($scriptProperties['tvFilters']) ? explode($tvFiltersOrDelimiter, $scriptProperties['tvFilters']) : array();

        $where = !empty($scriptProperties['where']) ? $modx->fromJSON($scriptProperties['where']) : array();
        $showUnpublished = !empty($scriptProperties['showUnpublished']) ? true : false;
        $showDeleted = !empty($scriptProperties['showDeleted']) ? true : false;
        $showHidden = !empty($scriptProperties['showHidden']) ? true : false;
        $hideContainers = !empty($scriptProperties['hideContainers']) ? true : false;

        $sortby = isset($scriptProperties['sortby']) ? $scriptProperties['sortby'] : 'publishedon';
        $sortbyTV = isset($scriptProperties['sortbyTV']) ? $scriptProperties['sortbyTV'] : '';
        $sortbyTVType = isset($scriptProperties['sortbyTVType']) ? $scriptProperties['sortbyTVType'] : 'string';
        $sortbyAlias = isset($scriptProperties['sortbyAlias']) ? $scriptProperties['sortbyAlias'] : 'modResource';
        $sortbyEscaped = !empty($scriptProperties['sortbyEscaped']) ? true : false;
        $sortdir = isset($scriptProperties['sortdir']) ? $scriptProperties['sortdir'] : 'DESC';
        $sortdirTV = isset($scriptProperties['sortdirTV']) ? $scriptProperties['sortdirTV'] : 'DESC';
        $limit = isset($scriptProperties['limit']) ? (integer)$scriptProperties['limit'] : 5;
        $offset = isset($scriptProperties['offset']) ? (integer)$scriptProperties['offset'] : 0;
        $totalVar = !empty($scriptProperties['totalVar']) ? $scriptProperties['totalVar'] : 'total';

        $fields = !empty($scriptProperties['selectfields']) ? explode(',', $scriptProperties['selectfields']) : array();

        $dbCacheFlag = !isset($scriptProperties['dbCacheFlag']) ? false : $scriptProperties['dbCacheFlag'];
        if (is_string($dbCacheFlag) || is_numeric($dbCacheFlag)) {
            if ($dbCacheFlag == '0') {
                $dbCacheFlag = false;
            } elseif ($dbCacheFlag == '1') {
                $dbCacheFlag = true;
            } else {
                $dbCacheFlag = (integer)$dbCacheFlag;
            }
        }

        $debug = !empty($scriptProperties['debug']) ? true : false;

        /* multiple context support */
        $contextArray = array();
        $contextSpecified = false;
        if (!empty($context)) {
            $contextArray = explode(',', $context);
            array_walk($contextArray, 'trim');
            $contexts = array();
            foreach ($contextArray as $ctx) {
                $contexts[] = $modx->quote($ctx);
            }
            $context = implode(',', $contexts);
            $contextSpecified = true;
            unset($contexts, $ctx);
        } else {
            $context = $modx->quote($modx->context->get('key'));
        }

        $pcMap = array();
        $pcQuery = $modx->newQuery('modResource', array('id:IN' => $parents), $dbCacheFlag);
        $pcQuery->select(array('id', 'context_key'));
        if ($pcQuery->prepare() && $pcQuery->stmt->execute()) {
            foreach ($pcQuery->stmt->fetchAll(PDO::FETCH_ASSOC) as $pcRow) {
                $pcMap[(integer)$pcRow['id']] = $pcRow['context_key'];
            }
        }

        $children = array();
        $parentArray = array();
        foreach ($parents as $parent) {
            $parent = (integer)$parent;
            if ($parent === 0) {
                $pchildren = array();
                if ($contextSpecified) {
                    foreach ($contextArray as $pCtx) {
                        if (!in_array($pCtx, $contextArray)) {
                            continue;
                        }
                        $options = $pCtx !== $modx->context->get('key') ? array('context' => $pCtx) : array();
                        $pcchildren = $modx->getChildIds($parent, $depth, $options);
                        if (!empty($pcchildren))
                            $pchildren = array_merge($pchildren, $pcchildren);
                    }
                } else {
                    $cQuery = $modx->newQuery('modContext', array('key:!=' => 'mgr'));
                    $cQuery->select(array('key'));
                    if ($cQuery->prepare() && $cQuery->stmt->execute()) {
                        foreach ($cQuery->stmt->fetchAll(PDO::FETCH_COLUMN) as $pCtx) {
                            $options = $pCtx !== $modx->context->get('key') ? array('context' => $pCtx) : array();
                            $pcchildren = $modx->getChildIds($parent, $depth, $options);
                            if (!empty($pcchildren))
                                $pchildren = array_merge($pchildren, $pcchildren);
                        }
                    }
                }
                $parentArray[] = $parent;
            } else {
                $pContext = array_key_exists($parent, $pcMap) ? $pcMap[$parent] : false;
                if ($debug)
                    $modx->log(modX::LOG_LEVEL_ERROR, "context for {$parent} is {$pContext}");
                if ($pContext && $contextSpecified && !in_array($pContext, $contextArray, true)) {
                    $parent = next($parents);
                    continue;
                }
                $parentArray[] = $parent;
                $options = !empty($pContext) && $pContext !== $modx->context->get('key') ? array('context' => $pContext) : array();
                $pchildren = $modx->getChildIds($parent, $depth, $options);
            }
            if (!empty($pchildren))
                $children = array_merge($children, $pchildren);
            $parent = next($parents);
        }
        $parents = array_merge($parentArray, $children);

        /* build query */
        $criteria = array("modResource.parent IN (" . implode(',', $parents) . ")");
        if ($contextSpecified) {
            $contextResourceTbl = $modx->getTableName('modContextResource');
            $criteria[] = "(modResource.context_key IN ({$context}) OR EXISTS(SELECT 1 FROM {$contextResourceTbl} ctx WHERE ctx.resource = modResource.id AND ctx.context_key IN ({$context})))";
        }
        if (empty($showDeleted)) {
            $criteria['deleted'] = '0';
        }
        if (empty($showUnpublished)) {
            $criteria['published'] = '1';
        }
        if (empty($showHidden)) {
            $criteria['hidemenu'] = '0';
        }
        if (!empty($hideContainers)) {
            $criteria['isfolder'] = '0';
        }
        $criteria = $modx->newQuery('modResource', $criteria);
        if (!empty($tvFilters)) {
            $tmplVarTbl = $modx->getTableName('modTemplateVar');
            $tmplVarResourceTbl = $modx->getTableName('modTemplateVarResource');
            $conditions = array();
            $operators = array(
                '<=>' => '<=>',
                '===' => '=',
                '!==' => '!=',
                '<>' => '<>',
                '==' => 'LIKE',
                '!=' => 'NOT LIKE',
                '<<' => '<',
                '<=' => '<=',
                '=<' => '=<',
                '>>' => '>',
                '>=' => '>=',
                '=>' => '=>');
            foreach ($tvFilters as $tvFilter) {
                $filterGroup = array();
                $filters = explode($tvFiltersAndDelimiter, $tvFilter);
                $multiple = count($filters) > 0;
                foreach ($filters as $filter) {
                    $operator = '==';
                    $sqlOperator = 'LIKE';
                    foreach ($operators as $op => $opSymbol) {
                        if (strpos($filter, $op, 1) !== false) {
                            $operator = $op;
                            $sqlOperator = $opSymbol;
                            break;
                        }
                    }
                    $tvValueField = 'tvr.value';
                    $tvDefaultField = 'tv.default_text';
                    $f = explode($operator, $filter);
                    if (count($f) == 2) {
                        $tvName = $modx->quote($f[0]);
                        if (is_numeric($f[1]) && !in_array($sqlOperator, array('LIKE', 'NOT LIKE'))) {
                            $tvValue = $f[1];
                            if ($f[1] == (integer)$f[1]) {
                                $tvValueField = "CAST({$tvValueField} AS SIGNED INTEGER)";
                                $tvDefaultField = "CAST({$tvDefaultField} AS SIGNED INTEGER)";
                            } else {
                                $tvValueField = "CAST({$tvValueField} AS DECIMAL)";
                                $tvDefaultField = "CAST({$tvDefaultField} AS DECIMAL)";
                            }
                        } else {
                            $tvValue = $modx->quote($f[1]);
                        }
                        if ($multiple) {
                            $filterGroup[] = "(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
                                "OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " .
                                ")";
                        } else {
                            $filterGroup = "(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
                                "OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " .
                                ")";
                        }
                    } elseif (count($f) == 1) {
                        $tvValue = $modx->quote($f[0]);
                        if ($multiple) {
                            $filterGroup[] = "EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id)";
                        } else {
                            $filterGroup = "EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id)";
                        }
                    }
                }
                $conditions[] = $filterGroup;
            }
            if (!empty($conditions)) {
                $firstGroup = true;
                foreach ($conditions as $cGroup => $c) {
                    if (is_array($c)) {
                        $first = true;
                        foreach ($c as $cond) {
                            if ($first && !$firstGroup) {
                                $criteria->condition($criteria->query['where'][0][1], $cond, xPDOQuery::SQL_OR, null, $cGroup);
                            } else {
                                $criteria->condition($criteria->query['where'][0][1], $cond, xPDOQuery::SQL_AND, null, $cGroup);
                            }
                            $first = false;
                        }
                    } else {
                        $criteria->condition($criteria->query['where'][0][1], $c, $firstGroup ? xPDOQuery::SQL_AND : xPDOQuery::SQL_OR, null, $cGroup);
                    }
                    $firstGroup = false;
                }
            }
        }
        /* include/exclude resources, via &resources=`123,-456` prop */
        if (!empty($resources)) {
            $resources = explode(',', $resources);
            $include = array();
            $exclude = array();
            foreach ($resources as $resource) {
                $resource = (int)$resource;
                if ($resource == 0)
                    continue;
                if ($resource < 0) {
                    $exclude[] = abs($resource);
                } else {
                    $include[] = $resource;
                }
            }
            if (!empty($include)) {
                $criteria->where(array('OR:modResource.id:IN' => $include), xPDOQuery::SQL_OR);
            }
            if (!empty($exclude)) {
                $criteria->where(array('modResource.id:NOT IN' => $exclude), xPDOQuery::SQL_AND, null, 1);
            }
        }
        if (!empty($where)) {
            $criteria->where($where);
        }

        $total = $modx->getCount('modResource', $criteria);
        $modx->setPlaceholder($totalVar, $total);

        $includeContent = in_array('content', $fields) ? 1 : 0;
        $fields = !empty($fields) ? $fields : array_keys($modx->getFields('modResource'));
        if (empty($includeContent)) {
            $fields = array_diff($fields, array('content'));
        }
        $columns = $modx->getSelectColumns('modResource', 'modResource', '', $fields);
        $criteria->select($columns);
        if (!empty($sortbyTV)) {
            $criteria->leftJoin('modTemplateVar', 'tvDefault', array("tvDefault.name" => $sortbyTV));
            $criteria->leftJoin('modTemplateVarResource', 'tvSort', array("tvSort.contentid = modResource.id", "tvSort.tmplvarid = tvDefault.id"));
            if ($modx->getOption('dbtype') === 'mysql') {
                switch ($sortbyTVType) {
                    case 'integer':
                        $criteria->select("CAST(IFNULL(tvSort.value, tvDefault.default_text) AS SIGNED INTEGER) AS sortTV");
                        break;
                    case 'decimal':
                        $criteria->select("CAST(IFNULL(tvSort.value, tvDefault.default_text) AS DECIMAL) AS sortTV");
                        break;
                    case 'datetime':
                        $criteria->select("CAST(IFNULL(tvSort.value, tvDefault.default_text) AS DATETIME) AS sortTV");
                        break;
                    case 'string':
                    default:
                        $criteria->select("IFNULL(tvSort.value, tvDefault.default_text) AS sortTV");
                        break;
                }
            } elseif ($modx->getOption('dbtype') === 'sqlsrv') {
                switch ($sortbyTVType) {
                    case 'integer':
                        $criteria->select("CAST(ISNULL(tvSort.value, tvDefault.default_text) AS BIGINT) AS sortTV");
                        break;
                    case 'decimal':
                        $criteria->select("CAST(ISNULL(tvSort.value, tvDefault.default_text) AS DECIMAL) AS sortTV");
                        break;
                    case 'datetime':
                        $criteria->select("CAST(ISNULL(tvSort.value, tvDefault.default_text) AS DATETIME) AS sortTV");
                        break;
                    case 'string':
                    default:
                        $criteria->select("ISNULL(tvSort.value, tvDefault.default_text) AS sortTV");
                        break;
                }
            }
            $criteria->sortby("sortTV", $sortdirTV);
        }
        if (!empty($sortby)) {
            if (strpos($sortby, '{') === 0) {
                $sorts = $modx->fromJSON($sortby);
            } else {
                $sorts = array($sortby => $sortdir);
            }
            if (is_array($sorts)) {
                while (list($sort, $dir) = each($sorts)) {
                    if ($sortbyEscaped)
                        $sort = $modx->escape($sort);
                    if (!empty($sortbyAlias))
                        $sort = $modx->escape($sortbyAlias) . ".{$sort}";
                    $criteria->sortby($sort, $dir);
                }
            }
        }
        if (!empty($limit))
            $criteria->limit($limit, $offset);

        if (!empty($debug)) {
            $criteria->prepare();
            $modx->log(modX::LOG_LEVEL_ERROR, $criteria->toSQL());
        }
        //$criteria->prepare();echo $criteria->toSQL();

        $collection = $modx->getCollection('modResource', $criteria, $dbCacheFlag);

        $idx = !empty($idx) && $idx !== '0' ? (integer)$idx : 1;
        $first = empty($first) && $first !== '0' ? 1 : (integer)$first;
        $last = empty($last) ? (count($collection) + $idx - 1) : (integer)$last;

        // collect the data
        $rows = array();
        $templateVars = array();
        if (!empty($includeTVs) && !empty($includeTVList)) {
            $templateVars = $modx->getCollection('modTemplateVar', array('name:IN' => $includeTVList));
        }
        foreach ($collection as $resource) {
            $tvs = array();
            if (!empty($includeTVs)) {
                if (empty($includeTVList)) {
                    $templateVars = $resource->getMany('TemplateVars');
                }
                /**
                 *  *  *  *  *  * @var modTemplateVar $templateVar */
                foreach ($templateVars as $templateVar) {
                    if (!empty($includeTVList) && !in_array($templateVar->get('name'), $includeTVList))
                        continue;
                    if ($processTVs && (empty($processTVList) || in_array($templateVar->get('name'), $processTVList))) {
                        $tvs[$tvPrefix . $templateVar->get('name')] = $templateVar->renderOutput($resource->get('id'));
                    } else {
                        $value = $templateVar->getValue($resource->get('id'));
                        if ($prepareTVs && method_exists($templateVar, 'prepareOutput') && (empty($prepareTVList) || in_array($templateVar->get('name'), $prepareTVList))) {
                            $value = $templateVar->prepareOutput($value);
                        }
                        $tvs[$tvPrefix . $templateVar->get('name')] = $value;
                    }
                }
            }
            $odd = ($idx & 1);
            $rows[$resource->get('id')] = array_merge(array(
                'idx' => $idx,
                'first' => $first,
                'last' => $last,
                'odd' => $odd), $resource->toArray('', false, true), $tvs);
            $idx++;
        }
        return $rows;
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


    //////////////////////////////////////////////////////////////////////
    // Ditto - Functions
    // Author:
    //         Mark Kaplan for MODx CMF
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

}
