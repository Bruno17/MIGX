{literal}

MODx.window.UpdateTvdbItem = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        title:'MIGX'
        ,id: '{/literal}modx-window-mi-grid-update-{$win_id}{literal}'
        ,width: '1000'
		,closeAction: 'hide'
        ,shadow: false
        ,resizable: true
        ,collapsible: true
        ,maximizable: true
        ,allowDrop: true
        ,height: '600'
        //,saveBtnText: _('done')
        ,forceLayout: true
        ,boxMaxHeight: '700'
        ,autoScroll: true
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
        /*
        ,keys: [{
            key: Ext.EventObject.ENTER
            ,fn: this.submit
            ,scope: this
        }]
        */		
        ,fields: []
    });
    MODx.window.UpdateTvdbItem.superclass.constructor.call(this,config);
    this.options = config;
    this.config = config;

    //this.on('show',this.onShow,this);
    this.addEvents({
        success: true
        ,failure: true
        ,beforeSubmit: true
		,hide:true
		//,show:true
    });
    this._loadForm();	
};
Ext.extend(MODx.window.UpdateTvdbItem,Ext.Window,{
    submit: function() {
        var v = this.fp.getForm().getValues();
        if (this.fp.getForm().isValid()) {
            if (this.action == 'd'){
                MODx.fireResourceFormChange();     
            }
            if (this.action == 'u'){
                /*update record*/
            }else{
                /*append record*/
            }

            var fields = Ext.util.JSON.decode(v['mulititems_grid_item_fields']);
            var item = {};
            var tvid = '';
            if (fields.length>0){
                for (var i = 0; i < fields.length; i++) {
                    tvid = (fields[i].tv_id);
                    if (v['tv'+tvid+'_prefix']) v['tv'+tvid]=v['tv'+tvid+'_prefix']+v['tv'+tvid];//url-TV support
                    item[fields[i].field]=v['tv'+tvid+'[]'] || v['tv'+tvid] || '';							
                }
                //we store the item.values to rec.json because perhaps sometimes we can have different fields for each record
            }					
			
            //console.log(this.config);
            MODx.Ajax.request({
            url: this.grid.url
            ,params: {
                action: 'mgr/migxdb/update'
                ,data: Ext.util.JSON.encode(item)
				,configs: this.grid.configs
                ,resource_id: this.grid.resource_id
                ,co_id: this.grid.co_id
                ,object_id: this.baseParams.object_id
                ,tv_id: this.baseParams.tv_id
                ,wctx: this.baseParams.wctx
            }
            ,listeners: {
                'success': {fn:this.onSubmitSuccess,scope:this}
            }
        });
        return true;
        }
        return false;
    },
    onSubmitSuccess: function(){
            this.grid.refresh();
            //this.grid.collectItems();
            //this.onDirty();
            this.fp.getForm().reset();
            this.hide();
            return true;
    },
    _loadForm: function() {
        //if (this.checkIfLoaded(this.config.record || null)) { return false; }
        this.fp = this.createForm({
            url: this.config.url
            ,baseParams: this.config.baseParams || { action: this.config.action || '' }
            //,items: this.config.fields || []
        });
		//console.log('renderForm');
        this.add(this.fp);
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
            ,errorReader: MODx.util.JSONReader
            ,url: this.config.url
            ,baseParams: this.config.baseParams || {}
            ,fileUpload: this.config.fileUpload || false
        });
        return new MODx.panel.MidbGridUpdate{/literal}{$win_id}{literal}(config);
    }
    ,switchForm: function() {
        var v = this.fp.getForm().getValues();
        //console.log(v);
        var fields = Ext.util.JSON.decode(v['mulititems_grid_item_fields']);
        var item = {};
        var tvs = {};        
        var tvid = '';
        if (fields.length>0){
            for (var i = 0; i < fields.length; i++) {
                
                tvid = (fields[i].tv_id);
                tvs['tv'+tvid] = true;
                item[fields[i].field]=v['tv'+tvid+'[]'] || v['tv'+tvid] || '';							
            }
        }

            if (typeof(Tiny) != 'undefined') {
                var ed = null;
                for (edId in tinyMCE.editors){
                    ed = tinyMCE.editors[edId];
                    if (typeof (ed) == 'object'){
                        if (tvs[ed.id]){
                            ed.remove();
                        }         
                    }
                }
            }
        //console.log(item);			        
        this.fp.autoLoad.params.record_json=Ext.util.JSON.encode(item);
        this.fp.doAutoLoad();        
    }
    
    ,onShow: function() {
        //console.log('onshow');
        if (this.fp.isloading) return;
        this.fp.isloading=true;
        this.fp.autoLoad.params.record_json=this.baseParams.record_json;
        this.fp.doAutoLoad();
    }

});
Ext.reg('modx-window-tv-dbitem-update-{/literal}{$win_id}{literal}',MODx.window.UpdateTvdbItem);

MODx.panel.MidbGridUpdate{/literal}{$win_id}{literal} = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'migxdb-panel-object-{/literal}{$win_id}{literal}'
		,title: ''
        ,url: config.url
        ,baseParams: config.baseParams	
        ,class_key: ''
        ,bodyStyle: 'padding: 15px;'
        //,autoSize: true
        ,autoLoad: this.autoload(config)
        ,width: '950'
        ,listeners: {
            //'beforeSubmit': {fn:this.beforeSubmit,scope:this},
            //'success': {fn:this.success,scope:this}
			'load': {fn:this.load,scope:this}
        }		
    });
 	MODx.panel.MidbGridUpdate{/literal}{$win_id}{literal}.superclass.constructor.call(this,config);
	
	//this.addEvents({ load: true });
};
Ext.extend(MODx.panel.MidbGridUpdate{/literal}{$win_id}{literal},MODx.FormPanel,{
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
	 ,load: function() {
		//MODx.loadRTE();
        //console.log('load');
		
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
		//this.syncSize();
		//this.popwindow.syncSize();
		return '';
	 }
});
Ext.reg('migxdb-panel-object',MODx.panel.MidbGridUpdate{/literal}{$win_id}{literal});

{/literal}