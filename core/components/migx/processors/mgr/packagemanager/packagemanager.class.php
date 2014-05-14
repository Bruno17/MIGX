<?php

/**
 * 
 *
 * Note: This page is not to be accessed directly.
 *
 * @package migx
 * @subpackage processors
 */

class migxCreatePackageProcessor extends modProcessor
{

    public function process()
    {

        $properties = $this->getProperties();
        
        $prefix = isset($properties['prefix']) && !empty($properties['prefix']) ? $properties['prefix'] : null;
        if (isset($properties['usecustomprefix']) && !empty($properties['usecustomprefix'])) {
            $prefix = isset($properties['prefix']) ? $properties['prefix'] : null;
        }

        $packageName = $properties['packageName'];
        //$tablename = $properties['tablename'];
        $tableList = isset($properties['tableList']) && !empty($properties['tableList']) ? $properties['tableList'] : null;
        //$tableList = array(array('table1'=>'classname1'),array('table2'=>'className2'));
        $restrictPrefix = true;

        $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
        $modelpath = $packagepath . 'model/';
        $schemapath = $modelpath . 'schema/';
        $schemafile = $schemapath . $packageName . '.mysql.schema.xml';

        $manager = $this->modx->getManager();
        $generator = $manager->getGenerator();

        if ($properties['task'] == 'createPackage' || $properties['task'] == 'writeSchema') {
            // create folders
            if (!is_dir($packagepath)) {
                mkdir($packagepath, 0777);
            }
            if (!is_dir($modelpath)) {
                mkdir($modelpath, 0777);
            }
            if (!is_dir($schemapath)) {
                mkdir($schemapath, 0777);
            }
        }


        if ($properties['task'] == 'createPackage') {

            if (!file_exists($schemafile)) {
                $handle = fopen($schemafile, "w");
            }

        }

        if ($properties['task'] == 'writeSchema') {

            //Use this to create a schema from an existing database
            $xml = $generator->writeSchema($schemafile, $packageName, 'xPDOObject', $prefix, $restrictPrefix, $tableList);

        }

        if ($properties['task'] == 'parseSchema') {
            //Use this to generate classes and maps from your schema
            // NOTE: by default, only maps are overwritten; delete class files if you want to regenerate classes
            $generator->parseSchema($schemafile, $modelpath);
        }

        if ($properties['task'] == 'addmissing' || $properties['task'] == 'removedeleted' || $properties['task'] == 'checkindexes') {
            $prefix = empty($prefix) ? null : $prefix;
            $options['addmissing'] = 0;
            $options['removedeleted'] = 0;
            $options[$properties['task']] = 1;
            
            $this->modx->addPackage($packageName, $modelpath, $prefix);
            $pkgman = $this->loadPackageManager();

            $pkgman->parseSchema($schemafile, $modelpath, true);

            $pkgman->checkClassesFields($options);

        }

        if ($properties['task'] == 'loadSchema') {
            if (file_exists($schemafile)) {
                return $this->success('', array('content' => @file_get_contents($schemafile)));
                //$this->setPlaceholder('schema', @file_get_contents($schemafile));
            }
        }

        if ($properties['task'] == 'createTables') {
            //$prefix = empty($prefix) ? null : $prefix;
            $this->modx->addPackage($packageName, $modelpath, $prefix);
            $pkgman = $this->loadPackageManager();
            $pkgman->parseSchema($schemafile, $modelpath, true);
            $pkgman->createTables();
        }

        if ($properties['task'] == 'saveSchema') {
            $fp = @fopen($schemafile, 'w+');
            if ($fp) {
                $result = @fwrite($fp, stripslashes($properties['schema']));
                @fclose($fp);
            }
        }


        return $this->success();
    }
    
    function loadPackageManager() {
        $modelPath = $this->modx->getOption('migx.core_path',null,$this->modx->getOption('core_path').'components/migx/').'model/';
        include_once ($modelPath . 'migx/migxpackagemanager.class.php');
        return new MigxPackageManager($this->modx);
    }    
    
}
return 'migxCreatePackageProcessor';
