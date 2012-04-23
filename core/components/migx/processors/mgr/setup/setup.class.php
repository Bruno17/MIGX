<?php

/**
 * 
 *
 * Note: This page is not to be accessed directly.
 *
 * @package migx
 * @subpackage processors
 */

class migxSetupProcessor extends modProcessor
{

    public function process()
    {

        $properties = $this->getProperties();
        $prefix = null;
        $packageName = 'migx';
        //$tablename = $properties['tablename'];
        $tableList = isset($properties['tableList']) && !empty($properties['tableList']) ?
            $properties['tableList'] : null;
        //$tableList = array(array('table1'=>'classname1'),array('table2'=>'className2'));
        $restrictPrefix = true;

        $packagepath = $this->modx->getOption('core_path') . 'components/' . $packageName .
            '/';
        $modelpath = $packagepath . 'model/';
        $schemapath = $modelpath . 'schema/';
        $schemafile = $schemapath . $packageName . '.mysql.schema.xml';

        $manager = $this->modx->getManager();
        $generator = $manager->getGenerator();


        if ($properties['task'] == 'setupmigx') {
            $this->modx->addPackage($packageName, $modelpath, $prefix);
            $pkgman = $this->modx->migx->loadPackageManager();
            $pkgman->parseSchema($schemafile, $modelpath, true);
            $pkgman->createTables();
            $options['addmissing'] = 1;
            $pkgman->checkClassesFields($options);
        }

        if ($properties['task'] == 'upgrademigx') {
            $c = $this->modx->newQuery('modTemplateVar');
            $c->where(array('type' => 'migx'));
            if ($collection = $this->modx->getCollection('modTemplateVar', $c)) {
                foreach ($collection as $object) {
                    $tvids[] = $object->get('id');
                }
                $c = $this->modx->newQuery('modTemplateVarResource');
                $c->where(array('tmplvarid:IN' => $tvids));
                if ($resourcetvs = $this->modx->getCollection('modTemplateVarResource', $c)){
                    foreach ($resourcetvs as $tv){
                        $items = $this->modx->fromJson($tv->get('value'));
                        if (is_array($items)){
                            $i = 1;
                            $newitems = array();
                            foreach ($items as $item){
                                if (!isset($item['MIGX_id'])){
                                    $item['MIGX_id'] = $i;
                                }
                                //unset($item['MIGX_id']);
                                $i++;
                                $newitems[]=$item;
                            }
                            $tv->set('value',$this->modx->toJson($newitems));
                            $tv->save();
                        }
                    }
                }
            }


        }


        return $this->success();
    }
}
return 'migxSetupProcessor';
