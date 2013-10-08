<?php

$items = is_array($items) ? $items : array();

if (!empty($isnew) || $index == 'append') {
    $autoinc = 1;
    foreach ($items as $item) {
        if (isset($item['MIGX_id'])) {
            if ($item['MIGX_id'] >= $autoinc) {
                $autoinc = $item['MIGX_id'] + 1;
            }
        }
    }
    $data['MIGX_id'] = $autoinc;
    $items[] = $data;
} else {
    $items[$index] = $data;
}

$items = count($items)>0 ? $items : '';
