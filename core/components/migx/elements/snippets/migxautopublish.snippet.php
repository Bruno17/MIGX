<?php
$classnames = explode(',', $modx->getOption('classnames',$scriptProperties,''));
$packageName = $modx->getOption('packageName',$scriptProperties,'');

switch ($mode) {
    case 'datetime' :
        $timeNow = strftime('%Y-%m-%d %H:%M:%S');
        break;
    case 'unixtime' :
        $timeNow = time();
        break;
    default :
        $timeNow = strftime('%Y-%m-%d %H:%M:%S');
        break;
}

$modx->addPackage($packageName, $modx->getOption('core_path') . 'components/' . $packageName . '/model/');

foreach ($classnames as $classname) {
    if (!empty($classname)) {
        $tblResource = $modx->getTableName($classname);
        if (!$result = $modx->exec("UPDATE {$tblResource} SET published=1,publishedon=pub_date,pub_date=null WHERE pub_date < '{$timeNow}' AND pub_date > 0 AND published=0")) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Error while refreshing resource publishing data: ' . print_r($modx->errorInfo(), true));
        }
        if (!$result = $modx->exec("UPDATE $tblResource SET published=0,unpub_date=null WHERE unpub_date < '{$timeNow}' AND unpub_date IS NOT NULL AND unpub_date > 0 AND published=1")) {
            $modx->log(modX::LOG_LEVEL_ERROR, 'Error while refreshing resource unpublishing data: ' . print_r($modx->errorInfo(), true));
        }
    }

}
$modx->cacheManager->refresh();