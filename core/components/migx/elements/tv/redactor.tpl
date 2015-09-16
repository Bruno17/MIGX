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
        if (MODx.loadRTE) {
            MODx.loadRTE(tvid);
        } else if (window.console) {
            console.error('Unable to instantiate editor to #' + tvid + ' - MODx.loadRTE is not defined');
        }
    };

    // We don't need any specific handling for onHide or onBeforeSubmit.
    field.onHide = function(){ };
    field.onBeforeSubmit = function(){ };
});
{/literal}
</script>