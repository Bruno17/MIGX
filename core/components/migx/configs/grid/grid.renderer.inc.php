<?php

$ctx = '{$ctx}';
$val = "' + val + '";
$httpimg = '<img style="height:60px" src="'.$val.'"/>';

$phpthumb = "'+MODx.config.connectors_url+'system/phpthumb.php?h=60&src='+val+source+'";
$phpthumbimg = '<img src="'.$phpthumb.'" alt="" />';

$renderer['this.renderImage'] = "
    renderImage : function(val, md, rec, row, col, s){
        var source = s.pathconfigs[col];
        if (val !== null) {
            if (val.substr(0,4) == 'http'){
                return '{$httpimg}' ;
            }        
            if (val != ''){
                return '{$phpthumbimg}';
            }
            return val;
        }
	}
";

$phpthumb = "'+MODx.config.connectors_url+'system/phpthumb.php?h=60&src='+val+'";
$phpthumbimg = '<img src="'.$phpthumb.'" alt="" />';

$renderer['this.renderImageFromHtml'] = "
    renderImageFromHtml : function(val, md, rec, row, col, s){
        var source = s.pathconfigs[col];
        if (val !== null) {
            if (val != ''){
                var el = document.createElement('div');
                el.innerHTML = val;               
                var img = el.querySelector('img');
                
                if (img){
                    val = img.getAttribute('src');
                    return '{$phpthumbimg}';
                }
                
            }
            return val;
        }
	}

";




$renderer['this.renderPlaceholder'] = "
renderPlaceholder : function(val, md, rec, row, col, s){
         return '[[+'+val+'.'+rec.json.MIGX_id+']]';
        
	}
";

$renderer['this.renderFirst'] = "
renderFirst : function(val, md, rec, row, col, s){
		val = val.split(':');
        return val[0];
	}        
";

$renderer['this.renderLimited'] = "
renderLimited : function(val, md, rec, row, col, s){
		var max = 100;
        var count = val.length;
		if (count>max){
            return(val.substring(0, max));
		}        
		return val;
	}    
";

$img = '<img src="{0}" alt="{1}" title="{2}">';
$renderer['this.renderCrossTick'] = "
renderCrossTick : function(val, md, rec, row, col, s) {
    var renderImage, altText, handler, classname;
    
    switch (val) {
        case 0:
        case '0':
        case '':
        case false:
            renderImage = '/assets/components/migx/style/images/cross.png';
            handler = 'this.publishObject';
            classname = 'publish';
            altText = 'No';
            break;
        case 1:
        case '1':
        case true:
            renderImage = '/assets/components/migx/style/images/tick.png';
            handler = 'this.unpublishObject';
            classname = 'unpublish';
            altText = 'Yes';
            break;
    }
    return String.format('{$img}', renderImage, altText, altText, classname, handler);
}
";

$img = '<a href="#" ><img class="controlBtn {3} {4}" src="{0}" alt="{1}" title="{2}"></a>';
$renderer['this.renderClickCrossTick'] = "
renderClickCrossTick : function(val, md, rec, row, col, s) {
    var renderImage, altText, handler, classname;
    switch (val) {
        case 0:
        case '0':
        case '':
        case false:
            renderImage = '/assets/components/migx/style/images/cross.png';
            handler = 'this.publishObject';
            classname = 'unpublished';
            altText = 'No';
            break;
        case 1:
        case '1':
        case true:
            renderImage = '/assets/components/migx/style/images/tick.png';
            handler = 'this.unpublishObject';
            classname = 'published';
            altText = 'Yes';
            break;
    }
    return String.format('{$img}', renderImage, altText, altText, classname, handler);
}
";

$base_url = $this->modx->getOption('base_url');
$img = '<a href="#" ><img class="controlBtn {3} {4} {5}" src="'.$base_url.'{0}" alt="{1}" title="{2}"></a>';
$renderer['this.renderSwitchStatusOptions'] = "
renderSwitchStatusOptions : function(val, md, rec, row, col, s) {
    var column = this.getColumnModel().getColumnAt(col);
    var ro = Ext.util.JSON.decode(rec.json[column.dataIndex+'_ro']);
    var renderImage, altText, handler, classname;
    renderImage = ro.image;
    handler = ro.handler;
    if (typeof(handler) == 'undefined' || handler == ''){
        handler = 'this.handleColumnSwitch'
    }
    classname = ro.name;
    altText = ro.name || val ;
    return String.format('{$img}', renderImage, altText, altText, classname, handler, column.dataIndex);
}
";

$tpl = '{6} <a href="#" ><img class="controlBtn btn_selectpos {4} selectpos" src="'.$base_url.'assets/components/migx/style/images/arrow_updown.png" alt="select" title="select position"></a>';
$tpl_active = '{6} '; 
$tpl_active .= '<a href="#" ><img class="controlBtn btn_before {4} {5}:before" src="'.$base_url.'assets/components/migx/style/images/arrow_up.png" alt="before" title="move before"></a>';
$tpl_active .= '<a href="#" ><img class="controlBtn btn_cancel {4} cancel" src="'.$base_url.'assets/components/migx/style/images/cancel.png" alt="cancel" title="cancel"></a>';
$tpl_active .= '<a href="#" ><img class="controlBtn btn_after {4} {5}:after" src="'.$base_url.'assets/components/migx/style/images/arrow_down.png" alt="after" title="move after"></a>';

$renderer['this.renderPositionSelector'] = "
renderPositionSelector : function(val, md, rec, row, col, s) {
    var column = this.getColumnModel().getColumnAt(col);
    var ro = Ext.util.JSON.decode(rec.json[column.dataIndex+'_ro']);
    var value, renderImage, altText, handler, classname;
    renderImage = ro.image;
    var handler = ro.handler;
    if (typeof(handler) == 'undefined' || handler == ''){
        handler = 'this.handlePositionSelector'
    }
    value = val;
    classname = 'test';
    
    if (this.isPosSelecting){
        altText = 'before' ;
        return String.format('{$tpl_active}', renderImage, altText, altText, classname, handler, column.dataIndex, value);            
    }
    else{
        altText = 'select' ;
        return String.format('{$tpl}', renderImage, altText, altText, classname, handler, column.dataIndex, value);
    }

}
";

$renderer['this.renderRowActions'] = "
	dummy:function(v,md,rec) {
        // this function is fixed in the grid
	} 
";

$renderer['this.renderChunk'] = "
renderChunk : function(val, md, rec, row, col, s) {
    this.call_collectmigxitems = true;
    return val;
}
";

$renderer['ImagePlus.MIGX_Renderer'] = "
	dummyImagePlus:function(v,md,rec) {
        // this function is included with the ImagePlus - TV
	} 
";

$renderer['this.renderDate'] = "
renderDate : function(val, md, rec, row, col, s) {
    var date;
	if (val && val != '') {
        if (typeof val == 'number') {
            date = new Date(val*1000);
        } else {
			date = Date.parseDate(val, 'Y-m-d H:i:s');
        }
        if (typeof(date) != 'undefined' ){
		    return String.format('{0}', date.format(MODx.config.manager_date_format+' '+MODx.config.manager_time_format));
        }    
	} 
	return '';
	
}
";

//$base_url = $this->modx->getOption('base_url');
$img = '<a href="#" ><img class="controlBtn {3} {4} {5}" src="{0}" alt="{1}" title="{2}"></a>';
$renderer['this.renderOptionSelector'] = "
renderOptionSelector : function(val, md, rec, row, col, s) {
    //var column = this.getColumnModel().getColumnAt(col);
    //var ro = Ext.util.JSON.decode(rec.json[column.dataIndex+'_ro']);
    var renderImage, altText, handler, classname;
    renderImage = '/assets/components/migx/style/images/tick.png';
    handler = 'this.selectSelectorOption';
    classname = 'test';
    altText = 'test';
    return String.format('{$img}', renderImage, altText, altText, classname, handler, col);
}
";