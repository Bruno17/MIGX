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
 * @subpackage snippet
 */

$bloxconfig = $array;
$bloxconfig['path'] = 'components/blox/';
$bloxconfig['absolutepath'] = $modx->getOption('core_path') . $bloxconfig['path'];
$bloxconfig['component'] = $modx->getOption('component', $scriptProperties, '');

// Include config files
$configs = explode(',', $modx->getOption('configs', $scriptProperties, ''));

foreach ($configs as $configName) {
    if ($bloxconfig['component'] != '') {
        $configFile = $modx->getOption('core_path') . 'components/' . $bloxconfig['component'] .'/bloxconfigs/' . $configName . '.config.inc.php'; // [ file ]
    } else {
        $configFile = $bloxconfig['absolutepath'] . 'configs/' . $configName . '.config.inc.php'; // [ file ]
    }
    if (file_exists($configFile)) {
        include ($configFile);
        if (isset($config) && is_array($config)) {
            $scriptProperties = array_merge($scriptProperties, $config);
        }
        unset($config);
    }
}
$bloxconfig = array_merge($bloxconfig, $scriptProperties);

$includes = $modx->getOption('includes', $scriptProperties, '');
$includes = ($includes != '') ? explode(',', $includes) : array();
$includes = array_merge(array('blox', 'chunkie'), $includes);

$adodbFile = $bloxconfig['absolutepath'] . 'inc/adodb-time.inc.php';
if (file_exists($adodbFile)) {
    include_once ($adodbFile);
}

// Set defaults
$bloxconfig['id'] = $modx->getOption('id', $scriptProperties, ''); // [ string ]
$bloxconfig['id_'] = ($bloxconfig['id'] != '') ? $bloxconfig['id'] . '_' : ''; // [ string ]
$bloxconfig['distinct'] = (intval($modx->getOption('distinct', $scriptProperties, '1'))) ? 'distinct' : ''; // 1 or 0 [ string ]
$bloxconfig['projectname'] = $modx->getOption('project', $scriptProperties, 'blox');
$bloxconfig['packagename'] = $modx->getOption('packagename', $scriptProperties, '');
$bloxconfig['classname'] = $modx->getOption('classname', $scriptProperties, 'modResource');
$bloxconfig['resourceclass'] = $modx->getOption('resourceclass', $scriptProperties, ($bloxconfig['classname'] !== 'modResource') ? 'modTable' : 'modResource');
$bloxconfig['htmlouter'] = $modx->getOption('htmlouter', $scriptProperties, 'div');
$bloxconfig['project'] = $modx->getOption('project', $scriptProperties, '');
$bloxconfig['projectparent'] = ($bloxconfig['project'] != '') ? 'custom' : 'blox';
$bloxconfig['componentpath'] = ($bloxconfig['component'] != '' && $bloxconfig['project'] != '') ? 'components/' . $bloxconfig['component'] . '/bloxprojects/' : $bloxconfig['path'] . 'projects/custom/';
$bloxconfig['projectpath'] = ($bloxconfig['project'] != '') ? $bloxconfig['componentpath'] . $bloxconfig['project'] . '/' : $bloxconfig['path'] . 'projects/blox/' . $bloxconfig['resourceclass'] .
    '/';
$bloxconfig['task'] = $modx->getOption('task', $scriptProperties, $bloxconfig['htmlouter']);
$bloxconfig['outputSeparator'] = $modx->getOption('outputSeparator', $scriptProperties, '');

$bloxconfig['tpls'] = $modx->getOption('tpls', $scriptProperties, '');
$bloxconfig['tplpath'] = (($tplpath = $modx->getOption('tplpath', $scriptProperties, '')) != '') ? $bloxconfig['projectpath'] . $tplpath : $bloxconfig['projectpath'] . $bloxconfig['task'] .
    '/templates/';
$bloxconfig['includespath'] = (($includespath = $modx->getOption('includespath', $scriptProperties, '')) != '') ? $bloxconfig['projectpath'] . $includespath : $bloxconfig['projectpath'] . $bloxconfig['task'] .
    '/includes/';
$bloxconfig['cachepath'] = $bloxconfig['path'] . 'cache/';
$bloxconfig['includesfile'] = $bloxconfig['includespath'] . 'getdatas.php'; // [ file ]
$bloxconfig['includesclassfile'] = $bloxconfig['includespath'] . 'getdatas.class.php'; // [ file ]
$pn = $bloxconfig['project'] != '' ? ucfirst($bloxconfig['project']) : $bloxconfig['resourceclass'];
$bloxconfig['includesclass'] = ucfirst($bloxconfig['projectparent']) . '_' . $pn . '_' . ucfirst($bloxconfig['task']); // [ class ]
$bloxconfig['onsavefile'] = $bloxconfig['includespath'] . 'onsavedatas.php'; // [ file ]

$timestamp = time();
$timestampday = xetadodb_strftime('%d', $timestamp);
$timestampmonth = xetadodb_strftime('%m', $timestamp);
$timestampyear = xetadodb_strftime('%Y', $timestamp);
$bloxconfig['nowtimestamp'] = $timestamp;
$bloxconfig['day'] = $modx->getOption('day', $scriptProperties, $timestampday);
$bloxconfig['day'] = (isset($_REQUEST['day']) && (trim($_REQUEST['day'] !== ''))) ? (string )intval($_REQUEST['day']) : $bloxconfig['day'];
$bloxconfig['month'] = $modx->getOption('month', $scriptProperties, $timestampmonth);
$bloxconfig['month'] = (isset($_REQUEST['month']) && (trim($_REQUEST['month'] !== ''))) ? (string )intval($_REQUEST['month']) : $bloxconfig['month'];
$bloxconfig['year'] = $modx->getOption('year', $scriptProperties, $timestampyear);
$bloxconfig['year'] = (isset($_REQUEST['year']) && (trim($_REQUEST['year'] !== ''))) ? (string )intval($_REQUEST['year']) : $bloxconfig['year'];

$bloxconfig['userID'] = $modx->getLoginUserID();

$bloxconfig['totalVar'] = $modx->getOption('totalVar', $scriptProperties, 'total');
$bloxconfig['pageVarKey'] = $modx->getOption('pageVarKey', $scriptProperties, 'page');
$bloxconfig['perPage'] = intval($modx->getOption('perPage', $scriptProperties, '10'));
$bloxconfig['numLinks'] = intval($modx->getOption('numLinks', $scriptProperties, '5'));
$bloxconfig['page'] = (isset($_GET[$bloxconfig['pageVarKey']]) && is_numeric($_GET[$bloxconfig['pageVarKey']])) ? $_GET[$bloxconfig['pageVarKey']] : '1';
$bloxconfig['limit'] = $modx->getOption('limit', $scriptProperties, $bloxconfig['perPage']);
$bloxconfig['offset'] = $modx->getOption('offset', $scriptProperties, '0');
$bloxconfig['offset'] = $bloxconfig['page'] > 1 ? ($bloxconfig['page'] - 1) * $bloxconfig['limit'] : $bloxconfig['offset'];
$bloxconfig['where'] = $modx->getOption('where', $scriptProperties, '');
$bloxconfig['queries'] = $modx->getOption('queries', $scriptProperties, '*');

$bloxconfig['selectfields'] = $modx->getOption('selectfields', $scriptProperties, '');
$bloxconfig['sortConfig'] = $modx->getOption('sortConfig', $scriptProperties, '');
$bloxconfig['joins'] = $modx->getOption('joins', $scriptProperties, '');

//Parameter for xedit:
$bloxconfig['keyField'] = $modx->getOption('keyField', $scriptProperties, 'id');
$bloxconfig['parents'] = $modx->getOption('parents', $scriptProperties, '0');
$bloxconfig['depth'] = $modx->getOption('depth', $scriptProperties, '10');
$bloxconfig['bloxfolder'] = $modx->getOption('bloxfolder', $scriptProperties, ''); // together with the first id in &parents here comes the pagetitle of subfolder for bloxcontainer
$bloxconfig['documents'] = $modx->getOption('documents', $scriptProperties, '999999999');
$bloxconfig['IDs'] = $modx->getOption('IDs', $scriptProperties, $bloxconfig['documents']);
$bloxconfig['filter'] = $modx->getOption('filter', $scriptProperties, '');

$bloxconfig['showdeleted'] = $modx->getOption('showdeleted', $scriptProperties, '0'); // 0 = no, 1 = yes, 2 = only deleted
$bloxconfig['showunpublished'] = $modx->getOption('showunpublished', $scriptProperties, '0');

$bloxconfig['debug'] = intval($modx->getOption('debug', $scriptProperties, '0'));
$bloxconfig['debugTime'] = intval($modx->getOption('debugTime', $scriptProperties, '0'));

if ($bloxconfig['debug']) {
    echo '<pre>' . print_r($bloxconfig, true) . '</pre>';
}

if ($bloxconfig['resourceclass'] == 'modResource') {
    if (!in_array('bloxhelpers', $includes)) {
        $includes[] = 'bloxhelpers';
    }
}

// Include classes
foreach ($includes as $includeclass) {

    if (!class_exists($includeclass)) {
        $includefile = $bloxconfig['absolutepath'] . 'inc/' . $includeclass . '.class.inc.php';
        if (file_exists($includefile)) {
            include_once ($includefile);
        } else {
            $output = 'Cannot find ' . $includeclass . ' class file! (' . $includefile . ')';
            return $output;
        }
    }

    switch ($includeclass) {
        case 'blox':
            if (class_exists($includeclass)) {
                // Initialize class
                $blox = new blox($bloxconfig);
            } else {
                $output = $includeclass . ' class not found';
                return $output;
            }
            break;
        case 'xettcal':
            if (class_exists($includeclass)) {
                // Initialize class
                $blox->xettcal = new xettcal($bloxconfig['id']);
                $blox->xettcal->blox = &$blox;
            } else {
                $output = $includeclass . ' class not found';
                return $output;
            }
            break;
    }
}

// Output
$starttotal = microtime(true);
$output = $blox->displayblox();
$endtotal = microtime(true);
if ($bloxconfig['debug'] || $bloxconfig['debugTime']) {

    echo '<pre>Total time: ' . ($endtotal - $starttotal) . ' seconds</pre>';
}

//store the blox-object for use in other scripts e.g. ajax-scripts
//$_SESSION['bloxobject'][$modx->resource->get('id')][$bloxconfig['id']] = $blox;

return $output;

?>