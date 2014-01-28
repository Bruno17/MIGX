<script type="text/javascript" src="{$assetsUrl}redactor-1.3.4.min.js"></script>
<textarea id="tv{$tv->id}" name="tv{$tv->id}" class="rtf-tinymcetv tv{$tv->id}" {literal}onchange="MODx.fireResourceFormChange();"{/literal}>{$tv->get('value')|escape}</textarea>

<script type="text/javascript">
{literal}
    
Ext.onReady(function() {
    {/literal}
    MODx.makeDroppable(Ext.get('tv{$tv->id}'));
    var tvid = 'tv{$tv->id}';
    var field = (Ext.get('tv{$tv->id}'));
    
    
    {literal}
    field.onLoad = function(){
        var $redTv = $redTv || ((typeof($red) != 'undefined') ? $red : $.noConflict());
        $redTv(document).ready(function($) {
          {/literal}
          var tv{$tv->id}Options = {$params_json};
          $('#tv{$tv->id}').redactor(tv{$tv->id}Options);
          {literal}
        });        
    };

    // We don't need any specific handling for onHide or onBeforeSubmit.
    field.onHide = function(){ };
    field.onBeforeSubmit = function(){ };
});
{/literal}
</script>