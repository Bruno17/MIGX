Ext.onReady(function() {
    MODx.load({ 
		xtype: 'migx-page-home'
        ,object_id: Migx.config.request.object_id
		,configs: Migx.config.request.configs
    });
});