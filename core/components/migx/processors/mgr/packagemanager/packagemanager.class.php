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

        $isMODX3 = $this->modx->getVersionData()['version'] >= 3;
        $properties = $this->getProperties();
        $prefix = isset($properties['prefix']) && !empty($properties['prefix']) ? $properties['prefix'] : null;
        $restrictPrefix = true;
        if (isset($properties['usecustomprefix']) && !empty($properties['usecustomprefix'])) {
            $prefix = isset($properties['prefix']) ? $properties['prefix'] : null;
            if (empty($prefix)) {
                $restrictPrefix = false;
            }
        }
        if (empty($properties['package']) && empty($properties['packageName'])){
            return $this->success('Error: No Package Name');
        }

        $packageName = $properties['package'] = $properties['packageName'];
        $lc_packageName = $properties['lc_package'] = strtolower($packageName);
        $namespace_prefix = ucfirst($packageName) . '\\';
        $package_namespace = $namespace_prefix . 'Model\\';

        //$tablename = $properties['tablename'];
        $tableList = isset($properties['tableList']) && !empty($properties['tableList']) ? $properties['tableList'] : null;
        //$tableList = array(array('table1'=>'classname1'),array('table2'=>'className2'));

        if ($properties['task'] == 'addExtensionPackage') {
            $this->modx->addExtensionPackage($packageName,"[[++core_path]]components/$packageName/model/");
            return $this->success('', array('content' => @file_get_contents($schemafile)));
            //$this->setPlaceholder('schema', @file_get_contents($schemafile));
        }

        $packagepath = $this->modx->migx->findPackagePath($packageName);

        if ($isMODX3){
            $modelpath = $packagepath . 'src/';
            $schemapath = $packagepath . 'schema/';
        }  else {
            $modelpath = $packagepath . 'model/';
            $schemapath = $modelpath . 'schema/';
        }

        $schemafile = $schemapath . $lc_packageName . '.mysql.schema.xml';

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

            $permissions = octdec('0' . (int)($this->modx->getOption('new_folder_permissions', null, '755', true)));

            if (!is_dir($packagepath)) {
                if (!@mkdir($packagepath, $permissions, true)) {
                    $this->modx->error->addError('could not create path: ' . $packagepath);
                    $this->modx->log(MODX_LOG_LEVEL_ERROR, sprintf('[migx create package]: could not create directory %s).', $packagepath));
                } else {
                    chmod($packagepath, $permissions);
                    $this->modx->error->addError('path created: ' . $packagepath);
                }
            } else {
                $this->modx->error->addError('path exists allready: ' . $packagepath);
            }
            if (!is_dir($modelpath)) {
                if (!@mkdir($modelpath, $permissions, true)) {
                    $this->modx->error->addError('could not create path: ' . $modelpath);
                    $this->modx->log(MODX_LOG_LEVEL_ERROR, sprintf('[migx create package]: could not create directory %s).', $modelpath));
                } else {
                    chmod($modelpath, $permissions);
                    $this->modx->error->addError('path created: ' . $modelpath);
                }
            } else {
                $this->modx->error->addError('path exists allready: ' . $modelpath);
            }
            if (!is_dir($schemapath)) {
                if (!@mkdir($schemapath, $permissions, true)) {
                    $this->modx->error->addError('could not create path: ' . $schemapath);
                    $this->modx->log(MODX_LOG_LEVEL_ERROR, sprintf('[migx create package]: could not create directory %s).', $schemapath));
                } else {
                    chmod($schemapath, $permissions);
                    $this->modx->error->addError('path created: ' . $schemapath);
                }
            } else {
                $this->modx->error->addError('path exists allready: ' . $schemapath);
            }
        }

        if ($properties['task'] == 'createPackage') {
            $content = '';
            if ($isMODX3){
                $ns_class = 'MODX\Revolution\modNamespace';
                $properties['package_namespace'] = $package_namespace;
                $schematemplate = $this->modx->migx->config['templatesPath'] . 'mgr/schemas/default.for3.mysql.schema.xml';
                $bootstraptemplate = $this->modx->migx->config['templatesPath'] . 'mgr/bootstrap/bootstrap.tpl';
                if (!file_exists($packagepath . 'bootstrap.php')) {
                    $contents = file_get_contents($bootstraptemplate);
                    $contents = str_replace('{$namespace}', rtrim($namespace_prefix,'\\'), $contents);
                    $contents = str_replace('{$table_prefix}', is_null($prefix) ? 'null' : "'" . $prefix . "'", $contents);
                    file_put_contents($packagepath . 'bootstrap.php', $contents);
                    $this->modx->error->addError('file created: ' . $packagepath . 'bootstrap.php');
                } else {
                    $this->modx->error->addError('file exists allready: ' . $packagepath . 'bootstrap.php');
                }

            }  else {
                $ns_class = 'modNamespace';
                $schematemplate = $this->modx->migx->config['templatesPath'] . 'mgr/schemas/default.mysql.schema.xml';
            }

            if ($namespace = $this->modx->getObject($ns_class,array('name' => $lc_packageName))){
                $this->modx->error->addError('MODX namespace exists allready: ' . $lc_packageName);
            } else {
                $namespace = $this->modx->newObject($ns_class);
                $namespace->set('name',$lc_packageName);
                $namespace->set('path','{core_path}components/' . $lc_packageName . '/');
                $namespace->set('assets_path','{assets_path}components/' . $lc_packageName . '/');
                $namespace->save();
                $this->modx->error->addError('MODX namespace created: ' . $lc_packageName);
            }

            if (file_exists($schematemplate)) {
                $content = file_get_contents($schematemplate);
                $chunk = $this->modx->newObject('modChunk');
                $chunk->setCacheable(false);
                $chunk->setContent($content);
                $content = $chunk->process($properties);
            }

            if (!file_exists($schemafile)) {
                file_put_contents($schemafile, $content);
                $this->modx->error->addError('File written: ' . $schemafile);
            } else {
                $this->modx->error->addError('File exists allready: ' . $schemafile);
            }

        }

        if ($properties['task'] == 'writeSchema') {

            //Use this to create a schema from an existing database
            if ($isMODX3){
                $result = $generator->writeSchema($schemafile, $package_namespace, 'xPDO\Om\xPDOObject', is_null($prefix) ? '' : $prefix, $restrictPrefix);
            } else {
                $result = $generator->writeSchema($schemafile, $packageName, 'xPDOObject', $prefix, $restrictPrefix, $tableList);
            }
            if ($result){
                $this->modx->error->addError('New file created: ' . $schemafile);
            } else {
                $this->modx->error->addError('File could not be created: ' . $schemafile);
            }

        }

        if ($properties['task'] == 'parseSchema') {
            //Use this to generate classes and maps from your schema
            // NOTE: by default, only maps are overwritten; delete class files if you want to regenerate all classes
            if ($isMODX3){
                //only update platform classes, let main classes alone
                $generator->parseSchema($schemafile, $modelpath, ["update" => 1,"namespacePrefix" => $namespace_prefix]);
            } else {
                $generator->parseSchema($schemafile, $modelpath);
            }
        }

        if ($properties['task'] == 'alterfields' || $properties['task'] == 'addmissing' || $properties['task'] == 'removedeleted' || $properties['task'] == 'checkindexes') {
            $prefix = empty($prefix) ? null : $prefix;
            $options = [];
            $options['addmissing'] = 0;
            $options['removedeleted'] = 0;
            $options[$properties['task']] = 1;

            if ($isMODX3){
                $xpdo->addPackage($package_namespace, $modelpath, $prefix, $namespace_prefix);
            } else {
                $xpdo->addPackage($packageName, $modelpath, $prefix);
            }

            $pkgman = $this->modx->migx->loadPackageManager();
            $pkgman->xpdo2 = &$xpdo;
            $pkgman->manager = $xpdo->getManager();
            if ($isMODX3){
                $pkgman->parseSchema($schemafile, $modelpath, ['compile' => true, 'update' => 1,"namespacePrefix" => $namespace_prefix]);
            } else {
                $pkgman->parseSchema($schemafile, $modelpath, true);
            }

            $pkgman->checkClassesFields($options);

        }

        if ($properties['task'] == 'loadSchema') {

            if (file_exists($schemafile)) {
                return $this->success('', array('content' => @file_get_contents($schemafile)));
                //$this->setPlaceholder('schema', @file_get_contents($schemafile));
            }
            return $this->success('Error: Could not find ' . $schemafile);
        }

        if ($properties['task'] == 'createTables') {
            //$prefix = empty($prefix) ? null : $prefix;
            $pkgman = $this->modx->migx->loadPackageManager();
            $pkgman->manager = $xpdo->getManager();
            if ($isMODX3){
                $xpdo->addPackage($package_namespace, $modelpath, $prefix, $namespace_prefix);
                $pkgman->parseSchema($schemafile, $modelpath, ['compile' => true, 'update' => 1,"namespacePrefix" => $namespace_prefix]);
            } else {
                $xpdo->addPackage($packageName, $modelpath, $prefix);
                $pkgman->parseSchema($schemafile, $modelpath, true);
            }
            $pkgman->createTables();
        }

        if ($properties['task'] == 'saveSchema') {
            $fp = @fopen($schemafile, 'w+');
            if ($fp) {
                if ($isMODX3){
                    //The schema now contains backslashes
                    $result = @fwrite($fp, $properties['schema']);
                } else {
                    $result = @fwrite($fp, stripslashes($properties['schema']));
                }
                @fclose($fp);
            }
        }

        $messages = $this->modx->error->getErrors();
        return $this->success(implode("\n",$messages));
    }
}
return 'migxCreatePackageProcessor';