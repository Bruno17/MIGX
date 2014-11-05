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