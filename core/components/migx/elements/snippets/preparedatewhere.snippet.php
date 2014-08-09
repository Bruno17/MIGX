<?php
$name = $modx->getOption('name', $scriptProperties, '');
$date = $modx->getOption($name . '_date', $_REQUEST, '');
$dir = str_replace('T', ' ', $modx->getOption($name . '_dir', $_REQUEST, ''));

if (!empty($date) && !empty($dir) && $dir != 'all') {
    switch ($dir) {
        case '=':
            $where = array(
            'enddate:>=' => strftime('%Y-%m-%d 00:00:00',strtotime($date)),
            'startdate:<=' => strftime('%Y-%m-%d 23:59:59',strtotime($date))
            );
            break;
        case '>=':
            $where = array(
            'enddate:>=' => strftime('%Y-%m-%d 00:00:00',strtotime($date))
            );
            break;
        case '<=':
            $where = array(
            'startdate:<=' => strftime('%Y-%m-%d 23:59:59',strtotime($date))
            );            
            break;

    }

    return $modx->toJson($where);
}