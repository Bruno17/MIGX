<?php

class migxConfig extends xPDOSimpleObject
{

    public function save($cacheFlag = null)
    {

        $result = parent::save($cacheFlag);

        $preventformtabhandling = $this->get('preventformtabhandling');

        if (!$preventformtabhandling && $result) {
            $saved_formtabs = array();
            $formtabs = $this->xpdo->fromJson($this->get('formtabs'));
            if (is_array($formtabs)) {
                $posted_tabs = array();
                $pos = 1;
                foreach ($formtabs as $formtab) {
                    $migx_id = $this->xpdo->getOption('MIGX_id', $formtab, 0);
                    $formtab['pos'] = $pos;
                    if (array_key_exists($migx_id, $posted_tabs)){
                        //duplicate MIGX_id, create another unique migx_id
                        $migx_id = $migx_id.'_'.$pos;    
                    }                    
                    $posted_tabs[$migx_id] = $formtab;
                    $pos++;
                }

                if ($existing_tabs = $this->getMany('Formtabs')) {
                    foreach ($existing_tabs as $formtab_object) {
                        $migx_id = $formtab_object->get('id');
                        if (array_key_exists($migx_id, $posted_tabs)) {
                            $formtab = $posted_tabs[$migx_id];
                            $formtab_object->fromArray($formtab);
                            $formtab_object->set('handleformtabfields',1);
                            $formtab_object->save();
                            $formtab['MIGX_id'] = $formtab_object->get('id');
                            $formtab['fields'] = $formtab_object->get('saved_fields');
                            $pos = isset($formtab['pos']) ? $formtab['pos'] : 0;                            
                            $saved_formtabs[$pos] = $formtab;
                            unset($posted_tabs[$migx_id]);
                        } else {
                            $formtab_object->remove();
                        }
                    }
                }

                foreach ($posted_tabs as $formtab) {
                    if ($formtab_object = $this->xpdo->newObject('migxFormtab')) {
                        $formtab_object->fromArray($formtab);
                        $formtab_object->set('config_id', $this->get('id'));
                        $formtab_object->set('handleformtabfields',1);
                        $formtab_object->save();
                        $formtab['MIGX_id'] = $formtab_object->get('id');
                        $formtab['fields'] = $formtab_object->get('saved_fields');
                        $pos = isset($formtab['pos']) ? $formtab['pos'] : 0;                        
                        $saved_formtabs[$pos] = $formtab;
                    }
                }
                
                ksort($saved_formtabs);
                $formtabs = array();
                foreach ($saved_formtabs as $tab){
                    $formtabs[]=$tab;
                }
                
                $this->set('formtabs',$this->xpdo->toJson($formtabs));
                $this->set('preventformtabhandling',1);
                $this->save();
            }
        }

        return $result;

    }
}
