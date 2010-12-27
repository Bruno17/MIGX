<?php
/**
 * getImageList
 *
 * Copyright 2009-2010 by Bruno Perner <b.perner@gmx.de>
 *
 * getImageList is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 * getImageList is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * FormIt; if not, write to the Free Software Foundation, Inc., 59 Temple Place,
 * Suite 330, Boston, MA 02111-1307 USA
 *
 * @package imageListTv
 */
 /**
 * getImageList
 *
 * get Images from TV with custom-input-type imageList for MODx Revolution 2.0.
 *
 * @version 1.0
 * @author Bruno Perner <b.perner@gmx.de>
 * @copyright Copyright &copy; 2009-2010
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License
 * version 2 or (at your option) any later version.
 * @package imageListTv
 */

/*example: <ul>[[!getImageList? &tvname=`myTV`&tpl=`@CODE:<li><img src="[[+imageURL]]"/><p>[[+imageAlt]]</p></li>`]]</ul>*/
/* get default properties */


$tvname = $modx->getOption('tvname', $scriptProperties, '');
$tpl = $modx->getOption('tpl', $scriptProperties, '');
$docid = $modx->getOption('docid', $scriptProperties, $modx->resource->get('id'));

if ($tvname == '' || $tpl == 'xx')
{
    return;
}

$tv = $modx->getObject('modTemplateVar', array ('name'=>$tvname));
$outputvalue = $tv->renderOutput($docid);
$items = $modx->fromJSON($outputvalue);
$output = '';
if (substr($tpl, 0, 6) == "@FILE:")
{
    $template = $this->get_file_contents($modx->config['base_path'].substr($tpl, 6));
} else if (substr($tpl, 0, 6) == "@CODE:")
{
    $template = substr($tpl, 6);
} else if ($chunk = $modx->getObject('modChunk', array ('name'=>$tpl), true))
{
    $template = $chunk->getContent();
} else
{
    $template = FALSE;
}

if ($template)
{
    if (count($items) > 0)
    {
        foreach ($items as $item)
        {
            $chunk = $modx->newObject('modChunk');
            $chunk->setCacheable(false);
            $output .= $chunk->process($item, $template);
        }
    }
}


return $output;
?>
