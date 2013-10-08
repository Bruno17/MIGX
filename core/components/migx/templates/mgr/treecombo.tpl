MODx.MigxTreeCombo = function(config) {
    config = config || {};
    Ext.applyIf(config,{

      
    });
    MODx.MigxTreeCombo.superclass.constructor.call(this,config);
    this.options = config;
    this.config = config;

    //this.on('show',this.onShow,this);
    this.addEvents({
        success: true
        ,failure: true
		//,hide:true
		//,show:true
    });
    //this.renderIframe();	
};

Ext.extend(MODx.MigxTreeCombo,Ext.form.ComboBox,{
    extStore: null,
    tree: null,
    treeId: 0,
    
       initComponent: function() {
            this.treeId = Ext.id();
            this.focusLinkId = Ext.id();
            Ext.apply(this, {
                store: new Ext.data.SimpleStore({
                    fields: [],
                    data: [[]]
                }),
                editable: false,
                shadow: false,
                mode: 'local',
                triggerAction: 'all',
                maxHeight: 200,
                tpl: '<tpl for="."><div style="height:200px"><div id="' + this.treeId + '"></tpl>',
                selectedClass: '',
                onSelect: Ext.emptyFn,
                valueField: 'id',
            });
            var baseParams = this.baseParams;
            var root = this.root;
            var listeners = this.treelisteners;
            this.tree = new Ext.tree.TreePanel({
			    loader: new Ext.tree.TreeLoader({
			        dataUrl: MODx.config.assets_url+'components/migx/connector.php',
                    baseParams: baseParams
			    }),
                root: root,
			    autoHeight: true
		    });

            this.on('expand', this.onExpand);
            this.tree.on('click', this.onClick, this);
            this.tree.on('expandnode', this.onBeforeexpandnode, this);
            this.tree.on('collapsenode', this.onBeforecollapsenode, this);
            MODx.MigxTreeCombo.superclass.initComponent.apply(this, arguments);
    },        


    onExpand: function() {

        this.tree.render(this.treeId);        
        this.tree.getRootNode().expand();
    },

    onBeforeexpandnode: function(node) {
        //expand combobox again, if expand-icon was clicked
        this.expand();
    },
    onBeforecollapsenode: function(node) {
        //expand combobox again, if collapse-icon was clicked
        this.expand();
    },               
    onClick: function(node) {
        var v = node.id;
        this.setValue(v);
        this.fireEvent('change', this, v, this.startValue);
        this.setValue(node.text);
    }
});
Ext.reg('migx-treecombo', MODx.MigxTreeCombo);