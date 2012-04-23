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

$gridactionbuttons['bulk']['text'] = "_('migx.bulk_actions')";
$gridactionbuttons['bulk']['menu'][0]['text'] = "_('migx.publish_selected')";
$gridactionbuttons['bulk']['menu'][0]['handler'] = 'this.publishSelected';
$gridactionbuttons['bulk']['menu'][0]['scope'] = 'this';
$gridactionbuttons['bulk']['menu'][1]['text'] = "_('migx.unpublish_selected')";
$gridactionbuttons['bulk']['menu'][1]['handler'] = 'this.unpublishSelected';
$gridactionbuttons['bulk']['menu'][1]['scope'] = 'this';
$gridactionbuttons['bulk']['menu'][2]['text'] = "_('migx.delete_selected')";
$gridactionbuttons['bulk']['menu'][2]['handler'] = 'this.deleteSelected';
$gridactionbuttons['bulk']['menu'][2]['scope'] = 'this';

$gridactionbuttons['toggletrash']['text'] = "_('migx.show_trash')";
$gridactionbuttons['toggletrash']['handler'] = 'this.toggleDeleted';
$gridactionbuttons['toggletrash']['scope'] = 'this';
$gridactionbuttons['toggletrash']['enableToggle'] = 'true';


$gridcontextmenus['update']['code']="
        m.push({
            text: _('migx.edit')
            ,handler: this.update
        });
        m.push('-');
";
$gridcontextmenus['update']['handler'] = 'this.update';

$gridcontextmenus['publish']['code']="
        if (n.published == 0) {
            m.push({
                text: _('migx.publish')
                ,handler: this.publishObject
            })
            m.push('-');
        }
        
";
$gridcontextmenus['publish']['handler'] = 'this.publishObject';

$gridcontextmenus['unpublish']['code']="
if (n.published == 1) {
            m.push({
                text: _('migx.unpublish')
                ,handler: this.unpublishObject
            });
            m.push('-');
        }      
";
$gridcontextmenus['unpublish']['handler'] = 'this.unpublishObject';

$gridcontextmenus['recall_remove_delete']['code']="
        if (n.deleted == 1) {
        m.push({
            text: _('migx.recall')
            ,handler: this.recallObject
        });
		m.push('-');
        m.push({
            text: _('migx.remove')
            ,handler: this.removeObject
        });						
        } else if (n.deleted == 0) {
        m.push({
            text: _('migx.delete')
            ,handler: this.deleteObject
        });		
        }
";
$gridcontextmenus['recall_remove_delete']['handler'] = 'this.recallObject,this.removeObject,this.deleteObject';

$gridfilters['textbox']['code']=
"
{
    xtype: 'textfield'
    ,id: '[[+name]]-migxdb-search-filter'
    ,fieldLabel: 'Test'
    ,emptyText: 'search...'
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
$gridfilters['textbox']['handler'] = 'searchtextbox';


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
        action: 'mgr/[[+config.task]]/[[+getcomboprocessor]]',
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
$gridfilters['combobox']['handler'] = 'searchcombobox';



$ctx = '{$ctx}';
$httpimg = '<img style="height:60px" src="' + val + '"/>';

$phpthumb = "'+MODx.config.connectors_url+'system/phpthumb.php?h=60&src='+val+'&wctx={/literal}{$ctx}{literal}'+source+'";
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
            renderImage = '/assets/components/migx/style/images/cross.png';
            altText = 'No';
            break;
        case 1:
            renderImage = '/assets/components/migx/style/images/tick.png';
            altText = 'Yes';
            break;
    }
    return String.format('{$img}', renderImage, altText, altText);
}
";


$gridfunctions['searchcombobox'] ="
filter[[+name]]: function(cb,nv,ov) {
        //console.log(cb.getValue());
        var s = this.getStore();
        s.baseParams.[[+name]] = cb.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();        
        return;
        
        this.setFilterParams({
			year:cb.getValue(),
			month:'alle'
		});
    }
";


$gridfunctions['searchtextbox'] = "
    filter[[+name]]: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.[[+name]] = tf.getValue();
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

$gridfunctions['this.remove'] = "
remove: function() {
        var _this=this;
		Ext.Msg.confirm(_('warning') || '',_('mig.remove_confirm') || '',function(e) {
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
                action: 'mgr/{/literal}{$config_task}{literal}/bulkupdate'
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
                action: 'mgr/{/literal}{$config_task}{literal}/bulkupdate'
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
                action: 'mgr/{/literal}{$config_task}{literal}/bulkupdate'
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
$gridfunctions['this.removeObject'] = "
removeObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/{/literal}{$config_task}{literal}/remove'
				,task: 'removeone'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
";








