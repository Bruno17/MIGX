<?php

if (isset($scriptProperties['data'])) {
    $settings_output = $scriptProperties['data'];
    $scriptProperties = array_merge($scriptProperties, json_decode($scriptProperties['data'], true));
}

$file = $modx->getOption('file', $scriptProperties, '');
$filepath = $modx->getOption('base_path') . $file;
$matchfields = $modx->getOption('matchfields', $scriptProperties, '');
$settings = $modx->getOption('settings', $scriptProperties, '');

$matchfields_array = explode(',', $matchfields);

$settings_array = array();
if (!is_array($settings)) {
    $settings_array[] = $settings;
} else {
    $settings_array = $settings;
}
$emptytable = in_array('empty', $settings_array) ? true : false;
$insert_missing_only = in_array('insert_missing_only', $settings_array) ? true : false;
$update_existing = in_array('update_existing', $settings_array) ? true : false;
$save_settings = in_array('save_settings', $settings_array) ? true : false;
$setPrimaryKeys = in_array('preserve_keys', $settings_array) ? true : false;

if (!file_exists($filepath)) {
    $message = 'could not find csv - file at: <br /> ' . $filepath;
    return $modx->error->failure($message);
}

function cleartable($classname) {
    global $modx;

    $c = $modx->newQuery($classname);

    if ($collection = $modx->getIterator($classname, $c)) {
        foreach ($collection as $object) {
            $object->remove();
        }
    }
    $tablename = $modx->getTableName($classname);
    $modx->exec("alter table {$tablename} AUTO_INCREMENT =1");
}


$config = $modx->migx->customconfigs;

$prefix = isset($config['prefix']) && !empty($config['prefix']) ? $config['prefix'] : null;
if (isset($config['use_custom_prefix']) && !empty($config['use_custom_prefix'])) {
    $prefix = isset($config['prefix']) ? $config['prefix'] : '';
}

if (!empty($config['packageName'])) {
    $packageNames = explode(',', $config['packageName']);
    $packageName = isset($packageNames[0]) ? $packageNames[0] : '';

    if (count($packageNames) == '1') {
        //for now connecting also to foreign databases, only with one package by default possible
        $xpdo = $modx->migx->getXpdoInstanceAndAddPackage($config);
    } else {
        //all packages must have the same prefix for now!
        foreach ($packageNames as $p) {
            $packagepath = $modx->getOption('core_path') . 'components/' . $p . '/';
            $modelpath = $packagepath . 'model/';
            if (is_dir($modelpath)) {
                $modx->addPackage($p, $modelpath, $prefix);
            }

        }
        $xpdo = &$modx;
    }
} else {
    $xpdo = &$modx;
}

set_time_limit(1000);
$classname = $config['classname'];

$settingName = $modx->getOption('core_path') . 'components/' . $packageName . '/import/' . $classname . '.settings.js';
if ($save_settings){
    $cacheManager = $modx->getCacheManager();
    $cacheManager->writeFile($settingName, $settings_output);    
}

$idx = 1;
if (($handle = fopen($filepath, "r")) !== false) {

    if ($emptytable) {
        cleartable($classname);
    }

    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        $row = array();
        if ($idx == 1) {
            $fields = $data;
        } else {
            $num = count($data);

            for ($c = 0; $c < $num; $c++) {
                $field = $fields[$c];
                //$row[$field] = mb_convert_encoding($data[$c], "UTF-8", "ISO-8859-1");
                $row[$field] = $data[$c];
            }

            $data = array();
            $object = false;
            $count = 0;
            if ($update_existing || $insert_missing_only) {

                if (count($matchfields_array)<1||empty($matchfields_array[0])) {
                    $message = 'no matchfields specified';
                    return $modx->error->failure($message);
                }

                $c = $xpdo->newQuery($classname);
                foreach ($matchfields_array as $matchfield) {
                    if (isset($row[$matchfield])) {
                        $c->where(array($matchfield => $row[$matchfield]));
                    }
                }
                $count = $xpdo->getCount($classname, $c);
                $object = $xpdo->getObject($classname, $c);
            }
            if ($update_existing) {
                if ($object) {
                    $object->fromArray($row, '', $setPrimaryKeys);
                    $object->save();
                }
            }

            if (($insert_missing_only && $count < 1) || $count < 1) {
                if ($object = $xpdo->newObject($classname)) {
                    $object->fromArray($row, '', $setPrimaryKeys);
                    $object->save();
                }
            }


        }
        $idx++;
    }
    fclose($handle);
}

return $modx->error->success();
