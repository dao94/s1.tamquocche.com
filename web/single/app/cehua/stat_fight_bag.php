<?php
//战力礼包
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';

//战力礼包 配置
$items_conf=array(
'22000000313'=>__('特惠强化礼包'),
'22000000324'=>__('超值强化礼包'),
'22000000335'=>__('疯狂强化礼包'),
'22000000343'=>__('特惠伙伴礼包'),
'22000000354'=>__('超值伙伴礼包'),
'22000000365'=>__('疯狂伙伴礼包'),
'22000000373'=>__('特惠坐骑礼包'),
'22000000384'=>__('超值坐骑礼包'),
'22000000395'=>__('疯狂坐骑礼包'),
'22000000493'=>__('特惠宝石礼包'),
'22000000504'=>__('超值宝石礼包'),
'22000000515'=>__('疯狂宝石礼包'),
);
$start_date=date('Y-m-d',SERVER_OPEN_TIME);//开服日期
$end_date=date('Y-m-d',SERVER_OPEN_TIME+86400*8);//  截止购买时间  开服1到7天
$start_date=empty($_GET['start_date']) ? $start_date : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? $end_date : my_escape_string(trim($_GET['end_date']));
$start_time=strtotime($start_date);
$end_time=strtotime($end_date);
$where=" where true ";
$where.=$start_date ? " and time>=$start_time" : '';
$where.=$end_date ? " and time<=$end_time " : '';
$sql="select item_id,time from log_items $where and type=94 and io=1 order by time asc";
$mysqli=new DbMysqli();
$query=$mysqli->query($sql);
$data=$array=array();
while($row=$query->fetch_assoc()){
	$date=date('Y-m-d',$row['time']);
	$array[$row['item_id']]=isset($array[$row['item_id']]) ? ++$array[$row['item_id']] : $array[$row['item_id']]=1;//累计各个礼包的数据
	foreach($items_conf as $key=>$item){
		$data[$date][$key]=isset($array[$key]) ? $array[$key] : 0;
	}
	$data[$date]['fight_bag']=array_sum($array);
}

krsort($data);
$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('items_conf',$items_conf);
$smarty->display();