{literal}
{
    title: '{/literal}{$cmptabcaption|escape}{literal}',
    defaults: {
        autoHeight: true
    },
    items: [{
        html: '<p>{/literal}{$cmptabdescription|escape}{literal}</p>',
        border: false,
        bodyCssClass: 'panel-desc'
    },
    {
        xtype: 'modx-grid-multitvdbgrid-{/literal}{$win_id}{literal}',
        preventRender: true,
        id: 'modx-grid-multitvdbgrid-{/literal}{$win_id}{literal}',
        configs: '{/literal}{$configs}{literal}',
        columns: Ext.util.JSON.decode('{/literal}{$columns}{literal}'),
        pathconfigs: Ext.util.JSON.decode('{/literal}{$pathconfigs}{literal}'),
        fields: Ext.util.JSON.decode('{/literal}{$fields}{literal}'),
        wctx: '{/literal}{$myctx}{literal}',
        url: Migx.config.connectorUrl,
        auth: '{/literal}{$auth}{literal}',
        resource_id: '{/literal}{$resource.id}{literal}',
        co_id: '{/literal}{$connected_object_id}{literal}',
        pageSize: 10,
        object_id: '{/literal}{$object_id}{literal}',
        bwrapCfg: {
            cls: 'main-wrapper'
        }       
    }]
}
{/literal}