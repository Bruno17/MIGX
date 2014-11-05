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

$gridcontextmenus['remove_migx']['code']="
        m.push({
            className : 'remove', 
            text: '[[%migx.remove]]',
            handler: 'this.remove'
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