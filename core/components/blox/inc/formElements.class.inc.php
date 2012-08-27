<?php



class bloxFormElements{
	
	function bloxFormElements(){
		
	}

	function renderFormElement($field_type, $field_name, $default_text, $field_elements, $field_value, $field_style='', $attributes='') {
		global $base_url;
		global $rb_base_url;
		global $manager_theme;

		$field_html ='';
		$field_value = ($field_value!="" ? $field_value : $default_text);

		switch ($field_type) {

			case "text": // handler for regular text boxes
			case "rawtext"; // non-htmlentity converted text boxes
			case "email": // handles email input fields
			case "number": // handles the input of numbers
				$field_html .=  '<input '.$attributes.' type="text" id="tv'.$field_name.'" name="tv'.$field_name.'" value="'.htmlspecialchars($field_value).'" '.$field_style.' tvtype="'.$field_type.'" onchange="documentDirty=true;"  />';
				break;
			case "textareamini": // handler for textarea mini boxes
				$field_html .=  '<textarea '.$attributes.' id="tv'.$field_name.'" name="tv'.$field_name.'" cols="40" rows="5" onchange="documentDirty=true;" >' . htmlspecialchars($field_value) .'</textarea>';
				break;
			case "textarea": // handler for textarea boxes
			case "rawtextarea": // non-htmlentity convertex textarea boxes
			case "htmlarea": // handler for textarea boxes (deprecated)
			case "richtext": // handler for textarea boxes
				$field_html .=  '<textarea '.$attributes.' id="tv'.$field_name.'" name="tv'.$field_name.'" cols="40" rows="15" onchange="documentDirty=true;" >' . htmlspecialchars($field_value) .'</textarea>';
				break;
			case "date":
                if($field_value=='') $field_value=0;
                $cal = 'cal' . str_replace('-','_',$field_name);

				$field_html .=  '<input '.$attributes.' id="tv'.$field_name.'" name="tv'.$field_name.'" type="hidden" value="' . ($field_value==0 || !isset($field_value) ? "" : $field_value) . '" onblur="documentDirty=true;">';

				$field_html .=  '	<table width="250" border="0" cellspacing="0" cellpadding="0">';
				$field_html .=  '	  <tr>';
				$field_html .=  '		<td width="160" style="border: 1px solid #808080;"><span id="tv'.$field_name.'_show" class="inputBox"> ' . ($field_value==0 || !isset($field_value) ? '(not set)' : $field_value) . '</span> </td>';

				$field_html .=  '		<td>&nbsp;';
				$field_html .=  '			<a onClick="documentDirty=false; '.$cal.'.popup();" onMouseover="window.status=\'Select a date\'; return true;" onMouseout="window.status=\'\'; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/'.($manager_theme ? "$manager_theme/":"").'images/icons/cal.gif" width="16" height="16" border="0"></a>';
				$field_html .=  '			<a onClick="document.forms[\'mutate\'].elements[\'tv'.$field_name.'\'].value=\'\';document.forms[\'mutate\'].elements[\'tv'.$field_name.'\'].onblur();document.getElementById(\'tv'.$field_name.'_show\').innerHTML=\'(not set)\'; return true;" onMouseover="window.status=\'clear the date\'; return true;" onMouseout="window.status=\'\'; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/'.($manager_theme ? "$manager_theme/":"").'images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date"></a>';

				$field_html .=  '		</td>';
				$field_html .=  '	  </tr>';
				$field_html .=  '    </table>';

				$field_html .=  '<script type="text/javascript">';
				$field_html .=  '	var '.$cal.' = new calendar1(document.forms[\'mutate\'].elements[\'tv'.$field_name.'\'], document.getElementById("tv'.$field_name.'_show"));';
				$field_html .=  '   '.$cal.'.path="' . MODX_MANAGER_URL . 'media/";';

				$field_html .=  '	'.$cal.'.year_scroll = true;';
				$field_html .=  '   '.$cal.'.time_comp = true;';

				$field_html .=  '</script>';

				break;
			case "dropdown": // handler for select boxes
				$field_html .=  '<select '.$attributes.' id="tv'.$field_name.'" name="tv'.$field_name.'" size="1" onchange="documentDirty=true;">';
				$index_list = $this->ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<option value="'.htmlspecialchars($itemvalue).'"'.($itemvalue==$field_value ?' selected="selected"':'').'>'.htmlspecialchars($item).'</option>';
				}
				$field_html .=  "</select>";
				break;
			case "listbox": // handler for select boxes
				$field_html .=  '<select '.$attributes.' id="tv'.$field_name.'" name="tv'.$field_name.'" onchange="documentDirty=true;" size="8">';	
				$index_list = $this->ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<option value="'.htmlspecialchars($itemvalue).'"'.($itemvalue==$field_value ?' selected="selected"':'').'>'.htmlspecialchars($item).'</option>';
				}
				$field_html .=  "</select>";
				break;
			case "listbox-multiple": // handler for select boxes where you can choose multiple items
				$field_value = explode("||",$field_value);
				$field_html .=  '<select  '.$attributes.' id="tv'.$field_name.'[]" name="tv'.$field_name.'[]" multiple="multiple" onchange="documentDirty=true;" size="8">';
				$index_list = $this->ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<option value="'.htmlspecialchars($itemvalue).'"'.(in_array($itemvalue,$field_value) ?' selected="selected"':'').'>'.htmlspecialchars($item).'</option>';
				}
				$field_html .=  "</select>";
				break;
			case "url": // handles url input fields
				$urls= array(''=>'--', 'http://'=>'http://', 'https://'=>'https://', 'ftp://'=>'ftp://', 'mailto:'=>'mailto:');
				$field_html ='<table border="0" cellspacing="0" cellpadding="0"><tr><td><select '.$attributes.' id="tv'.$field_name.'_prefix" name="tv'.$field_name.'_prefix" onchange="documentDirty=true;">';
				foreach($urls as $k => $v){
					if(strpos($field_value,$v)===false) $field_html.='<option value="'.$v.'">'.$k.'</option>';
					else{
						$field_value = str_replace($v,'',$field_value);
						$field_html.='<option value="'.$v.'" selected="selected">'.$k.'</option>';
					}
				}
				$field_html .='</select></td><td>';
				$field_html .=  '<input '.$attributes.' type="text" id="tv'.$field_name.'" name="tv'.$field_name.'" value="'.htmlspecialchars($field_value).'" width="100" '.$field_style.' onchange="documentDirty=true;" /></td></tr></table>';
				break;
			case "checkbox": // handles check boxes
				$field_value = explode("||",$field_value);
				$index_list = $this->ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				static $i=0;
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<input '.$attributes.' type="checkbox" value="'.htmlspecialchars($itemvalue).'" id="tv_'.$i.'" name="tv'.$field_name.'[]" '. (in_array($itemvalue,$field_value)?" checked='checked'":"").' onchange="documentDirty=true;" /><span class="label">'.$item.'</span>';
					$i++;
				}
				break;
			case "option": // handles radio buttons
				$index_list = $this->ParseIntputOptions(ProcessTVCommand($field_elements, $field_name));
				while (list($item, $itemvalue) = each ($index_list))
				{
					list($item,$itemvalue) =  (is_array($itemvalue)) ? $itemvalue : explode("==",$itemvalue);
					if (strlen($itemvalue)==0) $itemvalue = $item;
					$field_html .=  '<input '.$attributes.' type="radio" value="'.htmlspecialchars($itemvalue).'" name="tv'.$field_name.'" '.($itemvalue==$field_value ?'checked="checked"':'').' onchange="documentDirty=true;" /><span class="label">'.$item.'</span>';
				}
				break;
			case "image":	// handles image fields using htmlarea image manager
				global $_lang;
				global $ResourceManagerLoaded;
				global $content,$use_editor,$which_editor;
				if (!$ResourceManagerLoaded && !(($content['richtext']==1 || $_GET['a']==4) && $use_editor==1 && $which_editor==3)){ 
					$field_html .="
					<script type=\"text/javascript\">
							var lastImageCtrl;
							var lastFileCtrl;
							function OpenServerBrowser(url, width, height ) {
								var iLeft = (screen.width  - width) / 2 ;
								var iTop  = (screen.height - height) / 2 ;

								var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
								sOptions += ',width=' + width ;
								sOptions += ',height=' + height ;
								sOptions += ',left=' + iLeft ;
								sOptions += ',top=' + iTop ;

								var oWindow = window.open( url, 'FCKBrowseWindow', sOptions ) ;
							}			
							function BrowseServer(ctrl) {
								lastImageCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=images&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
							
							function BrowseFileServer(ctrl) {
								lastFileCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=files&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
							
							function SetUrl(url, width, height, alt){
								if(lastFileCtrl) {
									var c = document.mutate[lastFileCtrl];
									if(c) c.value = url;
									lastFileCtrl = '';
								} else if(lastImageCtrl) {
									var c = document.mutate[lastImageCtrl];
									if(c) c.value = url;
									lastImageCtrl = '';
								} else {
									return;
								}
							}
					</script>";
					$ResourceManagerLoaded  = true;					
				} 
				$field_html .='<input '.$attributes.' type="text" id="tv'.$field_name.'" name="tv'.$field_name.'"  value="'.$field_value .'" '.$field_style.' onchange="documentDirty=true;" />&nbsp;<input type="button" value="'.$_lang['insert'].'" onclick="BrowseServer(\'tv'.$field_name.'\')" />';
				break;
			case "file": // handles the input of file uploads
			/* Modified by Timon for use with resource browser */
                global $_lang;
				global $ResourceManagerLoaded;
				global $content,$use_editor,$which_editor;
				if (!$ResourceManagerLoaded && !(($content['richtext']==1 || $_GET['a']==4) && $use_editor==1 && $which_editor==3)){
				/* I didn't understand the meaning of the condition above, so I left it untouched ;-) */ 
					$field_html .="
					<script type=\"text/javascript\">
							var lastImageCtrl;
							var lastFileCtrl;
							function OpenServerBrowser(url, width, height ) {
								var iLeft = (screen.width  - width) / 2 ;
								var iTop  = (screen.height - height) / 2 ;

								var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes' ;
								sOptions += ',width=' + width ;
								sOptions += ',height=' + height ;
								sOptions += ',left=' + iLeft ;
								sOptions += ',top=' + iTop ;

								var oWindow = window.open( url, 'FCKBrowseWindow', sOptions ) ;
							}
							
								function BrowseServer(ctrl) {
								lastImageCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=images&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
										
							function BrowseFileServer(ctrl) {
								lastFileCtrl = ctrl;
								var w = screen.width * 0.7;
								var h = screen.height * 0.7;
								OpenServerBrowser('".$base_url."manager/media/browser/mcpuk/browser.html?Type=files&Connector=".$base_url."manager/media/browser/mcpuk/connectors/php/connector.php&ServerPath=".$base_url."', w, h);
							}
							
							function SetUrl(url, width, height, alt){
								if(lastFileCtrl) {
									var c = document.mutate[lastFileCtrl];
									if(c) c.value = url;
									lastFileCtrl = '';
								} else if(lastImageCtrl) {
									var c = document.mutate[lastImageCtrl];
									if(c) c.value = url;
									lastImageCtrl = '';
								} else {
									return;
								}
							}
					</script>";
					$ResourceManagerLoaded  = true;					
				} 
				$field_html .='<input '.$attributes.' type="text" id="tv'.$field_name.'" name="tv'.$field_name.'"  value="'.$field_value .'" '.$field_style.' onchange="documentDirty=true;" />&nbsp;<input type="button" value="'.$_lang['insert'].'" onclick="BrowseFileServer(\'tv'.$field_name.'\')" />';
                
				break;
			default: // the default handler -- for errors, mostly
				$field_html .=  '<input '.$attributes.' type="text" id="tv'.$field_name.'" name="tv'.$field_name.'" value="'.htmlspecialchars($field_value).'" '.$field_style.' onchange="documentDirty=true;" />';

		} // end switch statement
		return $field_html;
	} // end renderFormElement function	



	function ParseIntputOptions($v) {
		$a = array();
		if(is_array($v)) return $v;
		else if(is_resource($v)) {
			while ($cols = mysql_fetch_row($v)) $a[] = $cols;
		}
		else $a = explode("||", $v);
		return $a;
	}

function ProcessTVCommand($value, $name = '', $docid = '') {
    global $modx;
    $etomite = & $modx;
    $docid = intval($docid) ? intval($docid) : $modx->documentIdentifier;
    $nvalue = trim($value);
    if (substr($nvalue, 0, 1) != '@')
        return $value;
    else {
        list ($cmd, $param) = $this->ParseCommand($nvalue);
        $cmd = trim($cmd);
        switch ($cmd) {
            case "FILE" :
                $output = $this->ProcessFile($param);
                break;

            case "CHUNK" : // retrieve a chunk and process it's content
                $chunk = $modx->getChunk($param);
                $output = $chunk;
                break;

            case "DOCUMENT" : // retrieve a document and process it's content
                $rs = $modx->getDocument($param);
                if (is_array($rs))
                    $output = $rs['content'];
                else
                    $output = "Unable to locate document $param";
                break;

            case "SELECT" : // selects a record from the cms database
                $rt = array ();
                $replacementVars = array (
                    'DBASE' => $modx->db->config['dbase'],
                    'PREFIX' => $modx->db->config['table_prefix']
                );
                foreach ($replacementVars as $rvKey => $rvValue) {
                    $modx->setPlaceholder($rvKey, $rvValue);
                }
                $param = $modx->mergePlaceholderContent($param);
                $rs = $modx->db->query("SELECT $param;");
                $output = $rs;
                break;

            case "EVAL" : // evaluates text as php codes return the results
                $output = eval ($param);
                break;

            case "INHERIT" :
                $output = $param; // Default to param value if no content from parents
                $doc = $modx->getPageInfo($docid, 0, 'id,parent');

                while ($doc['parent'] != 0) {
                    $parent_id = $doc['parent'];

                    // Grab document regardless of publish status
                    $doc = $modx->getPageInfo($parent_id, 0, 'id,parent,published');
                    if ($doc['parent'] != 0 && !$doc['published'])
                        continue; // hide unpublished docs if we're not at the top

                    $tv = $modx->getTemplateVar($name, '*', $doc['id'], $doc['published']);
                    if ((string) $tv['value'] !== '' && $tv['value'] { 0 }
                        != '@') {
                        $output = (string) $tv['value'];
                        break 2;
                    }
                }
                break;

            case 'DIRECTORY' :
                $files = array ();
                $path = $modx->config['base_path'] . $param;
                if (substr($path, -1, 1) != '/') {
                    $path .= '/';
                }
                if (!is_dir($path)) {
                    die($path);
                    break;
                }
                $dir = dir($path);
                while (($file = $dir->read()) !== false) {
                    if (substr($file, 0, 1) != '.') {
                        $files[] = "{$file}=={$param}{$file}";
                    }
                }
                asort($files);
                $output = implode('||', $files);
                break;

            default :
                $output = $value;
                break;

        }
        // support for nested bindings
        return is_string($output) && ($output != $value) ? $this->ProcessTVCommand($output, $name, $docid) : $output;
    }
}

function ProcessFile($file) {
    // get the file
    if (file_exists($file) && @ $handle = fopen($file, "r")) {
        $buffer = "";
        while (!feof($handle)) {
            $buffer .= fgets($handle, 4096);
        }
        fclose($handle);
    } else {
        $buffer = " Could not retrieve document '$file'.";
    }
    return $buffer;
}

// ParseCommand - separate @ cmd from params
function ParseCommand($binding_string) {
    global $BINDINGS;
    $match = array ();
    $regexp = '/@(' . implode('|', $BINDINGS) . ')\s*(.*)/im'; // Split binding on whitespace
    if (preg_match($regexp, $binding_string, $match)) {
        // We can't return the match array directly because the first element is the whole string
        $binding_array = array (
            strtoupper($match[1]),
            $match[2]
        ); // Make command uppercase
        return $binding_array;
    }
}



function makeFormelement($tv,$prefix='tv', $attributes='')
{
	       
	//require_once ('tmplvars.inc.php');
    //require_once ('tmplvars.commands.inc.php');
    //require ('tmplvars.format.inc.php');// add by Bruno
    if ($tv['type'] == 'richtext' || $tv['type'] == 'htmlarea')
    { // htmlarea for backward compatibility
        if (is_array($replace_richtexteditor))
        $replace_richtexteditor = array_merge($replace_richtexteditor, array (
        "tv".$tv['formname']
        ));
        else
            $replace_richtexteditor = array (
            "tv".$tv['formname']
            );
    }
    $tvPBV = $_POST['tv'.$tv['formname']];
$defaultOutput = ($tv['content'] !== '' && ! empty($tv['content']))?$tv['content']:$tv['default_text'];

return $this->renderFormElement($tv['type'], $tv['formname'], $defaultOutput, $tv['elements'], ($tvPBV?$tvPBV:$tv['value']), $tv['style'], $attributes);
}	
	
	
}

?>