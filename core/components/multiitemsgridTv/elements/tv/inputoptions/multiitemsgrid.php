<?php
/**
 * MultiItemsGridTv
 *
 * Copyright 2010-2011 by Bruno Perner <b.perner@gmx.de>
 *
 * MultiItemsGridTv is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * MultiItemsGridTv is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * MultiItemsGridTv; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package multiitemsgridTv
 */
/**
 * Input TV render for MultiItemsGridTv
 *
 * @package multiitemsgridTv
 * @subpackage tv
 */
$modx->lexicon->load('tv_widget','multiitemsgridTv:tvprops');
$modx->smarty->assign('base_url',$modx->getOption('base_url'));

$path='components/multiitemsgridtv/';

$corePath = $modx->getOption('multiitemsgridTv.core_path', null, $modx->getOption('core_path') . $path);
//$modx->addPackage('gallery',$corePath.'model/');

/* get TV input properties specific language strings */
$lang = $modx->lexicon->fetch('mig.',true);
$modx->smarty->assign('mig',$lang);

return $modx->smarty->fetch($corePath.'elements/tv/multiitemsgrid.inputproperties.tpl');