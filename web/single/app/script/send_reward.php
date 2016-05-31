<?php
/* 发送奖励脚本
 * 执行命令：php send_reward.php --server=4399_s001 --action=login --level=40
 * server：服务器名称，格式如4399_s1
 * action:操作名称
 *		login:根据登陆过的玩家发奖励
 */

if(!isset($argc) || $argc<2) exit("参数错误\n");
array_shift($argv);
$parameters=parseArgs($argv);
define('__ROOT__', "/data/server/lwjs_{$parameters['server']}a/html/single/");
!is_dir(__ROOT__)&&exit('目录不存在:'.__ROOT__."\n");
include __ROOT__.'/config/config.php';
include __FUNCTIONS__.'function.php';

$action=isset($parameters['action']) ? $parameters['action'] : '';
$mysqli=new DbMysqli();
switch ($action){		
	case 'login':
		include __CLASSES__.'Gm.class.php';
		
		$email_title='［天降财神］开门大礼';//人物改名卡邮件标题
		$email_content='亲爱的玩家们，24日8时由于电信网络故障导致部分玩家无法正常登录游戏，给您带来的不变之处请您谅解，运营团队为表达歉意，对24日有登录的40级以上玩家补偿绑定元宝＊288、绑定铜币＊88888、高级元力丹＊2';
		$email=array(
			'title'=>$email_title, 
			'content'=>$email_content,
			'itemList'=>array(
				array('itemId'=>10112000034,'number'=>2,'bind'=>0),
			),
			'moneyList'=>array(
				'giftGold'=>88888,
				'giftJade'=>288,
			),
		);
		
		$level=isset($parameters['level']) ? intval($parameters['level']) : 0;
		$end_time=strtotime('today');
		$start_time=$end_time-86400;
		$sql="select distinct char_id from log_login where login_time>=$start_time and login_time<$end_time and level>=$level";
		$query=$mysqli->query($sql);
		$char_list=array();
		while ($row=$query->fetch_assoc()){
			$sql="select count(*) as count from log_mail where receive_id={$row['char_id']} and send_time>$end_time and title='$email_title'";
			$count=$mysqli->count($sql);
			if(!$count){
				$char_list[]=array('charId'=>floatval($row['char_id']),'emailId'=>uuid());
			}
		}
		
		$gm=new Gm();
		$rpc='borpc/boemail.rpc';
		$rpc_obj='borpc\\Sour_B2oEmail';
		$async='b2ocreateEmail_async';
		
		if($char_list){
			$limit=20;
			$offset=0;
			while ($limit){
				$email['list']=array_slice($char_list, $offset,$limit);
				$gm->async($rpc, $rpc_obj, $async, $email);
				if(count($email['list'])<$limit){
					$limit=0;
					break;
				}
				$offset+=$limit;
			}
			$player_count=count($char_list);
			echo "奖励发放完毕,共 $player_count 个\n";
		}
		break;
	
	default:
		exit("操作参数错误\n");
		break;
}


/**
 +----------------------------------------------------------
 * 解析args参数 转化为常用的GET/post的参数模式
 +----------------------------------------------------------
 * @param array $args 需要转换的字符串 格式array('--a=AA','--b=BB')
 +----------------------------------------------------------
 * @return array 格式 array('a'=>'AA','b'=>'BB')
 +----------------------------------------------------------
 */
function parseArgs($argv){
	$out=array();
	foreach ($argv as $arg){
		if (substr($arg,0,2) == '--'){
			$eqPos = strpos($arg,'=');
			if ($eqPos === false){
				$key = substr($arg,2);
				$out[$key] = isset($out[$key]) ? $out[$key] : true;
			} else {
				$key = substr($arg,2,$eqPos-2);
				$out[$key] = substr($arg,$eqPos+1);
			}
		} else if (substr($arg,0,1) == '-'){
			if (substr($arg,2,1) == '='){
				$key = substr($arg,1,1);
				$out[$key] = substr($arg,3);
			} else {
				$chars = str_split(substr($arg,1));
				foreach ($chars as $char){
					$key = $char;
					$out[$key] = isset($out[$key]) ? $out[$key] : true;
				}
			}
		} else {
			$out[] = $arg;
		}
	}
	return $out;
}

