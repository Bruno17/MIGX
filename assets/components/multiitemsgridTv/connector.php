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
 * xdbedit connector
 * 
 * @package xdbedit
 * @subpackage controllers
 */
require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config.core.php';
require_once MODX_CORE_PATH.'config/'.MODX_CONFIG_KEY.'.inc.php';
require_once MODX_CONNECTORS_PATH.'index.php';

//$modx->lexicon->load('multiitemsTV:default');

/* handle request */
$miTVCorePath = $modx->getOption('multiitemsgridTv.core_path',null,$modx->getOption('core_path').'components/multiitemsgridTv/');

$modx->request->handleRequest(array(
    'processors_path' => $miTVCorePath.'processors/',
    'location' => '',
));