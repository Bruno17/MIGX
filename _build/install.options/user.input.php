<?php

/**
 * Script to interact with user during MyComponent package install
 *
 * Copyright 2011 Your Name <you@yourdomain.com>
 * @author Your Name <you@yourdomain.com>
 * 1/1/11
 *
 *  is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option) any
 * later version.
 *
 *  is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * ; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package mycomponent
 */
/**
 * Description: Script to interact with user during MyComponent package install
 * @package mycomponent
 * @subpackage build
 */
/* Use these if you would like to do different things depending on what's happening */

/* The return value from this script should be an HTML form (minus the
* <form> tags and submit button) in a single string.
*
* The form will be shown to the user during install
* after the readme.txt display.
*
* This example presents an HTML form to the user with one input field
* (you can have more).
*
* The user's entries in the form's input field(s) will be available
* in any php resolvers with $modx->getOption('field_name', $options, 'default_value').
*
* You can use the value(s) to set system settings, snippet properties,
* chunk content, etc. One common use is to use a checkbox and ask the
* user if they would like to install an example resource for your
* component.
*/

$output = '';

$hasMenu = '1';

if ($hasMenu == '1') {
    $output = 
    '<p>&nbsp;</p>
    <label for="menu_placement">Where to place the menu for this Extra</label>
    <p>&nbsp;</p>
    <input type="radio" name="menu_placement" value="topnav" /> Top Nav <br>
    <input type="radio" name="menu_placement" value="components" /> Extras/Components <br>
    <p>&nbsp;</p>';
}


return $output;
