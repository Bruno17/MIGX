<textarea id="tv{$tv->id}" name="tv{$tv->id}" class="rtf-ace tv{$tv->id}">{$tv->get('value')|escape}</textarea>

<script type="text/javascript">
    // <![CDATA[{literal}
    Ext.onReady(function(){
    	{/literal}
		MODx.ux.Ace.replaceTextAreas(Ext.query('#tv{$tv->id}'));		
		{literal}
	});
    {/literal}
    // ]]>
</script>
