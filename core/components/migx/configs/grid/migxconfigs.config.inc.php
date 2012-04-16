<?php

$gridfunctions['this.editRaw']="
    editRaw: function(btn,e) {
      this.loadWin(btn,e,'u','raw');
    }  
";


$gridcontextmenus['editraw']['code'] = "
        m.push({
            text: '[[%migx_edit_raw]]'
            ,handler: this.editRaw
        });
";
$gridcontextmenus['editraw']['handler'] = 'this.editRaw';


$gridfunctions['this.export_import']="
    export_import: function(btn,e) {
      this.loadWin(btn,e,'u','export_import');
    }  
";

$gridcontextmenus['export_import']['code'] = "
        m.push({
            text: '[[%migx_export_import]]'
            ,handler: this.export_import
        });
";
$gridcontextmenus['export_import']['handler'] = 'this.export_import';