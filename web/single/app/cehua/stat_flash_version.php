<?php
//统计flash版本
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$cache_lifetime=0.5;//缓存时间(单位：小时)
$conditions=array(
	'time'=>date('Y-m-d H:i:s',time()),
	'cache_lifetime'=>$cache_lifetime,
);
$smarty->caching=true;
$smarty->cache_lifetime=3600*$cache_lifetime;//数据缓存

$mysqli=new DbMysqli();
$sql='select count(distinct account) as count,version from stat_flash_version group by version order by count desc';
$result=$mysqli->query($sql);
$total_count=0;
$data=array();
while ($result && $row=$result->fetch_assoc()){
	$total_count+=$row['count'];
	$data[]=$row;
}
$smarty->assign('total_count',$total_count);
$smarty->assign('data',$data);
$smarty->assign('conditions',$conditions);
$smarty->display();