
{literal}
<script type="text/javascript">
// <![CDATA[
Ext.onReady(function() {    
    var container = Ext.getCmp('migxdb-panel-object-{/literal}{$win_id}{literal}');
    //console.log(container);
    var panel = MODx.load({
        xtype: 'panel',
        id: '{/literal}modx-window-mi-grid-update-{$win_id}-tabs{literal}',
        anchor: '100% 100%',
        items: [{
            xtype: 'modx-tabs'
            //,applyTo: '{/literal}modx-window-mi-grid-update-{$win_id}-tabs{literal}'
            ,activeTab: 0
            ,bodyStyle: { background: 'transparent' }
            ,border: true
            ,deferredRender: false
            ,autoHeight: false
            ,autoScroll: false
            ,anchor: '100% 100%'
            ,defaults: {  
                labelSeparator: ''
                ,labelAlign: 'top'
                ,border: false
                ,layout: 'form'
            }
            ,items: [
            {/literal}{foreach from=$categories item=category name=cat}{literal}
            {
                title:'{/literal}{$category.category|default:$_lang.uncategorized|ucfirst}{literal}' 
                ,layout: 'form'  
                ,cls: 'modx-panel'
                ,autoHeight: false
                ,anchor: '100% 100%'
                ,labelWidth: 100
                ,items: [
                {/literal}{foreach from=$category.tvs item=tv name='tv'}{literal}
                {
                    html:'{/literal}{$tv->get('formElement')|regex_replace:"/[\r\n]/" : " "}{literal}'     
                }
                {/literal}{if $smarty.foreach.tv.last}{else},{/if}{/foreach}{literal}
                ]                                
            }
            {/literal}{if $smarty.foreach.cat.last}{else},{/if}{/foreach}{literal}
            ]
                        
            ,itemsXXX: [{
                title: _('resource')
                ,layout: 'form'
                ,cls: 'modx-panel'
                // ,bodyStyle: { background: 'transparent', padding: '10px' } // we handle this in CSS
                ,autoHeight: false
                ,anchor: '100% 100%'
                ,labelWidth: 100
                ,items: [{
                    xtype: 'textarea'
                    ,name: 'content'
                    ,id: 'modx-'+id+'-content'
                    // ,hideLabel: true
                    ,fieldLabel: _('content')
                    ,labelSeparator: ''
                    ,anchor: '100%'
                    ,style: 'min-height: 200px'
                    ,grow: true
                },{
                    html: '<input type="text" style="width: 50%;" >'
                }]
            }]            
            
        }]

    });
    
    container.add(panel);
    container.doLayout();
    
	{/literal}{if $tvcount GT 0}{literal}
    {/literal}{/if}{literal}
});    
// ]]>
</script>
{/literal}
{$scriptsXXX}