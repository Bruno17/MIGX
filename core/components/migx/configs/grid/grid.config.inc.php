<?php
/*
$lang = $this->modx->lexicon->fetch();
$migx_add = !empty($this->customconfigs['migx_add']) ? $this->customconfigs['migx_add'] : $lang['migx.add'];
*/
//$migx_add = $this->migxi18n['migx_add'];

$config_task = '{$customconfigs.task}';
$config_configs = '{$customconfigs.configs}';

include 'grid.actionbuttons.inc.php';
include 'grid.contextmenues.inc.php';
include 'grid.editors.inc.php';
include 'grid.renderer.inc.php';
include 'grid.winbuttons.inc.php';
include 'grid.filters.inc.php';

/*
$winfunctions['this.test'] = "
test: function(btn,e) {
    console.log('test');     
}";
*/

$gridfunctions['this.resetWinPosition'] = "
resetWinPosition: function(btn,e) {
    this.setWinPosition(10,10);     
}";

$gridfunctions['this.emptyTrash'] = "
emptyTrash: function(btn,e) {
    var _this=this;
    Ext.Msg.confirm(_('warning') || '','[[%migx.emptytrash_confirm]]',function(e) {
        if (e == 'yes') {    
            MODx.Ajax.request({
                url: _this.config.url
                ,params: {
                    action: 'mgr/migxdb/process'
                    ,processaction: 'emptytrash'                     
                    ,configs: _this.config.configs
                    ,resource_id: _this.config.resource_id
                    ,co_id: '[[+config.connected_object_id]]'                
                    ,reqConfigs: '[[+config.req_configs]]'
                }
                ,listeners: {
                    'success': {fn:function(r) {
                        _this.refresh();
                    },scope:_this}
                }
            });
        }
    }),this;           
    return true;
}
";

//$gridcontextmenus['remove_migx']['handler'] = 'this.remove';

$gridfunctions['this.handlePositionSelector'] = "
handlePositionSelector: function(n,e,col) {
    var btn,params;
    var column = col;
    //console.log(this.menu.record.json);
    var ro_json = this.menu.record.json[column+'_ro'];
    var ro = Ext.util.JSON.decode(ro_json);

    if (this.isPosSelecting){
        this.isPosSelecting = false;
        if (column != 'cancel'){
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'handlepositionselector'
                ,col: column
                ,co_id: '[[+config.connected_object_id]]' 
                ,new_pos_id: this.menu.record.id
                ,tv_type: this.config.tv_type
                ,object_id: this.posSelectingRecord.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
            }
            ,listeners: {
                'success': {fn: function(res){ 
                    res_object = res.object;
                    if (res_object.tv_type == 'migx'){
                        this.menu.record.json[column] = res_object.value;	
                        this.menu.record.json[column+'_ro'] = Ext.util.JSON.encode(res_object);
                        this.getView().refresh();
                        this.collectItems();
                        MODx.fireResourceFormChange();                        	                         
                        return;
                    }
                    this.refresh();
                    
                    },scope:this }
            }
        });                    
            
        }else{
        var view = this.getView();
        var result = view.renderBody();
        view.mainBody.update(result).setWidth(view.getTotalWidth()); 
        view.processRows(0, true);
        view.layout();     
        }

        
    }
    else{
        this.posSelectingRecord = this.menu.record;
        this.isPosSelecting = true;
        var view = this.getView();
        var result = view.renderBody();
        view.mainBody.update(result).setWidth(view.getTotalWidth()); 
        view.processRows(0, true);
        view.layout();               
        
     }

}	
";

$gridfunctions['this.selectSelectorOption'] = "
selectSelectorOption: function(n,e,col) {
    var btn,params;
    //console.log(this.menu.record);
    Ext.get('tv'+this.config.tv).dom.value = this.menu.record.data.id;
    var column = this.getColumnModel().getColumnAt(col);
    var ro_json = this.menu.record.json[column.dataIndex+'_ro'];
    var ro = Ext.util.JSON.decode(ro_json);
    
    return;
    if (ro.clickaction == 'showSelector'){
        //console.log(ro);
        params = {
            action: ro.clickaction
            ,col: column.dataIndex
            ,idx: ro.idx            
        }
        
        this.loadWin(btn,e,'u', Ext.util.JSON.encode(params));        
    }

    return;
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'handlecolumnswitch'
                ,col: column.dataIndex
                ,idx: ro.idx
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'                
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }	
";

$gridfunctions['gridfilter'] = "
    filter[[+name]]: function(tf,nv,ov) {
        var children = Ext.util.JSON.decode('[[+combochilds]]');
        var s = this.getStore();
        var value = tf.getValue();
        if (value == '_empty'){
            value = '';
        }
        s.baseParams.[[+name]] = value;
        var dddx_select = null;
        for(i = 0; i <  children.length; i++) {
 		    child = children[i];
            dddx_select = Ext.getCmp(child+'-migxdb-search-filter');
            if(typeof(dddx_select) != 'undefined'){
                dddx_select.baseParams.[[+name]] = tf.getValue();
                s.baseParams[dddx_select.getName()] = 'all';
                dddx_select.store.load({
                    callback: function() {
                        dddx_select.setValue('all');
                        //this.refreshChildren(true);
                    },scope:this
               });
            }

        }
       
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
";


$gridfunctions['this.addItem'] = "
addItem: function(btn,e) {
        var add_items_directly = '[[+config.add_items_directly]]';
        if (add_items_directly == '1'){
            this.addNewItem();    
        }else{
            this.loadWin(btn,e,'a');   
        }        
	}
";

$gridfunctions['this.addNewItem'] = "
addNewItem: function(item,tempParams) {
            if (item){
                var item = item;
                var items = [];
                items.push(item);
            }else{
                
                var items=Ext.util.JSON.decode('[[+newitem]]');
                var item = items[0];
            }
                    MODx.Ajax.request({
                        url: this.url
                        ,params: {
                            action: 'mgr/migxdb/update'
                            ,data: Ext.util.JSON.encode(item)
				            ,configs: this.configs
                            ,resource_id: this.resource_id
                            ,co_id: this.co_id
                            ,object_id: 'new'
                            ,tv_id: this.baseParams.tv_id
                            ,wctx: this.baseParams.wctx
                            ,tempParams: tempParams || ''
                        }
                        ,listeners: {
                            'success': {
                                fn:function(){
                                    this.refresh();
                                }
                                ,scope:this} 
                        }
                    });                      
	}
";

$gridfunctions['this.preview'] = "
preview: function(btn,e) {
		var s=this.getStore();
		this.loadPreviewWin(btn,e,s.getCount(),'a');
	}    	
";

$gridfunctions['this.uploadImages'] = "
uploadImages: function(btn,e) {
		var s=this.getStore();
        var tpl = 'ajaxupload.html';
		this.loadIframeWin(btn,e,tpl);
	}    	
";

$gridfunctions['this.remove'] = "
remove: function() {
        var _this=this;
		Ext.Msg.confirm(_('warning') || '','[[%migx.remove_confirm]]',function(e) {
            if (e == 'yes') {
				_this.getStore().removeAt(_this.menu.recordIndex);
                _this.getView().refresh();
		        _this.collectItems();
                MODx.fireResourceFormChange();	
                }
            }),this;		
	}   
";

$gridfunctions['this.update'] = "
update: function(btn,e) {
      this.loadWin(btn,e,'u');
    }
";

$gridfunctions['this.duplicate'] = "
duplicate: function(btn,e) {
      params = {
          duplicate: '1',
          button: 'duplicate',
          original_id: this.menu.record.id
      }          
      this.loadWin(btn,e,'d',Ext.util.JSON.encode(params));
    }
";

$gridfunctions['this.addbefore'] = "
addBefore: function(btn,e) {
      params = {
          button: 'addbefore',
          original_id: this.menu.record.id
      }
        var add_items_directly = '[[+config.add_items_directly]]';
        if (add_items_directly == '1'){
            this.addNewItem(false,Ext.util.JSON.encode(params));    
        }else{
            this.loadWin(btn,e,'a',Ext.util.JSON.encode(params));   
        }      
    }
";

$gridfunctions['this.addafter'] = "
addAfter: function(btn,e) {
      params = {
          button: 'addafter',
          original_id: this.menu.record.id
      }          
     
        var add_items_directly = '[[+config.add_items_directly]]';
        if (add_items_directly == '1'){
            this.addNewItem(false,Ext.util.JSON.encode(params));    
        }else{
            this.loadWin(btn,e,'a',Ext.util.JSON.encode(params));  
        }         
    }
";

$gridfunctions['this.toggleDeleted'] = "
    toggleDeleted: function(btn,e) {
        var s = this.getStore();
        if (btn.pressed) {
            s.setBaseParam('showtrash',1);
            btn.setText(_('migx.show_normal'));
        } else {
            s.setBaseParam('showtrash',0);
            btn.setText(_('migx.show_trash'));
        }
        this.getBottomToolbar().changePage(1);
        s.removeAll();
        this.refresh();
    }
";

$gridfunctions['this.handleColumnSwitch'] = "
handleColumnSwitch: function(n,e,col) {
    
    var btn,params;
    var column = col;
    //console.log(this.menu.record.json);
    var ro_json = this.menu.record.json[column+'_ro'];
    var ro = Ext.util.JSON.decode(ro_json);
    
    if (ro.clickaction == 'selectFromGrid'){
        //console.log(ro);
        params = {
            action: ro.clickaction
            ,col: col
            ,selectorconfig: ro.selectorconfig
            ,idx: ro.idx            
        }
        
        this.loadWin(btn,e,'u', Ext.util.JSON.encode(params));
        return false;        
    }

        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'handlecolumnswitch'
                ,col: column
                ,idx: ro.idx
                ,tv_type: this.config.tv_type
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'                
            }
            ,listeners: {
                'success': {fn: function(res){ 
                    
                    res_object = res.object;
                    if (res_object.tv_type == 'migx'){
                        this.selected_records = this.getSelectionModel().getSelections(); 
                        var columnobject = {dataIndex: column};
                        this.updateSelected(columnobject,res_object.value,true);
                        columnobject = {dataIndex: column+'_ro'};
                        this.updateSelected(columnobject,Ext.util.JSON.encode(res_object));
                        return;
                    }
                    else{
                        this.refresh();
                    }
                    
                    
                    },scope:this }
            }
        });
        return false;
    }	
";

$gridfunctions['this.publishObject'] = "
publishObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/update'
				,task: 'publish'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'                
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }	
";

$gridfunctions['this.unpublishObject'] = "
unpublishObject: function() {
 		MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/update'
				,task: 'unpublish'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'                
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }    
";

$gridfunctions['this.activateObject'] = "
activateObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'activaterelation'                
				,task: 'activate'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }	
";

$gridfunctions['this.deactivateObject'] = "
deactivateObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'activaterelation'                   
				,task: 'deactivate'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }	
";

$gridfunctions['this.publishSelected'] = "
publishSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'bulkupdate' 
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'                     
				,configs: this.config.configs
				,task: 'publish'
                ,objects: cs
                ,reqConfigs: '[[+config.req_configs]]'
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
";
$gridfunctions['this.unpublishSelected'] = "
unpublishSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'bulkupdate'                     
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'                
				,task: 'unpublish'
                ,objects: cs
                ,reqConfigs: '[[+config.req_configs]]'
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
";
$gridfunctions['this.deleteSelected'] = "
deleteSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'bulkupdate'                     
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'                
				,task: 'delete'
                ,objects: cs
                ,reqConfigs: '[[+config.req_configs]]'
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
";
$gridfunctions['this.deleteObject'] = "
deleteObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/update'
				,task: 'delete'
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'                
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,reqConfigs: '[[+config.req_configs]]'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
";
$gridfunctions['this.recallObject'] = "
recallObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/update'
				,task: 'recall'
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'                
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,reqConfigs: '[[+config.req_configs]]'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
";

$gridfunctions['this.csvExport'] = "
	csvExport: function(btn,e) {
		var s = this.getStore();
		var code, type, category, study_type, ebs_state;
		var box = Ext.MessageBox.wait('Preparing ...', _('migx.export_current_view'));
        var params = s.baseParams;
        var o_action = params.action || '';
        var o_processaction = params.processaction || '';
        var configs = this.config.configs;
        
        params.action = 'mgr/migxdb/process';
        params.processaction = 'export';
        params.configs = this.config.configs;
        params.resource_id = '[[+config.resource_id]]';     

		MODx.Ajax.request({
			url : this.config.url,
			params: params,
			listeners: {
				'success': {fn:function(r) {
					 location.href = this.config.url+'?action=mgr/migxdb/process&configs='+configs+'&processaction=export&download='+r.message+'&id='+id+'&HTTP_MODAUTH=' + MODx.siteId;
					 box.hide();
				},scope:this}
			}
		});
        
        params.action = o_action;
        params.processaction = o_processaction;
        
		return true;
	}
";


$gridfunctions['this.removeObject'] = "
removeObject: function() {
        var _this=this;
		Ext.Msg.confirm(_('warning') || '','[[%migx.remove_confirm]]',function(e) {
            if (e == 'yes') {
                MODx.Ajax.request({
                    url: _this.config.url
                    ,params: {
                        action: 'mgr/migxdb/process'
                        ,processaction: 'remove'
				        ,task: 'removeone'
                        ,resource_id: _this.config.resource_id
                        ,co_id: '[[+config.connected_object_id]]'                        
                        ,object_id: _this.menu.record.id
				        ,configs: _this.config.configs
                        ,reqConfigs: '[[+config.req_configs]]'                        
                    }
                    ,listeners: {
                        'success': {fn:_this.refresh,scope:_this}
                    }
                });  
            }
        }),this;    
    }
";

$gridfunctions['this.publishTargetObject'] = "
publishTargetObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'publishtarget'
				,task: 'publish'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }	
";

$gridfunctions['this.unpublishTargetObject'] = "
unpublishTargetObject: function() {
 		MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'publishtarget'
				,task: 'unpublish'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'
                ,reqConfigs: '[[+config.req_configs]]'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }    
";

$img = '<img src="' . "'+data.image+'" . '" alt="" onclick="Ext.getCmp(' . "\'gal-album-items-view\'" . ').ssWin.hide();" />';
$win_id = '{$win_id}';
$gridfunctions['this.showScreenshot'] = "
    showScreenshot: function(id) {
        //var data = this.lookup['gal-item-'+id];
        //if (!data) return false;
        
        //console.log(this.menu.record.json);
        var data = this.menu.record.json;
        
        if (!this.ssWin) {
            this.ssWin = new Ext.Window({
                layout:'fit'
                ,width: 600
                ,height: 450
                ,closeAction:'hide'
                ,plain: true
                ,items: [{
                    id: 'gal-item-ss-[[+config.win_id]]'
                    ,html: ''
                }]
                ,buttons: [{
                    text: _('close')
                    ,handler: function() { this.ssWin.hide(); }
                    ,scope: this
                }]
            });
        }
        this.ssWin.show();
        this.ssWin.setSize(data.image_width,data.image_height);
        this.ssWin.center();
        this.ssWin.setTitle(data.name);
        Ext.get('gal-item-ss-[[+config.win_id]]').update('{$img}');
    }     
";

$gridfunctions['this.uploadFiles'] = "
    uploadFiles: function(btn,e) {
        if (!this.uploader) {
            this.uploader = new MODx.util.MultiUploadDialog.Dialog({
                url: MODx.config.connector_url
                ,base_params: {
                    action: 'browser/file/upload'
                    ,wctx: MODx.ctx || ''
                    ,source: [[+config.media_source_id]]
                    ,path:'/'
                    ,configs: this.config.configs
                    ,object_id:'[[+config.connected_object_id]]'
                    ,reqConfigs: '[[+config.req_configs]]'
                }
                ,cls: 'ext-ux-uploaddialog-dialog modx-upload-window'
            });
            //this.uploader.on('show',this.beforeUpload,this);
            this.uploader.on('uploadsuccess',this.uploadSuccess,this);
            //this.uploader.on('uploaderror',this.uploadError,this);
            //this.uploader.on('uploadfailed',this.uploadFailed,this);
        }
        this.uploader.base_params.source = [[+config.media_source_id]];
        this.uploader.show(btn);
    } 	
";

$gridfunctions['this.uploadSuccess'] = "
    uploadSuccess: function() {
        this.loadFromSource();
    } 	
";

$gridfunctions['this.loadFromSource_db'] = "
	loadFromSource: function(btn,e,extra_params) {
	   
        var extra_params = extra_params || ''; 
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'loadfromsource'                     
                ,configs: this.config.configs
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]'                
                ,reqConfigs: '[[+config.req_configs]]'
                ,source: '[[+config.media_source_id]]'
                ,extra_params: extra_params
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.refresh();
                },scope:this}
            }
        });          
	}
";

$gridfunctions['this.migx_removeMigxAndImage'] = "
    migx_removeMigxAndImage: function() {
        var _this=this;
		Ext.Msg.confirm(_('warning') || '','[[%migx.remove_confirm]]' || '',function(e) {
            if (e == 'yes') {
                _this.loadFromSource(null,null,'removeimage');				
            }
        }),this;		        
    } 	
";

$gridfunctions['this.exportMigxItems'] = "
	exportMigxItems: function(btn,e) {
      this.loadWin(btn,e,this.menu.recordIndex,'e');
    }
";

$gridfunctions['this.selectImportFile'] = "
    selectImportFile: function(btn,e) {
            this.browser = MODx.load({
                xtype: 'modx-browser'
                ,closeAction: 'close'
                ,id: Ext.id()
                ,multiple: true
                ,source: [[+config.media_source_id]] || MODx.config.default_media_source
                ,hideFiles: this.config.hideFiles || false
                ,rootVisible: this.config.rootVisible || false
                ,allowedFileTypes: this.config.allowedFileTypes || ''
                ,wctx: this.config.wctx || 'web'
                ,openTo: this.config.openTo || ''
                ,rootId: this.config.rootId || '/'
                ,hideSourceCombo: this.config.hideSourceCombo || false
                ,listeners: {
                    'select': {fn: function(data) {
                        //console.log(this.config);
                        this.importCsvMigx(data);
                        //this.fireEvent('select',data);
                    },scope:this}
                }
            });
        //}
        this.browser.show(btn);
        return true;
    } 	
";

$gridfunctions['this.importCsvMigx'] = "
    importCsvMigx: function(data) {
        var recordIndex = 'none';
        var pathname = data.pathname;
        if (this.menu.recordIndex == 0){
            recordIndex = 0; 
        }else{
            recordIndex = this.menu.recordIndex || 'none';     
        }
        var tv_id =  this.config.tv;        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/migxdb/process'
                ,processaction: 'importcsvmigx'
                ,resource_id: this.config.resource_id
                ,co_id: '[[+config.connected_object_id]]' 
                ,items: Ext.get('tv' + tv_id).dom.value
                ,record_index: recordIndex
                ,pathname: pathname
            }
            ,listeners: {
                'success': {fn:function(res){
                    if (res.message==''){
                        var items = res.object;
                        var item = null;
                        Ext.get('tv' + tv_id).dom.value = Ext.util.JSON.encode(items);
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
";

