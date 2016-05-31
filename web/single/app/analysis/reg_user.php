<?php
//注册用户
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CONFIG__ . 'game_config.php';
include __CLASSES__. 'Page.class.php';
include __CLASSES__. 'Mdb.class.php';

$action=empty($_GET['action']) ? 'online' : trim($_GET['action']);
$id=empty($_GET['id']) ? '' : floatval(trim($_GET['id']));
$account=empty($_GET['account']) ? '' : my_escape_string($_GET['account']);
$name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));
$min_level=empty($_GET['min_level']) ? '' : intval($_GET['min_level']);
$max_level=empty($_GET['max_level']) ? '' : intval($_GET['max_level']);
$occ=empty($_GET['occ']) ? '' : intval($_GET['occ']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$list_row=empty($_GET['list_row']) ? 30 : intval($_GET['list_row']);//每页显示记录数
$condition=array();

$id&&$condition['_id']=$id;
$account&&$condition['account']=$account;
$name&&$condition['name']=$name;
$occ&&$condition['occ']=$occ;
$min_level&&$condition['level']=array('$gte'=>$min_level);
$max_level&&isset($condition['level'])&&$condition['level']=array_merge(array('$lte'=>$max_level),$condition['level']);
$max_level&&!isset($condition['level'])&&$condition['level']=array('$lte'=>$max_level);
$start_date&&$condition['creat_time']=array('$gte'=>strtotime($start_date));
$end_date&&isset($condition['creat_time'])&&$condition['creat_time']=array_merge(array('$lte'=>strtotime($end_date)+86400),$condition['creat_time']);
$end_date&&!isset($condition['creat_time'])&&$condition['creat_time']=array('$lte'=>strtotime($end_date)+86400);
$fields=array('account','serverId','name','occ','camp','level','creat_time','loginTime','ip');
switch ($action){
	default:
		$total_rows=0;
		$mdb=new Mdb();
		$mysqli=new DbMysqli();
		$data=$items=$page_arr=array();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$count=$mdb->count('characters', $condition);
			$total_rows+=$count;

			$page_count=intval($count/$list_row);
			$keep_num=$count%$list_row;
			$start_num=0;
			for($n=0;$n<$page_count;$n++){
				$page_arr[]=array($i=>array($start_num,$list_row));
				$start_num+=$list_row;
			}
			if($keep_num){
				$items[$i]=array($start_num,$keep_num);
			}
		}
		$current_num=0;//当前累计记录数
		$arr=array();//临时存储数组
		while (list($key,$item)=each($items)){
			$item=$items[$key];
			$current_num+=$item[1];
			if($current_num<=$list_row){
				$arr[$key]=$item;
				if($current_num==$list_row || count($items)==1){
					$page_arr[]=$arr;
					$arr=array();
				}
				unset($items[$key]);
			}else{
				$diff=$list_row-($current_num-$item[1]);//填补的数量
				$current_num=0;
				if($diff)	$arr[$key]=array($item[0],$diff);
				if($arr)	$page_arr[]=$arr;
				$keep_count=$item[1]-$diff;
				if($keep_count){
					$items[$key]=array($item[0]+$diff,$keep_count);//重置当前项
					$arr=array($key=>$items[$key]);
					prev($items);//内部指针倒回一位
				}else{
					unset($items[$key]);
					$arr=array();
				}
				if(count($items)==1){
					$page_arr[]=$arr;
				}
			}
		}
		$p=new Page($total_rows,$list_row);
		$db_rows=empty($page_arr[$p->nowPage-1]) ? array() : $page_arr[$p->nowPage-1];
		if($db_rows){
			foreach ($db_rows as $key=>$row){
				$mdb->selectDb(MONGO_PERFIX.''.$key);
				$result_condition=array('start'=>intval($row[0]),'limit'=>intval($row[1]));
				$list=$mdb->find('characters', $condition, $fields, $result_condition);
				foreach ($list as &$item){
					$item['occ_name']=$occ_conf[$item['occ']];
					$item['camp_name']=isset($camp_conf[$item['camp']]) ? $camp_conf[$item['camp']] : '';
					$item['creat_time']=date('Y-m-d H:i:s',$item['creat_time']);
					$item['last_login_time']=empty($item['loginTime']) ? '' : date('Y-m-d H:i:s',$item['loginTime']);
					$sql="select ip from log_login where char_id={$item['_id']} order by id desc limit 1";
					$info=$mysqli->findOne($sql);
					$item['ip']=$info ? $info['ip'] : '';
					$data[]=$item;
				}
			}
		}

		//查询条件
		$conditions=array(
			'id'=>$id,
			'account'=>$account,
			'name'=>$name,
			'min_level'=>$min_level,
			'max_level'=>$max_level,
			'occ'=>$occ,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'list_row'=>$list_row,
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);

		$smarty->assign('conditions',$conditions);
		$smarty->assign('occ_conf',$occ_conf);
		$smarty->assign('list_row_conf',range(30,100,10));
		$smarty->assign('data',$data);
		$smarty->assign('page',$p->show());
		$smarty->display();
		break;

		//导出数据
	case 'export':
		set_time_limit(300);
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
		$title.=__('职业')."\t";
		$title.=__('阵营')."\t";
		$title.=__('等级')."\t";
		$title.=__('注册时间')."\t";
		$title.=__('最后登录时间')."\t";
		$title.="IP \n";

		echo $title;
		$mdb=new Mdb();
		$mysqli=new DbMysqli();
		$data=array();
		for($i=0;$i<4;$i++){
			$mdb->selectDb(MONGO_PERFIX.$i);
			$limit=2000;
			$offset=0;
			while ($limit>0){
				$result_condition=array('start'=>$offset,'limit'=>$limit);
				$list=$mdb->find('characters', $condition, $fields, $result_condition);
				foreach ($list as $row){
					$sql="select ip from log_login where char_id={$row['_id']} order by id desc limit 1";
					$info=$mysqli->findOne($sql);
					$row['ip']=$info ? $info['ip'] : '';
					echo $row['_id']."\t";
					echo $row['account']."\t";
					echo $row['name']."\t";
					echo $occ_conf[$row['occ']]."\t";
					echo $camp_conf[$row['camp']]."\t";
					echo $row['level']."\t";
					echo date('Y-m-d H:i:s',$row['creat_time'])."\t";
					echo empty($row['loginTime']) ? '' : date('Y-m-d H:i:s',$row['loginTime'])."\t";
					echo $row['ip']."\n";
				}
				if(count($list)<$limit){
					$offset=$limit=0;
					break 1;
				}
				$offset+=$limit;
			}
		}

		break;
}