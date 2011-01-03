<?php
/**
 * Loads the TV panel for the resource page.
 *
 * Note: This page is not to be accessed directly.
 *
 * @package modx
 * @subpackage manager
 */

//if (!$modx->hasPermission('quip.thread_view')) return $modx->error->failure($modx->lexicon('access_denied'));

$modx->getService('smarty','smarty.modSmarty'); 

if (! isset ($modx->smarty)){
    $modx->getService('smarty', 'smarty.modSmarty', '', array (
    'template_dir'=>$modx->getOption('manager_path').'templates/'.$modx->getOption('manager_theme', null, 'default').'/',
    ));
}
$modx->smarty->template_dir = $modx->getOption('manager_path').'templates/'.$modx->getOption('manager_theme', null, 'default').'/';

$modx->smarty->assign('OnResourceTVFormPrerender',$onResourceTVFormPrerender);
$modx->smarty->assign('_config',$modx->config);

$tv = $modx->getObject('modTemplateVar',array('name'=>$scriptProperties['tv_name']));
//$options = $tv->parseInputOptions($tv->processBindings($tv->get('elements'),$tv->get('name')));
$properties=$tv->getProperties();
$formtabs=$modx->fromJSON($properties['formtabs']);


$fieldid=0;
$tabid=0;
$allfields=array();

/*actual record */
$record=$modx->fromJSON($scriptProperties['record_json']);


foreach ($formtabs as $tabid=>$tab){
/*virtual categories for tabs*/
$emptycat = $modx->newObject('modCategory');
$emptycat->set('category',$tab['caption']);
$emptycat->id = $tabid;
$categories[$tabid] = $emptycat;	
	
	$fields=$tab['fields'];
	//$fields=$tab['fields'];
	foreach ($fields as & $field){
		$fieldid++;
		//echo $angebot->get($field['field']);
		if ($tv = $modx->getObject('modTemplateVar',array('name'=>$field['inputTV']))){
			
		}else{
			$tv = $modx->newObject('modTemplateVar');
		}
		
		/*insert actual value from requested record, convert arrays to ||-delimeted string */
		$fieldvalue= is_array($record[$field['field']])? implode('||',$record[$field['field']]): $record[$field['field']];
        //if (!empty($fieldvalue)){}
		$tv->set('value',$fieldvalue);
		$tv->set('caption',$field['caption']);
		//$tv->set('id',$fieldid);
		
		/*generate unique tvid*/
		$field['tv_id']=$scriptProperties['tv_id']*100000000+$fieldid;
        				
		$field['array_tv_id']=$field['tv_id'].'[]'; 
   		$allfields[]=$field;
		
		$tv->set('id',$field['tv_id']);

            $default = $tv->processBindings($tv->get('default_text'),$resourceId);
            if (strpos($tv->get('default_text'),'@INHERIT') > -1 && (strcmp($default,$tv->get('value')) == 0 || $tv->get('value') == null)) {
                $tv->set('inherited',true);
            }
            if ($tv->get('value') == null) {
                $v = $tv->get('default_text');
                if ($tv->get('type') == 'checkbox' && $tv->get('value') == '') {
                    $v = '';
                }
                $tv->set('value',$v);
            }
 		
            if ($tv->type == 'richtext') {
                if (is_array($replace_richtexteditor))
                    $replace_richtexteditor = array_merge($replace_richtexteditor, array (
                        'tv' . $tv->id
                    ));
                else
                    $replace_richtexteditor = array (
                        'tv' . $tv->id
                    );
            }
				
			//$inputForm = $tv->renderInput($resource->id);
		
		$modx->smarty->assign('tv',$tv);	
			
        $params= array ();
        if ($paramstring= $tv->get('display_params')) {
            $cp= explode("&", $paramstring);
            foreach ($cp as $p => $v) {
                $v= trim($v);
                $ar= explode("=", $v);
                if (is_array($ar) && count($ar) == 2) {
                    $params[$ar[0]]= $tv->decodeParamValue($ar[1]);
                }
            }
        }
        
		$value= $tv->get('value');
        if ($value === null) {
            $value= $tv->get('default_text');
        }		
        /* find the correct renderer for the TV, if not one, render a textbox */
        $inputRenderPaths = $tv->getRenderDirectories('OnTVInputRenderList','input');
        $inputForm = $tv->getRender($params,$value,$inputRenderPaths,'input',$resourceId,$tv->get('type'));			
           

            if (empty($inputForm)) continue;

            $tv->set('formElement',$inputForm);
			
            if (!is_array($categories[$tabid]->tvs)) {
                $categories[$tabid]->tvs = array();
            }
            $categories[$tabid]->tvs[] = $tv;			
        
	}
}


$modx->smarty->assign('fields',$modx->toJSON($allfields));
//$modx->smarty->assign('customconfigs',$modx->xdbedit->customconfigs);
//$modx->smarty->assign('object',$object);
$modx->smarty->assign('categories',$categories);
$modx->smarty->assign('properties',$scriptProperties);

if (!empty($_REQUEST['showCheckbox'])) {
    $modx->smarty->assign('showCheckbox',1);
}
$miTVCorePath = $modx->getOption('multiitemsTV.core_path',null,$modx->getOption('core_path').'components/multiitemsTV/');
$modx->smarty->template_dir = $miTVCorePath.'templates/';
return $modx->smarty->fetch('mgr/fields.tpl');

