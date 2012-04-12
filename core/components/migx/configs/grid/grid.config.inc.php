<?php
$lang = $this->modx->lexicon->fetch();
$lang['mig_add'] = !empty($this->customconfigs['btntext']) ? $this->customconfigs['btntext'] : $lang['migx.add'];


$gridactionbuttons['addItem']['text'] = "'".$lang['mig_add']."'";
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

$ctx = '{$ctx}';
$gridfunctions['this.renderImage'] = "
    ,renderImage : function(val, md, rec, row, col, s){
		var source = s.pathconfigs[col];
		if (val.substr(0,4) == 'http'){
            return '<img style=\"height:60px\" src=\"' + val + '\"/>' ;
		}        
		if (val != ''){
			return '<img src=\"'+MODx.config.connectors_url+'system/phpthumb.php?h=60&src='+val+'&wctx={/literal}{$ctx}{literal}'+source+'\" alt=\"\" />';
		}
		return val;
	}
";

$gridfunctions['this.renderPlaceholder'] = "
renderPlaceholder : function(val, md, rec, row, col, s){
        return '[[+'+val+'.'+rec.json.MIGX_id+']]';
        
	}
";

$gridfunctions['this.renderFirst'] = "
renderFirst : function(val, md, rec, row, col, s){
		val = val.split(':');
        return val[0];
	}        
";

$gridfunctions['this.renderLimited'] = "
renderLimited : function(val, md, rec, row, col, s){
		var max = 100;
        var count = val.length;
		if (count>max){
            return(val.substring(0, max));
		}        
		return val;
	}    
";

$gridfunctions['this.renderPreview'] = "
renderPreview : function(val,md,rec){
		return val;
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
            btn.setText(_('mig.show_normal'));
        } else {
            s.setBaseParam('showtrash',0);
            btn.setText(_('mig.show_trash'));
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
                action: 'mgr/{/literal}\{$customconfigs.task}{literal}/bulkupdate'
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
                action: 'mgr/{/literal}\{$customconfigs.task}{literal}/bulkupdate'
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
    }
";
$gridfunctions['this.deleteSelected'] = "
deleteSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/{/literal}\{$customconfigs.task}{literal}/bulkupdate'
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
                action: 'mgr/{/literal}\{$customconfigs.task}{literal}/remove'
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








