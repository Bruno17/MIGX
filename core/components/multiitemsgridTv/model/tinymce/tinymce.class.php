<?php
/**
 * @package tinymce
 */
class TinyMCE {
    public $config = array();
    public $properties = array();
    public $jsLoaded = false;

    /**
     * The TinyMCE constructor.
     *
     * @param modX $modx A reference to the modX constructor.
     */
    function __construct(modX &$modx,array $config = array(),$tinyUrl) {
        $this->modx =& $modx;

        $assetsUrl = $this->modx->getOption('tiny.assets_url',$config,$this->modx->getOption('assets_url',null,MODX_ASSETS_URL).'components/tinymce/');
        $assetsPath = $this->modx->getOption('tiny.assets_path',$config,$this->modx->getOption('assets_path',null,MODX_ASSETS_PATH).'components/tinymce/');
        $corePath = $this->modx->getOption('tiny.core_path',$config,$this->modx->getOption('core_path',null,MODX_CORE_PATH).'components/tinymce/');

        $this->config = array_merge(array(
            'assetsPath' => $assetsPath,
            'assetsUrl' => $assetsUrl,
            'path' => $assetsPath,
            'corePath' => $corePath,
			'tinyUrl' => $tinyUrl,
        ),$config);
    }

    public function setProperties(array $properties = array()) {
        $browserAction = $this->_getBrowserAction();
        $this->properties = array_merge(array(
            'accessibility_warnings' => false,
            'apply_source_formatting' => true,
            'browserUrl' => $browserAction ? $this->modx->getOption('manager_url',null,MODX_MANAGER_URL).'index.php?a='.$browserAction->get('id') : null,
            'button_tile_map' => false,
            'cleanup' => true,
            'cleanup_on_startup' => false,
            'compressor' => '',
            'content_css' => $this->modx->getOption('editor_css_path'),
            'convert_fonts_to_spans' => true,
            'convert_newlines_to_brs' => false,
            'element_format' => 'xhtml',
            'element_list' => '',
            'entities' => '',
            'entity_encoding' => 'named',
            'execcommand_callback' => 'Tiny.onExecCommand',
            'file_browser_callback' => 'Tiny.loadBrowser',
            'force_p_newlines' => true,
            'force_br_newlines' => false,
            'forced_root_block' => false,
            'formats' => array(
		'alignleft' => array('selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', 'classes' => 'justifyleft'),
		'alignright' => array('selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', 'classes' => 'justifyright'),
		'alignfull' => array('selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img', 'classes' => 'justifyfull'),
            ),
            'frontend' => false,
            'height' => '400px',
            'invalid_elements' => '',
            'nowrap' => false,
            'path_options' => '',
            'plugin_insertdate_dateFormat' => '%Y-%m-%d',
            'plugin_insertdate_timeFormat' => '%H:%M:%S',
            'preformatted' => false,
            'resizable' => true,
            'relative_urls' => true,
            'remove_linebreaks' => false,
            'remove_script_host' => true,
            'resource_browser_path' => $this->modx->getOption('manager_url',null,MODX_MANAGER_URL).'controllers/browser/index.php?',
            'skin' => 'cirkuit',
            'skin_variant' => '',
            'table_inline_editing' => true,
            'template_external_list_url' => $this->config['assetsUrl'].'template.list.php',
            'theme_advanced_disable' => '',
            'theme_advanced_resizing' => true,
            'theme_advanced_resize_horizontal' => true,
            'theme_advanced_statusbar_location' => 'bottom',
            'theme_advanced_styles' => $this->modx->getOption('tiny.css_selectors',null,''),
            'theme_advanced_toolbar_align' => 'left',
            'theme_advanced_toolbar_location' => 'top',
            'width' => '95%',
        ),$properties);

        /* now do user/context/system setting overrides - these must override properties */
        $this->properties = array_merge($this->properties,array(
            'theme_advanced_blockformats' => $this->modx->getOption('tiny.theme_advanced_blockformats',$this->properties,'p,h1,h2,h3,h4,h5,h6,div,blockquote,code,pre,address'),
            'theme_advanced_font_sizes' => $this->modx->getOption('tiny.theme_advanced_font_sizes',$this->properties,'80%,90%,100%,120%,140%,160%,180%,220%,260%,320%,400%,500%,700%'),
            'buttons1' => $this->modx->getOption('tiny.custom_buttons1',$this->properties,''),
            'buttons2' => $this->modx->getOption('tiny.custom_buttons2',$this->properties,''),
            'buttons3' => $this->modx->getOption('tiny.custom_buttons3',$this->properties,''),
            'buttons4' => $this->modx->getOption('tiny.custom_buttons4',$this->properties,''),
            'buttons5' => $this->modx->getOption('tiny.custom_buttons5',$this->properties,''),
            'css_path' => $this->modx->getOption('editor_css_path',$this->properties,''),
            'plugins' => $this->modx->getOption('tiny.custom_plugins',$this->properties,''),
            'theme' => $this->modx->getOption('tiny.editor_theme',$this->properties,'advanced'),
            'theme_advanced_styles' => $this->modx->getOption('tiny.css_selectors',$this->properties,''),
            'theme_advanced_buttons1' => $this->modx->getOption('tiny.custom_buttons1',$this->properties,''),
            'theme_advanced_buttons2' => $this->modx->getOption('tiny.custom_buttons2',$this->properties,''),
            'theme_advanced_buttons3' => $this->modx->getOption('tiny.custom_buttons3',$this->properties,''),
            'theme_advanced_buttons4' => $this->modx->getOption('tiny.custom_buttons4',$this->properties,''),
            'theme_advanced_buttons5' => $this->modx->getOption('tiny.custom_buttons5',$this->properties,''),
            'toolbar_align' => $this->modx->getOption('manager_direction',$this->properties,'ltr'),
            'use_browser' => $this->modx->getOption('use_browser',$this->properties,true),
            'path_options' => $this->modx->getOption('tiny.path_options',$this->properties,''),
            'language' => $this->modx->getOption('manager_language',null,$this->modx->getOption('cultureKey',null,'en')),
        ));
       
       $this->modx->log(modX::LOG_LEVEL_ERROR,print_r($this->properties['plugins'],1));   
		
    }

    public function initialize() {
        if (!$this->jsLoaded) {
            $scriptfile = ((!$this->properties['frontend'] && $this->properties['compressor'] == 'enabled') ? 'tiny_mce_gzip.php' : 'tiny_mce.js');
            if ($this->modx->getOption('tiny.use_uncompressed_library',null,false)) {
                $scriptfile = 'tiny_mce_src.js';
            }
            $this->modx->regClientStartupScript($this->config['assetsUrl'].'jscripts/tiny_mce/'.$scriptfile);
            $this->modx->regClientStartupScript($this->config['assetsUrl'].'xconfig.js');
            $this->modx->regClientStartupScript($this->config['tinyUrl'].'tiny.js');
            $this->modx->lexicon->load('tinymce:default');
            $lang = $this->modx->lexicon->fetch('tiny.',true);
            $this->modx->regClientStartupHTMLBlock('<script type="text/javascript">Tiny.lang = '.$this->modx->toJSON($lang).';</script>');
            //$this->modx->regClientStartupScript($this->config['assetsUrl'].'tinymce.panel.js');
            $this->jsLoaded = true;
        }
        return $this->getScript();
    }

    /**
     * Renders the TinyMCE script code.
     * @access public
     * @param array $config An array of configuration parameters.
     */
    public function getScript() {
        $theme = $this->properties['theme'];
        if ($theme == 'editor' || $theme == 'custom') {
            $tinyTheme = 'advanced';
            $theme = 'advanced';
        } else {
            $tinyTheme = $theme;
        }
        $this->properties['theme'] = $theme;
        $this->properties['document_base_url'] = $this->config['assetsUrl'];

        /* Set relative URL options */
        switch ($this->properties['path_options']) {
            default:
            case 'docrelative':
                $this->properties['relative_urls'] = true;
                $this->properties['remove_script_host'] = true;

                $baseUrl = $this->modx->context->getOption('base_url',MODX_BASE_URL);
                $resource = $this->properties['resource'];
                if ($resource) {
                    $ctx = $resource->get('context_key');
                    $ctx = $this->modx->getObject('modContext',$ctx);
                    if ($ctx) {
                        $ctx->prepare();
                        $baseUrl = $ctx->getOption('base_url',$baseUrl);
                    }
                }
                $this->properties['document_base_url'] = $baseUrl;
            break;

            case 'rootrelative':
                $this->properties['relative_urls'] = false;
                $this->properties['remove_script_host'] = true;
            break;

            case 'fullpathurl':
                $this->properties['relative_urls'] = false;
                $this->properties['remove_script_host'] = false;
            break;
        }

        if (!empty($this->properties['resource'])) {
            $this->properties['resource'] = $this->properties['resource']->toArray();
        }
        //unset($this->properties['width'],$this->properties['height']);
        
        $templates = $this->getTemplateList();

        /* get formats */
        //$this->properties['formats'] = $this->getFormats();

        /* get JS */
        unset($this->properties['resource']);
        ob_start();
        include_once dirname(__FILE__).'/templates/script.tpl';
        $script = ob_get_contents();
        ob_end_clean();

        $this->modx->regClientStartupHTMLBlock($script);
        return '';
    }

    /**
     * Allows for custom templates
     */
    public function getTemplateList() {
        $list = array();

        $templateList = $this->modx->getOption('tiny.template_list',$this->properties,'');
        if (empty($templateList)) return $list;

        $templateList = explode(',',$templateList);
        foreach ($templateList as $template) {
            if (empty($template)) continue;
            $templateParams = explode(':',$template);
            if (count($templateParams) < 2) continue;

            $t = array($templateParams[0],$templateParams[1]);
            if (!empty($templateParams[2])) array_push($t,$templateParams[2]);
            $list[] = $t;
        }
        return $list;
    }

    /**
     * Gets the MODx modAction for the rte browser.
     */
    private function _getBrowserAction() {
        return $this->modx->getObject('modAction',array('controller' => 'browser'));
    }

    /**
     * Allows for custom formats.
     *
     * TODO: Figure out proprietary storage format to have this work. Currently
     * ignored.
     */
    public function getFormats() {
        $formats = explode(',',$this->properties['formats']);
        $fs = array();
        foreach ($formats as $format) {
            $fs[$format] = new stdClass();
        }
        $formats = json_encode($fs);
        unset($this->properties['formats']);
        return '';
    }
}

/*
 *
$formatMap = array(
    'alignleft' => array(
        'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
        'classes' => 'left',
    ),
    'aligncenter' => array(
        'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
        'classes' => 'center',
    ),
    'alignright' => array(
        'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
        'classes' => 'right',
    ),
    'alignfull' => array(
        'selector' => 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li,table,img',
        'classes' => 'full',
    ),
    'bold' => array(
        'inline' => 'span',
        'classes' => 'bold',
    ),
    'italic' => array(
        'inline' => 'span',
        'classes' => 'italic',
    ),
    'underline' => array(
        'inline' => 'span',
        'classes' => 'underline',
        'exact' => true,
    ),
    'strikethrough' => array(
        'inline' => 'del',
    ),
    'forecolor' => array(
        'inline' => 'span',
        'classes' => 'hilitecolor',
        'styles' => array(
            'backgroundColor' => '%value',
        ),
    ),
);
 */