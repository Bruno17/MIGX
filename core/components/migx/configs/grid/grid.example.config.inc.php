<?php

if ($this->config['configs'] == 'produktformate') {
    $gridactionbuttons['bulk']['menu'][3]['text'] = "'aktivieren'";
    $gridactionbuttons['bulk']['menu'][3]['handler'] = 'this.activateSelected';
    $gridactionbuttons['bulk']['menu'][3]['scope'] = 'this';
    $gridactionbuttons['bulk']['menu'][4]['text'] = "'deaktivieren'";
    $gridactionbuttons['bulk']['menu'][4]['handler'] = 'this.deactivateSelected';
    $gridactionbuttons['bulk']['menu'][4]['scope'] = 'this';
    $gridactionbuttons['bulk']['menu'][5]['text'] = "'listpreis'";
    $gridactionbuttons['bulk']['menu'][5]['handler'] = 'this.listpreisSelected';
    $gridactionbuttons['bulk']['menu'][5]['scope'] = 'this';
}

$gridfunctions['this.activateSelected'] = "
activateSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/bulkspecial'
				,configs: this.config.configs
				,task: 'activate'
                ,objects: cs
                ,co_id: '" . $_REQUEST['object_id'] . "'
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
";
$gridfunctions['this.deactivateSelected'] = "
deactivateSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/bulkspecial'
				,configs: this.config.configs
				,task: 'deactivate'
                ,co_id: '" . $_REQUEST['object_id'] . "'
                ,objects: cs
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
";
$gridfunctions['this.listpreisSelected'] = "
listpreisSelected: function(btn,e) {
        var cs = this.getSelectedAsList();
        if (cs === false) return false;
        
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/bulkspecial'
				,configs: this.config.configs
				,task: 'listpreis'
                ,objects: cs
                ,co_id: '" . $_REQUEST['object_id'] . "'
            }
            ,listeners: {
                'success': {fn:function(r) {
                    this.getSelectionModel().clearSelections(true);
                    this.refresh();
                },scope:this}
            }
        });
        return true;
    }
";

$gridactionbuttons['po.calcprices']['text'] = "'Formatpreise aktualisieren'";
$gridactionbuttons['po.calcprices']['handler'] = 'this.calculatePrices';
$gridactionbuttons['po.calcprices']['scope'] = 'this';

$gridcontextmenus['po.activateoption']['code'] = "
        m.push('-');
        if (n.active == 0) {
            m.push({
                text: 'aktivieren'
                ,handler: this.activateOption
            })
        } else if (n.active == 1) {
            m.push({
                text: 'deaktivieren'
                ,handler: this.deactivateOption
            });
        }          
";
$gridcontextmenus['po.activateoption']['handler'] = 'po.activateOption,po.deactivateOption';


$gridcontextmenus['po.activateformat']['code'] = "
        m.push('-');
        if (n.active == 0) {
            m.push({
                text: 'aktivieren'
                ,handler: this.activateFormat
            })
        } else if (n.active == 1) {
            m.push({
                text: 'deaktivieren'
                ,handler: this.deactivateFormat
            });
        }             
";
$gridcontextmenus['po.activateformat']['handler'] = 'po.activateFormat,po.deactivateFormat';


$gridfunctions['po.activateOption'] = "
activateOption: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/activateoption'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,co_id: '" . $_REQUEST['object_id'] . "'
                ,task: 'activate'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
            
        });
    }
";

$gridfunctions['po.deactivateOption'] = "
deactivateOption: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/activateoption'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,co_id: '" . $_REQUEST['object_id'] . "'
                ,task: 'deactivate'
            }
           ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
";

$gridfunctions['po.activateFormat'] = "
activateFormat: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/activateformat'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,co_id: '" . $_REQUEST['object_id'] . "'
                ,task: 'activate'
            }
            ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
            
        });
    }
";
$gridfunctions['po.deactivateFormat'] = "deactivateFormat: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/activateformat'
                ,object_id: this.menu.record.id
				,configs: this.config.configs
                ,co_id: '" . $_REQUEST['object_id'] . "'
                ,task: 'deactivate'
            }
           ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
";
$gridfunctions['this.calculatePrices'] = "
calculatePrices: function() {
        MODx.Ajax.request({
            url: this.config.url
            ,params: {
                action: 'mgr/[[+config.task]]/calculateformatprices'
				,configs: this.config.configs
                ,co_id: '" . $_REQUEST['object_id'] . "'
            }
           ,listeners: {
                'success': {fn:this.refresh,scope:this}
            }
        });
    }
";
