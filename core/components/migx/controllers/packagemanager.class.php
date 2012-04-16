<?php
class MigxPackagemanagerManagerController extends MigxManagerController
{
    public function process(array $scriptProperties = array())
    {

        if (isset($_POST['processPackageManager'])) {

            $prefix = $_POST['prefix'];
            $packageName = $_POST['packageName'];
            //$tablename = $scriptProperties['tablename'];
            $tableList = isset($_POST['tableList']) && !empty($_POST['tableList']) ? $_POST['tableList'] : null;
            //$tableList = array(array('table1'=>'classname1'),array('table2'=>'className2'));
            $restrictPrefix = true;

            $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName .
                '/';
            $modelpath = $packagepath . 'model/';
            $schemapath = $modelpath . 'schema/';
            $schemafile = $schemapath . $packageName . '.mysql.schema.xml';

            $manager = $this->modx->getManager();
            $generator = $manager->getGenerator();

            if (isset($_POST['createPackage']) || isset($_POST['writeSchema'])) {
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


            if (isset($_POST['createPackage'])) {

                if (!file_exists($schemafile)) {
                    $handle = fopen($schemafile, "w");
                }

            }

            if (isset($_POST['writeSchema'])) {

                //Use this to create a schema from an existing database
                $xml = $generator->writeSchema($schemafile, $packageName, 'xPDOObject', $prefix,
                    $restrictPrefix, $tableList);

            }

            if (isset($_POST['parseSchema'])) {
                //Use this to generate classes and maps from your schema
                // NOTE: by default, only maps are overwritten; delete class files if you want to regenerate classes
                $generator->parseSchema($schemafile, $modelpath);
            }

            if (isset($_POST['autoaddfields']) || isset($_POST['removefields'])) {
                $prefix = empty($prefix) ? null : $prefix;
                $options['addmissing'] = $_POST['autoaddfields'] ? 1 : 0;
                $options['removedeleted'] = $_POST['removefields'] ? 1 : 0;

                $this->modx->addPackage($packageName, $modelpath, $prefix);
                $pkgman = $this->modx->migx->loadPackageManager();

                $pkgman->parseSchema($schemafile, $modelpath, true);

                $pkgman->checkClassesFields($options);

            }

            if (isset($_POST['loadSchema'])) {
                if (file_exists($schemafile)) {
                    $this->setPlaceholder('schema', @file_get_contents($schemafile));
                }
            }

            if (isset($_POST['createTables'])) {
                $prefix = empty($prefix) ? null : $prefix;
                $this->modx->addPackage($packageName, $modelpath, $prefix);
                $pkgman = $this->modx->migx->loadPackageManager();
                $pkgman->parseSchema($schemafile, $modelpath, true);
                $pkgman->createTables();                
            }

            if (isset($_POST['saveSchema'])) {
                $fp = @fopen($schemafile, 'w+');
                if ($fp) {
                    $result = @fwrite($fp,stripslashes($_POST['schema']));
                    @fclose($fp);
                }
            }


            $this->setPlaceholder('request', $_REQUEST);
        }


    }
    public function getPageTitle()
    {
        return $this->modx->lexicon('migx');
    }
    public function loadCustomCssJs()
    {
        //$this->addJavascript($this->migx->config['jsUrl'].'mgr/widgets/doodles.grid.js');
        //$this->addJavascript($this->migx->config['jsUrl'].'mgr/widgets/home.panel.js');

        //$panelJs = $this->fetchTemplate($this->migx->config['templatesPath'].'mgr/formpanel.tpl');
        //$panelJs = $this->fetchTemplate($this->migx->config['templatesPath'].'mgr/gridpanel.tpl');
        //$this->addHtml('<script type="text/javascript">'.$panelJs.'</script>');
        //$this->addLastJavascript($this->migx->config['jsUrl'].'mgr/sections/index.js');
    }
    public function getTemplateFile()
    {
        return $this->migx->config['templatesPath'] . 'mgr/packagemanager.tpl';
    }
}
