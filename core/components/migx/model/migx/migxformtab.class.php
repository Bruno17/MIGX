<?php

class migxFormtab extends xPDOSimpleObject
{

    public function save($cacheFlag = null)
    {


        $result = parent::save($cacheFlag);

        $handleformtabfields = $this->get('handleformtabfields');

        if ($handleformtabfields && $result) {

            $fields = $this->get('fields');
            if (!is_array($fields)) {
                $fields = $this->xpdo->fromJson($fields);
            }

            $saved_fields = array();
            if (is_array($fields)) {
                $posted_fields = array();
                $pos = 1;
                foreach ($fields as $field) {
                    $migx_id = $this->xpdo->getOption('MIGX_id', $field, 0);
                    $field['pos'] = $pos;
                    if (array_key_exists($migx_id, $posted_fields)) {
                        //duplicate MIGX_id, create another unique migx_id
                        $migx_id = $migx_id . '_' . $pos;
                    }
                    $posted_fields[$migx_id] = $field;

                    //print_r($field);

                    $pos++;
                }

                if ($existing_fields = $this->getMany('Fields')) {
                    foreach ($existing_fields as $field_object) {
                        $migx_id = $field_object->get('id');
                        if (array_key_exists($migx_id, $posted_fields)) {
                            $field = $posted_fields[$migx_id];
                            $field_object->fromArray($field);
                            $field_object->save();
                            $field['MIGX_id'] = $field_object->get('id');
                            $pos = isset($field['pos']) ? $field['pos'] : 0;
                            $saved_fields[$pos] = $field;
                            unset($posted_fields[$migx_id]);
                        } else {
                            $field_object->remove();
                        }
                    }
                }

                foreach ($posted_fields as $field) {
                    if ($field_object = $this->xpdo->newObject('migxFormtabField')) {
                        $field_object->fromArray($field);
                        $field_object->set('formtab_id', $this->get('id'));
                        $field_object->set('config_id', $this->get('config_id'));
                        $field_object->save();
                        $field['MIGX_id'] = $field_object->get('id');
                        $pos = isset($field['pos']) ? $field['pos'] : 0;
                        $saved_fields[$pos] = $field;
                    }
                }

                ksort($saved_fields);
                $fields = array();
                foreach ($saved_fields as $field) {
                    $fields[] = $field;
                }

                $this->set('saved_fields', $fields);
            }
            $this->set('handleformtabfields', 0); //prevent double-saving of fields
        }

        return $result;

    }
}
