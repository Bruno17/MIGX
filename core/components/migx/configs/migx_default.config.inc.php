<?php

$tvname = isset($this->config['tvname'] ) ? $this->config['tvname'] : '';

if (!empty($tvname)){
    if ($tv = $this->modx->getObject('modTemplateVar',array('name'=>$tvname))){
        $properties = $tv->get('input_properties');
        if (isset($properties['columns']) && !empty($properties['columns'])){
            $this->customconfigs['columns'] = json_decode($properties['columns'],1);
        }
    }
}