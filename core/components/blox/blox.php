<?php
/*
 * rowTpl - 
 * &rowTpl = `@FIELD:`
 * &xeditTabs = `@FIELD:` - Datensatzfeld, wechselnd von Datensatz zu Datensatz 
 * &xeditTabs = `@TV:` - in TV des aufrufenden Documents - done
 * 
 * 
 * container-typen einbauen
 * 
 * 
 * Todos:  
 * dokumentengruppen in getdocs
 * 
 */
$bloxconfig = array();
$bloxconfig['path'] = 'components/blox/';
$bloxconfig['absolutepath'] = $modx->getOption('core_path') . $bloxconfig['path'];

$configs = ( isset($configs)) ? explode(',', $configs) : array();
$configs = array_merge(array('master'), $configs);

foreach ($configs as $config) {
	$configFile = $bloxconfig['absolutepath'] . 'configs/' . $config . '.config.inc.php'; // [ file ]
	if (file_exists($configFile)) {
		include($configFile);
	}
}
$includes = (isset($includes)) ? explode(',', $includes) : array();
$includes = array_merge(array('blox', 'chunkie'), $includes);

$adodbFile = $bloxconfig['absolutepath'] . 'inc/adodb-time.inc.php';
if (file_exists($adodbFile)) {
	include_once($adodbFile);
}

$bloxconfig['scriptProperties'] = $scriptProperties;
$bloxconfig['id'] = isset($id) ? $id : ''; // [ string ]
$bloxconfig['id_'] = isset($id) ? $id . '_' : ''; // [ string ]
$bloxconfig['distinct'] = isset($distinct) && $distinct == '0' ? '' : 'distinct'; // 1 or 0 [ string ]
$bloxconfig['projectname'] = (isset($project)) ? $project : 'blox';
$bloxconfig['packagename'] = (isset($packagename)) ? $packagename : '';
$bloxconfig['classname'] = (isset($classname)) ? $classname : '';
$bloxconfig['resourceclass'] = ($bloxconfig['classname'] !== '') ? 'modTable' : 'modDocument';
$bloxconfig['resourceclass'] = (isset($resourceclass)) ? $resourceclass : $bloxconfig['resourceclass'];
$bloxconfig['htmlouter'] = isset($htmlouter) ? $htmlouter : 'div';
$bloxconfig['projectpath'] = $bloxconfig['path'] . "projects/blox/" . $bloxconfig['resourceclass'] . '/';
$bloxconfig['projectpath'] = (isset($project)) ? $bloxconfig['path'] . "projects/custom/" . $project . '/' : $bloxconfig['projectpath'];
$bloxconfig['processTVs'] = (isset($processTVs)) ? $processTVs : '0';

//use htmlouter div,table,ul as task if nothing else defined
//see projects/blox/...
$bloxconfig['task'] = (isset($task)) ? $task : $bloxconfig['htmlouter'];

$bloxconfig['tpls'] = isset($tpls) ? $tpls : '';
$bloxconfig['tplpath'] = (isset($tpl_path)) ? $bloxconfig['projectpath'] . "templates/" . $tpl_path : '';
$bloxconfig['tplpath'] = ($bloxconfig['tplpath'] == '') ? $bloxconfig['projectpath'] . $bloxconfig['task'] . "/templates/" : $bloxconfig['tplpath'];
$bloxconfig['outputSeparator'] = isset($outputSeparator) ? $outputSeparator : '';

$bloxconfig['includespath'] = (isset($includes_path)) ? $bloxconfig['projectpath'] . $includes_path : '';
$bloxconfig['includespath'] = ($bloxconfig['includespath'] == '') ? $bloxconfig['projectpath'] . $bloxconfig['task'] . "/includes/" : $bloxconfig['includespath'];
$bloxconfig['cachepath'] = $bloxconfig['path'] . 'cache/';
$bloxconfig['includesfile'] = $bloxconfig['includespath'] . "getdatas.php"; // [ file ]
$bloxconfig['onsavefile'] = $bloxconfig['includespath'] . "onsavedatas.php"; // [ file ]

$timestamp = time();
$timestampday = xetadodb_strftime("%d", $timestamp);
$timestampmonth = xetadodb_strftime("%m", $timestamp);
$timestampyear = xetadodb_strftime("%Y", $timestamp);
$bloxconfig['nowtimestamp'] = $timestamp;
$bloxconfig['day'] = (isset($day)) ? $day : $timestampday;
$bloxconfig['day'] = (isset($_REQUEST['day']) && (trim($_REQUEST['day'] !== ''))) ? (string) intval($_REQUEST['day']) : $bloxconfig['day'];
$bloxconfig['month'] = (isset($month)) ? $month : $timestampmonth;
$bloxconfig['month'] = (isset($_REQUEST['month']) && (trim($_REQUEST['month'] !== ''))) ? (string) intval($_REQUEST['month']) : $bloxconfig['month'];
$bloxconfig['year'] = (isset($year)) ? $year : $timestampyear;
$bloxconfig['year'] = (isset($_REQUEST['year']) && (trim($_REQUEST['year'] !== ''))) ? (string) intval($_REQUEST['year']) : $bloxconfig['year'];

$bloxconfig['processpost'] = (isset($processpost)) ? $processpost : '1';
$bloxconfig['custom'] = (isset($custom)) ? $custom : array();
$bloxconfig['permissions'] = (isset($permissions)) ? $permissions : array();
$bloxconfig['userID'] = $modx->getLoginUserID();

$bloxconfig['pageVarKey'] = isset($pageVarKey) ? $pageVarKey : 'page';
$bloxconfig['perPage'] = (isset($perPage)) ? $perPage : 10;
$bloxconfig['numLinks'] = (isset($numLinks)) ? $numLinks : 5;
$bloxconfig['page'] = ( isset($_GET[$bloxconfig['pageVarKey']]) && is_numeric($_GET[$bloxconfig['pageVarKey']])) ? $_GET[$bloxconfig['pageVarKey']] : '1';
$bloxconfig['limit'] = (isset($limit)) ? $limit : $bloxconfig['perPage'];
$bloxconfig['offset'] = isset($offset) ? $offset : '0';
$bloxconfig['offset'] = $bloxconfig['page'] > 1 ? ($bloxconfig['page']-1) * $bloxconfig['limit'] : $bloxconfig['offset'];
$bloxconfig['where'] = (isset($where)) ? $where : '*';
$bloxconfig['queries'] = (isset($queries)) ? $queries : '*';

$bloxconfig['selectfields'] = (isset($selectfields)) ? $selectfields : '';
$bloxconfig['sortConfig'] = (isset($sortConfig)) ? $sortConfig : '';
$bloxconfig['joins'] = (isset($joins)) ? $joins : '';



//Parameter for xedit:
$bloxconfig['keyField'] = (isset($keyField)) ? $keyField : 'id';
$bloxconfig['parents'] = (isset($parents)) ? $parents : '';
$bloxconfig['depth'] = (isset($depth)) ? $depth : '1';
$bloxconfig['bloxfolder'] = (isset($bloxfolder)) ? $bloxfolder : ''; //together with the first id in &parents here comes the pagetitle of subfolder for bloxcontainer
$bloxconfig['documents'] = (isset($documents)) ? trim($documents) == '' ? '999999999' : $documents  : '';
$bloxconfig['IDs'] = (isset($IDs)) ? $IDs : $bloxconfig['documents'];
$bloxconfig['filter'] = (isset($filter)) ? $filter : '';

$bloxconfig['showdeleted'] = (isset($showdeleted)) ? $showdeleted : '0'; //0 = no, 1 = yes, 2 = only deleted
$bloxconfig['showunpublished'] = (isset($showunpublished)) ? $showunpublished : '0';

$bloxconfig['debug'] = (isset($debug)) ? intval($debug) : 0;

if ($bloxconfig['debug']) {
	echo '<pre>' . print_r($bloxconfig, true) . '</pre>';
}

/* --------------------------------
 *  SNIPPET LOGIC CODE STARTS HERE
 * -------------------------------- */
//Todo: make this better:
foreach ($includes as $includeclass) {

	if (!class_exists($includeclass)) {
		$includefile = $bloxconfig['absolutepath'] . 'inc/' . $includeclass . '.class.inc.php';
		if (file_exists($includefile)) {
			include_once($includefile);
		} else {
			$output = 'Cannot find ' . $includeclass . ' class file! (' . $includefile . ')';
			return;
		}
	}

	switch ($includeclass) {
		case 'blox':
			if (class_exists($includeclass)) {
				// Initialize class
				$blox = new blox($bloxconfig);
			} else {
				$output = $includeclass . ' class not found';
				return;
			}
			break;
		case 'xettcal':
			if (class_exists($includeclass)) {
				// Initialize class
				$blox->xettcal = new xettcal($bloxconfig['id']);
				$blox->xettcal->blox = &$blox;
			} else {
				$output = $includeclass . ' class not found';
				return;
			}
			break;
	}
}

//Output

$output = $blox->displayblox();

//store the blox-object for use in other scripts e.g. ajax-scripts
//$_SESSION['bloxobject'][$modx->resource->get('id')][$bloxconfig['id']] = $blox;
return $output;
?>