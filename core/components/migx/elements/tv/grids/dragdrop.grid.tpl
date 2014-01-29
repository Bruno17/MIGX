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
    primaryKey: 'id',
    isModified : false,
	ddGroup:'{/literal}{$tv->id}{literal}_gridDD',
    enableDragDrop: true, // enable drag and drop of grid rows    
 	viewConfig: {
        emptyText: 'No items found',
        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
        forceFit: true,
		autoFill: true
    },
    listeners: {
        "render": {
            scope: this,
            fn: function(grid) {

            // Enable sorting Rows via Drag & Drop
            // this drop target listens for a row drop
            //  and handles rearranging the rows

              var ddrow = new Ext.dd.DropTarget(grid.container, {
                  ddGroup : '{/literal}{$tv->id}{literal}_gridDD',
                  copy:false,
                  notifyDrop : function(dd, e, data){
                      var ds = grid.store;

					  alert('Hello world!'); 
                      // NOTE:
                      // you may need to make an ajax call here
                      // to send the new order
                      // and then reload the store


                      // alternatively, you can handle the changes
                      // in the order of the row as demonstrated below

                        // ***************************************

                        var sm = grid.getSelectionModel();
                        var rows = sm.getSelections();
                        if(dd.getDragData(e)) {
                            var cindex=dd.getDragData(e).rowIndex;
                            if(typeof(cindex) != "undefined") {
                                for(i = 0; i <  rows.length; i++) {
                                ds.remove(ds.getById(rows[i].id));
                                }
     							ds.insert(cindex,data.selections);
                                sm.clearSelections();
                             }
                             MODx.fireResourceFormChange();
                         }
						grid.collectItems();
                        grid.getView().refresh();

 
                        // ************************************
                      }
                   }) 
		
		this.setWidth('99%');
		//this.syncSize();
                   // load the grid store
                  //  after the grid has been rendered
                  //store.load();
       }
   }
},	
    url : config.url,
    baseParams: { 
        action: 'mgr/migxdb/getList',
        configs: config.configs,
        resource_id: config.resource_id,
        'HTTP_MODAUTH': config.auth
    },
    fields: ['id','pagetitle','createdon','published','deleted'],    
    columns: [], // define grid columns in a separate variable
    tbar: [{
            text: '{/literal}{$i18n.mig_add}{literal}',
			handler: this.addItem
        }
        {/literal}{if $properties.previewurl != ''}{literal}
        ,{
            text: '{/literal}{$i18n.mig_preview}{literal}',
			handler: this.preview
        }
        {/literal}{/if}{literal}
        ,{
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

	,loadData: function(){
	    var items_string = Ext.get('tv{/literal}{$tv->id}{literal}').dom.value;
        var items = [];
        var item = {};
        try {
            items = Ext.util.JSON.decode(items_string);
        }
        catch (e){
        }
                
        this.autoinc = 0;
        for(i = 0; i <  items.length; i++) {
 		    item = items[i];
            if (item.MIGX_id){
                if (item.MIGX_id > this.autoinc){
                    this.autoinc = item.MIGX_id;
                }
            }else{
                item.MIGX_id = this.autoinc +1 ;
                this.autoinc = item.MIGX_id;                 
            }	
           items[i] = item;  
        } 
        
		this.getStore().sortInfo = null;
		this.getStore().loadData(items);
			
		this.syncSize();
        this.setWidth('100%');
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
	,addItem: function(btn,e) {
		var s=this.getStore();
		this.loadWin(btn,e,s.getCount(),'a');
	}
	,preview: function(btn,e) {
		var s=this.getStore();
		this.loadPreviewWin(btn,e,s.getCount(),'a');
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
    ,publishSelected: function(btn,e) {
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
    }	,deleteObject: function() {
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
            this.windows[win_xtype].fp.autoLoad.params.configs=this.config.configs;
            this.windows[win_xtype].fp.autoLoad.params.tv_name='{/literal}{$tv->name}{literal}';
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

{/literal}