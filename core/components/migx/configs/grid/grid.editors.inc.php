<?php

$editors['this.textEditor'] = "
    textEditor : function(column){
        return new Ext.form.TextField({
            allowBlank: true,
            listeners: {
                blur: {
                    scope: this,
                    fn: function(field){
                          //on blur without specialkey reset to old value
                          field.setValue(field.value);
                          //this.updateSelected(column,newValue);
                    }                
                },
                focus: {
                    scope: this,
                    fn: function(){
                        //remember currently selected records
                        this.setSelectedRecords();
                    }
                },
                specialkey: {
                    scope: this,
                    fn: function(field, e){
                        // e.HOME, e.END, e.PAGE_UP, e.PAGE_DOWN,
                        // e.TAB, e.ESC, arrow keys: e.LEFT, e.RIGHT, e.UP, e.DOWN
                        if (e.getKey() == e.ENTER || e.getKey() == e.TAB) {
                            //remember currently selected records
                            this.setSelectedRecords();
                            //update all selected records with the new value
                            this.updateSelected(column,field.getValue());
                        }
                    } 
                }                             
            }
        });        
    }
";

$editors['this.listboxEditor'] = "
listboxEditor : function(column){
            //console.log(column);
            return new MODx.combo.ComboBox({
                typeAhead: true
                ,triggerAction: 'all'
                // transform the data already specified in html
            ,url: '[[+config.connectorUrl]]'
            ,fields: ['combo_id','combo_name']
            ,displayField: 'combo_name'
            ,valueField: 'combo_id'    
            ,pageSize: 0
            ,lazyInit: false
            ,baseParams: { 
                action: 'mgr/migxdb/process',
                processaction: 'geteditorcombo',
                configs: '[[+config.configs]]',
                resource_id: '[[+config.resource_id]]',
                field: column.dataIndex
            }			
            ,listeners: {
                'select': {
                    fn: function(tf,nv,ov){
                        this.setSelectedRecords();
                        this.updateSelected(column,tf.getValue());
                    }, 
                    scope: this
                }               
            }
            ,listClass: 'x-combo-list-small'
     })
}            
        
";