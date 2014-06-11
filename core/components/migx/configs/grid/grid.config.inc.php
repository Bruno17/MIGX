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

$gridactionbuttons['resetwinposition']['text'] = "'Reset Win Position'";
$gridactionbuttons['resetwinposition']['handler'] = 'this.resetWinPosition';
$gridactionbuttons['resetwinposition']['scope'] = 'this';

$gridactionbuttons['emptyThrash']['text'] = "'[[%migx.emptythrash]]'";
$gridactionbuttons['emptyThrash']['handler'] = 'this.emptyThrash';
$gridactionbuttons['emptyThrash']['scope'] = 'this';

$winbuttons['cancel']['text'] = "config.cancelBtnText || _('cancel')";
$winbuttons['cancel']['handler'] = 'this.cancel';
$winbuttons['cancel']['scope'] = 'this';

$winbuttons['done']['text'] = "config.saveBtnText || _('done')";
$winbuttons['done']['handler'] = 'this.submit';
$winbuttons['done']['scope'] = 'this';

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

$gridfunctions['this.emptyThrash'] = "
emptyThrash: function(btn,e) {
    var _this=this;
    Ext.Msg.confirm(_('warning') || '','[[%migx.emptythrash_confirm]]',function(e) {
        if (e == 'yes') {    
            MODx.Ajax.request({
                url: _this.config.url
                ,params: {
                    action: 'mgr/migxdb/process'
                    ,processaction: 'emptythrash'                     
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



$gridcontextmenus['update']['code']="
        m.push({
            className : 'update', 
            text: '[[%migx.edit]]',
            handler: 'this.update'
        });
        m.push('-');
";
$gridcontextmenus['update']['handler'] = 'this.update';

$gridcontextmenus['duplicate']['code']="
        m.push({
            className : 'duplicate', 
            text: '[[%migx.duplicate]]',
            handler: 'this.duplicate'
        });
        m.push('-');
";
$gridcontextmenus['duplicate']['handler'] = 'this.duplicate';

$gridcontextmenus['publish']['code']="
        if (n.published == 0) {
            m.push({
                className : 'publish', 
                text: '[[%migx.publish]]',
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
                text: '[[%migx.unpublish]]'
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
            text: '[[%migx.recall]]',
            handler: 'this.recallObject'
        });
		m.push('-');
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.removeObject'
        });						
        } else if (n.deleted == 0) {
        m.push({
            className : 'delete', 
            text: '[[%migx.delete]]',
            handler: 'this.deleteObject'
        });		
        }
";
$gridcontextmenus['recall_remove_delete']['handler'] = 'this.recallObject,this.removeObject,this.deleteObject';

$gridcontextmenus['remove']['code']="
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.removeObject'
        });						
";
$gridcontextmenus['remove']['handler'] = 'this.removeObject';

$gridcontextmenus['remove_migx']['code']="
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.remove'
        });						
";
//$gridcontextmenus['remove_migx']['handler'] = 'this.remove';

$gridfilters['date']['code']="
{
    xtype: 'compositefield',
    width: 300,
    items: [
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
                processaction: 'getdatecombo',
                configs: '[[+config.configs]]',
                searchname: '[[+name]]',
                resource_id: '[[+config.resource_id]]'
            }			
            ,listeners: {
                'select': {
                    fn: function(tf,nv,ov){
                        var s = this.getStore();
                        s.baseParams.[[+name]]_dir = tf.getValue();                        
                        this.filter[[+name]](tf,nv,ov);    
                    }, 
                    scope: this
                }
            }
        },
        {
            xtype     : 'datefield',
            id: '[[+name]]-migxdb-search-filter-date'
            ,name: '[[+name]]_date'
            ,format: 'Y-m-d'            
            ,listeners: {
                'select': {
                    fn: function(tf,nv,ov){
                        var s = this.getStore();
                        s.baseParams.[[+name]]_date = tf.getValue();       
                        this.filter[[+name]](tf,nv,ov);    
                    },
                    scope: this
                }
            }  
        }
      
    ]
}
";

$gridfilters['resetall']['code']=
"
{
    xtype: 'button'
    ,id: '[[+name]]-migxdb-search-filter'
    ,text: '[[%migx.reset_all]]'
    ,listeners: {
                'click': {
                    fn: function(tf,nv,ov){
                        var s = this.getStore();
                        this.setDefaultFilters();  
                    },
                    scope: this
                }
    }
}
";
//$gridfilters['resetall']['handler'] = 'gridfilter';


$gridfilters['date']['handler'] = 'gridfilter';

$gridfilters['textbox']['code']=
"
{
    xtype: 'textfield'
    ,id: '[[+name]]-migxdb-search-filter'
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
        searchname: '[[+name]]',
        resource_id: '[[+config.resource_id]]'
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

$gridfilters['treecombo']['code']=
"
{
    xtype: 'migx-treecombo'
    ,id: '[[+name]]-migxdb-search-filter'
    ,fieldLabel: 'Test'
    ,emptyText: '[[+emptytext]]'
    ,name: '[[+name]]'
    ,hiddenName: '[[+name]]'    
    ,baseParams: { 
        action: 'mgr/migxdb/process',
        processaction: '[[+getcomboprocessor]]',
        configs: '[[+config.configs]]',
        searchname: '[[+name]]',
        resource_id: '[[+config.resource_id]]',
        co_id: '[[+config.connected_object_id]]',
        reqConfigs: '[[+config.req_configs]]'
    }
    ,root: {
        nodeType: 'async',
        text: 'Root',
        draggable: false,
        id: 'currentctx_0'
    }
    ,listeners: {
        'nodeclick': {fn:this.filter[[+name]],scope:this}
    }
}
";
$gridfilters['treecombo']['handler'] = 'gridfilter';



$ctx = '{$ctx}';
$val = "' + val + '";
$httpimg = '<img style="height:60px" src="'.$val.'"/>';

$phpthumb = "'+MODx.config.connectors_url+'system/phpthumb.php?h=60&src='+val+source+'";
$phpthumbimg = '<img src="'.$phpthumb.'" alt="" />';

$renderer['this.renderImage'] = "
    renderImage : function(val, md, rec, row, col, s){
        var source = s.pathconfigs[col];
        if (val !== null) {
            if (val.substr(0,4) == 'http'){
                return '{$httpimg}' ;
            }        
            if (val != ''){
                return '{$phpthumbimg}';
            }
            return val;
        }
	}
";

$phpthumb = "'+MODx.config.connectors_url+'system/phpthumb.php?h=60&src='+val+'";
$phpthumbimg = '<img src="'.$phpthumb.'" alt="" />';

$renderer['this.renderImageFromHtml'] = "
    renderImageFromHtml : function(val, md, rec, row, col, s){
        var source = s.pathconfigs[col];
        if (val !== null) {
            if (val != ''){
                var el = document.createElement('div');
                el.innerHTML = val;               
                var img = el.querySelector('img');
                
                if (img){
                    val = img.getAttribute('src');
                    return '{$phpthumbimg}';
                }
                
            }
            return val;
        }
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
    var renderImage, altText, handler, classname;
    
    switch (val) {
        case 0:
        case '0':
        case '':
        case false:
            renderImage = '/assets/components/migx/style/images/cross.png';
            handler = 'this.publishObject';
            classname = 'publish';
            altText = 'No';
            break;
        case 1:
        case '1':
        case true:
            renderImage = '/assets/components/migx/style/images/tick.png';
            handler = 'this.unpublishObject';
            classname = 'unpublish';
            altText = 'Yes';
            break;
    }
    return String.format('{$img}', renderImage, altText, altText, classname, handler);
}
";

$img = '<a href="#" ><img class="controlBtn {3} {4}" src="{0}" alt="{1}" title="{2}"></a>';
$renderer['this.renderClickCrossTick'] = "
renderClickCrossTick : function(val, md, rec, row, col, s) {
    var renderImage, altText, handler, classname;
    switch (val) {
        case 0:
        case '0':
        case '':
        case false:
            renderImage = '/assets/components/migx/style/images/cross.png';
            handler = 'this.publishObject';
            classname = 'unpublished';
            altText = 'No';
            break;
        case 1:
        case '1':
        case true:
            renderImage = '/assets/components/migx/style/images/tick.png';
            handler = 'this.unpublishObject';
            classname = 'published';
            altText = 'Yes';
            break;
    }
    return String.format('{$img}', renderImage, altText, altText, classname, handler);
}
";

$base_url = $this->modx->getOption('base_url');
$img = '<a href="#" ><img class="controlBtn {3} {4} {5}" src="'.$base_url.'{0}" alt="{1}" title="{2}"></a>';
$renderer['this.renderSwitchStatusOptions'] = "
renderSwitchStatusOptions : function(val, md, rec, row, col, s) {
    var column = this.getColumnModel().getColumnAt(col);
    var ro = Ext.util.JSON.decode(rec.json[column.dataIndex+'_ro']);
    var renderImage, altText, handler, classname;
    renderImage = ro.image;
    handler = ro.handler;
    if (typeof(handler) == 'undefined' || handler == ''){
        handler = 'this.handleColumnSwitch'
    }
    classname = ro.name;
    altText = ro.name || val ;
    return String.format('{$img}', renderImage, altText, altText, classname, handler, column.dataIndex);
}
";

$tpl = '{6} <a href="#" ><img class="controlBtn btn_selectpos {4} selectpos" src="'.$base_url.'assets/components/migx/style/images/arrow_updown.png" alt="select" title="select position"></a>';
$tpl_active = '{6} '; 
$tpl_active .= '<a href="#" ><img class="controlBtn btn_before {4} {5}:before" src="'.$base_url.'assets/components/migx/style/images/arrow_up.png" alt="before" title="move before"></a>';
$tpl_active .= '<a href="#" ><img class="controlBtn btn_cancel {4} cancel" src="'.$base_url.'assets/components/migx/style/images/cancel.png" alt="cancel" title="cancel"></a>';
$tpl_active .= '<a href="#" ><img class="controlBtn btn_after {4} {5}:after" src="'.$base_url.'assets/components/migx/style/images/arrow_down.png" alt="after" title="move after"></a>';

$renderer['this.renderPositionSelector'] = "
renderPositionSelector : function(val, md, rec, row, col, s) {
    var column = this.getColumnModel().getColumnAt(col);
    var ro = Ext.util.JSON.decode(rec.json[column.dataIndex+'_ro']);
    var value, renderImage, altText, handler, classname;
    renderImage = ro.image;
    var handler = ro.handler;
    if (typeof(handler) == 'undefined' || handler == ''){
        handler = 'this.handlePositionSelector'
    }
    value = val;
    classname = 'test';
    
    if (this.isPosSelecting){
        altText = 'before' ;
        return String.format('{$tpl_active}', renderImage, altText, altText, classname, handler, column.dataIndex, value);            
    }
    else{
        altText = 'select' ;
        return String.format('{$tpl}', renderImage, altText, altText, classname, handler, column.dataIndex, value);
    }

}
";




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


$renderer['this.renderRowActions'] = "
	dummy:function(v,md,rec) {
        // this function is fixed in the grid
	} 
";

$renderer['this.renderChunk'] = "
renderChunk : function(val, md, rec, row, col, s) {
    this.call_collectmigxitems = true;
    return val;
}
";

$renderer['this.renderDate'] = "
renderDate : function(val, md, rec, row, col, s) {
    var date;
	if (val && val != '') {
        if (typeof val == 'number') {
            date = new Date(val*1000);
        } else {
			date = Date.parseDate(val, 'Y-m-d H:i:s');
        }
        if (typeof(date) != 'undefined' ){
		    return String.format('{0}', date.format(MODx.config.manager_date_format+' '+MODx.config.manager_time_format));
        }    
	} 
	return '';
	
}
";

//$base_url = $this->modx->getOption('base_url');
$img = '<a href="#" ><img class="controlBtn {3} {4} {5}" src="{0}" alt="{1}" title="{2}"></a>';
$renderer['this.renderOptionSelector'] = "
renderOptionSelector : function(val, md, rec, row, col, s) {
    //var column = this.getColumnModel().getColumnAt(col);
    //var ro = Ext.util.JSON.decode(rec.json[column.dataIndex+'_ro']);
    var renderImage, altText, handler, classname;
    renderImage = '/assets/components/migx/style/images/tick.png';
    handler = 'this.selectSelectorOption';
    classname = 'test';
    altText = 'test';
    return String.format('{$img}', renderImage, altText, altText, classname, handler, col);
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

$gridfunctions['this.duplicate'] = "
duplicate: function(btn,e) {
      params = {
          duplicate: '1'
      }          
      this.loadWin(btn,e,'d',Ext.util.JSON.encode(params));
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
        
        console.log(this.menu.record.json);
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

