<?php
$modx =& $object->xpdo;
$modx->log(modX::LOG_LEVEL_INFO, ' a ') ;
if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modx =& $object->xpdo;
            $processorsPath = $modx->getOption('migx.core_path',null,$modx->getOption('core_path').'components/migx/').'processors/mgr/packagemanager/';
            $action = 'packagemanager';
            $options = array(
                'processors_path'=>$processorsPath
            ); 
            $properties = array(
                'task'=>'createTables',
                'packageName'=>'migx'
            );
            $modx->log(modX::LOG_LEVEL_INFO, ' b ') ;            
            $response = $modx->runProcessor($action,$properties,$options);
            $modx->log(modX::LOG_LEVEL_INFO, ' c ') ;
  
            break;
        case xPDOTransport::ACTION_UPGRADE:
            $modx =& $object->xpdo;
            $processorsPath = $modx->getOption('migx.core_path',null,$modx->getOption('core_path').'components/migx/').'processors/mgr/packagemanager/';
            $action = 'packagemanager';
            $options = array(
                'processors_path'=>$processorsPath
            ); 
            $properties = array(
                'task'=>'createTables',
                'packageName'=>'migx'
            );            
 
            $response = $modx->runProcessor($action,$properties,$options);
            $properties = array(
                'task'=>'addmissing',
                'packageName'=>'migx'
            );            
 
            $response = $modx->runProcessor($action,$properties,$options);
            
            $response = $modx->runProcessor($action,$properties,$options);
            $properties = array(
                'task'=>'checkindexes',
                'packageName'=>'migx'
            );            

            $modx->log(modX::LOG_LEVEL_INFO, ' d ') ;            
            $response = $modx->runProcessor($action,$properties,$options);
            $modx->log(modX::LOG_LEVEL_INFO, ' e ') ;                         
                    
            break;
    }
    
}
return true;