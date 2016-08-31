<?php

$action = $this->modx->getOption('action', $_POST, '');
$configs = $this->modx->getOption('configs', $_POST, '');

$gridactionbuttons['import_from_package']['text'] = "'[[%migx.import_from_package]]'";
$gridactionbuttons['import_from_package']['handler'] = 'this.import_from_package';
$gridactionbuttons['import_from_package']['scope'] = 'this';

$gridfunctions['this.import_from_package'] = "
import_from_package: function() {
    Ext.Msg.prompt('Import configs from Package', 'Package:', function(btn, text) {
        if (btn == 'ok') {
            var package = text;
            var url = this.config.url;
            var configs = this.config.configs;
            MODx.Ajax.request({
                url: url,
                params: {
                    action: 'mgr/migxdb/process',
                    processaction: 'importfrompackage',
                    package: package,
                    configs: configs
                },
                listeners: {
                    'success': {
                        fn: this.refresh,
                        scope: this
                    }
                }
            });
        }
    },this);
}	
";

if ($action != 'mgr/migxdb/fields'){
    
   $gridfunctions['this.editRaw'] = "
   editRaw: function(btn,e) {
     this.loadWin(btn,e,'u','raw');
   }
   ";
    
    $gridfunctions['this.editFlat'] = "
    editFlat: function(btn,e) {
      this.loadWin(btn,e,'u','flat');
    }  
    ";

    $gridcontextmenus['editflat']['code'] = "
        m.push({
            text: '[[%migx.edit_flat]]'
            ,handler: this.editFlat
        });
    ";
    $gridcontextmenus['editflat']['handler'] = 'this.editFlat';    
    
    $gridfunctions['this.editFlat'] = "
    editFlat: function(btn,e) {
      this.loadWin(btn,e,'u','flat');
    }  
    ";


    $gridcontextmenus['editraw']['code'] = "
        m.push({
            text: '[[%migx.edit_raw]]'
            ,handler: this.editRaw
        });
    ";
    $gridcontextmenus['editraw']['handler'] = 'this.editRaw';
    
    


    $gridfunctions['this.export_import'] = "
    export_import: function(btn,e) {
      this.loadWin(btn,e,'u','export_import');
    }  
    ";

    $gridcontextmenus['export_import']['code'] = "
        m.push({
            text: '[[%migx.export_import]]'
            ,handler: this.export_import
        });
    ";
    $gridcontextmenus['export_import']['handler'] = 'this.export_import';
    
    $gridfunctions['this.export_to_package'] = "
    export_to_package: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'exporttopackage'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
            }
            ,listeners: {
                'success': {fn:function(r){alert(r.message)},scope:this}
            }
        });
    }  
    ";

    $gridcontextmenus['export_to_package']['code'] = "
        m.push({
            text: '[[%migx.export_to_package]]'
            ,handler: this.export_to_package
        });
    ";
    $gridcontextmenus['export_to_package']['handler'] = 'this.export_to_package';    
}
