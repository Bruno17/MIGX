<?php

/**
 * Loads the TV panel for MIGX.
 *
 * Note: This page is not to be accessed directly.
 *
 * @package migx
 * @subpackage processors
 */

class migxFormProcessor extends modProcessor {

    public function process() {

        $scriptProperties = $this->getProperties();
        $config = $this->modx->migx->customconfigs;
        $resource_id = $this->modx->getOption('resource_id', $scriptProperties, '');
        $tvname = $this->modx->getOption('tv_name', $scriptProperties, '');
        $items = $this->modx->getOption('items', $scriptProperties, '');
        $items = !empty($items) ? $this->modx->fromJson($items) : array();
        $extra_params = $this->modx->getOption('extra_params', $scriptProperties, '');
        $record_index = $this->modx->getOption('record_index', $scriptProperties, '');
        $remove_item = $extra_params == 'removeimage' ? true : false;
        
        $this->modx->migx->working_context = 'web';
        $limit = 100;

        $output = array();
        $message = 'no mediasource found';

        if ($resource = $this->modx->getObject('modResource', $resource_id)) {
            $wctx = $resource->get('context_key');
            $this->modx->migx->working_context = $wctx;

            if ($tv = $this->modx->getObject('modTemplateVar', array('name' => $tvname))) {
                if ($source = $tv->getSource($wctx, false)) {
                    $this->modx->setPlaceholder('docid', $resource_id);
                    $source->initialize();
                    $sourceProperties = $source->getPropertyList();

                    //echo '<pre>' . print_r($sourceProperties,1) . '</pre>';
                    $filefield = $this->modx->getOption('migxFileFieldname', $sourceProperties, 'image');
                    
                    //remove a file
                    if ($remove_item && isset($items[$record_index])){
                        $item = $items[$record_index];
                        if (isset($item[$filefield])){
                            $filename = $item[$filefield];
                            $source->removeObject($filename);
                        }
                    }
                    
                    $files = $source->getObjectsInContainer('');
                    $i = 1;
                    $imageList = array();
                    foreach ($files as $file) {
                        if (isset($limit) && $i > $limit) {
                            break;
                        }

                        $imageList[$file['url']] = $file;
                        $i++;
                    }

                    $maxID = 0;
                    $newitems = array();
                    foreach ($items as $item) {
                        $item['deleted'] = '1';
                        if (isset($item[$filefield]) && isset($imageList[$item[$filefield]])) {
                            unset($imageList[$item[$filefield]]);
                            $item['deleted'] = '0';
                        }
                        if ($item['deleted'] == '0') {
                            //remove items, which filename no longer exits
                            if (isset($item['MIGX_id']) && $item['MIGX_id'] > $maxID) {
                                $maxID = $item['MIGX_id'];
                            }
                            $item['published'] = $this->modx->getOption('published',$item,'1');
                            $newitems[] = $item;
                        }
                    }
                    foreach ($imageList as $image) {
                        $maxID++;
                        $item = array();
                        $item['MIGX_id'] = (string )$maxID;
                        $item[$filefield] = $image['url'];
                        $item['deleted'] = '0';
                        $item['published'] = '1';
                        $newitems[] = $item;
                    }
                    $output = $newitems;
                    $message = '';

                }
            }
        }
        return $this->success($message, $output);
    }
}
return 'migxFormProcessor';
