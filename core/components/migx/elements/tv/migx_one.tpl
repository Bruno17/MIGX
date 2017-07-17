<input id="tv{$tv->id}" name="tv{$tv->id}" type="hidden" class="textfield" value="{$tv->get('value')|escape}"{$style} tvtype="{$tv->type}" />
<div id="tvpanel{$tv->id}" style="width:650px">
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
		        this.setWidth('99%');
		        //this.syncSize();
                //load the grid store
                //after the grid has been rendered
                //store.load();
            }
        }
    }

		,tbar: [{
            text: '{/literal}{$i18n.mig_edit}{literal}',
			handler: this.update
        }]        
		,viewConfig: {
            forceFit:true
        }
    });
	
    MODx.grid.multiTVgrid.superclass.constructor.call(this,config)
    this.getStore().pathconfigs=config.pathconfigs;
	this.loadData();
};
Ext.extend(MODx.grid.multiTVgrid,MODx.grid.LocalGrid,{
    _renderUrl: function(v,md,rec) {
        return '<a href="'+v+'" target="_blank">'+rec.data.pagetitle+'</a>';
    }
    ,renderImage : function(val, md, rec, row, col, s){
		var pc = s.pathconfigs[col];
		if (val.substr(0,4) == 'http'){
			return '<img style="height:60px" src="' + val + '"/>' ;
		}        
		if (val != ''){
			//return '<img src="{/literal}{$_config.connectors_url}{literal}system/phpthumb.php?h=60&src=' + val + '" alt="" />';
			
			return '<img src="'+MODx.config.connectors_url+'{/literal}system/phpthumb.php?h=60&src='+val+'&wctx={$ctx}&basePath='+pc.basePath+'&basePathRelative='+pc.basePathRelative+'&baseUrl='+pc.baseUrl+'&baseUrlRelative='+pc.baseUrlRelative+'{literal}" alt="" />';
		
		}
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
	,addItem: function(btn,e) {
		var s=this.getStore();
		this.loadWin(btn,e,s.getCount(),'a');
	}	
	,update: function(btn,e) {
      this.loadWin(btn,e,'0','u');
    }
	,loadWin: function(btn,e,index,action) {
        if (action == 'a'){
           var json='{/literal}{$newitem}{literal}';
           var data=Ext.util.JSON.decode(json);
        }else{
		   var s = this.getStore();
           var rec = s.getAt(index)            
           var data = rec.data;
           var json = Ext.util.JSON.encode(rec.json);
        }
		
        var win_xtype = 'modx-window-tv-item-update';
		if (this.windows[win_xtype]){
			this.windows[win_xtype].fp.autoLoad.params.tv_id='{/literal}{$tv->id}{literal}';
			this.windows[win_xtype].fp.autoLoad.params.tv_name='{/literal}{$tv->name}{literal}';
		    this.windows[win_xtype].fp.autoLoad.params.itemid=index;
            this.windows[win_xtype].fp.autoLoad.params.record_json=json;
			this.windows[win_xtype].grid=this;
            this.windows[win_xtype].action=action;
		}
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
				'class_key': 'modDocument',
				itemid : index
			}
        });
    }	
	,collectItems: function(){
		var items=[];
		// read jsons from grid-store-items 
        var griddata=this.store.data;
		for(i = 0; i <  griddata.length; i++) {
 			items.push(griddata.items[i].json);
        }
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
        ,action: 'u'
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
            if (this.action == 'u'){
                var idx = this.baseParams.itemid; 
            }else{
                /*append record*/
                var items=Ext.util.JSON.decode('{/literal}{$newitem}{literal}');
		        s.loadData(items,true);
                idx=s.getCount()-1;                
            }
            
            var rec = s.getAt(idx);
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
		
    }		
    ,onShow: function() {
        if (this.fp.isloading) return;
        this.fp.isloading=true;
        this.fp.autoLoad.params.record_json=this.baseParams.record_json;
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
            url: MODx.config.assets_url+'components/migx/connector.php'
            //url: config.url
			,method: 'POST'
            ,params: config.baseParams
            ,scripts: true
            ,callback: function() {
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
		//MODx.loadRTE();
		
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
		    //s.language = "en";// de seems not to work at the moment
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
			,pathconfigs:Ext.util.JSON.decode('{/literal}{$pathconfigs}{literal}')
            ,fields:Ext.util.JSON.decode('{/literal}{$fields}{literal}')
            ,width: '97%'			
        });


{/literal}
</script>