{literal}

MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal} = function(config) {
    config = config || {};
	//console.log(config);
    this.sm = new Ext.grid.CheckboxSelectionModel();

    // define grid columns in a separate variable
    var cols=[this.sm];
	for(i = 0; i <  config.columns.length; i++) {
 		cols.push(config.columns[i]);
    } 
    config.columns=cols;    
    Ext.applyIf(config,{
	autoHeight: true,
    collapsible: true,
	resizable: true,
    loadMask: true,
    paging: true,
    autosave: false,
    remoteSort: true,
    primaryKey: 'id',
    isModified : false,    
    sm: this.sm,
	viewConfig: {
        emptyText: 'No items found',
        forceFit: true,
		autoFill: true
    },
    url : config.url,
    baseParams: { 
        action: 'mgr/migxdb/getList',
        configs: config.configs,
        resource_id: config.resource_id,
        object_id: config.object_id,
        'HTTP_MODAUTH': config.auth
    },
    fields: [],    
    columns: [], // define grid columns in a separate variable
    tbar: [{/literal}{$customconfigs.tbar}{literal}]        
    });
	
    MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal}.superclass.constructor.call(this,config)
    this.getStore().pathconfigs=config.pathconfigs;

};
Ext.extend(MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal},MODx.grid.Grid,{
    _renderUrl: function(v,md,rec) {
        return '<a href="'+v+'" target="_blank">'+rec.data.pagetitle+'</a>';
    }
    ,getSelectedAsList: function() {
        var sels = this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;

        var cs = '';
        for (var i=0;i<sels.length;i++) {
            cs += ','+sels[i].data.id;
        }
        cs = Ext.util.Format.substr(cs,1,cs.length-1);
        return cs;
    }
    
    {/literal}{$customconfigs.gridfunctions}{literal}
	
                	     
	,loadWin: function(btn,e,action,tempParams) {
	    var resource_id = '{/literal}{$resource.id}{literal}';
        var tempParams = tempParams || null;
        var co_id = '{/literal}{$connected_object_id}{literal}';
        {/literal}{if $properties.autoResourceFolders == 'true'}{literal}
        if (resource_id == 0){
            alert (_('migx.save_resource'));
            return;
        }
        {/literal}{/if}{literal}        
       
        if (action == 'a'){
           var object_id = 'new';
        }else{
           var object_id = this.menu.record.id;
        }
        
        var isnew = (action == 'u') ? '0':'1';
        
 		
        var win_xtype = 'modx-window-tv-dbitem-update-{/literal}{$win_id}{literal}';
		if (this.windows[win_xtype]){
			this.windows[win_xtype].fp.autoLoad.params.tv_id='{/literal}{$tv->id}{literal}';
			this.windows[win_xtype].fp.autoLoad.params.resource_id=resource_id;
            this.windows[win_xtype].fp.autoLoad.params.co_id=co_id;
            this.windows[win_xtype].fp.autoLoad.params.configs=this.config.configs;
            this.windows[win_xtype].fp.autoLoad.params.tv_name='{/literal}{$tv->name}{literal}';
            this.windows[win_xtype].fp.autoLoad.params.object_id=object_id;
            this.windows[win_xtype].fp.autoLoad.params.tempParams=tempParams;
			this.windows[win_xtype].grid=this;
            this.windows[win_xtype].action=action;
           
		}
		this.loadWindow(btn,e,{
            xtype: win_xtype
			,grid: this
            ,action: action
            
            ,baseParams : {
			    action: 'mgr/migxdb/fields',
				tv_id: '{/literal}{$tv->id}{literal}',
				tv_name: '{/literal}{$tv->name}{literal}',
				'class_key': 'modDocument',
                'wctx':'{/literal}{$myctx}{literal}',
                object_id: object_id,
                configs: this.config.configs,
                resource_id : resource_id,
                co_id : co_id,
                tempParams: tempParams
			}
        });
    }
    ,getMenu: function() {
		var n = this.menu.record;
        //console.log(this.menu); 
        var m = [];
        {/literal}{$customconfigs.gridcontextmenus}{literal}        	        
		return m;
    }
});
Ext.reg('modx-grid-multitvdbgrid-{/literal}{$win_id}{literal}',MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal});

{/literal}