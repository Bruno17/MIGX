<textarea id="tv{$tv->id}" name="tv{$tv->id}" class="modx-richtext rtf-tinymcetv tv{$tv->id}" {literal}onchange="MODx.fireResourceFormChange();"{/literal}>{$tv->get('value')|escape}</textarea>

<script type="text/javascript">
{literal}
Ext.onReady(function() {
    {/literal}
    //MODx.makeDroppable(Ext.get('tv{$tv->id}'));
    var tvid = 'tv{$tv->id}';
    
    var field = (Ext.get('tv{$tv->id}'));
    {literal}
    field.onLoad = function(){
        {/literal}{$tinymce_chunk}{literal}
    };
        
    field.onHide = function(){
        //console.log('we hide');
        if (typeof(tinymce) != 'undefined') {
            var tinyinstance = tinymce.get('{/literal}tv{$tv->id}{literal}');
            if (tinyinstance && typeof(tinyinstance) != 'undefined') {
                tinyinstance.remove();
            }
        }     
    };
        
    field.onBeforeSubmit = function(){
        //console.log('we submit');
        if (typeof(tinymce) != 'undefined') {
            //tinyMCE.getInstanceById('{/literal}tv{$tv->id}{literal}').save();
            tinymce.get('{/literal}tv{$tv->id}{literal}').save(); 
        }       
    };        


});
{/literal}
</script>
