<?php

$gridcontextmenus['editArticle']['code']="
        m.push({
            className : 'editArticle', 
            text: _('migx.edit'),
            handler: 'this.editArticle'
        });
        m.push('-');
";

$gridcontextmenus['editArticle']['handler'] = 'this.editArticle';

$gridfunctions['this.editArticle'] = "
editArticle: function(btn,e) {
        location.href = 'index.php?a='+MODx.request.a+'&id='+this.menu.record.id;
    }
";