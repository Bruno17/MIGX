<?php

/*
$lang = $this->modx->lexicon->fetch();
$migx_add = !empty($this->customconfigs['migx_add']) ? $this->customconfigs['migx_add'] : $lang['migx.add'];
*/
//$migx_add = $this->migxi18n['migx_add'];

$config_task = '{$customconfigs.task}';
$config_configs = '{$customconfigs.configs}';

$gridactionbuttons['addItem']['text'] = "'[[%migx.add]]'";
$gridactionbuttons['addItem']['handler'] = 'this.addItem';
$gridactionbuttons['addItem']['scope'] = 'this';

$gridactionbuttons['bulk']['text'] = "'[[%migx.bulk_actions]]'";
$gridactionbuttons['bulk']['menu'][0]['text'] = "'[[%migx.publish_selected]]'";
$gridactionbuttons['bulk']['menu'][0]['handler'] = 'this.publishSelected';
$gridactionbuttons['bulk']['menu'][0]['scope'] = 'this';
$gridactionbuttons['bulk']['menu'][1]['text'] = "'[[%migx.unpublish_selected]]'";
$gridactionbuttons['bulk']['menu'][1]['handler'] = 'this.unpublishSelected';
$gridactionbuttons['bulk']['menu'][1]['scope'] = 'this';
$gridactionbuttons['bulk']['menu'][2]['text'] = "'[[%migx.delete_selected]]'";
$gridactionbuttons['bulk']['menu'][2]['handler'] = 'this.deleteSelected';
$gridactionbuttons['bulk']['menu'][2]['scope'] = 'this';

$gridactionbuttons['toggletrash']['text'] = "_('migx.show_trash')";
$gridactionbuttons['toggletrash']['handler'] = 'this.toggleDeleted';
$gridactionbuttons['toggletrash']['scope'] = 'this';
$gridactionbuttons['toggletrash']['enableToggle'] = 'true';

$gridactionbuttons['exportview']['text'] = "_('migx.export_current_view')";
$gridactionbuttons['exportview']['handler'] = 'this.csvExport';
$gridactionbuttons['exportview']['scope'] = 'this';
$gridactionbuttons['exportview']['enableToggle'] = 'true';

$gridactionbuttons['upload']['text'] = "'[[%migx.upload_images]]'";
$gridactionbuttons['upload']['handler'] = 'this.uploadImages';
$gridactionbuttons['upload']['scope'] = 'this';
$gridactionbuttons['upload']['standalone'] = '1';

$gridactionbuttons['loadfromsource']['text'] = "'[[%migx.load_from_source]]'";
$gridactionbuttons['loadfromsource']['handler'] = 'this.loadFromSource';
$gridactionbuttons['loadfromsource']['scope'] = 'this';
$gridactionbuttons['loadfromsource']['standalone'] = '1';


$gridcontextmenus['update']['code']="
        m.push({
            className : 'update', 
            text: _('migx.edit'),
            handler: 'this.update'
        });
        m.push('-');
";
$gridcontextmenus['update']['handler'] = 'this.update';

$gridcontextmenus['publish']['code']="
        if (n.published == 0) {
            m.push({
                className : 'publish', 
                text: _('migx.publish'),
                handler: 'this.publishObject'
            })
            m.push('-');
        }
";
$gridcontextmenus['publish']['handler'] = 'this.publishObject';

$gridcontextmenus['unpublish']['code']="
if (n.published == 1) {
            m.push({
                className : 'unpublish', 
                text: _('migx.unpublish')
                ,handler: 'this.unpublishObject'
            });
            m.push('-');
        }      
";
$gridcontextmenus['unpublish']['handler'] = 'this.unpublishObject';

$gridcontextmenus['activate']['code']="
        var active = n.Joined_active || 0;
        if (active == 0) {
            m.push({
                className : 'activate', 
                text: '[[%migx.activate]]',
                handler: 'this.activateObject'
            })
            m.push('-');
        }
        
";
$gridcontextmenus['activate']['handler'] = 'this.activateObject';

$gridcontextmenus['deactivate']['code']="
        if (n.Joined_active == 1) {
            m.push({
                className : 'deactivate', 
                text: '[[%migx.deactivate]]',
                handler: 'this.deactivateObject'
            })
            m.push('-');
        }
        
";
$gridcontextmenus['deactivate']['handler'] = 'this.deactivateObject';


$gridcontextmenus['recall_remove_delete']['code']="
        if (n.deleted == 1) {
        m.push({
            className : 'recall', 
            text: _('migx.recall'),
            handler: 'this.recallObject'
        });
		m.push('-');
        m.push({
            className : 'remove', 
            text: _('migx.remove'),
            handler: 'this.removeObject'
        });						
        } else if (n.deleted == 0) {
        m.push({
            className : 'delete', 
            text: _('migx.delete'),
            handler: 'this.deleteObject'
        });		
        }
";
$gridcontextmenus['recall_remove_delete']['handler'] = 'this.recallObject,this.removeObject,this.deleteObject';

$gridcontextmenus['remove']['code']="
        m.push({
            className : 'remove', 
            text: _('migx.remove'),
            handler: 'this.removeObject'
        });						
";
$gridcontextmenus['remove']['handler'] = 'this.removeObject';

$gridfilters['textbox']['code']=
"
{
    xtype: 'textfield'
    ,idxxx: '[[+name]]-migxdb-search-filter'
    ,fieldLabel: 'Test'
    ,emptyText: '[[+emptytext]]'
    ,listeners: {
        'change': {fn:this.filter[[+name]],scope:this}
        ,'render': {fn: function(cmp) {
            new Ext.KeyMap(cmp.getEl(), {
                key: Ext.EventObject.ENTER
                ,fn: function() {
                    this.fireEvent('change',this);
                    this.blur();
                    return true;
                }
                ,scope: cmp
            });
        },scope:this}
    }
}
";
$gridfilters['textbox']['handler'] = 'gridfilter';


$gridfilters['combobox']['code'] = "
{
    xtype: 'modx-combo'
    ,id: '[[+name]]-migxdb-search-filter'
    ,name: '[[+name]]'
    ,hiddenName: '[[+name]]'
    ,url: '[[+config.connectorUrl]]'
    ,fields: ['combo_id','combo_name']
    ,displayField: 'combo_name'
    ,valueField: 'combo_id'    
    ,pageSize: 0
	,value: 'all'
    ,baseParams: { 
        action: 'mgr/migxdb/process',
        processaction: '[[+getcomboprocessor]]',
        configs: '[[+config.configs]]',
        searchname: '[[+name]]'
    }			
    ,listeners: {
        'select': {
            fn: this.filter[[+name]],
            scope: this
        }
    }
}
";
$gridfilters['combobox']['handler'] = 'gridfilter';



$ctx = '{$ctx}';
$val = "' + val + '";
$httpimg = '<img style="height:60px" src="'.$val.'"/>';

$phpthumb = "'+MODx.config.connectors_url+'system/phpthumb.php?h=60&src='+val+source+'";
$phpthumbimg = '<img src="'.$phpthumb.'" alt="" />';

$renderer['this.renderImage'] = "
    renderImage : function(val, md, rec, row, col, s){
        var source = s.pathconfigs[col];
        if (val.substr(0,4) == 'http'){
            return '{$httpimg}' ;
		}        
		if (val != ''){
			return '{$phpthumbimg}';
		}
		return val;
	}
";

$renderer['this.renderPlaceholder'] = "
renderPlaceholder : function(val, md, rec, row, col, s){
         return '[[+'+val+'.'+rec.json.MIGX_id+']]';
        
	}
";

$renderer['this.renderFirst'] = "
renderFirst : function(val, md, rec, row, col, s){
		val = val.split(':');
        return val[0];
	}        
";

$renderer['this.renderLimited'] = "
renderLimited : function(val, md, rec, row, col, s){
		var max = 100;
        var count = val.length;
		if (count>max){
            return(val.substring(0, max));
		}        
		return val;
	}    
";

$img = '<img src="{0}" alt="{1}" title="{2}">';
$renderer['this.renderCrossTick'] = "
renderCrossTick : function(val, md, rec, row, col, s) {
    var renderImage, altText;
    switch (val) {
        case 0:
        case '0':
        case false:
            renderImage = '/assets/components/migx/style/images/cross.png';
            altText = 'No';
            break;
        case 1:
        case '1':
        case true:
            renderImage = '/assets/components/migx/style/images/tick.png';
            altText = 'Yes';
            break;
    }
    return String.format('{$img}', renderImage, altText, altText);
}
";

$renderer['this.renderRowActions'] = "
	dummy:function(v,md,rec) {
        // this function is fixed in the grid
	} 
";

$renderer['this.renderDate'] = "
renderDate : function(val, md, rec, row, col, s) {
    var date;
	if (val && val != '') {
		date = Date.parseDate(val, 'Y-m-d H:i:s');
		return String.format('{0}', date.format(MODx.config.manager_date_format+' '+MODx.config.manager_time_format));
	} else {
		return '';
	}
}
";

$gridfunctions['gridfilter'] = "
    filter[[+name]]: function(tf,nv,ov) {
        var children = Ext.util.JSON.decode('[[+combochilds]]');
        var s = this.getStore();
        s.baseParams.[[+name]] = tf.getValue();
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
		this.loadWin(btn,e,'a');
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
				,config_task: 'unpublish'
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
";
$gridfunctions['this.deleteObject'] = "
deleteObject: function() {
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
    }
";
$gridfunctions['this.recallObject'] = "
recallObject: function() {
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
    }
";

$gridfunctions['this.csvExport'] = "
	csvExport: function(btn,e) {
		var s = this.getStore();
		var code, type, category, study_type, ebs_state;
		var box = Ext.MessageBox.wait('Preparing …', _('migx.export_current_view'));
        var params = s.baseParams;
        params.action = 'mgr/migxdb/process';
        params.processaction = 'export';
        params.configs = this.config.configs;     

		MODx.Ajax.request({
			url : this.config.url,
			params: params,
			listeners: {
				'success': {fn:function(r) {
					 location.href = this.config.url+'?action=mgr/migxdb/process&processaction=export&download='+r.message+'&id='+id+'&HTTP_MODAUTH=' + MODx.siteId;
					 box.hide();
				},scope:this}
			}
		});
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
                        ,object_id: _this.menu.record.id
				        ,configs: _this.config.configs
                    }
                    ,listeners: {
                        'success': {fn:_this.refresh,scope:_this}
                    }
                });  
            }
        }),this;    
    }
";

$gridcontextmenus['publishtarget']['code']="
        if (n.published == 0) {
            m.push({
                className : 'publish', 
                text: _('migx.publish'),
                handler: 'this.publishTargetObject'
            })
            m.push('-');
        }
        
";
$gridcontextmenus['publishtarget']['handler'] = 'this.publishTargetObject';

$gridcontextmenus['unpublishtarget']['code']="
if (n.published == 1) {
            m.push({
                className : 'unpublish', 
                text: _('migx.unpublish'),
                handler: 'this.unpublishTargetObject'
            });
            m.push('-');
        }      
";
$gridcontextmenus['unpublishtarget']['handler'] = 'this.unpublishTargetObject';

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
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }    
";






