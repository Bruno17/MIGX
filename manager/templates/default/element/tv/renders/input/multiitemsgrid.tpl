<input id="tv{$tv->id}" name="tv{$tv->id}" type="hidden" class="textfield" value="{$tv->get('value')|escape}"{$style} tvtype="{$tv->type}" />
<div id="tvpanel{$tv->id}" style="width:650px">
</div>
<div id="tvpanel2{$tv->id}">
</div>
<br/>

<script type="text/javascript">
    // <![CDATA[
    {literal}

MODx.grid.multiTVgrid = function(config) {
    config = config || {};
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
        sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
        forceFit: true,
		autoFill: true
    }, 
	columns: config.columns, // define grid columns in a separate variable
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
                         }
						grid.collectItems();

 
                        // ************************************
                      }
                   }) 
		
		this.setWidth('95%');
		//this.syncSize();
                   // load the grid store
                  //  after the grid has been rendered
                  //store.load();
       }
   }
}

		,tbar: [{
            text: 'Add Item',
			handler: this.addItem
        }]        
		,viewConfig: {
            forceFit:true
        }
    });
	
    MODx.grid.multiTVgrid.superclass.constructor.call(this,config)
	//this.getStore().on('load',this.onStoreLoad,this);
	this.loadData();
};
Ext.extend(MODx.grid.multiTVgrid,MODx.grid.LocalGrid,{
    _renderUrl: function(v,md,rec) {
        return '<a href="'+v+'" target="_blank">'+rec.data.pagetitle+'</a>';
    }
    ,renderImage : function(val){
		if (val.substr(0,4) == 'http'){
			return '<img style="height:60px" src="' + val + '"/>' ;
		}        
		if (val != ''){
			return '<img src="{/literal}{$_config.connectors_url}{literal}system/phpthumb.php?h=60&src=' + val + '" alt="" />';
		}
		return val;
	}
    ,renderPreview : function(val,md,rec){
		console.log(rec);
		return val;
	}

	,loadData: function(){
	    var items_string = Ext.get('tv{/literal}{$tv->id}{literal}').dom.value;
        var items = [];
        try {
            items = Ext.util.JSON.decode(items_string);
        }
        catch (e){
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
	,addItem: function() {
		var items=Ext.util.JSON.decode('{/literal}{$newitem}{literal}')
		this.getStore().loadData(items,true);
				
	}	
	,remove: function() {
        var _this=this;
		Ext.Msg.confirm(_('warning') || '','Remove Item?' || '',function(e) {
            if (e == 'yes') {
				_this.getStore().removeAt(_this.menu.recordIndex);
                _this.getView().refresh();
		        _this.collectItems();	
                }
            }),this;		
	}   
	,update: function(btn,e) {

        var s = this.getStore();
        var rec = s.getAt(this.menu.recordIndex);
		var win_xtype = 'modx-window-tv-item-update';
		if (this.windows[win_xtype]){
			this.windows[win_xtype].fp.autoLoad.params.tv_id='{/literal}{$tv->id}{literal}';
		    this.windows[win_xtype].fp.autoLoad.params.itemid=this.menu.recordIndex;
			this.windows[win_xtype].grid=this;
		}
		this.loadWindow(btn,e,{
            xtype: win_xtype
            ,record: this.menu.record
			,grid: this
			,baseParams : {
				record_json:Ext.util.JSON.encode(rec.json),
			    action: 'mgr/fields',
				tv_id: '{/literal}{$tv->id}{literal}',
				'class_key': 'modDocument',
				itemid : this.menu.recordIndex	
			}
        });
    }
    ,getMenu: function() {
		var n = this.menu.record; 
        //var cls = n.cls.split(',');
        var m = [];
        m.push({
            text: 'edit'
            ,handler: this.update
        });
        m.push('-');
        m.push({
            text: 'remove'
            ,handler: this.remove
        });
        //this.addContextMenuItem(m);
		return m;
    }
	,collectItems: function(){
		var items=[];
		
		// read jsons from grid-store-items 
		//console.log(this);
        var griddata=this.store.data;
		for(i = 0; i <  griddata.length; i++) {
 			items.push(griddata.items[i].json);
        }
		//console.log(Ext.get('tv{/literal}{$tv->id}{literal}'));
        Ext.get('tv{/literal}{$tv->id}{literal}').dom.value = Ext.util.JSON.encode(items);
		return;						 
    }
});
Ext.reg('modx-grid-multitvgrid',MODx.grid.multiTVgrid);

MODx.window.UpdateTvItem = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title: _('property_update')
        ,id: 'modx-window-mi-grid-update' 
        ,width: '1000px'
		,closeAction: 'hide'
        ,shadow: true
        ,resizable: true
        ,collapsible: true
        ,maximizable: true
        ,autoHeight: true
        ,allowDrop: true
        //,saveBtnText: _('done')
        ,forceLayout: true
        ,buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { this.hide(); }
        },{
            text: config.saveBtnText || _('done')
            ,scope: this
            ,handler: this.submit
        }]
        ,record: {}
		,grid: null
		,record_json: ''
        ,keys: [{
            key: Ext.EventObject.ENTER
            ,fn: this.submit
            ,scope: this
        }]		
        ,fields: []
    });
    MODx.window.UpdateTvItem.superclass.constructor.call(this,config);
    this.options = config;
    this.config = config;

    this.on('show',this.onShow,this);
    this.addEvents({
        success: true
        ,failure: true
        ,beforeSubmit: true
		,hide:true
		,show:true
    });
    this._loadForm();	
};
Ext.extend(MODx.window.UpdateTvItem,Ext.Window,{
    submit: function() {
        var v = this.fp.getForm().getValues();
        if (this.fp.getForm().isValid()) {
            var s = this.grid.getStore();
            var rec = s.getAt(this.grid.menu.recordIndex);
            var fields = Ext.util.JSON.decode(v['mulititems_grid_item_fields']);
            var item = {};
            var tvid = '';
            if (fields.length>0){
                for (var i = 0; i < fields.length; i++) {
                    tvid = (fields[i].tv_id);
                    item[fields[i].field]=v['tv'+tvid+'[]'] || v['tv'+tvid] || '';							
                    //set defined record-fields to its new value
                    rec.set(fields[i].field,item[fields[i].field])
                }
                //we store the item.values to rec.json because perhaps sometimes we can have different fields for each record
                rec.json=item;
            }					
            this.grid.getView().refresh();
            this.grid.collectItems();
            //this.onDirty();			
			
            if (this.fireEvent('success',v)) {
                this.fp.getForm().reset();
                this.hide();
                return true;
            }
        }
        return false;
    },
    _loadForm: function() {
        //if (this.checkIfLoaded(this.config.record || null)) { return false; }
		this.fp = this.createForm({
            url: this.config.url
            ,baseParams: this.config.baseParams || { action: this.config.action || '' }
            ,items: this.config.fields || []
        });
        this.renderForm();
    }	
    ,createForm: function(config){
        config = config || {};
        Ext.applyIf(config,{
            labelAlign: this.config.labelAlign || 'right'
            ,labelWidth: this.config.labelWidth || 100
            ,frame: this.config.formFrame || true
            ,popwindow : this
			,border: false
            ,bodyBorder: false
            ,autoHeight: true
            ,errorReader: MODx.util.JSONReader
            ,url: this.config.url
            ,baseParams: this.config.baseParams || {}
            ,fileUpload: this.config.fileUpload || false
        });
        return new MODx.panel.MiGridUpdate(config);

		
    }
    ,renderForm: function() {
		this.add(this.fp);
        //this.fp.doAutoLoad();
		
    }		
    ,onShow: function() {
        if (this.fp.isloading) return;
        this.fp.isloading=true;
        var s = this.grid.getStore();
        var rec = s.getAt(this.grid.menu.recordIndex);
        this.fp.autoLoad.params.record_json=Ext.util.JSON.encode(rec.json);
        this.fp.doAutoLoad();
    }

});
Ext.reg('modx-window-tv-item-update',MODx.window.UpdateTvItem);

MODx.panel.MiGridUpdate = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'xdbedit-panel-object-{/literal}{$tv->id}{literal}'
		,title: _('template_variables')
        ,url: config.url
        ,baseParams: config.baseParams	
        ,class_key: ''
        ,bodyStyle: 'padding: 15px;'
        ,autoSize: true
        ,autoLoad: this.autoload(config)
        ,width: '1000px'
        ,listeners: {
            //'beforeSubmit': {fn:this.beforeSubmit,scope:this},
            'success': {fn:this.success,scope:this}
			,'load': {fn:this.load,scope:this}
        }		
    });
 	MODx.panel.MiGridUpdate.superclass.constructor.call(this,config);
	
	//this.addEvents({ load: true });
};
Ext.extend(MODx.panel.MiGridUpdate,MODx.FormPanel,{
    autoload: function(config) {
		this.isloading=true;
		var a = {
            url: MODx.config.assets_url+'components/multiitemsTV/connector.php'
            //url: config.url
			,method: 'GET'
            ,params: config.baseParams
            ,scripts: true
            ,callback: function() {
          		//console.log(this.isloaded);
				this.isloading=false;
				this.isloaded=true;
				this.fireEvent('load');
                //MODx.fireEvent('ready');
            }
            ,scope: this
        };
        return a;        	
    },scope: this
    
    ,
    setup: function() {

    }
    ,beforeSubmit: function(o) {
        //tinyMCE.triggerSave(); 
    }
    ,success: function(o) {
		this.doAutoLoad();
		var gf = Ext.getCmp('xdbedit-grid-objects');
		gf.isModified = true;
		gf.refresh();
     },
	 load: function() {
        //console.log('load');
		//MODx.loadRTE();
		//
		if (typeof(Tiny) != 'undefined') {
		    var s={};
            if (Tiny.config){
                s = Tiny.config || {};
                delete s.assets_path;
                delete s.assets_url;
                delete s.core_path;
                delete s.css_path;
                delete s.editor;
                delete s.id;
                delete s.mode;
                delete s.path;
                s.cleanup_callback = "Tiny.onCleanup";
                var z = Ext.state.Manager.get(MODx.siteId + '-tiny');
                if (z !== false) {
                    delete s.elements;
                }			
		    }
			s.mode = "specific_textareas";
            s.editor_selector = "modx-richtext";
		    s.language = "en";// de seems not to work at the moment
		    //console.log(s); 
            tinyMCE.init(s);				
		}
        //this.popwindow.width='1000px';
		//this.width='1000px';
		this.syncSize();
		this.popwindow.syncSize();
		
		return '';
	 }
});
Ext.reg('xdbedit-panel-object',MODx.panel.MiGridUpdate);

        MODx.load({
            xtype: 'modx-grid-multitvgrid'
            ,renderTo: 'tvpanel{/literal}{$tv->id}{literal}'
            ,tv: '{/literal}{$tv->id}{literal}'
            ,cls:'tv{/literal}{$tv->id}{literal}_items'
            ,id:'tv{/literal}{$tv->id}{literal}_items'
			,columns:Ext.util.JSON.decode('{/literal}{$columns}{literal}')
            ,fields:Ext.util.JSON.decode('{/literal}{$fields}{literal}')

            ,width: '97%'			
        });


{/literal}
</script>