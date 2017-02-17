<?php
$properties = &$modx->getOption('scriptProperties', $scriptProperties, array());
$object = &$modx->getOption('object', $scriptProperties, null);
$postvalues = &$modx->getOption('postvalues', $scriptProperties, array());
$form_field = $modx->getOption('form_field', $scriptProperties, array());
$value = $modx->getOption('value', $scriptProperties, '');
$validation_type = $modx->getOption('validation_type', $scriptProperties, '');
$result = '';
switch ($validation_type) {
    case 'gt25':
        if ((int) $value > 25) {
        } else {
            $error_message = $validation_type; // may be custom validation message
            $error['caption'] = $form_field;
            $error['validation_type'] = $error_message;
            $result['error'] = $error;
            $result = $modx->toJson($result);
        }
        break;
}
return $result;