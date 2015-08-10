{literal}
MODx.grid.multiTVgrid{/literal}{$tv->id}{literal} = function(config) {
    config = config || {};
    //var cols=[this.sm];
    var cols=[];
    // add empty pathconfig (source) to array to match number of col in renderimage
    var renderer = null;
    //var editor = null;
	for(i = 0; i <  config.columns.length; i++) {
        renderer = config.columns[i]['renderer'];
        if (typeof renderer != 'undefined'){
            config.columns[i]['renderer'] = {fn:eval(renderer),scope:this};
        }
        editor = config.columns[i]['editor'];
        if (typeof editor != 'undefined'){
            editor = editor.replace('this.','');
            if (this[editor]){
                config.columns[i]['editor'] = this[editor](config.columns[i]);
            }
        } 
        cols.push(config.columns[i]);
    }
    
    config.columns=cols;    
        
	Ext.applyIf(config,{
	autoHeight: true,
    collapsible: true,
	resizable: true,
    store: 	new Ext.data.JsonStore({
        fields : config.fields
    }), // define the data store in a separate variable		
    loadMask: true,
    ddGroup:'{/literal}{$tv->id}{literal}_gridDD',
    enableDragDrop: true, // enable drag and drop of grid rows
	viewConfig: {
        emptyText: 'No items found',
        sm: new Ext.grid.RowSelectionModel({
            singleSelect:true
            }),
        forceFit: true,
		autoFill: true
    }, 
	columns: config.columns, // define grid columns in a separate variable
    listeners: {
        "render": {
            scope: this,
            fn: function(grid) {
            
            //Special Handling of Keystrokes within cell-editor, needs more improvements    
            var sm=grid.getSelectionModel();
                
            sm.onEditorKey = function(n, l) {
        var d = l.getKey(),
            h, i = this.grid,
            p = i.lastEdit,
            j = i.activeEditor,
            b = l.shiftKey,
            o, p, a, m;
        if (d == l.TAB) {
            l.stopEvent();
            if (j){
                j.completeEdit();
            if (b) {
                h = i.walkCells(j.row, j.col - 1, -1, this.acceptsNav, this)
            } else {
                h = i.walkCells(j.row, j.col + 1, 1, this.acceptsNav, this)
            }            
            }

        } else {
            if (d == l.ENTER) {
                if (this.moveEditorOnEnter !== false) {
                    if (b) {
                        h = i.walkCells(p.row - 1, p.col, -1, this.acceptsNav, this)
                    } else {
                        h = i.walkCells(p.row + 1, p.col, 1, this.acceptsNav, this)
                    }
                }
            }
        }
        if (h) {
            a = h[0];
            m = h[1];
            
            if (i.isEditor && i.editing) {
                o = i.activeEditor;
                if (o && o.field.triggerBlur) {
                    //console.log(o.field);
                }
            }
            this.onEditorSelect(a, p.row);
            i.startEditing(a, m)
        }
    };

            // Enable sorting Rows via Drag & Drop
            // this drop target listens for a row drop
            //  and handles rearranging the rows

              var ddrow = new Ext.dd.DropTarget(grid.container, {
                  ddGroup : '{/literal}{$tv->id}{literal}_gridDD',
                  copy:false,
                  notifyDrop : function(dd, e, data){
                      var ds = grid.store;

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
		
		//this.setWidth('99%');
        
		//this.syncSize();
                   // load the grid store
                  //  after the grid has been rendered
                  //store.load();
       }
   }
}

		,tbar: [
        {/literal}{if $customconfigs.disable_add_item != '1'}{literal}
        {
            text: '[[%migx.add]]',
			handler: this.addItem
        }
        {/literal}{/if}{literal}        
        {/literal}{if $properties.previewurl != ''}{literal}
        {/literal}
        {if $customconfigs.disable_add_item != '1'}
        ,
        {/if}
        {literal}        
        {
            text: '[[%migx.preview]]',
			handler: this.preview
        }
        {/literal}{/if}{literal}
        
        {/literal}{if $customconfigs.tbar != ''}
        {if $customconfigs.disable_add_item != '1'}
        ,
        {/if}        
        {$customconfigs.tbar}
        {/if}{literal}
        ]        
    });
	
    MODx.grid.multiTVgrid{/literal}{$tv->id}{literal}.superclass.constructor.call(this,config)
    this._makeTemplates();
    this.getStore().pathconfigs=config.pathconfigs;
    
	this.loadData();
    this.on('click', this.onClick, this);  
};
Ext.extend(MODx.grid.multiTVgrid{/literal}{$tv->id}{literal},MODx.grid.LocalGrid,{
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
    {/literal}{$customconfigs.gridfunctions}{literal}
    
	,loadData: function(){
	    var items_string = Ext.get('tv{/literal}{$tv->id}{literal}').dom.value;
        var items = [];
        var item = {};
        try {
            items = Ext.util.JSON.decode(items_string);
        }
        catch (e){
        }
        
        if (items.length == 0){
            Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = '';     
        }
                
        this.autoinc = 0;
        for(i = 0; i <  items.length; i++) {
 		    item = items[i];
            if (item.MIGX_id){
                if (parseInt(item.MIGX_id)  > this.autoinc){
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
        
        
			
		//this.syncSize();
        //this.setWidth('100%');
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
	    var maxRecords =  parseInt('{/literal}{$customconfigs.maxRecords}{literal}');
        var add_items_directly = '{/literal}{$customconfigs.add_items_directly}{literal}';
        var s=this.getStore();
        if(maxRecords != 0 && s.getCount() >= maxRecords){
            alert ('[[%migx.max_records_alert]]');
            return;            
        }
        if (add_items_directly == '1'){
            this.addNewItem();    
        }else{
            this.loadWin(btn,e,s.getCount(),'a');    
        }
	}
    ,addNewItem: function(olditem){
            if (olditem){
                var json = '[' + Ext.util.JSON.encode(olditem) + ']';
            }else{
                var json = '{/literal}{$newitem}{literal}';
            }
       
            var items=Ext.util.JSON.decode(json);
            var item = items[0];            
            var s = this.getStore();
            var addNewItemAt = '{/literal}{$customconfigs.addNewItemAt}{literal}';
            
	        this.autoinc = parseInt(this.autoinc) +1; 
            s.loadData(items,true);
            idx=s.getCount()-1;
            var rec = s.getAt(idx);
            rec.set('MIGX_id',this.autoinc);
            item['MIGX_id'] = this.autoinc;
            rec.json = item;
          
            if (addNewItemAt == 'top'){
                s.insert(0,rec);
            }
            this.getView().refresh();
            
            var call_collectmigxitems = this.call_collectmigxitems;
            this.call_collectmigxitems=true;
            this.collectItems(); 
            this.call_collectmigxitems = call_collectmigxitems;       
    }
	,preview: function(btn,e) {
		var s=this.getStore();
		this.loadPreviewWin(btn,e,s.getCount(),'a');
	}    	
	,migx_remove: function() {
        var _this=this;
		Ext.Msg.confirm(_('warning') || '','[[%migx.remove_confirm]]' || '',function(e) {
            if (e == 'yes') {
				
        var sels = _this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;
        for (var i=0;i<sels.length;i++) {
            var id = sels[i].id;
            var index = _this.getStore().findBy(function(record){if(record.id == id){return true;}});
            _this.getStore().removeAt(index);   
        }                
                
                _this.getView().refresh();
		        _this.collectItems();
                MODx.fireResourceFormChange();
               }
            }),this;		
	}
	,refresh: function() {
        return;
    }       
	,migx_update: function(btn,e) {
      this.loadWin(btn,e,this.menu.recordIndex,'u');
    }
	,migx_duplicate: function(btn,e) {
	    var add_items_directly = '{/literal}{$customconfigs.add_items_directly}{literal}';
        if (add_items_directly == '1'){
            var s=this.getStore();
            var rec = s.getAt(this.menu.recordIndex);
            this.addNewItem(rec.json);    
        }else{
            this.loadWin(btn,e,this.menu.recordIndex,'d'); 
        }       
       
      
    } 

	,loadFromSource: function(btn,e,extra_params) {
	   
        var recordIndex = 'none';
        if (this.menu.recordIndex == 0){
            recordIndex = 0; 
        }else{
            recordIndex = this.menu.recordIndex || 'none';     
        }
                
        var extra_params = extra_params || ''; 
        MODx.Ajax.request({
            url: '{/literal}{$config.connectorUrl}{literal}'
            ,params: {
                action: 'mgr/loadfromsource'
                ,resource_id: '{/literal}{$resource.id}{literal}'
				,co_id: '{/literal}{$connected_object_id}{literal}'
                ,tv_name: '{/literal}{$tv->name}{literal}'
                ,items: Ext.get('tv{/literal}{$tv->id}{literal}').dom.value
                ,record_index: recordIndex
                ,extra_params: extra_params
            }
            ,listeners: {
                'success': {fn:function(res){
                    if (res.message==''){
                        var items = res.object;
                        var item = null;
                        Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = Ext.util.JSON.encode(items);
                        this.autoinc = 0;
                        for(i = 0; i <  items.length; i++) {
 		                    item = items[i];
                            if (item.MIGX_id){
                                
                                if (parseInt(item.MIGX_id)  > this.autoinc){
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
                        var call_collectmigxitems = this.call_collectmigxitems;
                        this.call_collectmigxitems=true;
                        this.collectItems(); 
                        this.call_collectmigxitems = call_collectmigxitems;                                             
                    }
                    
                },scope:this}
            }
        });          
	}      
	,loadWin: function(btn,e,index,action) {
	    var resource_id = '{/literal}{$resource.id}{literal}';
        var co_id = '{/literal}{$connected_object_id}{literal}';
        var object_id = '{/literal}{$request.object_id}{literal}';
        var input_prefix = Ext.id(null,'inp_');
        {/literal}{if $properties.autoResourceFolders == 'true'}{literal}
        if (resource_id == 0){
            alert ('[[%migx.save_resource]]');
            return;
        }
        {/literal}{/if}{literal}        

        var isnew = (action == 'u') ? '0':'1';
        var isduplicate = (action == 'd') ? '1':'0';
       
        if (action == 'e'){
           var json=Ext.get('tv{/literal}{$tv->id}{literal}').dom.value;
           var data={};
           isnew = '0';
        }else if (action == 'a'){
           var json='{/literal}{$newitem}{literal}';
           var data=Ext.util.JSON.decode(json);
        }else{
		   var s = this.getStore();
           var rec = s.getAt(index)            
           var data = rec.data;
           var json = Ext.util.JSON.encode(rec.json);
        }
        
	
        var win_xtype = 'modx-window-tv-item-update-{/literal}{$tv->id}{literal}';
        this.windows[win_xtype] = null;
        /*        
		if (this.windows[win_xtype]){
			this.windows[win_xtype].fp.autoLoad.params.tv_id='{/literal}{$tv->id}{literal}';
			this.windows[win_xtype].fp.autoLoad.params.resource_id=resource_id;
            this.windows[win_xtype].fp.autoLoad.params.co_id=co_id;
            this.windows[win_xtype].fp.autoLoad.params.object_id=object_id;
            this.windows[win_xtype].fp.autoLoad.params.input_prefix=input_prefix;
            this.windows[win_xtype].fp.autoLoad.params.tv_name='{/literal}{$tv->name}{literal}';
            this.windows[win_xtype].fp.autoLoad.params.configs='{/literal}{$properties.configs}{literal}';
		    this.windows[win_xtype].fp.autoLoad.params.itemid=index;
            this.windows[win_xtype].fp.autoLoad.params.record_json=json;
            this.windows[win_xtype].fp.autoLoad.params.autoinc=this.autoinc;
            this.windows[win_xtype].fp.autoLoad.params.isnew=isnew;
            this.windows[win_xtype].fp.autoLoad.params.isduplicate=isduplicate;
			this.windows[win_xtype].grid=this;
            this.windows[win_xtype].action=action;
		}
        */
		this.loadWindow(btn,e,{
            xtype: win_xtype
            ,record: data
			,grid: this
            ,action: action
			,baseParams : {
				record_json:json,
			    action: 'mgr/fields',
				tv_id: '{/literal}{$tv->id}{literal}',
				tv_name: '{/literal}{$tv->name}{literal}',
                configs: '{/literal}{$properties.configs}{literal}',
				'class_key': 'modDocument',
                'wctx':'{/literal}{$myctx}{literal}',
				itemid : index,
                autoinc : this.autoinc,
                isnew : isnew,
                isduplicate : isduplicate,
                resource_id : resource_id,
                object_id: object_id,
                co_id : co_id,
                input_prefix: input_prefix,
                internal_action: action
			}
        });
    }
	,loadPreviewWin: function(btn,e,index,action) {
        var items = Ext.get('tv{/literal}{$tv->id}{literal}').dom.value;
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
	,loadIframeWin: function(btn,e,tpl) {
        var resource_id = '{/literal}{$resource.id}{literal}';
        var co_id = '{/literal}{$connected_object_id}{literal}';
        var object_id = '{/literal}{$request.object_id}{literal}';
        var url = '{/literal}{$config.connectorUrl}{literal}';
        var items = Ext.get('tv{/literal}{$tv->id}{literal}').dom.value;
        var jsonvarkey = '{/literal}{$properties.jsonvarkey}{literal}';
        if (jsonvarkey == ''){
            jsonvarkey = 'migx_outputvalue';
        }
        var win_xtype = 'modx-window-mi-iframe-{/literal}{$win_id}{literal}';
        var object_id_field = null;
    	if (this.windows[win_xtype]){
			//this.windows[win_xtype].fp.autoLoad.params.tv_id='{/literal}{$tv->id}{literal}';
			//this.windows[win_xtype].fp.autoLoad.params.tv_name='{/literal}{$tv->name}{literal}';
		    //this.windows[win_xtype].fp.autoLoad.params.itemid=index;
            //this.windows[win_xtype].fp.autoLoad.params.record_json=json;
            this.windows[win_xtype].src = url;
			this.windows[win_xtype].json=items;
            this.windows[win_xtype].jsonvarkey=jsonvarkey;
            //this.windows[win_xtype].action=action;
            this.windows[win_xtype].resource_id=resource_id;
            this.windows[win_xtype].co_id=co_id;
            this.windows[win_xtype].object_id = object_id;
            object_id_field = Ext.get('migx_iframewin_object_id_{/literal}{$win_id}{literal}');
            object_id_field.dom.value = object_id;            
            iframeTpl_field = Ext.get('migx_iframewin_iframeTpl_{/literal}{$win_id}{literal}');
            iframeTpl_field.dom.value = tpl;            
		}
		this.loadWindow(btn,e,{
            xtype: win_xtype
            ,src: url
            ,jsonvarkey:jsonvarkey
            ,json: items
			,grid: this
            //,action: action
            ,resource_id: resource_id
            ,object_id: object_id
            ,co_id: co_id
            ,title: '{/literal}{$customconfigs.iframeWindowTitle}{literal}'
            ,iframeTpl: tpl
        });
    } 
    ,moveToTop: function() {
        var s=this.getStore();
        var rec = s.getAt(this.menu.recordIndex);
        
        var sels = this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;
        for (var i=0;i<sels.length;i++) {
            s.insert(i,sels[i]);
        }         
        this.getView().refresh();
		this.collectItems();
        MODx.fireResourceFormChange();        
    }
    ,moveToBottom: function() {
        var s=this.getStore();
        var rec = s.getAt(this.menu.recordIndex);        
        idx=s.getCount()-1;
        var sels = this.getSelectionModel().getSelections();
        if (sels.length <= 0) return false;
        for (var i=0;i<sels.length;i++) {
            s.insert(idx,sels[i]);
        }          
        this.getView().refresh();
		this.collectItems();
        MODx.fireResourceFormChange();           
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
    ,setSelectedRecords:function(){
        this.selected_records = this.getSelectionModel().getSelections();    
    }        
	,updateSelected: function(column,value,stopRefresh){
        var col = null;	 
        var rec = null;        
        if (column && column.dataIndex){
            col = column.dataIndex;
 		    var records = this.selected_records;
            if (records){
                for(i = 0; i < records.length; i++) {
                rec = records[i];
                    rec.json[col] = value;
                    rec.set(col,value);           
                }            
            }
         }
         if (stopRefresh){
            
         }else{
             this.getView().refresh();
             this.collectItems();            
         }

         MODx.fireResourceFormChange();   
	}
    
    ,collectItems: function(){
		var items=[];
		// read jsons from grid-store-items 

        var griddata=this.store.data;
		for(i = 0; i <  griddata.length; i++) {
 			items.push(griddata.items[i].json);
        }

        if (this.call_collectmigxitems){
        items = Ext.util.JSON.encode(items); 
        MODx.Ajax.request({
            url: '{/literal}{$config.connectorUrl}{literal}'
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'collectmigxitems'
                ,resource_id: '{/literal}{$resource.id}{literal}'
				,co_id: '{/literal}{$connected_object_id}{literal}'
                ,tv_name: '{/literal}{$tv->name}{literal}'
                ,items: items 
                ,configs: '{/literal}{$properties.configs}{literal}'      
                
            }
            ,listeners: {
                'success': {fn:function(res){
                    if (res.message==''){
                        var items = res.object;
                        var item = null;
                        Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = Ext.util.JSON.encode(items);
                        this.autoinc = 0;
                        for(i = 0; i <  items.length; i++) {
 		                    item = items[i];
                            if (item.MIGX_id){
                                if (parseInt(item.MIGX_id)  > this.autoinc){
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
                    }
                    
                },scope:this}
            }
        });            
        }else{
        if (items.length >0){
           Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = Ext.util.JSON.encode(items); 
        }
        else{
           Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = '';  
        }            
        }
    	return;						 
    }
	,onClick: function(e){
		
        var t = e.getTarget();
        var elm = t.className.split(' ')[0];
        if(elm == 'controlBtn') {
            var handler = t.className.split(' ')[2];
            var col = t.className.split(' ')[3];
            var record = this.getSelectionModel().getSelected();
            var migxid = record.data.MIGX_id;
            if (migxid){
                this.menu.recordIndex = record.store.find('MIGX_id',migxid);        
            }else{
                this.menu.recordIndex = record.store.indexOfId(record.id);      
            }
            this.menu.record = record;
            var fn = eval(handler);
            fn = fn.createDelegate(this);
            fn(null,e,col);
            e.stopEvent();
 		}
	}       
});
Ext.reg('modx-grid-multitvgrid-{/literal}{$tv->id}{literal}',MODx.grid.multiTVgrid{/literal}{$tv->id}{literal});
{/literal}