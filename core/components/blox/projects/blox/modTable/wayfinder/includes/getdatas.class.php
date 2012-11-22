<?php

class Blox_modTable_Wayfinder {

    /**
     * Constructor
     *
     * @access	public
     * @param	array	initialization parameters
     */
    public function __construct(&$blox) {
        $this->blox = &$blox;
        $this->bloxconfig = &$blox->bloxconfig;
    }

    function getdatas() {
        global $modx;

        $scriptProperties = $this->bloxconfig;

        //custom prefix  
        $prefix = isset($this->bloxconfig['prefix']) && !empty($this->bloxconfig['prefix']) ? $this->bloxconfig['prefix'] : null;
        //if you have an empty prefix use this property
        if (isset($this->bloxconfig['use_custom_prefix']) && !empty($this->bloxconfig['use_custom_prefix'])) {
            $prefix = isset($this->bloxconfig['prefix']) ? $this->bloxconfig['prefix'] : '';
        }

        $modx->addPackage($this->bloxconfig['packagename'], $modx->getOption('core_path') . 'components/' . $this->bloxconfig['packagename'] . '/model/',$prefix);
        
        $parent = $modx->getOption('parent', $scriptProperties, '');

        //include_once('helpers.class.inc.php');

        if (class_exists('bloxhelpers')) {
            // Initialize class
            $helper = new bloxhelpers($this->blox);
        } else {
            echo 'bloxhelpers class not found';
        }

        $config['startid'] = $modx->getOption('startId', $scriptProperties, $modx->resource->get('id'));
        $config['depth'] = $modx->getOption('depth', $scriptProperties, '10');
        $config['startingLevel'] = $modx->getOption('startingLevel', $scriptProperties, '1');
        $config['hideSubmenuesStartlevel'] = $modx->getOption('hideSubmenuesStartlevel', $scriptProperties, $config['depth']);
        $config['selectfields'] = $modx->getOption('selectfields', $scriptProperties, 'id,pagetitle');
        $config['excludechildrenofdocs'] = $modx->getOption('excludeChildrenOfDocs', $scriptProperties, '');
        $config['activeid'] = $modx->getOption('activeid', $scriptProperties, $_GET['objectId']);
        $config['titlefield'] = $modx->getOption('titlefield', $scriptProperties, 'title'); 
        $config['sortby'] = $modx->getOption('sortby', $scriptProperties, $config['titlefield']);
        $config['sortdir'] = $modx->getOption('sortdir', $scriptProperties, 'ASC');
        $config['classname'] = $modx->getOption('classname', $scriptProperties, 'modResource'); 
        
        $config['limit'] = '1000';        

        $children = $helper->getSiteMap($config);

        $bloxdatas['menue'] = $this->buildmenue($children,$config);
        return $bloxdatas;

    }
    
    function buildmenue($children,$config){
        global $modx;
        $output = '';
        foreach ($children as $child) {
            $active = $child['_active'] == '1' ? 'active' : '';
            $strongopen = $child['_active'] == '1' ? '<strong>' : '';
            $strongclose = $child['_active'] == '1' ? '</strong>' : '';
            $link = $modx->makeUrl($modx->resource->get('id'),'',array('objectId'=>$child['id']));
            $wrapper = '';
            if (is_array($child['innerrows']['children'])) {
                $wrapper = $this->buildmenue($child['innerrows']['children'],$config);
            }
            $output .= '<li class= "' .$active.'"> <a href="'.$link.'">'.$strongopen.$child[$config['titlefield']].$strongclose.'</a> '.$wrapper.' </li>';
        }
        return '<ul>' . $output . '</ul>';        
    }
    
}


