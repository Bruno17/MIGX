<?php

include ('index.class.php');
class MigxCustomexampleManagerController extends MigxIndexManagerController {

    public function checkPermissions() {
        $configs = $this->modx->getOption('configs',$this->scriptProperties);
        //run MIGX - CMP only with given configs
        if ($configs == 'custom'){
            return true;    
        }
        return false;
    }
    
}