{literal}

Migx.page.Object = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        formpanel: 'modx-panel-resource'
        ,buttons: [{
            text: _('save')
            ,id: 'migx-btn-save'
            ,process: 'mgr/migx/update'
            ,method: 'remote'
            ,keys: [{
                key: 's'
                ,alt: true
                ,ctrl: true
            }]
        }]
		,components: [/*{
            xtype: 'modx-panel-resource'
            ,object_id: config.object_id
			,configs: config.configs
	        ,url: Migx.config.connector_url
        },*/{
            xtype: 'modx-grid-multitvdbgrid-migxdb'
            ,preventRender: true
			,id: 'modx-grid-multitvdbgrid-migxdb'
			,configs: config.configs
			,columns:Ext.util.JSON.decode('{/literal}{$columns}{literal}')
			,pathconfigs:Ext.util.JSON.decode('{/literal}{$pathconfigs}{literal}')
            ,fields:Ext.util.JSON.decode('{/literal}{$fields}{literal}')
            ,wctx: '{/literal}{$myctx}{literal}'
            ,url: '{/literal}{$config.connectorUrl}{literal}'
            ,auth: '{/literal}{$auth}{literal}'
            ,resource_id: '{/literal}{$resource.id}{literal}' 
            ,co_id: '{/literal}{$connected_object_id}{literal}' 
            ,pageSize: 10
            ,object_id : '{/literal}{$object_id}{literal}'             
        }]

    }); 
    Migx.page.Object.superclass.constructor.call(this,config);
};
Ext.extend(Migx.page.Object,MODx.Component);
Ext.reg('migx-page-home',Migx.page.Object);

{/literal}{$grid}{literal}

/*
Ext.onReady(function() {
    MODx.load({ xtype: 'migx-page-home'});
});
 
Migx.page.Home = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'migx-panel-home'
            ,renderTo: 'migx-panel-home-div'
        }]
    });
    Migx.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(Migx.page.Home,MODx.Component);
Ext.reg('migx-page-home',Migx.page.Home);
*/

{/literal}