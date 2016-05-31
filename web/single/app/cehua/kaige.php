<?php

//付费开格分析
define('__ROOT__',str_replace(array('//','\\'),array('/','/'),dirname(dirname(__DIR__) )));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__. 'Mdb.class.php';

$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$where=' where 1';
$where.=$start_date ? " and date>='$start_date'" : '';
$where.=$end_date ? " and date<='$end_date'" : '';

//开格数组
$dataKaige = array();
$cache_id='cehua/kaige.php';//缓存id

if(!S($cache_id)){
	$mdb=new Mdb();
	//获取付费开格
	$fields = array('hunt');
	for($i=0;$i<4;$i++){
		$mdb->selectDb(MONGO_PERFIX.$i);
		$limit=5000;
		$offset=0;
		while ($limit>0){
			$result_condition=array('start'=>$offset,'limit'=>$limit);
			$list = $mdb->find('pets', array('hunt.bag.openSize'=>array('$gt'=>0)), $fields, $result_condition);
			foreach($list as $row){
				$openSize=$row['hunt']['bag']['openSize'];
				isset($dataKaige[$openSize]) ? $dataKaige[$openSize]++ : $dataKaige[$openSize]=1;
			}

			if(count($list) < $limit) break;
			$offset +=$limit;

		}

	}
	S($cache_id,$dataKaige,3600);
}else{
	$dataKaige=S($cache_id);
}

$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'today'=>date('Y-m-d',strtotime('today')),
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME));
$time_conf=array(
date('Y-m-d',SERVER_OPEN_TIME)=>__('开服当天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*1)=>__('开服2天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*2)=>__('开服3天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*3)=>__('开服4天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*4)=>__('开服5天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*5)=>__('开服6天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*6)=>__('开服7天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*13)=>__('开服14天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*29)=>__('开服30天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*59)=>__('开服60天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*89)=>__('开服90天'),
date('Y-m-d',SERVER_OPEN_TIME+86400*179)=>__('开服180天'),
date('Y-m-d',strtotime('yesterday'))=>__('昨天'),
);

$smarty->assign('dataKaige',$dataKaige);
$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->display();