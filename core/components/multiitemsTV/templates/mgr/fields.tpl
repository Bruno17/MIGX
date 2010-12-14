{$OnResourceTVFormPrerender}

<h2>TV</h2>

<input type="hidden" class="mulititems_grid_item_fields" name="mulititems_grid_item_fields" value='{$fields}' />

<div id="modx-multiitemsgridtv-tabs">
{foreach from=$categories item=category}
{if count($category->tvs) > 0}

    <div id="modx-tv-tab{$category->id}" class="x-tab" title="{$category->category|default:$_lang.uncategorized|ucfirst}">
    
    {foreach from=$category->tvs item=tv name='tv'}

{if $tv->type NEQ "hidden"}
    <div class="x-form-item x-tab-item {cycle values=",alt"} modx-tv" id="tv{$tv->id}-tr">
        <label for="tv{$tv->id}" class="modx-tv-label">

            {if $showCheckbox}<input type="checkbox" name="tv{$tv->id}-checkbox" class="modx-tv-checkbox" value="1" />{/if}
            <span class="modx-tv-caption" id="tv{$tv->id}-caption">{$tv->caption}</span>
            <a class="modx-tv-reset" href="javascript:;" onclick="MODx.resetTV({$tv->id});" title="{$_lang.set_to_default}"></a>

            {if $tv->description}<span class="modx-tv-description">{$tv->description}</span>{/if}
            {if $tv->inherited}<br /><span class="modx-tv-inherited">{$_lang.tv_value_inherited}</span>{/if}
        </label>
        <div class="x-form-element modx-tv-form-element" style="padding-left: 200px;">
            <input type="hidden" id="tvdef{$tv->id}" value="{$tv->default_text|escape}" />
            {$tv->get('formElement')}
        </div>

        <br class="clear" />
    </div>
    <script type="text/javascript">{literal}Ext.onReady(function() { new Ext.ToolTip({{/literal}target: 'tv{$tv->id}-caption',html: '[[*{$tv->name}]]'{literal}});});{/literal}</script>
{else}
    <input type="hidden" id="tvdef{$tv->id}" value="{$tv->default_text|escape}" />
    {$tv->get('formElement')}
{/if}
    {/foreach}

    </div>
{/if}
{/foreach}
</div>

{literal}
<script type="text/javascript">
// <![CDATA[
Ext.onReady(function() {    

    MODx.load({
        xtype: 'modx-tabs'
        ,applyTo: 'modx-multiitemsgridtv-tabs'
        ,activeTab: 0
        ,autoTabs: true
        ,border: false
        ,plain: true
        ,width: '800px'
        ,hideMode: 'offsets'
        ,autoScroll: true
        ,defaults: {
            bodyStyle: 'padding: 5px;'
            ,autoScroll: true
            ,autoHeight: true
        }
        ,deferredRender: false
    });
	{/literal}{if $tvcount GT 0}{literal}
    {/literal}{/if}{literal}
});    
// ]]>
</script>
{/literal}

{$OnResourceTVFormRender}

<br class="clear" />