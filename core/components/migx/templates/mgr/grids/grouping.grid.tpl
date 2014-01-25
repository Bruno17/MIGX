{literal}

MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal} = function(config) {
    config = config || {};
	//console.log(config);
    this.sm = new Ext.grid.CheckboxSelectionModel();

    // define grid columns in a separate variable
    //var cols=[this.sm];
    var cols=[];
    // add empty pathconfig (source) to array to match number of col in renderimage
    var pc=[''];
    var renderer = null;
	for(i = 0; i <  config.columns.length; i++) {
        renderer = config.columns[i]['renderer'];
        if (typeof renderer != 'undefined'){
            config.columns[i]['renderer'] = {fn:eval(renderer),scope:this};
        }
        cols.push(config.columns[i]);
        pc.push(config.pathconfigs[i]);
        
    }
    config.pathconfigs = pc; 
    config.columns=cols;    
    Ext.applyIf(config,{
	autoHeight: true,
    collapsible: true,
	resizable: true,
    loadMask: true,
    paging: true,
    
    grouping:true,
    groupBy: 'content_de',
    sortBy: 'pos',
    
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
    this.view.groupTextTpl = '<div class="statisticGroup">{text}</div>';
    this._makeTemplates();
    this.getStore().pathconfigs=config.pathconfigs;
    this.on('click', this.onClick, this);   

};
Ext.extend(MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal},MODx.grid.Grid,{
    _renderUrl: function(v,md,rec) {
        return '<a href="'+v+'" target="_blank">'+rec.data.pagetitle+'</a>';
    }
    ,_makeTemplates: function() {
        this.tplRowActions = new Ext.XTemplate('<tpl for="."><div class="migx-actions-column">'
										    +'<h3 class="main-column">{column_value}</h3>'
												+'<tpl if="column_actions">'
													+'<ul class="actions">'
                                                        +'<tpl for="column_actions">'
                                                            +'<tpl if="typeof (className) != '+"'undefined'"+'">'   
														    +'<li><a href="#" class="controlBtn {className} {handler}">{text}</a></li>'
                                                          +'</tpl>'
													    +'</tpl>'
                                                    +'</ul>'
												+'</tpl>'
											+'</div></tpl>',{
			compiled: true
		});
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
        var m = [];
        {/literal}{$customconfigs.gridcontextmenus}{literal}        	        
		return m;
    }
    ,renderRowActions:function(v,md,rec) {
        var n = rec.data;
        var m = [];	   
        {/literal}{$customconfigs.gridcolumnbuttons}{literal} 
        rec.data.column_actions = m;
        rec.data.column_value = v;
        return this.tplRowActions.apply(rec.data);
	} 
	,onClick: function(e){
		
        var t = e.getTarget();
		var elm = t.className.split(' ')[0];
		if(elm == 'controlBtn') {
			var handler = t.className.split(' ')[2];
			var record = this.getSelectionModel().getSelected();
            this.menu.record = record;
            var fn = eval(handler);
            fn = fn.createDelegate(this);
            fn(null,e);
 		}
	}    
});
Ext.reg('modx-grid-multitvdbgrid-{/literal}{$win_id}{literal}',MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal});

{/literal}