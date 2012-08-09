MODx.grid.Object = function(config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();
	Ext.applyIf(config,{
        url: Xdbedit.config.connector_url
        ,baseParams: { 
		    action: 'mgr/xdbedit/getList',
			configs: config.configs}
        ,fields: ['id','pagetitle','jobtitle','createdon','published','deleted']
        ,paging: true
		,autosave: false
        ,remoteSort: true
        ,primaryKey: 'id'
		,isModified : false
        ,sm: this.sm		
        ,columns: [this.sm,{
            header: 'id'
            ,dataIndex: 'id'
            ,sortable: true
            ,width: 50
        },{
            header: 'Pagetitle'
            ,dataIndex: 'pagetitle'
            ,sortable: true
            ,width: 200
        },{
            header: 'Job Title'
            ,dataIndex: 'jobtitle'
            ,sortable: true
            ,width: 300
        },{
            header: 'Created on'
            ,dataIndex: 'createdon'
            ,sortable: true
            ,width: 200
        },{
            header: 'Published'
            ,dataIndex: 'published'
            ,sortable: true
            ,width: 200
        }]
,tbar: [{
				xtype: 'buttongroup',
				id: 'filter-buttongroup',
				title: 'Filter',
				columns: 8,
				defaults: {
					scale: 'large'
				},
				items: [{
					text: 'Region:'
				}, {
					xtype: 'xdbedit-combo-region',
					id: 'xdbedit-filter-region',
					itemId: 'region',
					value: 'all',
					width: 120,
					listeners: {
						'select': {
							fn: this.changeRegion,
							scope: this
						}
					}
				}, {
					text: _('year') + ':'
				}, {
					xtype: 'xdbedit-combo-year',
					id: 'xdbedit-filter-year',
					itemId: 'year',
					value: 'all',
					width: 120,
					listeners: {
						'select': {
							fn: this.changeYear,
							scope: this
						}
					}
				}, {
					text: _('month') + ':'
				}, {
					xtype: 'xdbedit-combo-month',
					id: 'xdbedit-filter-month',
					itemId: 'month',
					value: 'all',
					width: 120,
					listeners: {
						'select': {
							fn: this.changeMonth,
							scope: this
						}
					}
				}]
			}, {
				xtype: 'buttongroup',
				title: 'Aktionen',
				columns: 2,
				defaults: {
					scale: 'large'
				},
				items: [{
					text: _(Xdbedit.customconfigs.task + '.bulk_actions') || _('xdbedit.bulk_actions'),
					menu: [{
						text: _(Xdbedit.customconfigs.task + '.publish_selected') || _('xdbedit.publish_selected'),
						handler: this.publishSelected,
						scope: this
					}, {
						text: _(Xdbedit.customconfigs.task + '.unpublish_selected') || _('xdbedit.unpublish_selected'),
						handler: this.unpublishSelected,
						scope: this
					}, {
						text: _(Xdbedit.customconfigs.task + '.delete_selected') || _('xdbedit.delete_selected'),
						handler: this.deleteSelected,
						scope: this
					}]
				},{
                    text: _(Xdbedit.customconfigs.task + '.show_trash') || _('xdbedit.show_trash')
                    ,handler: this.toggleDeleted
                    ,enableToggle: true
                    ,scope: this
        }]
			
			}]     
		,viewConfig: {
            forceFit:true,
            //enableRowBody:true,
            //showPreview:true,
            getRowClass : function(rec, ri, p){
                var cls = 'xdbedit-object';
                if (!rec.data.published) cls += ' xdbedit-unpublished';
                if (rec.data.deleted) cls += ' xdbedit-deleted';

                return cls;
            }
        }
    });
	
    MODx.grid.Object.superclass.constructor.call(this,config)
	this.getStore().on('load',this.onStoreLoad,this);
};
Ext.extend(MODx.grid.Object,MODx.grid.Grid,{
    _renderUrl: function(v,md,rec) {
        return '<a href="'+v+'" target="_blank">'+rec.data.pagetitle+'</a>';
    }
    ,editObject: function() {
		formpanel=Ext.getCmp('modx-panel-resource');
        formpanel.autoLoad.params.object_id=this.menu.record.id;
		formpanel.autoLoad.params['region']=null;
		formpanel.doAutoLoad();
		
		//location.href = '?a='+MODx.request.a+'&action=editorpage&object_id='+this.menu.record.id;
    }
    ,createObject: function() {
		formpanel=Ext.getCmp('modx-panel-resource');
        formpanel.autoLoad.params.object_id='neu';
		formpanel.autoLoad.params['region']=null;
		formpanel.doAutoLoad();		
        //location.href = '?a='+MODx.request.a+'&action=editorpage&object_id=neu';
    }
	,publishObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/xdbedit/update'
				,task: 'publish'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
	,deleteObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/xdbedit/update'
				,task: 'delete'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    },recallObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/xdbedit/update'
				,task: 'recall'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    },removeObject: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/'+Xdbedit.customconfigs.task+'/remove'
				,task: 'removeone'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }		
	,unpublishObject: function() {
 		MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/xdbedit/update'
				,task: 'unpublish'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    },toggleDeleted: function(btn,e) {
        var s = this.getStore();
        if (btn.pressed) {
            s.setBaseParam('showtrash',1);
            btn.setText(_(Xdbedit.customconfigs.task + '.show_normal') || _('xdbedit.show_normal'));
        } else {
            s.setBaseParam('showtrash',0);
            btn.setText(_(Xdbedit.customconfigs.task + '.show_trash') || _('xdbedit.show_trash'));
        }
        this.getBottomToolbar().changePage(1);
        s.removeAll();
        this.refresh();
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
    },publishSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/'+Xdbedit.customconfigs.task+'/bulkupdate'
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
    },unpublishSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/'+Xdbedit.customconfigs.task+'/bulkupdate'
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
    },deleteSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/'+Xdbedit.customconfigs.task+'/bulkupdate'
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
    },changeYear: function(cb,nv,ov) {
        this.setFilterParams({
			year:cb.getValue(),
			month:'all'
		});
    }
    ,changeMonth: function(cb,nv,ov) {
        this.setFilterParams({
			month:cb.getValue()
		});        
    }
	,changeRegion: function(cb,nv,ov) {
        this.setFilterParams({
			region:cb.getValue()
		});      		
    }
   ,onStoreLoad: function() {
		if (this.isModified){
		var tb = this.getTopToolbar();
        if (!tb) {return false;}
           ycb = tb.getComponent('year');
            if (ycb) {
                //mcb.store.baseParams['year'] = y;
                ycb.store.load({
                    callback: function() {
                        ycb.collapse();
                    }
                });
				

            }
            mcb = tb.getComponent('month');
            if (mcb) {
                //mcb.store.baseParams['year'] = y;
                mcb.store.load({
                    callback: function() {
                        mcb.collapse();
                    }
                });

            }
		}

            this.isModified=false;
        /*
		var s = this.getStore();
        if (s) {
            //if (y) {s.baseParams['year'] = y;}
            //if (m) {s.baseParams['month'] = m || 'all';}
            //s.removeAll();
        }
        */
        //this.getBottomToolbar().changePage(1);
        //this.refresh();
    }
    ,setFilterParams: function(params) {
        var tb = this.getTopToolbar();
        if (!tb) {return false;}
        var ccb = null;
		var ycb = null;
		var mcb = null;
        if (params.region) {
            params.month = params.month||'all';
			params.year = params.year||'all';
			Ext.getCmp('xdbedit-filter-region').setValue(params.region);
            //ycb = tb.getComponent('year');
			ycb = Ext.getCmp('xdbedit-filter-year');
			
            if (ycb) {
                ycb.store.baseParams['region'] = params.region;
                ycb.store.load({
                    callback: function() {
                        ycb.setValue('all');
                    }
                });
            }
        } 
        
        if (params.year) {
            params.month = params.month||'all';
			Ext.getCmp('xdbedit-filter-year').setValue(params.year);
            //mcb = tb.getComponent('month');
			mcb = Ext.getCmp('xdbedit-filter-month');
            if (mcb) {
                mcb.store.baseParams['year'] = params.year;
				if (params.region) {mcb.store.baseParams['region'] = params.region;}
                mcb.store.load({
                    callback: function() {
                        mcb.setValue(params.month);
                    }
                });
            }
        } 

        var s = this.getStore();
        if (s) {
            if (params.year) {s.baseParams['year'] = params.year;}
            if (params.month) {s.baseParams['month'] = params.month ;}
			if (params.region) {s.baseParams['region'] = params.region ;}
            s.removeAll();
        }
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,getMenu: function() {
        //this.store.on('load', this.reloadDateCombos(this)); 
		//console.log(this.store);
		var n = this.menu.record; 
        //var cls = n.cls.split(',');
        var m = [];
        m.push({
            text: _(Xdbedit.customconfigs.task+'.edit')||_('xdbedit.edit')
            ,handler: this.editObject
        });
        m.push('-');
        m.push({
            text: _(Xdbedit.customconfigs.task+'.create')||_('xdbedit.create')
            ,handler: this.createObject
        });
        m.push('-');
        if (n.published == 0) {
            m.push({
                text: _(Xdbedit.customconfigs.task+'.publish')||_('xdbedit.publish')
                ,handler: this.publishObject
            })
        } else if (n.published == 1) {
            m.push({
                text:_(Xdbedit.customconfigs.task+'.unpublish')||_('xdbedit.unpublish')
                ,handler: this.unpublishObject
            });
        }
        m.push('-');
        if (n.deleted == 1) {
        m.push({
            text: _(Xdbedit.customconfigs.task+'.recall')||_('xdbedit.recall')
            ,handler: this.recallObject
        });
		m.push('-');
        m.push({
            text: _(Xdbedit.customconfigs.task+'.remove')||_('xdbedit.remove')
            ,handler: this.removeObject
        });						
        } else if (n.deleted == 0) {
        m.push({
            text: _(Xdbedit.customconfigs.task+'.delete')||_('xdbedit.delete')
            ,handler: this.deleteObject
        });		
        }		
		
        this.addContextMenuItem(m);
    }
});
Ext.reg('xdbedit-grid-objects',MODx.grid.Object);

MODx.combo.Month = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        name: 'month'
        ,hiddenName: 'month'
        ,forceSelection: true
        ,typeAhead: false
        ,editable: false
        ,allowBlank: false
        ,listWidth: 300		
		,resizable: false
        ,pageSize: 0		
        ,url: Xdbedit.config.connector_url
        ,fields: ['optionname']
        ,displayField: 'optionname'
        ,valueField: 'optionname'
        ,baseParams: {
		    action: 'mgr/'+Xdbedit.customconfigs.task+'/getdates',
			configs: Xdbedit.config.configs,
			mode: 'month',
			year: 'all'
        }
    });
    MODx.combo.Month.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.Month,MODx.combo.ComboBox);
Ext.reg('xdbedit-combo-month',MODx.combo.Month);

MODx.combo.Year = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        name: 'year'
        ,hiddenName: 'year'
        ,forceSelection: true
        ,typeAhead: false
        ,editable: false
        ,allowBlank: false
        ,listWidth: 300
		,resizable: false
        ,pageSize: 0
        ,url: Xdbedit.config.connector_url
        ,fields: ['optionname']
        ,displayField: 'optionname'
        ,valueField: 'optionname'
        ,baseParams: { 
		    action: 'mgr/'+Xdbedit.customconfigs.task+'/getdates',
			configs: Xdbedit.config.configs,
			mode: 'year'}			

    });
    MODx.combo.Year.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.Year,MODx.combo.ComboBox);
Ext.reg('xdbedit-combo-year',MODx.combo.Year);

MODx.combo.Region = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        name: 'region'
        ,hiddenName: 'region'
        ,forceSelection: true
        ,typeAhead: false
        ,editable: false
        ,allowBlank: false
        ,listWidth: 300		
		,resizable: false
        ,pageSize: 0		
        ,url: Xdbedit.config.connector_url
        ,fields: ['name']
        ,displayField: 'name'
        ,valueField: 'name'
        ,baseParams: {
		    action: 'mgr/'+Xdbedit.customconfigs.task+'/getregions',
			configs: Xdbedit.config.configs,
        }
    });
    MODx.combo.Region.superclass.constructor.call(this,config);
};
Ext.extend(MODx.combo.Region,MODx.combo.ComboBox);
Ext.reg('xdbedit-combo-region',MODx.combo.Region);