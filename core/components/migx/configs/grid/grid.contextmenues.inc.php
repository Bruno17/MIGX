<?php

$gridcontextmenus['update']['code']="
        m.push({
            className : 'update', 
            text: '[[%migx.edit]]',
            handler: 'this.update'
        });
        m.push('-');
";
$gridcontextmenus['update']['handler'] = 'this.update';

$gridcontextmenus['duplicate']['code']="
        m.push({
            className : 'duplicate', 
            text: '[[%migx.duplicate]]',
            handler: 'this.duplicate'
        });
        m.push('-');
";
$gridcontextmenus['duplicate']['handler'] = 'this.duplicate';

$gridcontextmenus['publish']['code']="
        if (n.published == 0) {
            m.push({
                className : 'publish', 
                text: '[[%migx.publish]]',
                handler: 'this.publishObject'
            })
            m.push('-');
        }
";
$gridcontextmenus['publish']['handler'] = 'this.publishObject';

$gridcontextmenus['unpublish']['code']="
if (n.published == 1) {
            m.push({
                className : 'unpublish', 
                text: '[[%migx.unpublish]]'
                ,handler: 'this.unpublishObject'
            });
            m.push('-');
        }      
";
$gridcontextmenus['unpublish']['handler'] = 'this.unpublishObject';

$gridcontextmenus['activate']['code']="
        var active = n.Joined_active || 0;
        if (active == 0) {
            m.push({
                className : 'activate', 
                text: '[[%migx.activate]]',
                handler: 'this.activateObject'
            })
            m.push('-');
        }
        
";
$gridcontextmenus['activate']['handler'] = 'this.activateObject';

$gridcontextmenus['deactivate']['code']="
        if (n.Joined_active == 1) {
            m.push({
                className : 'deactivate', 
                text: '[[%migx.deactivate]]',
                handler: 'this.deactivateObject'
            })
            m.push('-');
        }
        
";
$gridcontextmenus['deactivate']['handler'] = 'this.deactivateObject';


$gridcontextmenus['recall_remove_delete']['code']="
        if (n.deleted == 1) {
        m.push({
            className : 'recall', 
            text: '[[%migx.recall]]',
            handler: 'this.recallObject'
        });
		m.push('-');
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.removeObject'
        });						
        } else if (n.deleted == 0) {
        m.push({
            className : 'delete', 
            text: '[[%migx.delete]]',
            handler: 'this.deleteObject'
        });		
        }
";
$gridcontextmenus['recall_remove_delete']['handler'] = 'this.recallObject,this.removeObject,this.deleteObject';

$gridcontextmenus['remove']['code']="
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.removeObject'
        });						
";
$gridcontextmenus['remove']['handler'] = 'this.removeObject';


$gridcontextmenus['edit_migx']['code']="
        m.push({
            className : 'editmigx',
            text: '[[%migx.edit]]'
            ,handler: 'this.migx_update'
        });					
";

$gridcontextmenus['duplicate_migx']['code']="
        m.push({
            className : 'duplicatemigx',
            text: '[[%migx.duplicate]]'
            ,handler: 'this.migx_duplicate'
        });        					
";

$gridcontextmenus['remove_migx']['code']="
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.migx_remove'
        });						
";

$gridcontextmenus['remove_migx_and_image']['code']="
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.migx_removeMigxAndImage'
        });						
";
$gridcontextmenus['remove_migx_and_image']['handler'] = 'this.migx_removeMigxAndImage';


$gridcontextmenus['movetotop_migx']['code']="
        m.push({
            text: '[[%migx.move_to_top]]'
            ,handler: this.moveToTop
        }); 				
";

$gridcontextmenus['movetotop_bottom']['code']="
        m.push({
            text: '[[%migx.move_to_bottom]]'
            ,handler: this.moveToBottom
        }); 			
";

$gridcontextmenus['publishtarget']['code']="
        if (n.published == 0) {
            m.push({
                className : 'publish', 
                text: _('migx.publish'),
                handler: 'this.publishTargetObject'
            })
            m.push('-');
        }
        
";
$gridcontextmenus['publishtarget']['handler'] = 'this.publishTargetObject';

$gridcontextmenus['unpublishtarget']['code']="
if (n.published == 1) {
            m.push({
                className : 'unpublish', 
                text: _('migx.unpublish'),
                handler: 'this.unpublishTargetObject'
            });
            m.push('-');
        }      
";
$gridcontextmenus['unpublishtarget']['handler'] = 'this.unpublishTargetObject';