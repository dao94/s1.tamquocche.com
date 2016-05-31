<?php
//卡类活动
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__.'Page.class.php';
include __CLASSES__.'Mdb.class.php';
include __LIB__.'phprpc_php/phprpc_client.php';

$action_conf=array(
	'log'=>__('Danh sách'),
	'used_card'=>__('Đã nhận'),
	'set_view'=>__('Cài đặt'),
);
$action=empty($_GET['action']) ? 'log' : trim($_GET['action']);
$conditions=array('action'=>$action);

$mysqli=new DbMysqli();
switch ($action){
	case 'log':
		$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));
		$gmer=empty($_GET['gmer']) ? '' : my_escape_string(trim($_GET['gmer']));
		$verify=empty($_GET['verify']) ? '' : intval(trim($_GET['verify']));
		$category=empty($_GET['category']) ? '' : intval(trim($_GET['category']));
		$start_start_date=empty($_GET['start_start_date']) ? '' : trim($_GET['start_start_date']);
		$start_end_date=empty($_GET['start_end_date']) ? '' : trim($_GET['start_end_date']);
		$end_start_date=empty($_GET['end_start_date']) ? '' : trim($_GET['end_start_date']);
		$end_end_date=empty($_GET['end_end_date']) ? '' : trim($_GET['end_end_date']);
		$list_rows=empty($_GET['list_rows']) ? 20 : intval(trim($_GET['list_rows']));

		$start_start_time=strtotime($start_start_date);
		$start_end_time=strtotime($start_end_date);
		$end_start_time=strtotime($end_start_date);
		$end_end_time=strtotime($end_end_date);

		$conditions=array(
			'action'=>$action,
			'type'=>$type,
			'gmer'=>$gmer,
			'verify'=>$verify,
			'category'=>$category,
			'start_start_date'=>$start_start_date,
			'start_end_date'=>$start_end_date,
			'end_start_date'=>$end_start_date,
			'end_end_date'=>$end_end_date,
			'list_rows'=>$list_rows,
		);
		$condition=array();
		$type ? $condition['type']=$type : '';
		$gmer ? $condition['gmer']=$gmer : '';
		$verify ? $condition['verify']=$verify : '';
		$category ? $condition['category']=$category : '';

		$cond=array();
		$start_start_date ? $cond['$gte']=$start_start_time : '';
		$start_end_date ? $cond['$lte']=$start_end_time : '';
		$cond ? $condition['start']=$cond : '';

		$cond=array();
		$end_start_date ? $cond['$gte']=$end_start_time : '';
		$end_end_date ? $cond['$lte']=$end_end_time : '';
		$cond ? $condition['over']=$cond : '';

		$mdb=new Mdb();
		$mdb->selectDb(MONGO_PERFIX.'5');
		$count=$mdb->count('card', $condition);
		$p=new Page($count);
		$result_condition=array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array('type'=>1));
		$list=$mdb->find('card', $condition, array(), $result_condition);
		$data=array();
		foreach ($list as $row){
			//领取数量
			if($row['category']==21){
				//导卡方式已领数量
				$row['use_count']=$mdb->count('import_card', array('type'=>intval($row['type']),'isused'=>1));
			}else{
				$row['use_count']=$mdb->count('usedCard', array('type'=>intval($row['type'])));
			}
			$row['start_time']=date('Y-m-d H:i',$row['start']);
			$row['end_time']=date('Y-m-d H:i',$row['over']);
			$row['money']=empty($row['money']) ? array() : $row['money'];
			$row['item']=empty($row['item']) ? array() : $row['item'];
			$data[]=$row;
		}
		$money_type_conf=array(1=>__('铜币'),2=>__('铜券'),3=>__('元宝'),4=>'礼券');
		$list_rows_conf=range(20,100,20);
		$smarty->assign('money_type_conf',$money_type_conf);
		$smarty->assign('list_rows_conf',$list_rows_conf);
		$smarty->assign('data',$data);
		$smarty->assign('page',$p->show());
		break;

	case 'remove':
		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->useService("http://{$_SERVER['HTTP_HOST']}/single/app/interface/pm_api.php");
		$phprpc_client->setKeyLength(128);
		$phprpc_client->setEncryptMode(2);
		$phprpc_client->setTimeout(10);

		$data['type']=empty($_POST['type']) ? 0 : intval($_POST['type']);
		$data['action']='remove';
		$result=$phprpc_client->pm_card($data);
		if(is_array($result) && isset($result['status'])){
			ajax_return($result['status'], $result['info']);
		}else{
			ajax_return(0, __('网络错误'));
		}
		break;

	case 'used_card':
		//领取记录
		$id=empty($_GET['id']) ? '' : floatval(trim($_GET['id']));//角色id
		$char_name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));//角色名
		$type=empty($_GET['type']) ? '' : intval(trim($_GET['type']));
		$code=empty($_GET['code']) ? '' : my_escape_string(trim($_GET['code']));
		$start_date=empty($_GET['start_date']) ? '' : trim($_GET['start_date']);
		$end_date=empty($_GET['end_date']) ? '' : trim($_GET['end_date']);

		$condition=array();
		$id ? $condition['charId']=$id : '';
		$type ? $condition['type']=$type : '';
		$code ? $condition['code']=$code : '';

		$cond=array();
		$start_date ? $cond['$gte']=strtotime($start_date) : '';
		$end_date ? $cond['$lte']=strtotime($end_date)+86400 : '';
		$cond ? $condition['time']=$cond : '';

		$mdb=new Mdb();
		if($char_name){
			for($i=0;$i<4;$i++){
				$mdb->selectDb(MONGO_PERFIX.$i);
				$char=$mdb->findOne('characters', array('name'=>$char_name), array('_id'));
				if(!empty($char['_id'])){
					$char_id=floatval($char['_id']);
					break;
				}
			}
			isset($char_id) ? $condition['charId']=$char_id : '';
		}

		$mdb->selectDb(MONGO_PERFIX.'5');
		$count=$mdb->count('usedCard', $condition);
		$p=new Page($count,30);
		$list=$mdb->find('usedCard', $condition, array('_id'=>false), array('start'=>$p->firstRow,'limit'=>$p->listRows,'sort'=>array('time'=>-1)));
		$data=array();
		$type_list=$char_list=array();
		foreach ($list as $row){
			if(isset($type_list[$row['type']])){
				$row['type_name']=$type_list[$row['type']];
			}else{
				$mdb->selectDb(MONGO_PERFIX.'5');
				$card=$mdb->findOne('card', array('type'=>intval($row['type'])), array('name'));
				$row['type_name']=$type_list[$row['type']]=$card ? $card['name'] : $row['type'];
			}
			if(isset($char_list[$row['charId']])){
				$row['char_name']=$char_list[$row['charId']];
			}else{
				$mdb->selectDb(MONGO_PERFIX.$row['charId']%4);
				$char=$mdb->findOne('characters', array('_id'=>floatval($row['charId'])), array('name'));
				$row['char_name']=$char_list[$row['charId']]=$char ? $char['name'] : $row['charId'];
			}
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}

		$conditions=array(
			'action'=>$action,
			'id'=>$id,
			'name'=>$char_name,
			'type'=>$type,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'code'=>$code,
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		$smarty->assign('data',$data);
		$smarty->assign('page',$p->show());
		break;

	case 'set_view':
		$id=empty($_GET['id']) ? 0 : intval($_GET['id']);//type类型
		$data=array();
		if($id){
			//读取编辑数据
			$mdb=new Mdb();
			$mdb->selectDb(MONGO_PERFIX.'5');
			$data=$mdb->findOne('card', array('type'=>$id));
			empty($data['start']) ? '' : $data['start']=date('Y-m-d H:i:s',$data['start']);
			empty($data['over']) ? '' : $data['over']=date('Y-m-d H:i:s',$data['over']);
			empty($data['type']) ? '' : $data['id']=$data['type'];
		}
		$smarty->assign('data',$data);
		break;

	case 'set':
		//卡类设置
		//非空字段
		$fields=array('type','limit','name','count','category_parent','category','verify','start_date','end_date','reward_list');
		$data=array();
		foreach ($_POST as $key=>$value){
			if(in_array($key,$fields) && !empty($value)){
				$data[$key]=is_array($value) ? $value : my_escape_string($value);
			}
		}
		if(count($fields)!=count($data)){
			ajax_return(0, __('参数缺失,请核对设置参数'));
		}
		$id=empty($_POST['id']) ? 0 : intval($_POST['id']);//更新、插入标识
		$data['url']=empty($_POST['url']) ? '' : my_escape_string($_POST['url']);
		$data['union_id']=empty($_POST['union_id']) ? 0 : intval($_POST['union_id']);//礼包显示

		//奖励构造
		$money_type=array('gold'=>1,'giftGold'=>2,'jade'=>3,'giftJade'=>4);
		foreach ($data['reward_list'] as $reward) {
			if ($reward['type']=='item' && $reward['num']>0) {
				list($item_id,$name)=explode('|',$reward['item']);
				$data['item'][]=array($item_id, intval($reward['num']), intval($reward['bind']));
			} elseif (in_array($reward['type'], array('gold', 'giftGold', 'jade', 'giftJade')) && $reward['num']>0) {
				$data['money'][]=array($money_type[$reward['type']],intval($reward['num']));
			}
		}

		empty($data['money']) ? '' : $data['money']=json_encode($data['money']);
		empty($data['item']) ? '' : $data['item']=json_encode($data['item']);
		$data['gmer']=$gmer = $_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];;
		//注销不必要的参数
		unset($data['reward_list']);

		$agent_config_file=__CONFIG__.'agent_list_config.php';
		if(!file_exists($agent_config_file)){
			ajax_return(0, __('代理配置缺失'));
		}
		include $agent_config_file;
		$data['agent_id']=array_search(SERVER_AGENT,$agent_list);
		$data['sid']=intval(substr(SERVER_ID,1));//剔除首字母S、T、X、F、H

		$phprpc_client = new PHPRPC_Client();
		$phprpc_client->setTimeout(10);
		$phprpc_client->setKeyLength(128);
		$phprpc_client->setEncryptMode(2);
		$phprpc_client->useService("http://{$_SERVER['HTTP_HOST']}/single/app/interface/pm_api.php");
		$result_info=array();
		switch ($data['category']){
			case 11;
				//通服16位卡号，赋予agent_id和sid=0
				$data['agent_id']=$data['sid']=0;
				break;
			case 12:
				//单平台32为卡号,赋予sid=0
				$data['sid']=0;
				break;
		}
		$data['action']='add';
		unset($data['id']);
		$result=$phprpc_client->pm_card($data);
		if(is_array($result) && isset($result['status'])){
			ajax_return($result['status'], $result['info']);
		}else{
			ajax_return(0, __('网络错误'));
		}
		break;
}
$category_conf=array(
	10=>array(
		'name'=>'Nhân tạo',
		'type'=>array(
			11=>__('Multi server(16 kí tự)'),
			12=>__('Single Platform(32 kí tự)'),
			13=>__('Single Serving(32 kí tự)'),
		),
	),
	20=>array(
		'name'=>'Guide Card',
		'type'=>array(
			21=>__('Guide Card(8 kí tự)'),
		),
	),
	30=>array(
		'name'=>'Interface',
		'type'=>array(
			31=>__('Generate md5(32 kí tự)'),
		),
	)
);

$verify_conf=array(
	1=>__('Game Server'),
	2=>__('Guide Card'),
	3=>__('PHP Validation'),
	4=>__('Novice card interface'),
	5=>__('Phone Interface'),
);
$smarty->assign('category_conf',$category_conf);
$smarty->assign('verify_conf',$verify_conf);
$smarty->assign('action_conf',$action_conf);
$smarty->assign('conditions',$conditions);
$smarty->display();