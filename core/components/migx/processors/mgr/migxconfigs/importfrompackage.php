<?php
if (!function_exists('recursive_encode')) {

    function recursive_encode($array) {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = recursive_encode($value);
            }
            if (!is_assoc($array)) {
                $array = json_encode($array);
            }
        }
        return $array;
    }

    function is_assoc($array) {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

}

$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];
$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';
$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];


$packageName = $modx->getOption('package', $scriptProperties, '');

if (!empty($packageName)) {
    $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
    $configpath = $packagepath . 'migxconfigs/';
    if (is_dir($configpath)) {
        if ($handle = opendir($configpath)) {
            while (false !== ($file = readdir($handle))) {
                $exploded = explode('.', $file);
                if (count($exploded) == 3 && $exploded[1] == 'config' && $exploded[2] == 'js') {
                    $name = $exploded[0];
                    if ($object = $modx->getObject($classname, array('name' => $name))) {
                        $object->set('name', $name . '_bkup_' . strftime('%Y%m%d'));
                        $object->save();
                    }
                    $content = @file_get_contents($configpath . $file);
                    $object = $modx->newObject($classname);
                    $object->fromArray(recursive_encode($modx->fromJson($content)));
                    $object->set('name', $name);
                    $object->save();
                }
            }
            closedir($handle);
        }
    }
}

return $modx->error->success('');