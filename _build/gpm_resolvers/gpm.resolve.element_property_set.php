<?php
use xPDO\Transport\xPDOTransport;

/**
 * Include bootstrap when installing the package
 *
 * THIS RESOLVER IS AUTOMATICALLY GENERATED, NO CHANGES WILL APPLY
 *
 * @package migx
 * @subpackage build
 *
 * @var \MODX\Revolution\modCategory $object
 * @var \MODX\Revolution\modX $modx
 * @var array $options
 * @var array $fileMeta
 */

$modx =& $object->xpdo;
if ($options[xPDOTransport::PACKAGE_ACTION] === xPDOTransport::ACTION_UNINSTALL) return true;

$propertySetsCache = [];

$elementClasses = [
    'snippets' => 'MODX\\Revolution\\modSnippet',
    'chunks' => 'MODX\\Revolution\\modChunk',
    'templates' => 'MODX\\Revolution\\modTemplate',
    'plugins' => 'MODX\\Revolution\\modPlugin',
];

foreach ($elementClasses as $type => $elementClass) {
    if (isset($fileMeta[$type]) && is_array($fileMeta[$type])) {
        foreach ($fileMeta[$type] as $elementName => $propertySets) {
            /** @var \MODX\Revolution\modElement $element */
            $element = $modx->getObject($elementClass, ['name' => $elementName]);
            if (!$element) continue;

            if (empty($propertySets)) {
                $modx->removeCollection(\MODX\Revolution\modElementPropertySet::class, ['element' => $element->id, 'element_class' => $elementClass]);
                continue;
            }

            if (!is_array($propertySets)) continue;

            foreach ($propertySets as $propertySetName) {
                if (!isset($propertySetsCache[$propertySetName])) {
                    /** @var \MODX\Revolution\modPropertySet $propertySet */
                    $propertySet = $modx->getObject(\MODX\Revolution\modPropertySet::class, ['name' => $propertySetName]);
                    if (!$propertySet) continue;

                    $propertySetsCache[$propertySetName] = $propertySet->id;
                }

                $elementPropertySet = $modx->getObject(\MODX\Revolution\modElementPropertySet::class, ['element' => $element->id, 'element_class' => $elementClass, 'property_set' => $propertySetsCache[$propertySetName]]);
                if ($elementPropertySet) continue;

                $elementPropertySet = $modx->newObject(\MODX\Revolution\modElementPropertySet::class);
                $elementPropertySet->set('element', $element->id);
                $elementPropertySet->set('element_class', $elementClass);
                $elementPropertySet->set('property_set', $propertySetsCache[$propertySetName]);
                $elementPropertySet->save();
            }
        }
    }
}

return true;
