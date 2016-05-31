<?php
//在线数据
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __ROOT__.'/config/agent_list_config.php';
include __ROOT__.'/config/lianyun_list_config.php';

$action=empty($_GET['action']) ? 'online' : trim($_GET['action']);
$action_conf=array(
	'online'=>__('在线数据'),
	'history'=>__('历史在线'),
	'online_user'=>__('在线用户'),
	'map'=>__('玩家地图分布'),
);
$type_conf=array(
	'count'=>array('name'=>__('在线人数'),'max'=>__('最高在线'),'avg'=>__('平均在线'),'min'=>__('最低在线'),'decline'=>__('下降')),
	'ip'=>array('name'=>__('独立IP'),'max'=>__('最高IP'),'avg'=>'平均IP','min'=>'最低IP','decline'=>__('下降')),
);
$open_time=date('Y-m-d',SERVER_OPEN_TIME);
switch ($action){
	case 'online':
	default:
		$today=date('Y-m-d');
		$date=empty($_GET['date']) ? $today : trim($_GET['date']);
		$smarty->assign('date',$date);
		$smarty->assign('today',$today);
		$smarty->assign('type_conf',$type_conf);
		break;

	case 'chart':
		//在线数据的图表数据
		$start_time=empty($_GET['date']) ? strtotime('today') : strtotime(trim($_GET['date']));
		$end_time=($start_time==strtotime('today')) ? intval(time()/120)*120 : $start_time+86400;
		$mysqli=new DbMysqli();
		$online=$max=$min=$avg=$diff=$current=$flags=array();
		for($i=$start_time;$i<=$end_time;$i+=120){
			$sql="select count,ip from log_online where time=$i order by count desc limit 1";
			$list=$mysqli->findOne($sql);
			if(!$list){
				$list['ip']=$list['count']=0;
			}
			foreach ($list as $field=>$value){
				$value=intval($value);
				$current[$field]=array($i,$value);//当前在线
				$diff_{$field}=empty($diff[$field]) ? 0 : intval($diff[$field][1]);
				if(isset($arr[$field]) && ($arr[$field][1]-$value>=$diff_{$field}) ){
					$diff[$field]=array($i*1000,$arr[$field][1]-$value);
				}
				$arr[$field]=array($i*1000,$value);
				$online[$field][]=$arr[$field];
				$max[$field]=(empty($max[$field]) || $value>=$max[$field][1]) ? $arr[$field] : $max[$field];
				$min[$field]=((empty($min[$field]) || $value<=$min[$field][1])) ? $arr[$field] : $min[$field];
			}
		}
		//平均在线
		$sql="select avg(count) as count,avg(ip) as ip from log_online where time>=$start_time and time<=$end_time";
		$avg=$mysqli->findOne($sql);
		
		$title=$subtitle=$decline='';
		foreach ($type_conf as $type=>$item){
			$title.=$start_time!=strtotime('today') ? '' : $item['name'].":{$current[$type][1]}(".date('H:i',$current[$type][0]).') ';
			$subtitle.='【'.$item['max'].":{$max[$type][1]}(".date('H:i',$max[$type][0]/1000).") ";
			$subtitle.=$item['min'].":{$min[$type][1]}(".date('H:i',$min[$type][0]/1000).") ";	
			$subtitle.=$item['avg'].':'.intval($avg[$type])."】";
			$decline.="{$item['name']}{$item['decline']}:{$diff[$type][1]}(".date('H:i',$diff[$type][0]/1000).") ";
			if($diff[$type][1]>50){
				$flags[$type]=array(array('x'=>$diff[$type][0],'title'=>$item['decline'].":{$diff[$type][1]}",'text'=>$item['name'].$item['decline'].":{$diff[$type][1]}"));	
			}
		}
		$data=array(
			'title'=>$title,
			'subtitle'=>$subtitle.'【'.$decline.'】',
			'flags'=>$flags,
			'online'=>$online,
		);
		ajax_return(1,'online data',$data);
		break;
		
	case 'history':
		include __CLASSES__.'Page.class.php';
		$end_date=empty($_GET['end_date']) ? date('Y-m-d',strtotime('yesterday')) : my_escape_string(trim($_GET['end_date']));
		$start_date=empty($_GET['start_date']) ? date('Y-m-d',SERVER_OPEN_TIME) : my_escape_string(trim($_GET['start_date']));
		$mysqli=new DbMysqli();
		$sql="select count(*) as count from stat_online where date>='$start_date' and date<='$end_date'";
		$count=$mysqli->count($sql);
		$p=new Page($count,30);
		$sql="select * from stat_online where date>='$start_date' and date<='$end_date' order by date desc";
		$data=$mysqli->find($sql);
		$smarty->assign('start_date',$start_date);
		$smarty->assign('end_date',$end_date);
		$smarty->assign('data',$data);
		$smarty->assign('page',$p->show());
		break;
		
	case 'history_chart';
		//历史在线图表数据
		$type=array(
			'count'=>array('max_count'=>__('最高在线'),'avg_count'=>__('平均在线'),'min_count'=>__('最低在线')),
			'ip'=>array('max_ip'=>__('最高IP数'),'avg_ip'=>'平均IP数','min_ip'=>'最低IP数')
		);
		$end_date=empty($_GET['end_date']) ? date('Y-m-d',strtotime('yesterday')) : my_escape_string(trim($_GET['end_date']));
		$start_date=empty($_GET['start_date']) ? date('Y-m-d',time()-30*86400) : my_escape_string(trim($_GET['start_date']));
		$start_date=$open_time>$start_date ? $open_time : $start_date;
		$mysqli=new DbMysqli();
		$sql="select * from stat_online where date>='$start_date' and date<='$end_date'";
		$list=$mysqli->find($sql);
		$online=$max=$min=array();
		foreach ($list as $row){
			$time=strtotime($row['date'])*1000;
			foreach ($type as $key=>$items){
				foreach ($items as $k=>$item){
					$count=intval($row[$k]);
					$arr=array($time,$count);
					$online[$key][$k][]=$arr;
					$max[$key][$k]=(empty($max[$key][$k]) || $count>=$max[$key][$k][1]) ? $arr : $max[$key][$k];
					$min[$key][$k]=(empty($min[$key][$k]) || $count<=$min[$key][$k][1]) ? $arr : $min[$key][$k];
				}
			}
		}
	
		$data=array();
		foreach ($online as $key=>$item){
			$name=($key=='count') ? __('在线人数') : __('独立ip数');
			$max_value=$max[$key]['max_'.$key][1];
			$max_date=date('Y-m-d',($max[$key]['max_'.$key][0]/1000));
			$min_value=$min[$key]['min_'.$key][1];
			$min_date=date('Y-m-d',($min[$key]['min_'.$key][0]/1000));
			$title=$name.__('最高')."：{$max_value} 【{$max_date}】 ";
			$title.=__('最低')."：{$min_value} 【{$min_date}】";
			$data[]=array(
					'base'=>array('name'=>$name,'title'=>$title),
					'name'=>$type[$key],
					'data'=>$item,
			);
		}
		unset($online);
		echo ajax_return(1,'online data',$data);
	break;

	case 'online_user':
		//在线玩家
		include __CLASSES__.'Mdb.class.php';
		include __CLASSES__.'Scene.class.php';
		include __CLASSES__.'Ip.class.php';
		include __CLASSES__.'Page.class.php';

		$search_type=array('char_name'=>'角色名','account'=>__('帐号'));
		$type=empty($_GET['type']) ? '' : my_escape_string(trim($_GET['type']));
		$type=!array_key_exists($type, $search_type) ? 'char_name' : $type;
		$text=empty($_GET['text']) ? '' : my_escape_string(trim($_GET['text']));
		$is_export=isset($_GET['do']) && $_GET['do']=='export' ? true : false;
		$today=strtotime('today');
		$login_time=time()-86400*2;
		$data=$info=array();
		$mysqli=new DbMysqli();
		$where=" where login_time>=$login_time and logout_time=0";
		$where.=$text ? " and $type='$text'" : '';
		$fields=array('level','creat_time');
		$time=time();
		$scene=new Scene();
		$ip=new Ip();
		$mdb=new Mdb();
		if($is_export){
			//导出数据
			$sql="select char_id,account,char_name,max(login_time) as login_time,ip from log_login
			$where group by char_id order by login_time desc";
			$result=$mysqli->query($sql);

			set_time_limit(180);
			$filename=date('YmdHis').'.xls';
			header("Pragma:public");
			header("Expires:0");
			header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
			header("Content-Type:application/force-download");
			header("Content-Type:application/vnd.ms-execl;charset=utf-8");
			header("Content-Type:application/octet-stream");
			header("Content-Type:application/download");
			header('Content-Disposition:attachment;filename="'.$filename.'"');
			header("Content-Transfer-Encoding:binary");

			$title="ID \t";
			$title.=__('帐号')."\t";
			$title.=__('角色')."\t";
			$title.=__('创建时间')."\t";
			$title.=__('登录时间')."\t";
			$title.=__('在线时长')."\t";
			$title.=__('等级')."\t";
			$title.=__('所在地图')."\t";
			$title.=__('地图坐标')."\t";
			$title.="IP \t";
			$title.=__('城市')."\n";
			echo $title;
		}else{
			//分页显示在线用户
			$ragent_list = array_flip($agent_list);
			$agent_id=$ragent_list[SERVER_AGENT];
			$lianyun_power_list=$_SESSION['__' . SERVER_TYPE . '__LIANYUN_POWER'][$agent_id];
			$lianyun_list_temp=$LianYun_List;
		
			if(is_array($lianyun_list_temp)&&count($lianyun_list_temp)&&is_array($lianyun_power_list)&&count($lianyun_power_list)){
						if(count($lianyun_power_list)!=count($lianyun_list_temp)){
								$d_flag=false;
								foreach($lianyun_power_list as $key => $value){
										if($lianyun_list_temp[$key]['query_key']==''){
												$d_flag=true;
												unset($lianyun_list_temp[$key]);
										}
								}
								if($d_flag){
										foreach($lianyun_power_list as $key => $value){
												unset($lianyun_list_temp[$key]);
										}
										foreach($lianyun_list_temp as $lianyun_temp){
												$where.=" and account not like '%".$lianyun_temp['query_key']."'";
										}
								}
								else{
										$where_add=" and ( false  ";
										$lianyun_power=$lianyun_power_list;
										foreach($lianyun_power as $key => $value){
												$where_add.=" or account like '%".$lianyun_list_temp[$key]['query_key']."'";
										}
										$where_add.=" )";
										$where.=$where_add;
								}
						}
						else{
								//do nothing
						}
			}
			else{
					//do nothing
			}
			
			$sql="select count(distinct char_id) as count from log_login $where";
			$total_rows=$mysqli->count($sql);
			$p=new Page($total_rows);
			$sql="select char_id,account,char_name,max(login_time) as login_time,ip from log_login $where
				 group by char_id order by login_time desc limit {$p->firstRow},{$p->listRows}";
			$result=$mysqli->query($sql);
		}
		while ($result && $row=$result->fetch_assoc()){
			$db_name=MONGO_PERFIX.''.$row['char_id']%4;
			$mdb->selectDb($db_name);
			$condition=array('_id'=>floatval($row['char_id']));
			$character=$mdb->findOne('characters', $condition, $fields);
			$row['level']=$character['level'];
			$row['create_time']=date('Y-m-d H:i:s',$character['creat_time']);
			$row['hour']=intval(($time-$row['login_time'])/3600);
			$row['minute']=intval(($time-$row['login_time'])%3600/60);
			$row['login_time']=date('Y-m-d H:i:s',$row['login_time']);
			//地图
			$location=$mdb->findOne('location', $condition, array('curScene'));
			$row['map_name']=$row['map_x']=$row['map_y']='';
			if(isset($location['curScene']['mapId'])){
				$row['map_name']=$scene->getName($location['curScene']['mapId']);
				$row['map_id']=$location['curScene']['mapId'];
				$row['map_x']=$location['curScene']['x'];
				$row['map_y']=$location['curScene']['y'];
			}
			//ip地址
			$location=$ip->getlocation($row['ip']);
			$row['country']=$location['country'];
			if(!$is_export){
				$data[]=$row;
			}else{
				echo $row['char_id']."\t";
				echo $row['account']."\t";
				echo $row['char_name']."\t";
				echo $row['create_time']."\t";
				echo $row['login_time']."\t";
				echo $row['hour'].'h'.$row['minute']."m \t";
				echo $row['level']."\t";
				echo $row['map_name']."\t";
				echo "{$row['map_x']},{$row['map_y']} \t";
				echo $row['ip']."\t";
				echo $row['country']."\n";
			}
		}
		if(!$is_export){
			//总注册数
			$sql="select max(total_character_count) as count from stat_reg";
			$list=$mysqli->findOne($sql);
			$info['reg_count']=intval($list['count']);
			$condition=array('creat_time'=>array('$gte'=>strtotime('today')));
			$info['reg_count']+=$mdb->allCount('characters', $condition);
				
			//总登录数
			$sql='select sum(login_count) as count from stat_login';
			$list=$mysqli->findOne($sql);
			$info['login_count']=intval($list['count']);
			$sql="select count(*) as count from log_login where login_time>=$today";
			$info['login_count']+=$mysqli->count($sql);
				
			//当前在线IP
			$sql="select count(distinct ip) as count from log_login where login_time>=$login_time and logout_time=0";
			$info['ip']=$mysqli->count($sql);
			$info['online']=$total_rows;
			$smarty->assign('type',$type);
			$smarty->assign('text',$text);
			$smarty->assign('search_type',$search_type);
			$smarty->assign('data',$data);
			$smarty->assign('info',$info);
			$smarty->assign('page',$p->show());
		}else{
			exit;
		}
		break;

	case 'list_ip':
		//当前在线ip列表
		$login_time=time()-86400*2;
		$sql="select ip,count(distinct char_id) as count from log_login where login_time>=$login_time and
			logout_time=0 group by ip order by count desc";
		$mysqli=new DbMysqli();
		$data=$mysqli->find($sql);
		echo ajax_return(1, count($data),$data);
		break;

	case 'map':
		//统计当前在线用户，什么地图有多少个玩家
		include __CLASSES__.'Mdb.class.php';
		include __CLASSES__.'Scene.class.php';

		$login_time=time()-86400*2;
		$mysqli=new DbMysqli();
		$sql="select distinct char_id from log_login where login_time>=$login_time and logout_time=0";
		$result=$mysqli->query($sql);
		$data_char=array();
		$total_count=0;
		while ($result && $row=$result->fetch_assoc()){
			$data_char[$row['char_id']%4][]=floatval($row['char_id']);
			$total_count++;
		}

		$mdb=new Mdb();
		$data=array();
		$fields=array('curScene.mapId','_id'=>false);
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			if(!empty($data_char[$i])){
				$condition=array('_id'=>array('$in'=>$data_char[$i]));
				$list=$mdb->find('location', $condition, $fields);
				foreach ($list as $row){
					$map_id=empty($row['curScene']['mapId']) ? '' : $row['curScene']['mapId'];
					if(isset($data[$map_id]))
					$data[$map_id]++;
					elseif($map_id){
						$data[$map_id]=1;
					}
				}
			}
		}
		unset($data_char);
		arsort($data);
		$scene=new Scene();
		foreach ($data as $map_id=>$count){
			$row=array(
				'name'=>$scene->getName($map_id),
				'count'=>$count,
				'ratio'=>$total_count ? round($count/$total_count,4)*100 : 0,
			);
			$data[$map_id]=$row;
		}
		$smarty->assign('total_count',$total_count);
		$smarty->assign('data',$data);
		break;
}
$smarty->assign('action',$action);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('open_time',$open_time);
$smarty->display();
?>