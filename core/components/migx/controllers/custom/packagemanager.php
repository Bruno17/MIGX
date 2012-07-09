<?php

$customHandlers[] = "
    updatePackage : function(task) {
        var packageName = Ext.get('migxpm_packageName').dom.value;
        var schema = '';
        var prefix = '';
        var usecustomprefix = 0;
        if (task == 'saveSchema'){
            schema = Ext.get('migxpm_schema').dom.value;
        }
        if (task == 'writeSchema' || task == 'createTables'){
            usecustomprefix = Ext.getCmp('migxpm_use_custom_prefix').getValue();
            prefix = Ext.get('migxpm_prefix').dom.value;
        }        
        MODx.Ajax.request({
            url: Migx.config.connectorUrl
            ,params: {
                action: 'mgr/packagemanager/packagemanager'
				,task: task
                ,packageName : packageName
                ,schema : schema
                ,prefix : prefix
                ,usecustomprefix : usecustomprefix
            }
            ,listeners: {
                'success': {fn:function(r){this.updatePackageSuccess(r)},scope:this}
            }
        });
    }	
";

$customHandlers[] = "
    updatePackageSuccess : function(r) {
        
        if (r.object.content){
            //console.log(r.object.content);
            Ext.get('migxpm_schema').dom.value = r.object.content;
            
            
            return;
        }
        alert ('success');
        
    }	
";

$tabTemplate = $this->config['templatesPath'] . 'mgr/packagemanagertab.tpl';