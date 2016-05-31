<?php
//世界boss
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';



$char_name=empty($_GET['char_name']) ? '' : my_escape_string(trim($_GET['char_name']));
$boss=empty($_GET['boss']) ? '' : my_escape_string(trim($_GET['boss']));
$item_id=empty($_GET['item_id']) ? '' : my_escape_string(trim($_GET['item_id']));
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date'])); 
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));

$conditions=array(
	'start_date'=>$start_date,
	'end_date'=>$end_date,
	'char_name'=>$char_name,
	'boss'=>$boss,
	'item_id'=>$item_id,
	'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
);

////查询条件
$where=" where true ";
$where.=$char_name ? ' and char_name='.$char_name : '';
$where.=$boss ? ' and boss_id='.$boss : '';
$where.=$item_id ? ' and item_id='.$item_id : '';
$where.=$start_date ? ' and time>='.strtotime($start_date) : '';
$where.=$end_date ? ' and time<'.(strtotime($end_date)+86400) : '';

//世界boss刷新时间配置
$time1=strtotime(date('Y-m-d 23:30'));
$time2=strtotime(date('Y-m-d 08:00'));
$refresh=array();
for($time=$time1;$time>=$time2;$time-=1800){
	$refresh[]=date('H:i',$time);
	
}
//print_r($refresh);
$refresh_conf=array(
	'1500'=>array('time'=>$refresh,'name'=>__('铁归藏(40)')),
	'1501'=>array('time'=>$refresh,'name'=>__('银归藏(50)')),
	'1502'=>array('time'=>$refresh,'name'=>__('金归藏(60)')),
	'6401'=>array('time'=>array('22:00','19:00','14:00','11:00'),'name'=>__('深渊霸王(35)')),
	'6402'=>array('time'=>array('22:00','19:00','14:00','11:00'),'name'=>__('双蛇郎君(35')),
	'6403'=>array('time'=>array('22:00','19:00','14:00','11:00'),'name'=>__('八卦冥主(45)')),
    '6404'=>array('time'=>array('22:00','19:00','14:00','11:00'),'name'=>__('镇魂鬼座(55)')),
	'6405'=>array('time'=>array('22:00','19:00','14:00','11:00'),'name'=>__('玄武妖兽(60)')),
);
$boss_id=implode(',', array_keys($refresh_conf));

$mysqli=new DbMysqli();
$sql="select distinct from_unixtime(time,'%Y-%m-%d') as date from log_pick_up_item $where and boss_id in ($boss_id) order by date desc";
$query=$mysqli->query($sql);
$total_rows=0;
$list=array();
while ($row=$query->fetch_assoc()){
	$list[]=$row['date'];
	$total_rows++;
}
$p=new Page($total_rows,5);
$date_list=array_slice($list, $p->firstRow,$p->listRows);
$date_list&&$max_time=strtotime($date_list[0])+86400;
$date_list&&$min_time=strtotime($date_list[count($date_list)-1]);

$data=array();
$mdb=new Mdb();
if(isset($min_time)&&isset($max_time)){
	//按天遍历
	for($i=$min_time;$i<=$max_time;$i+=86400){
		//按boss遍历
		foreach ($refresh_conf as $boss_id=>$refresh_time){
			//boss查询
			$refresh_time_conf=array();
			if($boss&&$boss_id==$boss || empty($boss)){
				$refresh_time_conf=$refresh_time['time'];
			}
			
			//按boss刷新时间遍历
			foreach($refresh_time_conf as $key=>$time){
				$start_time=strtotime(date('Y-m-d ',$i).$time);
				if(isset($refresh_time_conf[$key-1])){
					$end_time=strtotime(date('Y-m-d ',$i).$refresh_time_conf[$key-1]);
				}else{
					$end_time=$i+86400;
				}
				//击杀成员和时间
				$sql="select char_id,char_name,time from log_boss where time>=$start_time and time<$end_time and boss_id=$boss_id  order by time asc";
				$query=$mysqli->query($sql);
				$kill_char_list=array();
				$kill_time='';
				while ($row=$query->fetch_assoc()){
					$kill_char_list[$row['char_id']]=$row['char_name'];
					$kill_time=date('H:i:s',$row['time']);//击杀时间
				}
				
				
				//掉落拾取
				$sql="select char_id,char_name,boss_name,item_id from log_pick_up_item $where and time>=$start_time and time<$end_time  and boss_id=$boss_id";
				$query=$mysqli->query($sql);
				$pick_up_list=array();
				$boss_name='';
				$char_list=array();
				while ($row=$query->fetch_assoc()){
					//如果数据库中角色名记录为空
					if(empty($row['char_name'])){
						if(isset($char_list[$row['char_id']])){
							$pick_up_list[$row['char_id']]['name']=$char_list[$row['char_id']];//角色名称
						}else{
							for($j=0;$j<4;$j++){
								$mdb->selectDb(MONGO_PERFIX.$j);
								$condition=array('_id'=>floatval($row['char_id']));
								$list=$mdb->findOne('characters',$condition,array('name'));
								if($list){
									$pick_up_list[$row['char_id']]['name']=$list['name'];//角色名称
									$char_list[$row['char_id']]=$list['name'];
									break;
								}
							}
						}
					}else{
						$pick_up_list[$row['char_id']]['name']=$row['char_name'];//角色名称
					}						
					isset($pick_up_list[$row['char_id']]['item'][$row['item_id']]) ? $pick_up_list[$row['char_id']]['item'][$row['item_id']]++ : $pick_up_list[$row['char_id']]['item'][$row['item_id']]=1;//拾取道具
					$boss_name=$row['boss_name'];
				}
				if($kill_char_list && $pick_up_list){
					$date=date('Y-m-d',$i);
					isset($data[$date]['count']) ? $data[$date]['count']++ : $data[$date]['count']=1;
					$data[$date]['list'][$time][$boss_id]['boss_name']=$boss_name;
					$data[$date]['list'][$time][$boss_id]['kill_time']=$kill_time;
					$data[$date]['list'][$time][$boss_id]['kill_char_list']=$kill_char_list;
					$data[$date]['list'][$time][$boss_id]['pick_up_list']=$pick_up_list;
					ksort($data[$date]['list']);
				}
			}
		}
	}
}
$page=$p->show();

$smarty->assign('refresh_conf',$refresh_conf);
$smarty->assign('conditions',$conditions);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();