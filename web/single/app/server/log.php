<?php
//在线数据
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action=empty($_GET['action']) ? 'frame' : trim($_GET['action']);
$action_conf=array(
	'frame'=>__('逻辑帧日志'),
	'memory'=>__('帧占用内存日志'),
	'rpc'=>__('rpc调用日志'),
	'mongodb'=>__('mongodb日志'),
);
$server_type=array(
1=>'NET_NUMBER_CLIENT',
2=>'NET_NUMBER_BG',
11=>'NET_NUMBER_LOGIN',
21=>'NET_NUMBER_GATE',
31=>'NET_NUMBER_MAP',
41=>'NET_NUMBER_USER',
51=>'NET_NUMBER_COMMON',
52=>'NET_NUMBER_COMMON_SLAVE',
61=>'NET_NUMBER_COMMON_SLAVE',
71=>'NET_NUMBER_CHAT',
);
$server_id=empty($_GET['server_id']) ? '' : intval($_GET['server_id']);
$date=empty($_GET['date']) ? date('Y-m-d') : trim($_GET['date']);
switch ($action){
	case 'frame':
		include_once __CLASSES__.'Page.class.php';
		$show_fields=array(
			'server_id'=>__('服务器id'),
			'server_name'=>__('服务器进程名字'),
			'server_index'=>__('服务器索引'),
			'frame'=>__('帧执行时间'),
			'time'=>__('时间'),
		);
		$min_time=empty($_GET['min_time']) ? '' : intval($_GET['min_time']);
		$max_time=empty($_GET['max_time']) ? '' : intval($_GET['max_time']);
		$start_date=empty($_GET['start_date']) ? date('Y-m-d',SERVER_OPEN_TIME) : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['end_date']));
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date)+86400;
		$field=empty($_GET['field']) ? 'id' : trim($_GET['field']);
		$field=!array_key_exists($field, $show_fields) ? 'time' : $field;
		$sort=empty($_GET['sort']) ? 0 : intval($_GET['sort']);
		$order=$sort==1 ? 'asc' : 'desc';
		$conditions=array(
			'server_id'=>$server_id,
			'min_time'=>$min_time,
			'max_time'=>$max_time,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'field'=>$field,
			'sort'=>$sort,
		);

		$mysqli=new DbMysqli();
		$where=" where time>=$start_time and time<$end_time and system=2";
		$where.=$server_id ? " and server_id=$server_id" : '';
		$where.=$min_time ? " and frame>=$min_time" : '';
		$where.=$max_time ? " and frame<=$max_time" : '';
		$sql="select count(*) as count from log_server_frame $where";
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_server_frame $where order by $field $order limit {$p->firstRow},{$p->listRows}";
		$data=$mysqli->find($sql);
		$smarty->assign('conditions',$conditions);
		$smarty->assign('show_fields',$show_fields);
		$smarty->assign('data',$data);
		$smarty->assign('page',$p->show());
		//dump($data);
		break;
	case 'memory':
		$server_id=$server_id ? $server_id :41;
		$start_time=strtotime($date);
		$end_time=$start_time+86400;

		$mysqli=new DbMysqli();
		$sql="select server_index from log_lua_memory where time>=$start_time and time<$end_time and
			server_id=$server_id and system=2 group by server_index";
		$data=$mysqli->find($sql);
		$smarty->assign('data',$data);
		$mysqli->close();
		break;

	case 'rpc':
		include_once __CLASSES__.'Page.class.php';
		$show_fields=array(
			'rpc_name'=>__('rpc类名'),
			'rpc_oper'=>__('rpc函数偏移'),
			'rpc_cmd'=>__('命令字'),
			'use_time'=>__('执行时长(ms)'),
			'time'=>__('时间'),
		);
		$min_time=empty($_GET['min_time']) ? '' : intval($_GET['min_time']);
		$max_time=empty($_GET['max_time']) ? '' : intval($_GET['max_time']);
		$start_date=empty($_GET['start_date']) ? date('Y-m-d',SERVER_OPEN_TIME) : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? date('Y-m-d') : my_escape_string(trim($_GET['end_date']));
		$start_time=strtotime($start_date);
		$end_time=strtotime($end_date)+86400;
		$field=empty($_GET['field']) ? 'id' : trim($_GET['field']);
		$field=!array_key_exists($field, $show_fields) ? 'time' : $field;
		$sort=empty($_GET['sort']) ? 0 : intval($_GET['sort']);
		$order=$sort==1 ? 'asc' : 'desc';
		$conditions=array(
			'min_time'=>$min_time,
			'max_time'=>$max_time,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'field'=>$field,
			'sort'=>$sort,
		);

		$mysqli=new DbMysqli();
		$where=" where time>=$start_time and time<$end_time and system=2";
		$where.=$min_time ? " and use_time>=$min_time" : '';
		$where.=$max_time ? " and use_time<=$max_time" : '';
		$sql="select count(*) as count from log_rpc_oper $where";
		$list=$mysqli->find($sql);
		$total_rows=intval($list[0]['count']);
		$p=new Page($total_rows);
		$sql="select * from log_rpc_oper $where order by $field $order limit {$p->firstRow},{$p->listRows}";
		$data=$mysqli->find($sql);

		$smarty->assign('data',$data);
		$smarty->assign('show_fields',$show_fields);
		$smarty->assign('page',$p->show());
		$smarty->assign('conditions',$conditions);
		$mysqli->close();
		break;

	case 'chart':
		$base=array('max'=>__('最大'),'min'=>'最小');
		if($_GET['type']=='frame'){
			$table='log_server_frame';
			$field='frame';
			$base['name']=__('执行时长');
			$base['unit']=__('单位(毫秒)');
		}else{
			$table='log_lua_memory';
			$field='memory';
			$base['name']=__('占用内存');
			$base['unit']=__('单位(kb)');
		}
		$server_index=empty($_GET['server_index']) ? -1 : intval($_GET['server_index']);
		$arr=$data=array();
		$start_time=strtotime($date);
		$end_time=$start_time+86400;

		$mysqli=new DbMysqli();
		$sql="select $field,time from $table where time>=$start_time and time<$end_time and
			server_id=$server_id and server_index=$server_index and system=2 order by time asc";
		$list=$mysqli->find($sql);
		$max=$min=array();
		$max['count']=0;
		$min['count']=9999999999;
		foreach ($list as $row){
			$time=intval($row['time'])*1000;
			$count=intval($row[$field]);
			$data[]=array($time,$count);
			if($count>=$max['count']){
				$max['count']=$count;
				$max['time']=$time;
			}
			if($count<=$min['count']){
				$min['count']=$count;
				$min['time']=$time;
			}
		}
		$info=array(
		array('x'=>$max['time'],'title'=>$base['max'].'：'.$max['count'],'text'=>$base['max'].'：'.$max['count']),
		array('x'=>$min['time'],'title'=>$base['min'].'：'.$min['count'],'text'=>$base['min'].'：'.$min['count']),
		);
		if($data){
			$arr['base']=$base;
			$arr['data']=$data;
			$arr['info']=$info;
			$arr['title']=$server_type[$server_id]."($server_index)".$base['max'].$base['name'].':'.$max['count'].'【'.date('H:i:s',$max['time']/1000).'】 '.
			$base['min'].'：'.$min['count'].'【'.date('H:i:s',$min['time']/1000).'】 '.$base['unit'];
			echo ajax_return(1,'data',$arr);
		}else{
			echo ajax_return(0, 'No data');
		}
		unset($arr,$data,$info);
		break;

	case 'mongodb':
		//mongodb日志
		include __CLASSES__.'/Page.class.php';
		$mysqli=new DbMysqli();
		$sql='select count(*) as count from log_mongodb';
		$total_rows=$mysqli->count($sql);
		$p=new Page($total_rows);
		$sql="select * from log_mongodb order by id desc limit {$p->firstRow},{$p->listRows}";
		$data=$mysqli->find($sql);
		$smarty->assign('data',$data);
		$smarty->assign('page',$p->show());
		break;
}
$smarty->assign('action',$action);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('server_type',$server_type);
$smarty->assign('date',$date);
$smarty->assign('server_id',$server_id);
$smarty->assign('open_time',date('Y-m-d',SERVER_OPEN_TIME));
$smarty->display();
?>