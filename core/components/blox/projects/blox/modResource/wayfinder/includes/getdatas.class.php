<?php

class Blox_modResource_Wayfinder {

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
        //$config['activeid'] = $_GET['activeid'];
        $config['selectfields'] = $modx->getOption('selectfields', $scriptProperties, 'id,pagetitle');
        $config['excludechildrenofdocs'] = $modx->getOption('excludeChildrenOfDocs', $scriptProperties, '');
        $config['activeid'] = $modx->getOption('activeid', $scriptProperties, 0);
        $config['sortby'] = $modx->getOption('sortby', $scriptProperties, 'menuindex');
        $config['sortdir'] = $modx->getOption('sortdir', $scriptProperties, 'ASC');
        $config['classname'] = $modx->getOption('classname', $scriptProperties, 'modResource'); 
        $config['limit'] = '1000';        

        $children = $helper->getSiteMap($config);

        $bloxdatas['menue'] = $this->buildmenue($children);
        return $bloxdatas;

    }
    
    function buildmenue($children){
        global $modx;
        $output = '';
        foreach ($children as $child) {
            $active = $child['_active'] == '1' ? 'active' : '';
            $link = $modx->makeUrl($child['id']);
            $wrapper = '';
            if (is_array($child['innerrows']['children'])) {
                $wrapper = $this->buildmenue($child['innerrows']['children']);
            }
            $output .= '<li class= "' .$active.'"> <a href="'.$link.'">'.$child['pagetitle'].'</a> '.$wrapper.' </li>';
        }
        return '<ul>' . $output . '</ul>';        
    }
    
}


