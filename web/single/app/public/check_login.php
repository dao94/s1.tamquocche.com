<?php
//登录验证
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__ . 'lang.php';

//加入code 登录
$action = isset($_REQUEST['action']) ? trim($_REQUEST['action']) : '';
switch ($action) {
	//跳转登录
	case 'jump':
		$jump['account'] = isset($_REQUEST['account']) ? trim($_REQUEST['account']) : '';
		$jump['code'] = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
		$jump['isSave'] = isset($_REQUEST['isSave']) ? intval($_REQUEST['isSave']) : 0;
		$jump['time'] = isset($_REQUEST['time']) ? $_REQUEST['time'] : '';
		$jump['agent'] = isset($_REQUEST['agent']) ? trim($_REQUEST['agent']) : '';
		$jump['sid'] = isset($_REQUEST['sid']) ? trim($_REQUEST['sid']) : '';
		ksort($jump);
		$flag = md5(http_build_query($jump).PHPRPC_KEY);
		$jump['sign'] = isset($_REQUEST['sign']) ? $_REQUEST['sign'] : '';

		if (SERVER_AGENT != $jump['agent'] || SERVER_ID != $jump['sid'] || $flag != $jump['sign'] || abs(time() - $jump['time']) > 300) {
			header('location:logout.php');
			exit;
		}

		$loginInfo['account']=$jump['account'];
		$loginInfo['code'] = $jump['code'];
		$loginInfo['isSave'] = $jump['isSave'];
		break;

	case 'code':
		//code验证
		(!isset($_COOKIE['__CODE']) || empty($_COOKIE['__CODE'])) && ajax_return('error', __('验证码错误'));
		$loginInfo['code'] = $_COOKIE['__CODE'];
		$loginInfo['account'] = $_COOKIE['__USER_ACCOUNT'];
		$loginInfo['isSave'] = isset($_COOKIE['__IS_SAVE']) ? intval($_COOKIE['__IS_SAVE']) : 0;
		break;

	default:
		//默认的Tài khoảnMật mã方式
		$user = isset($_POST['user']) ? trim($_POST['user']) : '';
		$pwd = isset($_POST['pwd']) ? trim($_POST['pwd']) : '';
		$verify = isset($_POST['verify_code']) ? trim($_POST['verify_code']) : '';
		empty($user) && ajax_return('error', __('Tài khoản不能为空！'));
		empty($pwd) && ajax_return('error', __('Mật mã不能为空！'));
		(empty($verify) || $_SESSION['verify'] != $verify) && ajax_return('error', __('đăng nhập失败,验证码错误！'));
		$user = my_escape_string($user);

		$loginInfo['account'] = $user;
		$loginInfo['password'] = $pwd;
		$loginInfo['verify'] = $verify;
		//是Phủ保存Trạng thái
		$loginInfo['isSave'] = isset($_POST['is_save']) ? intval($_POST['is_save']) : 0;
		break;
}
//验证Tài khoảnMật mã获取Tài khoản信息  中央后台登录只返回true | false
$loginInfo['serverType'] = SERVER_TYPE;
$loginInfo['agent'] = SERVER_AGENT;
$loginInfo['sid'] = SERVER_ID;
$loginInfo['ip'] = get_client_ip();

//远程调用
// include __LIB__ . 'phprpc_php/phprpc_client.php';
// $phprpc_client = new PHPRPC_Client();
// $phprpc_client->useService('http://' . CENTER_DOMAIN . '/center/app/interface/rbac.php');
// $phprpc_client->setKeyLength(128);
// $phprpc_client->setEncryptMode(2);
// $phprpc_client->setTimeout(10);
// $userInfo = $phprpc_client->login($loginInfo);

$userInfo = array(
	'__ROLE_MENU'		=> array(
		'index' => [
			'title' => 'Index',
			'show'  => 1,
			'childs' => [
				'index.php' => [
					'show' => 1,
					'title' => 'Chung'
				],
			],
		],
		'analysis' => [
			'title' => 'Thống Kê',
			'show'	=> 1,
			'childs' => [
				'analy_cost.php' => array(
					'show'	=> 1,
					'title' => 'analy_cost'
				),
				'analy_mall.php' => array(
					'show'	=> 1,
					'title' => 'analy_mall'
				),
				'analy_money.php' => array(
					'show'	=> 1,
					'title' => 'analy_money'
				),
				'cehua.php' => array(
					'show'	=> 1,
					'title' => 'cehua'
				),
				'character.php' => array(
					'show'	=> 1,
					'title' => 'character'
				),
				'keep.php' => array(
					'show'	=> 1,
					'title' => 'keep'
				),
				'level.php' => array(
					'show'	=> 1,
					'title' => 'level'
				),
				'login_reg.php' => array(
					'show'	=> 1,
					'title' => 'login_reg'
				),
				'map.php' => array(
					'show'	=> 1,
					'title' => 'map'
				),
				'mission_loss.php' => array(
					'show'	=> 1,
					'title' => 'mission_loss'
				),
				'money_keep.php' => array(
					'show'	=> 1,
					'title' => 'money_keep'
				),
				'online.php' => array(
					'show'	=> 1,
					'title' => 'online'
				),
				'rank.php' => array(
					'show'	=> 1,
					'title' => 'rank'
				),
				'reg_loss.php' => array(
					'show'	=> 1,
					'title' => 'reg_loss'
				),
				'reg_user.php' => array(
					'show'	=> 1,
					'title' => 'reg_user'
				),
				'time_loss.php' => array(
					'show'	=> 1,
					'title' => 'time_loss'
				),
			],
		],
		'base'  => [
			'title'	=> 'Base',
			'show'	=> 1,
			'childs' => [
				'login_reg.php' => array(
					'show' => 1,
					'title' => 'login_reg',
			 	),
				'online.php' => array(
					'show' => 1,
					'title' => 'online',
			 	),
				'reg_loss.php' => array(
					'show' => 1,
					'title' => 'reg_loss',
			 	),
				'reg_user.php' => array(
					'show' => 1,
					'title' => 'reg_user',
			 	),
				'time_loss.php' => array(
					'show' => 1,
					'title' => 'time_loss',
			 	),
			]
		],
		'cehua' => [
			'title' => 'cehua',
			'show'  => 1,
			'childs' => [
				'battlefield.php' => [
					'show' => 1,
					'title' => 'battlefield',
				],
				'box.php' => [
					'show' => 1,
					'title' => 'box',
				],
				'character.php' => [
					'show' => 1,
					'title' => 'character',
				],
				'click_box.php' => [
					'show' => 1,
					'title' => 'click_box',
				],
				'copy.php' => [
					'show' => 1,
					'title' => 'copy',
				],
				'equip_up.php' => [
					'show' => 1,
					'title' => 'equip_up',
				],
				'fight.php' => [
					'show' => 1,
					'title' => 'fight',
				],
				'jinjian.php' => [
					'show' => 1,
					'title' => 'jinjian',
				],
				'kaige.php' => [
					'show' => 1,
					'title' => 'kaige',
				],
				'map.php' => [
					'show' => 1,
					'title' => 'map',
				],
				'money_keep.php' => [
					'show' => 1,
					'title' => 'money_keep',
				],
				'pet_egg.php' => [
					'show' => 1,
					'title' => 'pet_egg',
				],
				'shake_tree.php' => [
					'show' => 1,
					'title' => 'shake_tree',
				],
				'stat_activity.php' => [
					'show' => 1,
					'title' => 'stat_activity',
				],
				'stat_check_point_zzsf.php' => [
					'show' => 1,
					'title' => 'stat_check_point_zzsf',
				],
				'stat_faction_manor.php' => [
					'show' => 1,
					'title' => 'stat_faction_manor',
				],
				'stat_fight_bag.php' => [
					'show' => 1,
					'title' => 'stat_fight_bag',
				],
				'stat_flash_version.php' => [
					'show' => 1,
					'title' => 'stat_flash_version',
				],
				'stat_gem.php' => [
					'show' => 1,
					'title' => 'stat_gem',
				],
				'stat_god.php' => [
					'show' => 1,
					'title' => 'stat_god',
				],
				'stat_hero.php' => [
					'show' => 1,
					'title' => 'stat_hero',
				],
				'stat_hero_meet.php' => [
					'show' => 1,
					'title' => 'stat_hero_meet',
				],
				'stat_home.php' => [
					'show' => 1,
					'title' => 'stat_home',
				],
				'stat_loader_time.php' => [
					'show' => 1,
					'title' => 'stat_loader_time',
				],
				'stat_offline_arena.php' => [
					'show' => 1,
					'title' => 'stat_offline_arena',
				],
				'stat_pet.php' => [
					'show' => 1,
					'title' => 'stat_pet',
				],
				'stat_ride.php' => [
					'show' => 1,
					'title' => 'stat_ride',
				],
				'stat_strong.php' => [
					'show' => 1,
					'title' => 'stat_strong',
				],
				'stat_vip.php' => [
					'show' => 1,
					'title' => 'stat_vip',
				],
				'stat_wing.php' => [
					'show' => 1,
					'title' => 'stat_wing',
				],
				'stat_wuhun.php' => [
					'show' => 1,
					'title' => 'stat_wuhun',
				],
			],
		],
		'gm' => [
			'title' => 'gm',
			'show'  => 1,
			'childs' => [
				'advice.php' => [
					'show' => 1,
					'title' => 'advice',
				],
				'consignment.php' => [
					'show' => 1,
					'title' => 'consignment',
				],
				'faction.php' => [
					'show' => 1,
					'title' => 'faction',
				],
				'forbid.php' => [
					'show' => 1,
					'title' => 'forbid',
				],
				'gm_card.php' => [
					'show' => 1,
					'title' => 'gm_card',
				],
				'ip.php' => [
					'show' => 1,
					'title' => 'ip',
				],
				'item.php' => [
					'show' => 1,
					'title' => 'item',
				],
				'online_pay.php' => [
					'show' => 1,
					'title' => 'online_pay',
				],
				'online_pay_verify.php' => [
					'show' => 1,
					'title' => 'online_pay_verify',
				],
				'player.php' => [
					'show' => 1,
					'title' => 'player',
				],
				'player_login.php' => [
					'show' => 1,
					'title' => 'player_login',
				],
				'player_update.php' => [
					'show' => 1,
					'title' => 'player_update',
				],
				'question.php' => [
					'show' => 1,
					'title' => 'question',
				],
				'rank.php' => [
					'show' => 1,
					'title' => 'rank',
				],
			],
		],
		'gm' => [
			'title' => 'gm',
			'show'  => 1,
			'childs' => [
				'advice.php' => [
					'show' => 1,
					'title' => 'advice',
				],
				'consignment.php' => [
					'show' => 1,
					'title' => 'consignment',
				],
				'faction.php' => [
					'show' => 1,
					'title' => 'faction',
				],
				'forbid.php' => [
					'show' => 1,
					'title' => 'forbid',
				],
				'gm_card.php' => [
					'show' => 1,
					'title' => 'gm_card',
				],
				'ip.php' => [
					'show' => 1,
					'title' => 'ip',
				],
				'item.php' => [
					'show' => 1,
					'title' => 'item',
				],
				'online_pay.php' => [
					'show' => 1,
					'title' => 'online_pay',
				],
				'online_pay_verify.php' => [
					'show' => 1,
					'title' => 'online_pay_verify',
				],
				'player.php' => [
					'show' => 1,
					'title' => 'player',
				],
				'player_login.php' => [
					'show' => 1,
					'title' => 'player_login',
				],
				'player_update.php' => [
					'show' => 1,
					'title' => 'player_update',
				],
				'question.php' => [
					'show' => 1,
					'title' => 'question',
				],
				'rank.php' => [
					'show' => 1,
					'title' => 'rank',
				],
			],
		],
		'log' => [
			'title' => 'log',
			'show'  => 1,
			'childs' => [
				'log_answer.php' => [
					'show' => 1,
					'title' => 'log_answer',
				 ],
				'log_arena.php' => [
					'show' => 1,
					'title' => 'log_arena',
				 ],
				'log_boss.php' => [
					'show' => 1,
					'title' => 'log_boss',
				 ],
				'log_consignment.php' => [
					'show' => 1,
					'title' => 'log_consignment',
				 ],
				'log_copy.php' => [
					'show' => 1,
					'title' => 'log_copy',
				 ],
				'log_equip.php' => [
					'show' => 1,
					'title' => 'log_equip',
				 ],
				'log_faction.php' => [
					'show' => 1,
					'title' => 'log_faction',
				 ],
				'log_faction_resource.php' => [
					'show' => 1,
					'title' => 'log_faction_resource',
				 ],
				'log_forge.php' => [
					'show' => 1,
					'title' => 'log_forge',
				 ],
				'log_item.php' => [
					'show' => 1,
					'title' => 'log_item',
				 ],
				'log_jinjian.php' => [
					'show' => 1,
					'title' => 'log_jinjian',
				 ],
				'log_login.php' => [
					'show' => 1,
					'title' => 'log_login',
				 ],
				'log_mail.php' => [
					'show' => 1,
					'title' => 'log_mail',
				 ],
				'log_marry.php' => [
					'show' => 1,
					'title' => 'log_marry',
				 ],
				'log_mission.php' => [
					'show' => 1,
					'title' => 'log_mission',
				 ],
				'log_money.php' => [
					'show' => 1,
					'title' => 'log_money',
				 ],
				'log_pass_card.php' => [
					'show' => 1,
					'title' => 'log_pass_card',
				 ],
				'log_pet.php' => [
					'show' => 1,
					'title' => 'log_pet',
				 ],
				// 'log_restore.php' => [
				// 	'show' => 1,
				// 	'title' => 'log_restore',
				//  ],
				'log_skill.php' => [
					'show' => 1,
					'title' => 'log_skill',
				 ],
				'log_trade.php' => [
					'show' => 1,
					'title' => 'log_trade',
				 ],
				'log_upgrade.php' => [
					'show' => 1,
					'title' => 'log_upgrade',
				 ],
				'log_wing.php' => [
					'show' => 1,
					'title' => 'log_wing',
				 ],
				'log_zuoqi.php' => [
					'show' => 1,
					'title' => 'log_zuoqi',
				 ],
			],
		],
		'pay'	=> array(
			'title'		=> 'Trung Tâm Thẻ Nạp',
			'show'		=> 1,
			'childs'	=> array(
				'pay_add.php' => array(
					'show'	=> 1,
					'title' => 'Pay Add'
				),
				'pay_distributed.php' => array(
					'show'	=> 1,
					'title' => 'Pay Distriubted'
				),
				'pay_log.php' => array(
					'show'	=> 1,
					'title' => 'Pay Log'
				),
				'pay_mall.php' => array(
					'show'	=> 1,
					'title' => 'Pay Mall'
				),
				'pay_new_user.php' => array(
					'show'	=> 1,
					'title' => 'Pay New User'
				),
				'pay_rankings.php' => array(
					'show'	=> 1,
					'title' => 'Pay Rankings'
				),
				'pay_rate.php' => array(
					'show'	=> 1,
					'title' => 'Pay Rate'
				),
				'pay_statistics.php' => array(
					'show'	=> 1,
					'title' => 'Pay Statistics'
				),
			)
		),

		'pm' => [
			'title' => 'pm',
			'show'  => 1,
			'childs' => [
				'pm_activity.php' => [
					'title' => 'pm_activity',
					'show' => 1,
				],
				'pm_broadcasting.php' => [
					'title' => 'pm_broadcasting',
					'show' => 1,
				],
				'pm_card.php' => [
					'title' => 'pm_card',
					'show' => 1,
				],
				'pm_email.php' => [
					'title' => 'pm_email',
					'show' => 1,
				],
				'pm_gmer.php' => [
					'title' => 'pm_gmer',
					'show' => 1,
				],
				'pm_gmer_apply.php' => [
					'title' => 'pm_gmer_apply',
					'show' => 1,
				],
				'pm_mall.php' => [
					'title' => 'pm_mall',
					'show' => 1,
				],
				'pm_notice.php' => [
					'title' => 'pm_notice',
					'show' => 1,
				],
				'pm_online.php' => [
					'title' => 'pm_online',
					'show' => 1,
				],
				'pm_reward_apply.php' => [
					'title' => 'pm_reward_apply',
					'show' => 1,
				],
				'pm_reward_send.php' => [
					'title' => 'pm_reward_send',
					'show' => 1,
				],
				'pm_reward_verify.php' => [
					'title' => 'pm_reward_verify',
					'show' => 1,
				],
				'pm_server.php' => [
					'title' => 'pm_server',
					'show' => 1,
				],
				'pm_switch.php' => [
					'title' => 'pm_switch',
					'show' => 1,
				],
			],
		],

		// 'script' => [
		// 	'title' => 'script',
		// 	'show'  => 1,
		// 	'childs' => [
		// 		'all_gold.php' => [
		// 			'title' => 'all_gold',
		// 			'show' => 1,
		// 		],
		// 		'cehua_data.php' => [
		// 			'title' => 'cehua_data',
		// 			'show' => 1,
		// 		],
		// 		'login_old.php' => [
		// 			'title' => 'login_old',
		// 			'show' => 1,
		// 		],
		// 		'login_old_new.php' => [
		// 			'title' => 'login_old_new',
		// 			'show' => 1,
		// 		],
		// 		'login_player_five_fight.php' => [
		// 			'title' => 'login_player_five_fight',
		// 			'show' => 1,
		// 		],
		// 		'login_player_seven.php' => [
		// 			'title' => 'login_player_seven',
		// 			'show' => 1,
		// 		],
		// 		'player_equip.php' => [
		// 			'title' => 'player_equip',
		// 			'show' => 1,
		// 		],
		// 		'send_reward.php' => [
		// 			'title' => 'send_reward',
		// 			'show' => 1,
		// 		],
		// 		'stat_day_ended.php' => [
		// 			'title' => 'stat_day_ended',
		// 			'show' => 1,
		// 		],
		// 		'world_cup.php' => [
		// 			'title' => 'world_cup',
		// 			'show' => 1,
		// 		],
		// 	],
		// ],

		'server' => [
			'title' => 'server',
			'show'  => 1,
			'childs' => [
				'cache_clear.php' => [
					'title' => 'cache_clear',
					'show' => 1,
				],
				'log.php' => [
					'title' => 'log',
					'show' => 1,
				],
				'server_tools.php' => [
					'title' => 'server_tools',
					'show' => 1,
				],
				'task.php' => [
					'title' => 'task',
					'show' => 1,
				],
			],
		],

		// 'task' => [
		// 	'title' => 'task',
		// 	'show'  => 1,
		// 	'childs' => [
		// 		'ceshi_mulu.php' => [
		// 			'title' => 'ceshi_mulu',
		// 			'show' => 1,
		// 		],
		// 		'clear_data.php' => [
		// 			'title' => 'clear_data',
		// 			'show' => 1,
		// 		],
		// 		'core_backups.php' => [
		// 			'title' => 'core_backups',
		// 			'show' => 1,
		// 		],
		// 		'output_txt.php' => [
		// 			'title' => 'output_txt',
		// 			'show' => 1,
		// 		],
		// 		'stat_copy.php' => [
		// 			'title' => 'stat_copy',
		// 			'show' => 1,
		// 		],
		// 		'stat_copy_new.php' => [
		// 			'title' => 'stat_copy_new',
		// 			'show' => 1,
		// 		],
		// 		'stat_cost.php' => [
		// 			'title' => 'stat_cost',
		// 			'show' => 1,
		// 		],
		// 		'stat_day_ended.php' => [
		// 			'title' => 'stat_day_ended',
		// 			'show' => 1,
		// 		],
		// 		'stat_equip.php' => [
		// 			'title' => 'stat_equip',
		// 			'show' => 1,
		// 		],
		// 		'stat_gunfu.php' => [
		// 			'title' => 'stat_gunfu',
		// 			'show' => 1,
		// 		],
		// 		'stat_info.php' => [
		// 			'title' => 'stat_info',
		// 			'show' => 1,
		// 		],
		// 		'stat_loss.php' => [
		// 			'title' => 'stat_loss',
		// 			'show' => 1,
		// 		],
		// 		'stat_map_mission.php' => [
		// 			'title' => 'stat_map_mission',
		// 			'show' => 1,
		// 		],
		// 		'stat_money.php' => [
		// 			'title' => 'stat_money',
		// 			'show' => 1,
		// 		],
		// 		'stat_others.php' => [
		// 			'title' => 'stat_others',
		// 			'show' => 1,
		// 		],
		// 		'stat_pay.php' => [
		// 			'title' => 'stat_pay',
		// 			'show' => 1,
		// 		],
		// 		'stat_rank.php' => [
		// 			'title' => 'stat_rank',
		// 			'show' => 1,
		// 		],
		// 		'stat_wing.php' => [
		// 			'title' => 'stat_wing',
		// 			'show' => 1,
		// 		],
		// 		'transit_data.php' => [
		// 			'title' => 'transit_data',
		// 			'show' => 1,
		// 		],
		// 	],
		// ],


	),
	'__ROLE_ID'			=> '0', // quyen admin
	'__USER_NAME'		=> 'test',
	'__USER_ACCOUNT'	=> 'testaccount',
	'__AGENTS'			=> array(
		'admin','administrator','TQC'
	),
	'__USER_SERVER_LIST'=> array(
		'TQC' => array(
			'test01' => 'ceshi01'
		)
	),
	'__EXPIRE'			=> '2000000000',
	'__CODE'			=> '1'
);

if ($userInfo === false || (isset($userInfo[0]) && $userInfo[0] === false) || is_a($userInfo, 'PHPRPC_Error')) {
	setcookie('__CODE', NULL);
	setcookie('__IS_SAVE', NULL);
	if ($action !='jump') {
		is_array($userInfo) && $userInfo[0] === false && ajax_return('error', $userInfo[1]);
		ajax_return('error', __('登录失败！'));
	}
	header('location:logout.php');
} else {
	$_SESSION['__' . SERVER_TYPE . '_ROLE_MENU'] = $userInfo['__ROLE_MENU'];
	$_SESSION['__' . SERVER_TYPE . '_ROLE_ID'] = $userInfo['__ROLE_ID'];
	$_SESSION['__' . SERVER_TYPE . '_USER_NAME'] = $userInfo['__USER_NAME'];
	$_SESSION['__' . SERVER_TYPE . '_USER_ACCOUNT'] = $userInfo['__USER_ACCOUNT'];
	$_SESSION['__' . SERVER_TYPE . '_AGENTS'] = $userInfo['__AGENTS'];
	$_SESSION['__' . SERVER_TYPE . '_USER_SERVER_LIST'] = $userInfo['__USER_SERVER_LIST'];
	$_SESSION['__' . SERVER_TYPE . '__LIANYUN_POWER'] = $userInfo['__LIANYUN_POWER'];

	$_SESSION['__' . SERVER_TYPE . '_CURRENT'] = empty($_REQUEST['current']) ? '' : trim($_REQUEST['current']);//跳转，保存当前页

	setcookie('__IS_SAVE', isset($loginInfo['isSave']) ? intval($loginInfo['isSave']) : 0, $userInfo['__EXPIRE']);
	setcookie('__CODE', $userInfo['__CODE'], isset($loginInfo['isSave']) && intval($loginInfo['isSave']) == 1 ? $userInfo['__EXPIRE'] : 0);
	setcookie('__USER_ACCOUNT', $userInfo['__USER_ACCOUNT'], time()+86400*365);

	$action != 'jump' && ajax_return('ok', __('登录Thành công！'));
	header('location:../../index.php');
}
?>