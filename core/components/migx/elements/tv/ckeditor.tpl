<textarea id="tv{$tv->id}" style="heigth:200;" name="tv{$tv->id}" class="rtf-ckeditor tv{$tv->id}" {literal}onchange="MODx.fireResourceFormChange();"{/literal}>{$tv->get('value')|escape}</textarea>

<script type="text/javascript">
{literal}
Ext.onReady(function() {
    {/literal}
    MODx.makeDroppable(Ext.get('tv{$tv->id}'));
    var tvid = 'tv{$tv->id}';
    
    var field = (Ext.get('tv{$tv->id}'));
   
    {literal}
    field.onLoad = function(){
        //console.log('we load');
                var textArea = Ext.get('{/literal}tv{$tv->id}{literal}').dom;
                field.htmlEditor = MODx.load({
                    xtype: 'modx-htmleditor',
                    width: 'auto',
                    height: parseInt(textArea.style.height) || 200,
                    name: textArea.name,
                    value: textArea.value || '<p></p>'
                });

                textArea.name = '';
                textArea.style.display = 'none';

                field.htmlEditor.render(textArea.parentNode);
                field.htmlEditor.editor.on('key', function(e){ MODx.fireResourceFormChange() });
            
		
    };
        
    field.onHide = function(){
        //console.log('we hide');
        field.htmlEditor.destroy();
   
    };
        
    field.onBeforeSubmit = function(){
        //console.log('we submit');
        field.htmlEditor.getValue();
   
    };        


});
{/literal}
</script>
