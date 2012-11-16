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
			}
			if ($c->prepare() && $c->stmt->execute()) {
				$rows = $c->stmt->fetchAll(PDO::FETCH_COLUMN);
				$total = intval(reset($rows));
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

	function getResources($scriptProperties = NULL) {
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
		$depth = isset($scriptProperties['depth']) ? (integer) $scriptProperties['depth'] : 10;
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
		$limit = isset($scriptProperties['limit']) ? (integer) $scriptProperties['limit'] : 5;
		$offset = isset($scriptProperties['offset']) ? (integer) $scriptProperties['offset'] : 0;
		$totalVar = !empty($scriptProperties['totalVar']) ? $scriptProperties['totalVar'] : 'total';

		$dbCacheFlag = !isset($scriptProperties['dbCacheFlag']) ? false : $scriptProperties['dbCacheFlag'];
		if (is_string($dbCacheFlag) || is_numeric($dbCacheFlag)) {
			if ($dbCacheFlag == '0') {
				$dbCacheFlag = false;
			} elseif ($dbCacheFlag == '1') {
				$dbCacheFlag = true;
			} else {
				$dbCacheFlag = (integer) $dbCacheFlag;
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
				$pcMap[(integer) $pcRow['id']] = $pcRow['context_key'];
			}
		}

		$children = array();
		$parentArray = array();
		foreach ($parents as $parent) {
			$parent = (integer) $parent;
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
				'=>' => '=>'
			);
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
							if ($f[1] == (integer) $f[1]) {
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
							$filterGroup[] =
									"(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
									"OR EXISTS (SELECT 1 FROM {$tmplVarTbl} tv WHERE tv.name = {$tvName} AND {$tvDefaultField} {$sqlOperator} {$tvValue} AND tv.id NOT IN (SELECT tmplvarid FROM {$tmplVarResourceTbl} WHERE contentid = modResource.id)) " .
									")";
						} else {
							$filterGroup =
									"(EXISTS (SELECT 1 FROM {$tmplVarResourceTbl} tvr JOIN {$tmplVarTbl} tv ON {$tvValueField} {$sqlOperator} {$tvValue} AND tv.name = {$tvName} AND tv.id = tvr.tmplvarid WHERE tvr.contentid = modResource.id) " .
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
				$resource = (int) $resource;
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

		$fields = array_keys($modx->getFields('modResource'));
		if (empty($includeContent)) {
			$fields = array_diff($fields, array('content'));
		}
		$columns = $includeContent ? $modx->getSelectColumns('modResource', 'modResource') : $modx->getSelectColumns('modResource', 'modResource', '', array('content'), true);
		$criteria->select($columns);
		if (!empty($sortbyTV)) {
			$criteria->leftJoin('modTemplateVar', 'tvDefault', array(
				"tvDefault.name" => $sortbyTV
			));
			$criteria->leftJoin('modTemplateVarResource', 'tvSort', array(
				"tvSort.contentid = modResource.id",
				"tvSort.tmplvarid = tvDefault.id"
			));
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
		$collection = $modx->getCollection('modResource', $criteria, $dbCacheFlag);

		$idx = !empty($idx) && $idx !== '0' ? (integer) $idx : 1;
		$first = empty($first) && $first !== '0' ? 1 : (integer) $first;
		$last = empty($last) ? (count($collection) + $idx - 1) : (integer) $last;

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
				/** @var modTemplateVar $templateVar */
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
			$rows[] = array_merge(array('idx' => $idx, 'first' => $first, 'last' => $last, 'odd' => $odd), ($includeContent) ? $resource->toArray() : $resource->get($fields), $tvs);
			$idx++;
		}
		return $rows;
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
		if ($date == 'dayisempty') {
			$bloxdatas = array();
		} else {

			if (file_exists($file)) {
				include $file;
			} elseif (file_exists($classfile)) {
				$class = include $classfile;
				$gd = new $class($this);
				$bloxdatas = $gd->getdatas();
			} else {
				if ($this->bloxconfig['debug']) {
					echo "<pre>File " . $file . " not found</pre>";
				}
			}
		}

		return $bloxdatas;
	}

}

?>