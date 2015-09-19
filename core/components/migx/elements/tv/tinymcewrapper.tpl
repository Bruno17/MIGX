<input id="tv{$tv->id}_checkbox" onchange="{$tv->id}onmouseup(event,this)" data-tiny="tv{$tv->id}" checked="checked" title="Disable TinyMCE" type="checkbox" class="tinyTVcheck" />
<textarea rows="15" style="width:99%;" id="tv{$tv->id}" name="tv{$tv->id}" class="modx-richtext rtf-tinymcetv tv{$tv->id} migx-richtext" {literal}onchange="MODx.fireResourceFormChange();"{/literal}>{$tv->get('value')|escape}</textarea>

<script type="text/javascript">

var {$tv->id}onmouseup = function(event,el){
    var checked = Ext.get(el).dom.checked;
    if (checked) {
      tinymce.get('tv{$tv->id}').show();
      Ext.get(el).dom.title = "Disable TinyMCE";
    }
    else{
      tinymce.get('tv{$tv->id}').hide();
      Ext.get(el).dom.title = "Enable TinyMCE";
    }    
};

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
        var checked = Ext.get({/literal}tv{$tv->id}_checkbox{literal}).dom.checked;
        if (checked != false && typeof(tinymce) != 'undefined') {
            //tinyMCE.getInstanceById('{/literal}tv{$tv->id}{literal}').save();
            tinymce.get('{/literal}tv{$tv->id}{literal}').save(); 
        }       
    };        


});
{/literal}
</script>
