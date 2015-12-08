<?php

/*
$cats = array (
0 => 'MIGX',
);
return $cats;
*/


//$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';

$element_types = 'snippets,chunks,plugins,templates';
$element_types = explode(',', $element_types);

$cats = array();

foreach ($element_types as $element_type) {
    $elementspath = $packagepath . 'elements/' . $element_type . '/';

    if (is_dir($elementspath)) {
        if ($handle = opendir($elementspath)) {
            while (false !== ($file = readdir($handle))) {
                $exploded = explode('.', $file);
                if (count($exploded) == 3 && $exploded[0] == $element_type && $exploded[2] == 'json') {
                    $elements = @file_get_contents($elementspath . $file);
                    $elements = $modx->fromJson($elements);
                    if (is_array($elements)) {
                        foreach ($elements as $element) {
                            $category_name = $modx->getOption('category_name', $element, '');
                            $element_name = $modx->getOption($nameField, $element, '');
                            $filename = $modx->getOption('filename', $element, '');

                            if (!isset($cats[$category_name])) {
                                $cats[$category_name] = array();
                                $cats[$category_name]['category'] = $category_name;
                            }

                            if (!isset($cats[$category_name][$element_type])) {
                                $cats[$category_name][$element_type] = array();
                            }
                            $element['content'] = @file_get_contents($elementspath . $filename);
                            $cats[$category_name][$element_type][] = $element;

                        }
                    }
                }
            }
            closedir($handle);
        }
    }

}

return $cats;
