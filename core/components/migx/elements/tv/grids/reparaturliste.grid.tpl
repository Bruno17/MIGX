{literal}

MODx.grid.multiTVdbgrid = function(config) {
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
    pageSize: 10,
    primaryKey: 'id',
    isModified : false,    
    sm: this.sm,
	viewConfig: {
        emptyText: 'No items found',
        forceFit: true,
		autoFill: true,
        getRowClass : function(rec, ri, p){
            var cls = 'xdbedit-object';
            if (!rec.data.published) cls += ' xdbedit-unpublished';
            if (rec.data.deleted) cls += ' xdbedit-deleted';

            return cls;
        }        
    },
    url : config.url,
    baseParams: { 
        action: 'mgr/migxdb/getList',
        configs: config.configs,
        resource_id: config.resource_id,
        'HTTP_MODAUTH': config.auth},
    fields: ['id','pagetitle','content','createdon','published','deleted'],    
        columns: []

		,tbar: [{
				xtype: 'buttongroup',
				id: 'filter-buttongroup',
				title: 'Filter',
				columns: 2,
				defaults: {
					scale: 'large'
				},
				items: [{
					text: 'Status:'
				}, {
					xtype: 'migx-combo-status',
					id: 'migx-filter-status',
					itemId: 'status',
					value: 'all',
					width: 150,
					listeners: {
						'select': {
							fn: this.changeStatus,
							scope: this
						}
					}
				}]
			},{
            xtype: 'buttongroup',
            title: 'Aktionen',
            columns: 2,
            defaults: {
                scale: 'large'
            },
            items: [{
					text: 'Stapeloperationen',
					menu: [{
						text: 'markierte veröffentlichen',
						handler: this.publishSelected,
						scope: this
					}, {
						text: 'markierte zurückziehen',
						handler: this.unpublishSelected,
						scope: this
					}, {
						text: 'markierte löschen',
						handler: this.deleteSelected,
						scope: this
					}]
				},{
                    text: ('{/literal}{$i18n.mig_show_trash}{literal}')
                    ,handler: this.toggleDeleted
                    ,enableToggle: true
                    ,scope: this
                }]
			
			}         
        
        ]        

       
    });
	
    MODx.grid.multiTVdbgrid.superclass.constructor.call(this,config)
    this.getStore().pathconfigs=config.pathconfigs;
	//this.loadData();
};
Ext.extend(MODx.grid.multiTVdbgrid,MODx.grid.Grid,{
    _renderUrl: function(v,md,rec) {
        return '<a href="'+v+'" target="_blank">'+rec.data.pagetitle+'</a>';
    }
    ,renderImage : function(val, md, rec, row, col, s){
		var source = s.pathconfigs[col];
		if (val.substr(0,4) == 'http'){
			return '<img style="height:60px" src="' + val + '"/>' ;
		}        
		if (val != ''){
			//return '<img src="{/literal}{$_config.connectors_url}{literal}system/phpthumb.php?h=60&src=' + val + '" alt="" />';
			
			return '<img src="'+MODx.config.connectors_url+'{/literal}system/phpthumb.php?h=60&src='+val+'&wctx={$ctx}'+source+'{literal}" alt="" />';
		
		}
		return val;
	}
    ,renderPlaceholder : function(val, md, rec, row, col, s){
        return '[[+'+val+'.'+rec.json.MIGX_id+']]';
        
	}       
    ,renderFirst : function(val, md, rec, row, col, s){
		val = val.split(':');
        return val[0];
        
        /*
        var max = 100;
        var count = val.length;
		if (count>max){
            return(val.substring(0, max));
		}
        */        
		return val;
	}        
    ,renderLimited : function(val, md, rec, row, col, s){
		var max = 100;
        var count = val.length;
		if (count>max){
            return(val.substring(0, max));
		}        
		return val;
	}    
    ,renderPreview : function(val,md,rec){
		return val;
	}
    ,toggleDeleted: function(btn,e) {
        var s = this.getStore();
        if (btn.pressed) {
            s.setBaseParam('showtrash',1);
            btn.setText('{/literal}{$i18n.mig_show_normal}{literal}');
        } else {
            s.setBaseParam('showtrash',0);
            btn.setText('{/literal}{$i18n.mig_show_trash}{literal}');
        }
        this.getBottomToolbar().changePage(1);
        s.removeAll();
        this.refresh();
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
    },publishSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/{/literal}{$customconfigs.task}{literal}/bulkupdate'
				,configs: this.config.configs
				,task: 'publish'
                ,objects: cs
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    },unpublishSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/{/literal}{$customconfigs.task}{literal}/bulkupdate'
				,configs: this.config.configs
				,task: 'unpublish'
                ,objects: cs
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    },deleteSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/{/literal}{$customconfigs.task}{literal}/bulkupdate'
				,configs: this.config.configs
				,task: 'delete'
                ,objects: cs
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
	,addItem: function(btn,e) {
		var s=this.getStore();
		this.loadWin(btn,e,s.getCount(),'a');
	}
	,preview: function(btn,e) {
		var s=this.getStore();
		this.loadPreviewWin(btn,e,s.getCount(),'a');
	}
	,changeStatus: function(cb,nv,ov) {
        this.setFilterParams({
			status:cb.getValue()
		});      		
    }
    ,setFilterParams: function(params) {
        var tb = this.getTopToolbar();
        if (!tb) {return false;}
        //var ccb = null;
		//var ycb = null;
		//var mcb = null;
        if (params.status) {
            //params.month = params.month||'all';
			//params.year = params.year||'all';
			//Ext.getCmp('migx-filter-status').setValue(params.status);
            //ycb = tb.getComponent('year');
			//ycb = Ext.getCmp('xdbedit-filter-year');
			
            /*
            if (ycb) {
                ycb.store.baseParams['region'] = params.region;
                ycb.store.load({
                    callback: function() {
                        ycb.setValue('all');
                    }
                });
            }
            */
        } 
        /*
        if (params.year) {
            params.month = params.month||'all';
			Ext.getCmp('xdbedit-filter-year').setValue(params.year);
            //mcb = tb.getComponent('month');
			mcb = Ext.getCmp('xdbedit-filter-month');
            if (mcb) {
                mcb.store.baseParams['year'] = params.year;
				if (params.region) {mcb.store.baseParams['region'] = params.region;}
                mcb.store.load({
                    callback: function() {
                        mcb.setValue(params.month);
                    }
                });
            }
        } 
        */
        var s = this.getStore();
        if (s) {
            //if (params.year) {s.baseParams['year'] = params.year;}
            //if (params.month) {s.baseParams['month'] = params.month ;}
			if (params.status) {s.baseParams['status'] = params.status ;}
            s.removeAll();
        }
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
	,deleteObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/update'
				,task: 'delete'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    },recallObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/update'
				,task: 'recall'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    },removeObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/{/literal}{$customconfigs.task}{literal}/remove'
				,task: 'removeone'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }                	
	,remove: function() {
        var _this=this;
		Ext.Msg.confirm(_('warning') || '','{/literal}{$i18n.mig_remove_confirm}{literal}' || '',function(e) {
            if (e == 'yes') {
				_this.getStore().removeAt(_this.menu.recordIndex);
                _this.getView().refresh();
		        _this.collectItems();
                MODx.fireResourceFormChange();	
                }
            }),this;		
	}   
	,update: function(btn,e) {
      this.loadWin(btn,e,this.menu.recordIndex,'u');
    }
	,duplicate: function(btn,e) {
      this.loadWin(btn,e,this.menu.recordIndex,'d');
    }    
	,loadWin: function(btn,e,index,action) {
	    var resource_id = '{/literal}{$resource.id}{literal}';
        {/literal}{if $properties.autoResourceFolders == 'true'}{literal}
        if (resource_id == 0){
            alert ('{/literal}{$i18n.mig_save_resource}{literal}');
            return;
        }
        {/literal}{/if}{literal}        
       
        if (action == 'a'){
           //var json='{/literal}{$newitem}{literal}';
           //var data=Ext.util.JSON.decode(json);
           var object_id = 'new';
        }else{
		   //var s = this.getStore();
           //var rec = s.getAt(index)            
           //var data = rec.data;
           //console.log(data);
           //var json = Ext.util.JSON.encode(rec.json);
           var object_id = this.menu.record.id;
        }
        
        var isnew = (action == 'u') ? '0':'1';
        
 		
        var win_xtype = 'modx-window-tv-dbitem-update';
		if (this.windows[win_xtype]){
			this.windows[win_xtype].fp.autoLoad.params.tv_id='{/literal}{$tv->id}{literal}';
			this.windows[win_xtype].fp.autoLoad.params.resource_id=resource_id;
            this.windows[win_xtype].fp.autoLoad.params.tv_name='{/literal}{$tv->name}{literal}';
            this.windows[win_xtype].fp.autoLoad.params.configs=this.config.configs;
		    //this.windows[win_xtype].fp.autoLoad.params.itemid=index;
            //this.windows[win_xtype].fp.autoLoad.params.record_json=json;
            //this.windows[win_xtype].fp.autoLoad.params.autoinc=this.autoinc;
            //this.windows[win_xtype].fp.autoLoad.params.isnew=isnew;
            this.windows[win_xtype].fp.autoLoad.params.object_id=object_id;
			this.windows[win_xtype].grid=this;
            this.windows[win_xtype].action=action;
		}
		this.loadWindow(btn,e,{
            xtype: win_xtype
            //,record: data
			,grid: this
            ,action: action
            ,baseParams : {
				//record_json:json,
			    action: 'mgr/migxdb/fields',
				tv_id: '{/literal}{$tv->id}{literal}',
				tv_name: '{/literal}{$tv->name}{literal}',
				'class_key': 'modDocument',
                'wctx':'{/literal}{$myctx}{literal}',
				//itemid : index,
                //autoinc : this.autoinc,
                object_id: object_id,
                configs: this.config.configs,
                //isnew : isnew,
                resource_id : resource_id
			}
        });
    }
	,loadPreviewWin: function(btn,e,index,action) {
        var items = Ext.get('tv{/literal}{$tv->id}{literal}').dom.value;
		//console.log((items));
        var jsonvarkey = '{/literal}{$properties.jsonvarkey}{literal}';
        if (jsonvarkey == ''){
            jsonvarkey = 'migx_outputvalue';
        }
        var win_xtype = 'modx-window-mi-preview';
		if (this.windows[win_xtype]){
			//this.windows[win_xtype].fp.autoLoad.params.tv_id='{/literal}{$tv->id}{literal}';
			//this.windows[win_xtype].fp.autoLoad.params.tv_name='{/literal}{$tv->name}{literal}';
		    //this.windows[win_xtype].fp.autoLoad.params.itemid=index;
            //this.windows[win_xtype].fp.autoLoad.params.record_json=json;
            this.windows[win_xtype].src='{/literal}{$properties.previewurl}{literal}';
			this.windows[win_xtype].json=items;
            this.windows[win_xtype].jsonvarkey=jsonvarkey;
            this.windows[win_xtype].action=action;
		}
		this.loadWindow(btn,e,{
            xtype: win_xtype
            ,src: '{/literal}{$properties.previewurl}{literal}'
            ,jsonvarkey:jsonvarkey
            ,json: items
			,grid: this
            ,action: action
        });
    }    	
    ,getMenu: function() {
		var n = this.menu.record;
        console.log(this.menu); 
        var m = [];
        m.push({
            text: '{/literal}{$i18n.mig_edit}{literal}'
            ,handler: this.update
        });
        m.push('-');
        if (n.deleted == 1) {
        m.push({
            text: 'wiederherstellen'
            ,handler: this.recallObject
        });
		m.push('-');
        m.push({
            text: 'entfernen'
            ,handler: this.removeObject
        });						
        } else if (n.deleted == 0) {
        m.push({
            text: 'löschen'
            ,handler: this.deleteObject
        });		
        }	        
		return m;
    }
	,collectItems: function(){
		var items=[];
		// read jsons from grid-store-items 
        var griddata=this.store.data;
		for(i = 0; i <  griddata.length; i++) {
 			items.push(griddata.items[i].json);
        }
        if (items.length >0){
           Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = Ext.util.JSON.encode(items); 
        }
        else{
           Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = '';  
        }
        
		return;						 
    }
});
Ext.reg('modx-grid-multitvdbgrid',MODx.grid.multiTVdbgrid);



MODx.combo.Status = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        name: 'status'
        ,hiddenName: 'status'
        ,forceSelection: true
        ,typeAhead: false
        ,editable: false
        ,allowBlank: false
        ,listWidth: 300		
		,resizable: false
        ,pageSize: 0		
        ,url: MODx.config.assets_url+'components/migx/connector.php'
        ,fields: ['name']
        ,displayField: 'name'
        ,valueField: 'name'
        ,baseParams: {
		    action: 'mgr/{/literal}{$customconfigs.task}{literal}/gettvoptions',
            tvname: 'auftrag_status',
			configs: '{/literal}{$properties.configs}{literal}',
        }
    });
    MODx.combo.Status.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.Status,MODx.combo.ComboBox);
Ext.reg('migx-combo-status',MODx.combo.Status);

{/literal}