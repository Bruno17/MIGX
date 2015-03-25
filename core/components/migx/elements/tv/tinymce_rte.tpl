<textarea id="tv{$tv->id}" name="tv{$tv->id}" class="modx-richtext rtf-tinymcetv tv{$tv->id}" {literal}onchange="MODx.fireResourceFormChange();"{/literal}>{$tv->get('value')|escape}</textarea>

<script type="text/javascript">
{literal}
Ext.onReady(function() {
    {/literal}
    MODx.makeDroppable(Ext.get('tv{$tv->id}'));
    var tvid = 'tv{$tv->id}';
    
    var field = (Ext.get('tv{$tv->id}'));
    {literal}
    field.onLoad = function(){
        TinyMCERTE.loadForTVs();
        //console.log('we load');
        if (typeof(Tiny) != 'undefined') {
		    var s={};
            if (Tiny.config){
                s = Tiny.config || {};
                delete s.assets_path;
                delete s.assets_url;
                delete s.core_path;
                delete s.css_path;
                delete s.editor;
                delete s.id;
                delete s.mode;
                delete s.path;
                s.cleanup_callback = "Tiny.onCleanup";
                var z = Ext.state.Manager.get(MODx.siteId + '-tiny');
                if (z !== false) {
                    delete s.elements;
                }			
		    }
			//s.mode = "specific_textareas";
            //s.editor_selector = "modx-richtext";
            			
            s.mode = "exact";
            s.elements = "{/literal}tv{$tv->id}{literal}";              
 		    //s.language = "en";// de seems not to work at the moment
            tinyMCE.init(s);
            
		}
    };
        
    field.onHide = function(){
        //console.log('we hide');
        if (typeof(tinyMCE) != 'undefined') {
            var tinyinstance = tinyMCE.get('{/literal}tv{$tv->id}{literal}');
            if (typeof(tinyinstance) != 'undefined') {
                tinyinstance.remove();
            }
        }     
    };
        
    field.onBeforeSubmit = function(){
        //console.log('we submit');
        if (typeof(tinyMCE) != 'undefined') {
            //tinyMCE.getInstanceById('{/literal}tv{$tv->id}{literal}').save();
            tinyMCE.get('{/literal}tv{$tv->id}{literal}').save(); 
        }       
    };        


});
{/literal}
</script>
