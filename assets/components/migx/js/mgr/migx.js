var Migx = function(config) {
    config = config || {};
    Migx.superclass.constructor.call(this,config);
};
Ext.extend(Migx,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('migx',Migx);
Migx = new Migx();