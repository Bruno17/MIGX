<?php

$customHandlers[] = "
    setupmigx : function(task) {
        MODx.Ajax.request({
            url: Migx.config.connectorUrl
            ,params: {
                action: 'mgr/setup/setup'
				,task: task
            }
            ,listeners: {
                'success': {fn:function(r){this.updatePackageSuccess(r)},scope:this}
            }
        });
    }	
";


$customHandlers[] = "
    setupSuccess : function(r) {
        
        if (r.object.content){
            //console.log(r.object.content);
            Ext.get('migxpm_schema').dom.value = r.object.content;
            
            
            return;
        }
        alert ('success');
        
    }	
";

$tabTemplate = $this->config['templatesPath'] . 'mgr/setuptab.tpl';