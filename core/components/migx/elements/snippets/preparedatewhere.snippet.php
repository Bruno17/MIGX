$name = $modx->getOption('name',$scriptProperties,'');
$date = $modx->getOption($name.'_date',$_REQUEST,'');
$dir = str_replace('T',' ',$modx->getOption($name.'_dir',$_REQUEST,'')) ;

if (!empty($date) && !empty($dir) && $dir != 'all'){
    return '{"startdate:'.$dir.'":"'.$date.'"}';
}