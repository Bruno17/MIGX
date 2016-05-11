<?php

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

$gridfilters['listbox-multiple']['code'] = "
{
    xtype: 'superboxselect'
    ,id: '[[+name]]-migxdb-search-filter'
    ,name: '[[+name]]'
    ,hiddenName: '[[+name]]'
    ,triggerAction: 'all'
    ,extraItemCls: 'x-tag'
    ,expandBtnCls: 'x-form-trigger'
    ,clearBtnCls: 'x-form-trigger'    
    ,fields: ['combo_id','combo_name']
    ,displayField: 'combo_name'
    ,valueField: 'combo_id'    
    ,pageSize: 0
    ,mode: 'remote'
	,value: 'all'
    ,store: new Ext.data.JsonStore({
        id:'id',
        root:'results',
        fields: ['combo_id','combo_name'],
        remoteSort: true,
        url: '[[+config.connectorUrl]]',
        baseParams: { 
            action: 'mgr/migxdb/process',
            processaction: '[[+getcomboprocessor]]',
            configs: '[[+config.configs]]',
            searchname: '[[+name]]',
            resource_id: '[[+config.resource_id]]'
        }		
        ,listeners: {}
    })
    ,listeners: {
        'additem': {
            fn: this.filter[[+name]],
            scope: this
        }
        ,'removeitem': {
            fn: this.filter[[+name]],
            scope: this
        }
    }    
}
";
$gridfilters['listbox-multiple']['handler'] = 'gridfilter';


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