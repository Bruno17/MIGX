{$OnResourceTVFormPrerender|default}
{$error|default}
{if $formcaption != ''}
    <h2>{$formcaption}</h2>
{/if} 

<input type="hidden" class="mulititems_grid_item_fields" name="mulititems_grid_item_fields" value='{$fields|escape}' />
<input type="hidden" class="tvmigxid" name="tvmigxid" value='{$migxid|default}' />


    {foreach from=$categories item=category name=cat}
        {if count($category.layouts) > 0}
            {if isset($formnames) && count($formnames) > 0}
                {if $smarty.foreach.cat.first}
                    <div class="x-form-item x-tab-item {cycle values=",alt"} modx-tv" id="tvFormname-tr">
                        <label for="tvFormname" class="modx-tv-label">
                            <span class="modx-tv-caption" id="tvFormname-caption">{$multiple_formtabs_label}</span>
                            <span class="modx-tv-reset" ></span> 
                            {if $tv->descriptionX}<span class="modx-tv-description">{$tv->descriptionX}</span>{/if}
                        </label>
                        <div class="x-form-element modx-tv-form-element" style="padding-left: 200px;">
                            <select id="tvFormname" name="tvFormname">
                            {foreach from=$formnames item=item}
	                            <option value="{$item.value}" {if $item.selected} selected="selected"{/if}>{$item.text}</option>
                            {/foreach}
                            </select>
                        </div>
                        <br class="clear" />
                    </div>
                    <script type="text/javascript">
                    // <![CDATA[
                    {literal}

                    MODx.combo.FormnameDropdown = function(config) {
                        config = config || {};
                        Ext.applyIf(config,{
                            transform: 'tvFormname'
                            ,id: 'tvFormname'
                            ,triggerAction: 'all'
                            ,width: 350
                            ,allowBlank: true
                            ,maxHeight: 300
                            ,editable: false
                            ,typeAhead: false
                            ,forceSelection: false
                            ,msgTarget: 'under'
                            ,listeners: { 
		                    'select': {fn:this.selectForm,scope:this}
		                }});
                        MODx.combo.FormnameDropdown.superclass.constructor.call(this,config);
                        //this.config = config;
                        //return this;
                    };
                    Ext.extend(MODx.combo.FormnameDropdown,Ext.form.ComboBox,{
	                    selectForm: function() {
		                    var win = Ext.getCmp('{/literal}modx-window-mi-grid-update-{$win_id}{literal}');
                            //win.fp.autoLoad.params.record_json=this.baseParams.record_json;
                            win.switchForm();
		                    //panel.autoLoad.params['context']=this.getValue();
		                    //panel.doAutoLoad();
		                    //MODx.fireResourceFormChange();
	                    }
                    }); 
                    Ext.reg('modx-combo-formnamedropdown',MODx.combo.FormnameDropdown);
                    MODx.load({
                        xtype: 'modx-combo-formnamedropdown'
                    });
                    {/literal}
                    // ]]>
                    </script>    
                {/if}
            {/if}
            {if count($categories) > 1 AND !$category.print_before_tabs}
            <div id="modx-window-mi-grid-update-{$win_id}-tabs">    
            {/if}
            {if count($categories) < 2 OR ($smarty.foreach.cat.first AND $category.print_before_tabs)}
            <div id="modx-tv-tab{$category.id}" >
            {else}
            <div id="modx-tv-tab{$category.id}" class="x-tab" title="{$category.category|default:$_lang.uncategorized|ucfirst}">
            {/if}
            {foreach from=$category.layouts item=layout name='layout'}
                <div class="layout" style="width:100%;clear:both;float:left;{$layout.style|default}">
                    {if $layout.caption != ''}
                        <h3>{$layout.caption}</h3>
                    {/if}                    
                    {foreach from=$layout.columns item=layoutcolumn name='layoutcolumn'}
                        <div class="column" style="{if !$smarty.foreach.layoutcolumn.last}padding-right: 10px;{/if}width:{$layoutcolumn.width};min-width:{$layoutcolumn.minwidth};float:left;{$layoutcolumn.style}">
                            {if $layoutcolumn.caption != ''}
                                <h4>{$layoutcolumn.caption}</h4>
                            {/if}                              
                            {foreach from=$layoutcolumn.tvs item=tv name='tv'}
                                {if $tv->type EQ "description_is_code"}
                                    {$tv->get('formElement')}
                                {elseif $tv->type NEQ "hidden"}
                                    <div class="x-form-item x-tab-item {cycle values=",alt"} modx-tv" id="tv{$tv->id}-tr" style="width: calc(100% - 5px); padding:0 !important;{if $tv->display EQ "none"}display:none;{/if} ">
                                        <label for="tv{$tv->id}" class="x-form-item-label modx-tv-label" style="width: auto;">
                                            <div class="modx-tv-label-title"> 
                                                {if $showCheckbox|default}<input type="checkbox" name="tv{$tv->id}-checkbox" class="modx-tv-checkbox" value="1" />{/if}
                                                <span class="modx-tv-caption" id="tv{$tv->id}-caption">{$tv->caption}</span>
                                            </div>    
                                            <a class="modx-tv-reset" id="modx-tv-reset-{$tv->id}" onclick="MODx.resetTV({$tv->id});" title="{$_lang.set_to_default}"></a>
                                            {if $tv->description}<span class="modx-tv-label-description">{$tv->description}</span>{/if}
                                        </label>
                                        {if $tv->inherited}<br /><span class="modx-tv-inherited">{$_lang.tv_value_inherited}</span>{/if}
                                        <div class="x-form-clear-left"></div>
                                        <div class="x-form-element modx-tv-form-element" style="padding-left: 0px;">
                                            <input type="hidden" id="tvdef{$tv->id}" value="{$tv->default_text|escape}" />
                                            {$tv->get('formElement')}
                                        </div>
                                     </div>
                                    <script type="text/javascript">{literal}Ext.onReady(function() { new Ext.ToolTip({{/literal}target: 'tv{$tv->id}-caption',html: '[[*{$tv->name}]]'{literal}});});{/literal}</script>
                                {else}
                                    <input type="hidden" id="tvdef{$tv->id}" value="{$tv->default_text|escape}" />
                                    {$tv->get('formElement')}
                                {/if}
                            {/foreach}<!-- $layoutcolumn.tvs  -->
                        </div> <!-- column  -->
                    {/foreach}<!-- $layout.columns  -->    
                </div> <!-- layout  -->
            {/foreach}<!-- $category.layouts  -->
            </div>
            {if $smarty.foreach.cat.first AND $category.print_before_tabs}
                <br class="clear" />
            {/if}            
            {if count($categories) > 1 AND ($smarty.foreach.cat.last)}
            </div>    
            {/if}         
        {/if}
       
    {/foreach}<!-- $categories  -->


{if count($categories) > 1}
{literal}
<script type="text/javascript">
// <![CDATA[
Ext.onReady(function() {    
    var tabs = MODx.load({
        xtype: 'modx-tabs'
        ,applyTo: '{/literal}modx-window-mi-grid-update-{$win_id}-tabs{literal}'
        ,activeTab: 0
        ,autoTabs: true
        ,border: false
        ,plain: true
        ,hideMode: 'offsets'
        ,defaults: {
            bodyStyle: 'padding: 5px;'
            ,autoHeight: true
        }
        ,deferredRender: false
    });
    var win = Ext.getCmp('{/literal}modx-window-mi-grid-update-{$win_id}{literal}');    
    win.tabs = tabs;
    
	{/literal}{if $tvcount GT 0}{literal}
    {/literal}{/if}{literal}
});    
// ]]>
</script>
{/literal}

{/if}
{$scripts|default}
{$OnResourceTVFormRender|default}
<br class="clear" />