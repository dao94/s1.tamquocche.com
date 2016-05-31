<?php
//注册流失
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__. 'Mdb.class.php';
//D:/php/single/config/config.php,
//D:/php/single/config/smarty_config.php,
//D:/php/single/auth/auth.php,
//D:/php/single/class/Page.class.php,
//echo __ROOT__.'/config/config.php'.',';
//echo __CONFIG__ . 'smarty_config.php'.',';
//echo __AUTH__ . 'auth.php'.',';
//echo __CLASSES__. 'Page.class.php'.',';

$action=empty($_GET['action']) ? 'day' : trim($_GET['action']);
$today=strtotime('today');
$field_conf=array(
//account_data(mongo)查询字段=>stat_reg(mysql)存储字段
	'create_time'=>'account_count',//帐号数量（注册连接数）
	'loader_page'=>'loader_page',//web页面完成加载
	'loader_main'=>'loader_main',//完成加载LoaderMain.swf
	'loader_login'=>'loader_login',//完成加载LoginModule.swf（到达注册页数）
	'loader_resource'=>'loader_resource',//完成加载resource/Main.swf
//'loader_character'=>'loader_character',//完成创号
	'loader_game'=>'loader_game',//完成加载GameModule.swf（进入游戏数）
);
switch ($action){
	case 'day':
	default:
		//按天统计
		$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
		$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
		$sort=isset($_GET['sort']) ? intval($_GET['sort']) : 0;
		$conditions=array(
			'action'=>$action,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'sort'=>$sort,
			'today'=>date('Y-m-d',$today),
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		$sort=($sort==0) ? 'desc' : 'asc';//日期排序

		$mysqli=new DbMysqli();
		$where=' where 1';
		$where.=$start_date ? " and date>='$start_date'" : '';
		$where.=$end_date ? " and date<='$end_date'" : '';
		$sql='select count(*) as count from stat_reg '.$where;
		$total_count=$mysqli->count($sql);
		$p=new Page($total_count,30);
		$sql="select * from stat_reg $where order by date $sort limit {$p->firstRow},{$p->listRows}";
		$list=$mysqli->find($sql);

		//今天数据（查询mongo）
		$date=date('Y-m-d',$today);
		if(($date>=$start_date && $date<=$end_date) || (!$start_date && !$end_date) && $p->nowPage==1){
			$today_data=array();
			$today_data['date']=date('Y-m-d',$today);
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'4');
			foreach ($field_conf as $key=>$item){
				$condition=array($key=>array('$gte'=>$today,'$lt'=>$today+86400));
				$today_data[$item]=$mdb->count('account_data', $condition);
			}
			//今天角色数
			$condition=array('creat_time'=>array('$gte'=>$today,'$lt'=>$today+86400));
			$today_data['character_count']=$mdb->allCount('characters', $condition);
			array_unshift($list,$today_data);
		}
		$smarty->assign('page',$p->show());
		break;

	case 'hour':
		//按小时统计
		$date=empty($_GET['date']) ? date('Y-m-d',$today) : trim($_GET['date']);
		$start_time=strtotime($date);
		$end_time=$start_time+86400>time() ? time() : $start_time+86400;
		$mdb=new Mdb();
		$list=array();
		for ($i=$start_time;$i<$end_time;$i+=3600){
			$row=array();
			$row['hour']=date('H:i',$i);
			$mdb->selectDb(MONGO_PERFIX.'4');
			foreach ($field_conf as $key=>$item){
				$condition=array($key=>array('$gte'=>$i,'$lt'=>$i+3600));
				$row[$item]=$mdb->count('account_data',$condition);
			}

			//角色数
			$row['character_count']=$mdb->allCount('characters',array('creat_time'=>array('$gte'=>$i,'$lt'=>$i+3600)));
			$list[]=$row;
		}
		$conditions=array(
			'action'=>$action,
			'date'=>$date,
			'start_date'=>$date,
			'end_date'=>$date,
			'today'=>date('Y-m-d',$today),
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		break;
}
$data=array();
/****
 * <!-- 连接数 -->
    <!-- web页面 -->
    <!-- loaderMain -->
    <!-- 到达注册数 -->
    <!-- 注册数 -->
    <!-- Main.swf -->
    <!-- 进入游戏数 -->
    <!-- 加载注册页 -->
    <!-- 创号页面 -->
    <!-- 进入游戏 -->
    <!-- 创号 -->
 ****/
	$countContent = 0;
	$countWeb = 0;
	$countMain = 0;
	$countDDLog	= 0;
	$countLog=0;
	$countMainSWF=0;
	$countEnterGames=0;
	$countLoader=0;
	$countIdPage=0;
	$countEnterGame=0;
	$countCreateId=0;
	$dataCount = array();
foreach ($list as &$row){
	isset($row['date']) ? $row['week']=date('N',strtotime($row['date'])) : '';
	//加载注册页流失率=（连接数-到达注册数）÷连接数×100%
	$row['loader_login_loss']=$row['account_count'] ?
	round(($row['account_count']-$row['loader_login'])/$row['account_count'],4)*100 : 0;
	//创号页面流失率=（到达注册数 - 注册数）÷ 到达注册数×100%
	$row['character_page_loss']=$row['loader_login'] ?
	round(($row['loader_login']-$row['character_count'])/$row['loader_login'],4)*100 : 0;
	//进入游戏流失率=（注册数 - 进入游戏数）÷注册数×100%
	$row['loader_game_loss']=$row['character_count'] ?
	round(($row['character_count']-$row['loader_game'])/$row['character_count'],4)*100 : 0;
	//进入游戏流失率=（注册数 - 进入游戏数）÷注册数×100%
	$row['character_loss']=$row['account_count'] ?
	round(($row['account_count']-$row['character_count'])/$row['account_count'],4)*100 : 0;
	$data[]=$row;

	$countContent += $row['account_count'];
	$countWeb += $row['loader_page'];
	$countMain += $row['loader_main'];
	$countDDLog	+= $row['loader_login'];
	$countLog += $row['character_count'];
	$countMainSWF += $row['loader_resource'];
	$countEnterGames += $row['loader_game'];
}

	$countLoader = $countContent ?
	round(($countContent-$countDDLog)/$countContent,4)*100 : 0;
	$countIdPage = $countDDLog ?
	round(($countDDLog-$countLog)/$countDDLog,4)*100 : 0;
	$countEnterGame = $countLog ?
	round(($countLog-$countEnterGames)/$countLog,4)*100 : 0;
	$countCreateId = $countContent ?
	round(($countContent-$countLog)/$countContent,4)*100 : 0;

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
date('Y-m-d',$today)=>__('今天'),
);

/****
 * <!-- 连接数 -->
    <!-- web页面 -->
    <!-- loaderMain -->
    <!-- 到达注册数 -->
    <!-- 注册数 -->
    <!-- Main.swf -->
    <!-- 进入游戏数 -->
    <!-- 加载注册页 -->
    <!-- 创号页面 -->
    <!-- 进入游戏 -->
    <!-- 创号 -->
 ****/
	$smarty->assign('countContent',$countContent);
	$smarty->assign('countWeb',$countWeb);
	$smarty->assign('countMain',$countMain);
	$smarty->assign('countDDLog',$countDDLog);
	$smarty->assign('countLog',$countLog);
	$smarty->assign('countMainSWF',$countMainSWF);
	$smarty->assign('countEnterGames',$countEnterGames);
	$smarty->assign('countLoader',$countLoader);
	$smarty->assign('countIdPage',$countIdPage);
	$smarty->assign('countEnterGame',$countEnterGame);
	$smarty->assign('countCreateId',$countCreateId);

	//echo $countContent.','.$countWeb.','.$countMain.','.$countDDLog.','.$countLog.','.$countMainSWF.','.$countEnterGames.',';
	//echo $countLoader.','.$countIdPage.','.$countEnterGame.','.$countCreateId;

$smarty->assign('time_conf',$time_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->display();