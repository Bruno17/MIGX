<script type="text/javascript">
// <[CDATA[
Ext.onReady(function() {
    
	Tiny.config = <?php echo $this->modx->toJSON($this->properties); ?>;
    Tiny.templates = <?php echo $this->modx->toJSON($templates); ?>;
    
});
// ]]>
</script>