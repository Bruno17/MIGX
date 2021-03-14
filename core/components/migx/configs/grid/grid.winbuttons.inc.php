<?php

$winbuttons['cancel']['text'] = "config.cancelBtnText || _('cancel')";
$winbuttons['cancel']['handler'] = 'this.cancel';
$winbuttons['cancel']['scope'] = 'this';
$winbuttons['cancel']['default'] = '1';

$winbuttons['save']['text'] = "config.saveBtnText || _('save')";
$winbuttons['save']['handler'] = 'this.submitUnclosed';
$winbuttons['save']['scope'] = 'this';
$winbuttons['save']['default'] = '1';

$winbuttons['done']['text'] = "_('save_and_close')";
$winbuttons['done']['handler'] = 'this.submit';
$winbuttons['done']['scope'] = 'this';
$winbuttons['done']['default'] = '1';