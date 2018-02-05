<?php

/**
 * 
 *
 * Note: This page is not to be accessed directly.
 *
 * @package migx
 * @subpackage processors
 */

class migxCreatePackageProcessor extends modProcessor {

    public function process() {

        $properties = $this->getProperties();
        $prefix = isset($properties['prefix']) && !empty($properties['prefix']) ? $properties['prefix'] : null;
        $restrictPrefix = true;
        if (isset($properties['usecustomprefix']) && !empty($properties['usecustomprefix'])) {
            $prefix = isset($properties['prefix']) ? $properties['prefix'] : null;
            if (empty($prefix)) {
                $restrictPrefix = false;
            }
        }

        $packageName = $properties['package'] = $properties['packageName'];
        //$tablename = $properties['tablename'];
        $tableList = isset($properties['tableList']) && !empty($properties['tableList']) ? $properties['tableList'] : null;
        //$tableList = array(array('table1'=>'classname1'),array('table2'=>'className2'));

        if ($properties['task'] == 'addExtensionPackage') {
            $this->modx->addExtensionPackage($packageName,"[[++core_path]]components/$packageName/model/");            
            return $this->success('', array('content' => @file_get_contents($schemafile)));
            //$this->setPlaceholder('schema', @file_get_contents($schemafile));
        }

        $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName . '/';
        $modelpath = $packagepath . 'model/';
        $schemapath = $modelpath . 'schema/';
        $schemafile = $schemapath . $packageName . '.mysql.schema.xml';

        if (file_exists($packagepath . 'config/config.inc.php')) {
            include ($packagepath . 'config/config.inc.php');
            if (is_null($prefix) && isset($table_prefix)) {
                $prefix = $table_prefix;
            }
            $charset = '';
            if (!empty($database_connection_charset)) {
                $charset = ';charset=' . $database_connection_charset;
            }
            $dsn = $database_type . ':host=' . $database_server . ';dbname=' . $dbase . $charset;
            $xpdo = new xPDO($dsn, $database_user, $database_password);
            //echo $o=($xpdo->connect()) ? 'Connected' : 'Not Connected';
        } else {
            $xpdo = &$this->modx;
        }

        $manager = $xpdo->getManager();
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
            $content = '';
            $schematemplate = $this->modx->migx->config['templatesPath'] . 'mgr/schemas/default.mysql.schema.xml';
            if (file_exists($schematemplate)) {
                $content = file_get_contents($schematemplate);
                $chunk = $this->modx->newObject('modChunk');
                $chunk->setCacheable(false);
                $chunk->setContent($content);
                $content = $chunk->process($properties);                
            }            

            if (!file_exists($schemafile)) {
                file_put_contents($schemafile, $content);
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

        if ($properties['task'] == 'alterfields' || $properties['task'] == 'addmissing' || $properties['task'] == 'removedeleted' || $properties['task'] == 'checkindexes') {
            $prefix = empty($prefix) ? null : $prefix;
            $options['addmissing'] = 0;
            $options['removedeleted'] = 0;
            $options[$properties['task']] = 1;

            $xpdo->addPackage($packageName, $modelpath, $prefix);
            $pkgman = $this->modx->migx->loadPackageManager();
            $pkgman->xpdo2 = &$xpdo;
            $pkgman->manager = $xpdo->getManager();
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
            $xpdo->addPackage($packageName, $modelpath, $prefix);
            $pkgman = $this->modx->migx->loadPackageManager();
            $pkgman->manager = $xpdo->getManager();
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
}
return 'migxCreatePackageProcessor';
