<?php
//提供接口给客户端调用
define('__ROOT__', str_replace(array('//', '\\','api'), array('/', '/','single'), __DIR__));
include __ROOT__.'/config/config.php';
$action = empty($_REQUEST['action']) ? '' : trim($_REQUEST['action']);

switch ($action) {
	case 'fcm':
		include __CONFIG__ . 'key_config.php';
		include __API__ . 'lib/Fcm.class.php';
		include __FUNCTIONS__ . 'function.php';
		$fcmobj = Fcm::factory();
		$flag = $fcmobj->run();
		session_start();
		$_SESSION['__' . SERVER_TYPE . '_GAME_DATA']['cm'] = $flag;
		echo $flag;
		exit;
		break;

	case 'gm':
		//GM面板
		include __FUNCTIONS__ . 'function.php';
		include_once __CLASSES__ . 'DbMysqli.class.php';
		$char_id = floatval($_POST['char_id']);
		$type = intval($_POST['type']);
		$account = my_escape_string(trim($_POST['account']));
		$char_name = my_escape_string(trim($_POST['char_name']));
		$title = my_escape_string(trim($_POST['title']));
		$content = my_escape_string(trim($_POST['content']));
		if (empty($char_id) || empty($type) || empty($account) || empty($char_name) || empty($title) || empty($content)) {
			exit('0'); //参数不齐
		}
		$time = time();
		$mysqli = new DbMysqli();
		$sql = "insert into gm_question (account,char_id,char_name,type,title,content,time,status) values
    		('$account',$char_id,'$char_name',$type,'$title','$content',$time,2)";
		if ($mysqli->query($sql)) {
			exit('1');
		} else {
			exit('0');
		}
		break;

	case 'advice':
		//咨询建议
		include __FUNCTIONS__ . 'function.php';
		include_once __CLASSES__ . 'DbMysqli.class.php';
		$char_id = floatval($_POST['char_id']);
		$type = intval($_POST['type']);
		$account = my_escape_string(trim($_POST['account']));
		$char_name = my_escape_string(trim($_POST['char_name']));
		$title = my_escape_string(trim($_POST['title']));
		$content = my_escape_string(trim($_POST['content']));
		if (empty($char_id) || empty($type) || empty($account) || empty($char_name) || empty($title) || empty($content)) {
			exit('0'); //参数不齐
		}
		$time = time();
		$mysqli = new DbMysqli();
		$sql = "insert into gm_advice (account,char_id,char_name,type,title,content,time,status) values
    		('$account',$char_id,'$char_name',$type,'$title','$content',$time,2)";
		if ($mysqli->query($sql)) {
			exit('1');
		} else {
			exit('0');
		}
		break;

	case 'loader':
		include __FUNCTIONS__ . 'function.php';
		//创号各种加载流程
		session_start();
		if (!isset($_SESSION['__'.SERVER_TYPE.'_GAME_DATA'])){
			ajax_return(0, 'not login');
		}
		include __CLASSES__ . 'Mdb.class.php';
		$gevent = isset($_POST['gevent']) ? trim($_POST['gevent']) : '';
		$account = $_SESSION['__' . SERVER_TYPE . '_GAME_DATA']['account'];
		$sid = intval($_SESSION['__' . SERVER_TYPE . '_GAME_DATA']['sid']);
		$gevents = array('loader_page', 'loader_main', 'loader_resource', 'loader_login', 'loader_character', 'loader_game');
		if (!$account || !$gevent || !in_array($gevent, $gevents)) {
			ajax_return(0, 'invalid');
		}
		$mdb = new Mdb();
		$mdb->selectDb(MONGO_PERFIX . '4');
		$condition = array('account' => $account, 'serverId' => $sid, $gevent => array('$exists' => true));
		$account_data = $mdb->findOne('account_data', $condition,array('char_id'));

		//进入游戏后，告知Gm 平台会员等级
		if($gevent=='loader_game'&&isset($_SESSION['__'.SERVER_TYPE.'_GAME_DATA']['member_level'])){
			if(!isset($account_data['char_id'])){
				$list=$mdb->findOne('account_data', array('account' => $account, 'serverId' => $sid),array('char_id'));
				$char_id=empty($list['char_id']) ? 0  : $list['char_id'];
			}else{
				$char_id=$account_data['char_id'];
			}
			if($char_id){
				include __CLASSES__.'Gm.class.php';
				$rpc = 'brrpc/bractivity.rpc';
				$rpc_obj = 'brrpc\Sour_B2rBaiduVip';
				$async = 'b2rBaiduVipInfo_async';
				$msg=array(
					'charId'=>$char_id,
					'vipInfo'=>array(
						array('key'=>1,'value'=>intval($_SESSION['__'.SERVER_TYPE.'_GAME_DATA']['member_level'])),//会员等级
						array('key'=>2,'value'=>intval($_SESSION['__'.SERVER_TYPE.'_GAME_DATA']['is_annual'])),//是否是年钻
						array('key'=>3,'value'=>intval($_SESSION['__'.SERVER_TYPE.'_GAME_DATA']['is_quarter'])),//是否是季度
					)
				);
				$gm=new Gm();
				$gm->async($rpc, $rpc_obj, $async, $msg);
			}
		}


		if(($gevent=='loader_game'&&SERVER_AGENT=='is'&&SERVER_ID=='s6')||($gevent=='loader_game'&&SERVER_AGENT=='feiyin')){
			$mysqli=new DbMysqli();
			$sql="select * from is_pay_compensate where status=0 and account='$account'";
			$list=$mysqli->findOne($sql);
			if(isset($list['status'])&&$list['status']==0){
				$character=array();
				for($i=0;$i<4;$i++){
					$mdb->selectDb(MONGO_PERFIX.$i);
					$character=$mdb->findOne('characters', array('account'=>$account), array('_id','name'));
					if($character){
						break;
					}
				}
				if($character){
					$char_id=$character['_id'];
					$char_name=$character['name'];
					$gold=intval($list['gold'])*2;
					include_once __CLASSES__.'Gm.class.php';
					$gm = new Gm();
					$rpc = 'borpc/boemail.rpc';
					$rpc_obj = 'borpc\\Sour_B2oEmail';
					$async = 'b2ocreateEmail_async';
					$emailId=uuid();
					$time=time();
					$email_title='双倍元宝补偿';
					$email_content='亲爱的玩家：您好！由于程序异常，造成若干玩家的数据遗失。我们将根据您5月30日22：00前的充值金额进行双倍元宝补偿，请查收！给您造成的不便敬请谅解，感谢您对《乱舞江山》的支持！';
					$svemail=array(
						'title' => $email_title,
						'content' => $email_content,
						'moneyList'=>array(
							'jade'=>$gold,
						),
						'list'=>array(
							array('charId'=>$char_id,'emailId'=>$emailId),
						),
					);
					$sql="update is_pay_compensate set status=1,char_id=$char_id,time=$time where account='$account'";
					if($mysqli->query($sql)){
						$gm->async($rpc, $rpc_obj, $async, $svemail);

						$reward_list['moneyList']=$svemail['moneyList'];
						$reward_list=my_escape_string(json_encode($reward_list));
						$sql = "insert into `reward_log`(`id`,`account`,`char_id`,`char_name`,`gm`,`reason`,`email_title`,`email_content`,`send_ts`,`reward_list`) values
						('$emailId','$account',$char_id,'$char_name','gm','充值元宝补偿','$email_title','$email_content',$time,'$reward_list')";
						$mysqli->query($sql);
					}
				}
			}
		}


		if ($account_data) {
			ajax_return(0, 'exist');
		}else{
			$condition = array('account' => $account, 'serverId' => $sid);
			$newdata = array('$set' => array($gevent => time()));
			if($mdb->update('account_data', $condition, $newdata)){
				ajax_return(1, 'ok');
			}else{
				ajax_return(0, 'failed');
			}
		}
		exit;
		break;

	case 'online':
		//保持游戏在线5分钟一次访问
		session_start();
		//echo session_is_registered('__'.SERVER_TYPE.'_GAME_DATA');
		exit('1');
		break;

	case 'favorite':
		//收藏快捷方式
		include __CONFIG__.'url_config.php';
		$url=empty($url_config['xuan']) ? $url_config['guan'] : $url_config['xuan'];
		$filename=SERVER_TITLE.'.url';
		$encoded_filename=urlencode($filename);
		$encoded_filename=str_replace("+", "%20", $encoded_filename);
		$ua=$_SERVER["HTTP_USER_AGENT"];
		header('Content-type:application/octet-stream;');
		if (preg_match("/MSIE/", $ua)){
			header('Content-Disposition: attachment; filename="'.$encoded_filename.'"');
		}else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition: attachment; filename="'.$filename.'"');
		}else {
			header('Content-Disposition: attachment; filename="'.$filename.'"');
		}
		$shortcut="[InternetShortcut]
			URL=$url
			IDList=
			IconFile=http://{$_SERVER['HTTP_HOST']}/favicon.ico
			IconIndex=1
			[{000214A0-0000-0000-C000-000000000046}]
			Prop3=19,2";
		echo $shortcut;
		exit;
		break;

	case 'flash':
		//flash版本入库
		include __FUNCTIONS__ . 'function.php';
		session_start();
		if (!isset($_SESSION['__' . SERVER_TYPE . '_GAME_DATA'])){
			ajax_return(0,'not login');
		}
		$version = round(trim($_POST['version']), 2);
		$account = $_SESSION['__' . SERVER_TYPE . '_GAME_DATA']['account'];
		$sid = $_SESSION['__' . SERVER_TYPE . '_GAME_DATA']['sid'];
		if (!$account || !$sid || !$version){
			ajax_return(0,'parameter error');//参数不齐
		}
		$mysqli = new DbMysqli();
		$sql = "select count(*) as count from stat_flash_version where account='$account' and sid=$sid and version=$version";
		$count = $mysqli->count($sql);
		if ($count > 0) {
			ajax_return(0,'exist');//记录已存在
		}
		$time = time();
		$sql = "replace into stat_flash_version (account,sid,version,time) value ('$account',$sid,'$version',$time)";
		if($mysqli->query($sql)){
			ajax_return(1, 'succeed');
		}else{
			ajax_return(0, 'failed');
		}
		break;

	case 'log_client':
		//客户端日志
		include __FUNCTIONS__ . 'function.php';
		$char_name=empty($_POST['char_name']) ? '' : my_escape_string(trim($_POST['char_name']));
		$content=empty($_POST['content']) ? '' : my_escape_string(trim($_POST['content']));
		if(!$char_name || !$content){
			ajax_return(0, 'parameter error');
		}
		$time=time();
		$sql="insert into log_client (char_name,content,time) value ('$char_name','$content',$time)";
		$mysqli=new DbMysqli();
		if($mysqli->query($sql)){
			ajax_return(1, 'succeed');
		}else{
			ajax_return(0, 'failed');
		}
		break;

	case 'check_card':
		// Các hoạt động xác minh Giftcode
		include __FUNCTIONS__ . 'function.php';
		$account=empty($_POST['account']) ? '' : my_escape_string(trim($_POST['account']));
		$server_id=empty($_POST['server_id']) ? '' : my_escape_string(trim($_POST['server_id']));
		$card_type=empty($_POST['card_type']) ? 0 : intval($_POST['card_type']);
		$verify=empty($_POST['verify']) ? 0 : intval($_POST['verify']);
		$card_num=empty($_POST['card_num']) ? '' : trim($_POST['card_num']);
		$char_name=empty($_POST['char_name']) ? '' : my_escape_string(trim($_POST['char_name']));

		// Xác thực
		// 1 là Game server
		// 2 Guide Card
		// 3 PHP
		// 4 Thẻ tân thủ
		// 5 Mobile
		switch ($verify){
			case 3:
				// Nghiệm chứng PHP - Giftcode dùng xong sẽ vô hiệu.
				include __CLASSES__.'Xcrypt.class.php';

				if(!$char_name || !$account || !$card_num){
					ajax_return(0,'parameter error');
				}

				$key = '$#1^*f^*';// Key encryption
				$Iv  ='!#$^&*(&';// Iv vector

				$des = new Xcrypt($key,'cbc',$Iv);

				$card_str = $des->decrypt($card_num,'hex');

				$card_length = strlen($card_str);

				if($card_length!=8 && $card_length!=16){
					ajax_return(0,'card length error');
				}

				$agent_id=$sid=0;

				switch ($card_length)
				{
					case 8:
						//8位原始卡号规则：3位卡号+5位置数量
						// 8 chỗ quy tắc ban đầu: 3 cái loại giftcode + 5 vị trí số lượng
						$type=intval(substr($card_str,0,3));
						$num=intval(substr($card_str,3,6));
					break;

					case 16:
						//16位原始卡号规则：3位代理id+5位区服id+3位卡号+5位置数量
						// 16 chỗ quy tắc thẻ ban đầu: 3 chỗ Id đại lý + 5 id server + 3 cái loại giftcode + 5 số vị trí
						$agent_id=intval(substr($card_str,0,3));
						$sid=intval(substr($card_str,3,5));
						$type=intval(substr($card_str,8,3));
						$num=intval(substr($card_str,11,5));
					break;
				}

				if($card_type&&$card_type!=$type){
					ajax_return(0, 'type error');
				}

				$card_type=$card_type ? $card_type : $type;

				include __CLASSES__.'Mdb.class.php';
				$mdb=new Mdb();
				$mdb->selectDb(MONGO_PERFIX.'5');
				//verify=3 是php解密且到中央服验证唯一性
				$condition=array('type'=>$card_type,'verify'=>$verify,'agent_id'=>$agent_id,'sid'=>$sid,'limit'=>array('$gte'=>$num),'over'=>array('$gte'=>time()));
				$card=$mdb->count('card', $condition);
				if(!$card){
					ajax_return(0, 'activity not exist');
				}

				$data['action']='check';
				$data['card_type']=$card_type;
				$data['card_num']=$card_num;

				//中央后台验证
				include __LIB__.'phprpc_php/phprpc_client.php';
				$phprpc_client = new PHPRPC_Client();
				$phprpc_client->useService('http://'.CENTER_DOMAIN.'/center/app/interface/pm_api.php');
				$phprpc_client->setKeyLength(128);
				$phprpc_client->setTimeout(10);
				$check=$phprpc_client->pm_card($data);
				if($check===true){
					$mdb->selectDb(MONGO_PERFIX.'4');
					$condition=array('account'=>$account,'serverId'=>intval(substr($server_id,1)));
					$list=$mdb->findOne('account_data', $condition, array('char_id'));
					if(empty($list['char_id'])){
						ajax_return(0,'check character error');
					}
					$char_id=floatval($list['char_id']);

					//中央后台入库
					$data=array(
						'action'=>'insert',
						'agent'=>SERVER_AGENT,
						'sid'=>intval(substr($server_id,1)),
						'account'=>$account,
						'char_name'=>$char_name,
						'card_type'=>$card_type,
						'card_num'=>$card_num
					);
					$result=$phprpc_client->pm_card($data);
					if($result!==true){
						ajax_return(0, 'failed');
					}

					//验证通过,发协议
					include __CLASSES__.'Gm.class.php';
					$gm=new Gm();
					$msg=array('charId'=>$char_id,'cardId'=>$card_type,'code'=>$card_num,'flag'=>intval($verify));
					$rpc='brrpc/brcard.rpc';
					$rpc_obj='brrpc\\Sour_B2rCardReward';
					$async='b2rNotifyCardReward_async';
					$gm->async($rpc, $rpc_obj, $async, $msg);
					ajax_return(1,'ok');
				}else{
					ajax_return(0, 'centre check error');
				}
				break;

			case '4':
			case '5':
				include __CONFIG__.'key_config.php';
				if(!$account || !$card_type || !$card_num){
					ajax_return(0, 'check account error');
				}
				$code='';
				if($verify==4){
					switch (SERVER_AGENT){
						case 'baidu':
							//百度新手卡 (常量（key）+常量（游戏名称）+常量（all）+变量（$userId）+变量标识码（***）)
							$api_key='45ea078501457c9149381056ee5635bf';
							$code=md5($api_key.'lwjs'.'all'.$account.'baidu');
							break;
						case '360':
							//360新手卡 md5($qid.$server_id.$type.$key)
							$code=strtoupper(md5($account.strtoupper($server_id).'1'.LOGIN_KEY));
							break;
						default:
							//默认：新手卡接口 md5(key+username)
							$code=md5(LOGIN_KEY.$account);
							break;
					}
				}else if($verify==5){ // giao diện Mobile
					//手机绑定礼包 md5(key + game + server + username + type)
					$code=md5(LOGIN_KEY.'lwjs'.strtoupper($server_id).urlencode($account).'sj');
				}
				if($code!=$card_num){
					ajax_return(0, 'check code error');//激活码错误
				}
				$mdb=new Mdb();
				$mdb->selectDb(MONGO_PERFIX.'4');
				$condition=array('account'=>$account,'serverId'=>intval(substr($server_id,1)));
				$list=$mdb->findOne('account_data', $condition, array('char_id'));
				if(empty($list['char_id'])){
					ajax_return(0,'check character error');
				}
				$char_id=floatval($list['char_id']);

				//验证通过,发协议
				include __CLASSES__.'Gm.class.php';
				$gm=new Gm();
				$msg=array('charId'=>$char_id,'cardId'=>$card_type,'code'=>$card_num,'flag'=>intval($verify));
				$rpc='brrpc/brcard.rpc';
				$rpc_obj='brrpc\\Sour_B2rCardReward';
				$async='b2rNotifyCardReward_async';
				$gm->async($rpc, $rpc_obj, $async, $msg);
				ajax_return(1,'ok');
				break;

			default:
				ajax_return(0, 'forbid');
				break;
		}
		break;

	case 'click_box':
		//点击功能面板流水
		include __FUNCTIONS__ . 'function.php';
		session_start();
		!isset($_SESSION['__'.SERVER_TYPE.'_GAME_DATA']) && exit ('not login');
		$char_id=empty($_POST['char_id']) ? '' : floatval($_POST['char_id']);
		$type=empty($_POST['type']) ? '' : floatval($_POST['type']);
		$level=empty($_POST['level']) ? '' : floatval($_POST['level']);
		(!$char_id || !$type || !$level) && exit('parameter error');
		$time=time();
		$mysqli=new DbMysqli();
		$sql="insert into log_click_box (char_id,type,level,time) values ($char_id,$type,$level,$time)";
		$mysqli->query($sql);
		break;

	default:
		exit('forbid');
		break;
}
?>
