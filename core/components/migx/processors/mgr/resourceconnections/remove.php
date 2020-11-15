<?php
/**
 * XdbEdit
 *
 * Copyright 2010 by Bruno Perner <b.perner@gmx.de>
 *
 * This file is part of XdbEdit, for editing custom-tables in MODx Revolution CMP.
 *
 * XdbEdit is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * XdbEdit is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * XdbEdit; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA 
 *
 * @package xdbedit
 */
/**
 * Update and Create-processor for xdbedit
 *
 * @package xdbedit
 * @subpackage processors
 */
//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));

//return $modx->error->failure('huhu');


if (empty($scriptProperties['object_id'])){

	return $modx->error->failure($modx->lexicon('quip.thread_err_ns'));

} 

$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];

$packagepath = $modx->getOption('core_path') . 'components/' . $packageName .
    '/';
$modelpath = $packagepath . 'model/';

$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

if ($modx->lexicon)
{
    $modx->lexicon->load($packageName.':default');
}

switch ($scriptProperties['task']) {
	case 'removeone':
	    $object = $modx->getObject($classname, $scriptProperties['object_id']);
        if ($object->remove() === false) {
            return $modx->error->failure($modx->lexicon('quip.comment_err_remove'));
        }
	    break;

    default:
    break;    
}
    
//clear cache for all contexts
$collection = $modx->getCollection('modContext');
foreach ($collection as $context) {
    $contexts = $context->get('key');
}
$modx->cacheManager->refresh(array(
    'db' => array(),
    'auto_publish' => array('contexts' => $contexts),
    'context_settings' => array('contexts' => $contexts),
    'resource' => array('contexts' => $contexts),
    ));

return $modx->error->success();

?>