<?php

$gridfunctions['this.editRaw']="
    editRaw: function(btn,e) {
      this.loadWin(btn,e,'u','raw');
    }  
";

$gridcontextmenus['editraw']['code'] = "
        m.push({
            text: 'Edit raw'
            ,handler: this.editRaw
        });
";
$gridcontextmenus['editraw']['handler'] = 'this.editRaw';