{literal}

MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal} = function(config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();
    this.ident = config.ident || 'quip-'+Ext.id();

    var quipconfig = Ext.util.JSON.decode('{/literal}{$customconfigs.quipconfig}{literal}');    
    config.url = quipconfig.connectorUrl;
    config.columns = [this.sm,{
            header: _('quip.comment')
            ,dataIndex: 'username'
            ,sortable: false
            ,width: 400
            ,renderer: this.renderAuthor
        },{
            header: _('quip.posted')
            ,dataIndex: 'createdon'
            ,sortable: false
            ,editable: false
            ,align: 'right'
            ,width: 100
            ,renderer: this._renderPosted
        },{
            header: _('quip.thread')
            ,dataIndex: 'url'
            ,sortable: false
            ,editable: false
            ,width: 100
            ,renderer: this._renderUrl
        }];
        
    config.fields = ['id','author','username','body','createdon','name','approved','deleted','ip','url','pagetitle','comments','website','email','cls'];    

    Ext.applyIf(config,{
        url: config.url
        ,baseParams: { 
            action: 'mgr/comment/getList'
            ,thread: config.thread || null
            ,family: config.family || null
        }
        ,fields: config.fields 
        ,paging: true
        ,autosave: false
        ,remoteSort: true
        ,autoExpandColumn: 'body'
        ,sm: this.sm
        ,columns: config.columns
        ,viewConfig: {
            forceFit:true,
            enableRowBody:true,
            showPreview:true,
            getRowClass : function(rec, ri, p){
                var cls = 'quip-comment';
                if (!rec.data.approved) cls += ' quip-unapproved';
                if (rec.data.deleted) cls += ' quip-deleted';

                if(this.showPreview){
                    p.body = '<div class="quip-comment-body">'+rec.data.body+'</div>';
                    return cls+' quip-comment-expanded';
                }
                return cls+' quip-comment-collapsed';
            }
        }
        ,tbar: [{
            text: _('quip.bulk_actions')
            ,menu: [{
                text: _('quip.approve_selected')
                ,handler: this.approveSelected
                ,scope: this
            },{
                text: _('quip.delete_selected')
                ,handler: this.deleteSelected
                ,scope: this
            },'-',{
                text: _('quip.remove_selected')
                ,handler: this.removeSelected
                ,scope: this
            }]
        },{
            text: _('quip.show_deleted')
            ,handler: this.toggleDeleted
            ,enableToggle: true
            ,scope: this
        },'->',{
            xtype: 'textfield'
            ,name: 'search'
            ,id: this.ident+'-tf-search'
            ,emptyText: _('search')+'...'
            ,listeners: {
                'change': {fn: this.search, scope: this}
                ,'render': {fn: function(cmp) {
                    new Ext.KeyMap(cmp.getEl(), {
                        key: Ext.EventObject.ENTER
                        ,fn: function() {
                            this.fireEvent('change',this.getValue());
                            this.blur();
                            return true;}
                        ,scope: cmp
                    });
                },scope:this}
            }
        },{
            xtype: 'button'
            ,id: this.ident+'-filter-clear'
            ,text: _('filter_clear')
            ,listeners: {
                'click': {fn: this.clearFilter, scope: this}
            }
        }]
    });
    MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal}.superclass.constructor.call(this,config)
};
Ext.extend(MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal},MODx.grid.Grid,{
    _addEnterKeyHandler: function() {
        this.getEl().addKeyListener(Ext.EventObject.ENTER,function() {
            this.fireEvent('change');
        },this);
    }
    ,clearFilter: function() {
    	var s = this.getStore();
        s.baseParams.search = '';
        Ext.getCmp(this.ident+'-tf-search').reset();
    	this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,search: function(tf,newValue,oldValue) {
        var nv = newValue || tf;
        this.getStore().baseParams.search = nv;
        this.getBottomToolbar().changePage(1);
        this.refresh();
        return true;
    }

    ,renderAuthor: function(value,p, rec){
        return String.format(
            '<span class="quip-author"><b>{1}</b>: <a href="mailto:{2}">{2}</a><br /><i>{0}</i></span>',
            value,rec.data.name,rec.data.email,rec.data.approved
        );

        return value;
    }
    ,_renderUrl: function(v,md,rec) {
        return '<a href="'+rec.data.url+'" target="_blank">'+rec.data.pagetitle+'</a><br /><i>'+rec.data.comments+' '+_('quip.comments')+'</i>';
    }
    ,_renderPosted: function(v,md,rec) {
        var cls = 'quip-posted';
        if (!rec.data.approved) cls += ' quip-unapproved';
        if (rec.data.deleted) cls += ' quip-deleted';
        
        return '<div class="'+cls+'">'+v+'<br /><span class="quip-ip">'+rec.data.ip+'</span></div>';
    }
    ,toggleDeleted: function(btn,e) {
        var s = this.getStore();
        if (btn.pressed) {
            s.setBaseParam('deleted',1);
            btn.setText(_('quip.hide_deleted'));
        } else {
            s.setBaseParam('deleted',0);
            btn.setText(_('quip.show_deleted'));
        }
        this.getBottomToolbar().changePage(1);
        s.removeAll();
        this.refresh();
    }
    ,approveSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/approveMultiple'
                ,comments: cs
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
    ,unapproveSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/unapproveMultiple'
                ,comments: cs
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

    ,deleteSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/deleteMultiple'
                ,comments: cs
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
    ,undeleteSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/undeleteMultiple'
                ,comments: cs
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

    ,removeSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;

        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/removeMultiple'
                ,comments: cs
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
    ,updateComment: function(btn,e) {
        if (!this.updateCommentWindow) {
            this.updateCommentWindow = MODx.load({
                xtype: 'quip-window-comment-update'
                ,record: this.menu.record
                ,listeners: {
                    'success': {fn:this.refresh,scope:this}
                }
            });
        }
        this.updateCommentWindow.setValues(this.menu.record);
        this.updateCommentWindow.show(e.target);
    }
    ,rejectComment: function(btn,e) {
        if (!this.rejectCommentWindow) {
            this.rejectCommentWindow = MODx.load({
                xtype: 'quip-window-comment-reject'
                ,record: this.menu.record
                ,listeners: {
                    'success': {fn:this.refresh,scope:this}
                }
            });
        }
        this.rejectCommentWindow.setValues(this.menu.record);
        this.rejectCommentWindow.show(e.target);
    }
    ,approveComment: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/approve'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,unapproveComment: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/unapprove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,deleteComment: function() {
        MODx.msg.confirm({
            title: _('warning')
            ,text: _('quip.comment_delete_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/comment/delete'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.removeActiveRow,scope:this}
            }
        });
    }
    ,undeleteComment: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/comment/undelete'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
    ,removeComment: function() {
        MODx.msg.confirm({
            title: _('warning')
            ,text: _('quip.comment_remove_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/comment/remove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:this.removeActiveRow,scope:this}
            }
        });
    }
    ,verifyPerm: function(perm,rs) {
        var valid = true;
        for (var i=0;i<rs.length;i++) {
            if (rs[i].data.cls.indexOf(perm) == -1) {
                valid = false;
            }
        }
        return valid;
    }
    ,getMenu: function() {
        var m = [];
        if (this.getSelectionModel().getCount() > 1) {
            var rs = this.getSelectionModel().getSelections();
            if (this.verifyPerm('approve', rs)) {
                m.push({
                    text: _('quip.comment_approve_selected')
                    ,handler: this.approveSelected
                });
                m.push({
                    text: _('quip.comment_unapprove_selected')
                    ,handler: this.unapproveSelected
                });
            }
            if (this.verifyPerm('remove', rs)) {
                if (m.length > 0) { m.push('-'); }
                m.push({
                    text: _('quip.comment_delete_selected')
                    ,handler: this.deleteSelected
                });
                m.push({
                    text: _('quip.comment_undelete_selected')
                    ,handler: this.undeleteSelected
                });
                m.push('-');
                m.push({
                    text: _('quip.comment_remove_selected')
                    ,handler: this.removeSelected
                });
            }
        } else {
            var n = this.menu.record;
            var cls = n.cls.split(',');

            if (cls.indexOf('pupdate') != -1) {
                m.push({
                    text: _('quip.comment_update')
                    ,handler: this.updateComment
                });
            }
            if (cls.indexOf('papprove') != -1 && n.approved == 0) {
                m.push({
                    text: _('quip.comment_approve')
                    ,handler: this.approveComment
                })
            } else if (cls.indexOf('papprove') != -1 && n.approved == 1) {
                m.push({
                    text: _('quip.comment_unapprove')
                    ,handler: this.unapproveComment
                });
            }

            if (cls.indexOf('premove') != -1 && n.deleted == 0) {
                m.push({
                    text: _('quip.comment_delete')
                    ,handler: this.deleteComment
                })
            } else if (cls.indexOf('premove') != -1 && n.deleted == 1) {
                m.push({
                    text: _('quip.comment_undelete')
                    ,handler: this.undeleteComment
                });
            }
            if (cls.indexOf('premove') != -1 && n.deleted == 1) {
                m.push('-');
                m.push({
                    text: _('quip.comment_remove')
                    ,handler: this.removeComment
                });
            }
        }
        if (m.length > 0) {
            this.addContextMenuItem(m);
        }
    }
});
Ext.reg('modx-grid-multitvdbgrid-{/literal}{$win_id}{literal}',MODx.grid.multiTVdbgrid{/literal}{$win_id}{literal});


Quip.window.UpdateComment = function(config) {
    config = config || {};
    var quipconfig = Ext.util.JSON.decode('{/literal}{$customconfigs.quipconfig}{literal}');    
    config.url = quipconfig.connectorUrl;    
    Ext.applyIf(config,{
        title: _('quip.comment_update')
        ,url: Quip.config.connector_url
        ,baseParams: {
            action: 'mgr/comment/update'
        }
        ,width: 600
        ,fields: [{
            xtype: 'hidden'
            ,name: 'id'
        },{
            xtype: 'textfield'
            ,fieldLabel: _('quip.name')
            ,name: 'name'
            ,anchor: '90%'        
        },{
            xtype: 'textfield'
            ,fieldLabel: _('quip.email')
            ,name: 'email'
            ,anchor: '90%'        
        },{
            xtype: 'textfield'
            ,fieldLabel: _('quip.website')
            ,name: 'website'
            ,anchor: '90%'        
        },{
            xtype: 'statictextfield'
            ,fieldLabel: _('quip.ip')
            ,name: 'ip'
            ,anchor: '90%'
            ,submitValue: false
        },{
            xtype: 'textarea'
            ,hideLabel: true
            ,name: 'body'
            ,width: 550
            ,grow: true
        }]
        ,keys: []
    });
    Quip.window.UpdateComment.superclass.constructor.call(this,config);
};
Ext.extend(Quip.window.UpdateComment,MODx.Window);
Ext.reg('quip-window-comment-update',Quip.window.UpdateComment);

{/literal}