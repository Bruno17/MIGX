<?php
if (!function_exists('recursive_decode')) {

    function recursive_decode($array) {
        foreach ($array as $key => $value) {
            if ($decoded = json_decode($value, TRUE)) {
                $array[$key] = recursive_decode($decoded);
            } else {
                $array[$key] = recursive_decode($value);
            }
        }
        return $array;
    }

    /**
     * Indents a flat JSON string to make it more human-readable.
     * Source: http://recursive-design.com/blog/2008/03/11/format-json-with-php/
     *
     * @param string $json The original JSON string to process.
     *
     * @return string Indented version of the original JSON string.
     */
    function indent($json) {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = '  ';
        $newLine = "\n";
        $prevChar = '';
        $outOfQuotes = true;

        for ($i = 0; $i <= $strLen; $i++) {
            // Grab the next character in the string.
            $char = substr($json, $i, 1);
            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;
                // If this character is the end of an element, 
                // output a new line and indent the next line.
            } else if (($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos--;
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            // Add the character to the result string.
            $result .= $char;
            // If the last character was the beginning of an element, 
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
            $prevChar = $char;
        }
        return $result;
    }

}


$config = $modx->migx->customconfigs;
$prefix = $config['prefix'];
$packageName = $config['packageName'];
$packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
$modelpath = $packagepath . 'model/';
$modx->addPackage($packageName, $modelpath, $prefix);
$classname = $config['classname'];

if ($object = $modx->getObject($classname, $scriptProperties['object_id'])) {
<<<<<<< HEAD
    $row = recursive_decode($object->toArray());
=======
    $row = $modx->migx->recursive_decode($object->toArray());
>>>>>>> dd2739c2c53ed4c7c5dced486c8c7bd203f8f6bb
    $packageName = $row['extended']['packageName'];
    if (!empty($packageName)) {
        $packagepath = $modx->getOption('core_path') . 'components/' . $packageName . '/';
        $configpath = $packagepath . 'migxconfigs/';
        $filepath = $configpath . $row['name'] . '.config.js';
        if (file_exists($packagepath)) {
            if (!is_dir($configpath)) {
                mkdir($configpath, 0755);
            }
            if (is_dir($configpath)) {
                $fp = @fopen($filepath, 'w+');
                if ($fp) {
<<<<<<< HEAD
                    $result = @fwrite($fp, indent($modx->toJson($row)));
=======
                    $result = @fwrite($fp, $modx->migx->indent($modx->toJson($row)));
>>>>>>> dd2739c2c53ed4c7c5dced486c8c7bd203f8f6bb
                    @fclose($fp);
                }
                if ($result) {
                    $message = 'Config exported to ' . $filepath;
                    return $modx->error->success($message);
                }
            }
        }
    }
}

$message = 'Could not write ' . $filepath;

return $modx->error->failure($message);


