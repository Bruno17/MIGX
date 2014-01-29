<div id="tv-input-properties-form{$tv}"></div>
{literal}

<script type="text/javascript">
// <![CDATA[
var params = {
{/literal}{foreach from=$params key=k item=v name='p'}
 '{$k}': '{$v|escape:"javascript"}'{if NOT $smarty.foreach.p.last},{/if}
{/foreach}{literal}
};
var oc = {'change':{fn:function(){Ext.getCmp('modx-panel-tv').markDirty();},scope:this}};
MODx.load({
    xtype: 'panel'
    ,layout: 'form'
    ,autoHeight: true
    ,labelWidth: 150
    ,border: false
    ,items: [{
        xtype: 'textarea'
        ,fieldLabel: '{/literal}{$mig.tabs}{literal}'
        ,description: '{/literal}{$mig.tabs_desc}{literal}'
        ,name: 'inopt_formtabs'
        ,hiddenName: 'inopt_formtabs'
        ,id: 'inopt_formtabs{/literal}{$tv}{literal}'
        ,value: params['formtabs']
        ,width: 600
        ,height: 150
        ,listeners: oc
    }]
    ,renderTo: 'tv-input-properties-form{/literal}{$tv}{literal}'
});
// ]]>
</script>
{/literal}