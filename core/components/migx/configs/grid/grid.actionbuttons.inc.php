<?php

$gridactionbuttons['addItem']['text'] = "'[[%migx.add]]'";
$gridactionbuttons['addItem']['handler'] = 'this.addItem,this.addNewItem';
$gridactionbuttons['addItem']['scope'] = 'this';

$gridactionbuttons['bulk']['text'] = "'[[%migx.bulk_actions]]'";
$gridactionbuttons['bulk']['menu'][0]['text'] = "'[[%migx.publish_selected]]'";
$gridactionbuttons['bulk']['menu'][0]['handler'] = 'this.publishSelected';
$gridactionbuttons['bulk']['menu'][0]['scope'] = 'this';
$gridactionbuttons['bulk']['menu'][1]['text'] = "'[[%migx.unpublish_selected]]'";
$gridactionbuttons['bulk']['menu'][1]['handler'] = 'this.unpublishSelected';
$gridactionbuttons['bulk']['menu'][1]['scope'] = 'this';
$gridactionbuttons['bulk']['menu'][2]['text'] = "'[[%migx.delete_selected]]'";
$gridactionbuttons['bulk']['menu'][2]['handler'] = 'this.deleteSelected';
$gridactionbuttons['bulk']['menu'][2]['scope'] = 'this';

$gridactionbuttons['toggletrash']['text'] = "_('migx.show_trash')";
$gridactionbuttons['toggletrash']['handler'] = 'this.toggleDeleted';
$gridactionbuttons['toggletrash']['scope'] = 'this';
$gridactionbuttons['toggletrash']['enableToggle'] = 'true';

$gridactionbuttons['exportview']['text'] = "_('migx.export_current_view')";
$gridactionbuttons['exportview']['handler'] = 'this.csvExport';
$gridactionbuttons['exportview']['scope'] = 'this';
$gridactionbuttons['exportview']['enableToggle'] = 'true';

$gridactionbuttons['exportimportmigx']['text'] = "'[[%migx.export_import]]'";
$gridactionbuttons['exportimportmigx']['handler'] = 'this.exportMigxItems';
$gridactionbuttons['exportimportmigx']['scope'] = 'this';
$gridactionbuttons['exportimportmigx']['standalone'] = '1';

$gridactionbuttons['upload']['text'] = "'[[%migx.upload_images]]'";
$gridactionbuttons['upload']['handler'] = 'this.uploadImages';
$gridactionbuttons['upload']['scope'] = 'this';
$gridactionbuttons['upload']['standalone'] = '1';

$gridactionbuttons['loadfromsource']['text'] = "'[[%migx.load_from_source]]'";
$gridactionbuttons['loadfromsource']['handler'] = 'this.loadFromSource';
$gridactionbuttons['loadfromsource']['scope'] = 'this';
$gridactionbuttons['loadfromsource']['standalone'] = '1';

$gridactionbuttons['resetwinposition']['text'] = "'Reset Win Position'";
$gridactionbuttons['resetwinposition']['handler'] = 'this.resetWinPosition';
$gridactionbuttons['resetwinposition']['scope'] = 'this';

$gridactionbuttons['emptyThrash']['text'] = "'[[%migx.emptythrash]]'";
$gridactionbuttons['emptyThrash']['handler'] = 'this.emptyThrash';
$gridactionbuttons['emptyThrash']['scope'] = 'this';

$gridactionbuttons['uploadfiles']['text'] = "'[[%migx.upload_images]]'";
$gridactionbuttons['uploadfiles']['handler'] = 'this.uploadFiles,this.uploadSuccess,this.loadFromSource';
$gridactionbuttons['uploadfiles']['scope'] = 'this';
$gridactionbuttons['uploadfiles']['standalone'] = '1';