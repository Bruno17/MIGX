<?php

$modx = &$object->xpdo;
if ($object->xpdo) {
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:

            $modx->getVersionData();
            if (version_compare($modx->version['full_version'], '2.3', '>=')) {
                $modx->log(modX::LOG_LEVEL_INFO, 'Prepare menu for MODX Revolution 2.3.x');
                if ($object = $modx->getObject('modMenu',array('text' => 'migx'))){
                    $object->set('action','index');
                    $object->set('namespace','migx');
                    $object->save();
                    
                    if ($action = $object->getOne('Action')){
                        $action->remove();
                    }
                }
                
            } else {
                
            }

            break;
    }

}
return true;
