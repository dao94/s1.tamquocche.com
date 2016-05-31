<?php

/*
 * 	函数库
 */

/*
 * 	绝对路径转相对路径
 * 	参数说明： $file 引用文件据对路径      $incfile   被引用文件路径
 * 	返回值：   相对路径（string）
 */

function get_relative_path($file, $incfile) {
	$virtualPath = '';
	$file_path = explode('/', str_replace('//', '/', $file));
	$incfile_path = explode('/', str_replace('//', '/', $incfile));

	$i = 0;
	while (true) {
		if ($file_path[$i] == $incfile_path[$i]) {
			$i++;
		} else {
			break;
		}
	}
	if ($i <> 0) {
		$file_dirs = count($file_path);
		$incfile_dirs = count($incfile_path);
		$root_num = $file_dirs - $i - 2;
		for ($k = 0; $k <= $root_num; $k++) {
			$virtualPath .= '../';
		}
		for ($j = $i; $j < $incfile_dirs; $j++) {
			$PathArray[] = $incfile_path[$j];
		}
		$virtualPath .= implode('/', $PathArray);
	} else {
		$virtualPath = '';
	}
	return $virtualPath;
}

/*
 * 	获取客户端真实ip地址
 */

function get_client_ip() {
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	$ip = getenv("HTTP_CLIENT_IP");
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	$ip = getenv("REMOTE_ADDR");
	else if (isset($_SERVER ['REMOTE_ADDR']) && $_SERVER ['REMOTE_ADDR'] && strcasecmp($_SERVER ['REMOTE_ADDR'], "unknown"))
	$ip = $_SERVER ['REMOTE_ADDR'];
	else
	$ip = "unknown";
	return ($ip);
}

/**
 +----------------------------------------------------------
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 +----------------------------------------------------------
 * @param string $len 长度
 * @param string $type 字串类型
 * 0 字母 1 数字 2 大写字母 3 小写字母 4 中文字符 默认 字母数字混合
 * @param string $addChars 额外字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function rand_string($len = 6, $type = '', $addChars = '') {
	$str = '';
	switch ($type) {
		case 0:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 1:
			$chars = str_repeat('0123456789', 3);
			break;
		case 2:
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
			break;
		case 3:
			$chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
			break;
		case 4:
			$chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" . $addChars;
			break;
		default :
			// 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
			$chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' . $addChars;
			break;
	}
	if ($len > 10) {//位数过长重复字符串一定次数
		$chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
	}
	if ($type != 4) {
		$chars = str_shuffle($chars);
		$str = substr($chars, 0, $len);
	} else {
		// 中文随机字
		for ($i = 0; $i < $len; $i++) {
			$str.= msubstr($chars, floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
		}
	}
	return $str;
}

/*
 * 	祛除跨站攻击
 */

function remove_xss($val) {
	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	// this prevents some character re-spacing such as <java\0script>
	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

	// straight replacements, the user should never need these since they're normal characters
	// this prevents like <IMG SRC=@avascript:alert('XSS')>
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		// ;? matches the ;, which is optional
		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
		// @ @ search for the hex values
		$val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
		// @ @ 0{0,7} matches '0' zero to seven times
		$val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
	}

	// now the only remaining whitespace attacks are \t, \n, and \r
	$ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);

	$found = true; // keep replacing as long as the previous round replaced something
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			$replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			}
		}
	}
	return $val;
}

/**
 +----------------------------------------------------------
 * 是否AJAX请求
 +----------------------------------------------------------
 * @return bool
 +----------------------------------------------------------
 */
function is_ajax() {
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
		if ('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
		return true;
	}
	return false;
}

/**
 +----------------------------------------------------------
 * 解决file_get_contents 远程获取文件内容导致php-cgi进程cpu100%
 * 常用于get方式接口
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function file_get_content($url, $timeout = 30) {
	$ctx = stream_context_create(array(
        'http' => array(
            'timeout' => $timeout   //设置一个超时时间，单位为秒
	)
	)
	);
	return file_get_contents($url, 0, $ctx);
}

/**
 +----------------------------------------------------------
 * curl模拟post方式访问远端接口
 +----------------------------------------------------------
 * $url	远端接口地址
 * $pstr 参数   形似 'para1=val1&para2=val2&...'或使用一个以字段名为键值，字段数据为值的数组
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function http_post($url, $pstr = array()) {
	//判断是否开启了curl扩展
	if (!function_exists('curl_init'))
	return NULL;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $pstr);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERAGENT, 'TEST PHP5 Client 1.1 (curl) ' . phpversion());
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

/**
 +----------------------------------------------------------
 * 提示信息跳转
 +----------------------------------------------------------
 * $message	提示信息
 * $jumpUrl	跳转地址		默认跳回来路页面
 +----------------------------------------------------------
 */
function notice($message, $jumpUrl = '') {
	$jumpUrl = $jumpUrl == '' ? $_SERVER['HTTP_REFERER'] : $jumpUrl;
	//跳转到提示信息页面
	header('Location:../public/tips.php?message=' . urlencode($message) . '&jumpUrl=' . urlencode($jumpUrl));
	exit;
}

/**
 +----------------------------------------------------------
 * 自动加载类库
 +----------------------------------------------------------
 */
function __autoload($class_name) {
	$file_name=__CLASSES__.$class_name.'.class.php';
	if(is_file($file_name)){
		include $file_name;
	}
}

/**
 +----------------------------------------------------------
 * 日志记录函数
 +----------------------------------------------------------
 * $log_file_name	日志文件名（不包含文件扩展名）
 * $log_data		日志记录数据（数组类型、字符类型）
 +----------------------------------------------------------
 */
function write_log($log_data, $log_file_name) {
	if (LOG_SWITCH) {
		if (is_array($log_data)) {
			$log = array_map('pack_log', $log_data);
			$log = implode('|', $log);
		} else {
			$log = str_replace(array("\n", "\r", "|"), array('', '', '&#124;'), $log_data);
		}
		error_log("<?php die;?>|" . $log . "\n", 3, __LOGS__ . SERVER_TYPE . '_' . SERVER_AGENT . '_' . SERVER_ID . '_' . $log_file_name . '.log.php');
	}
}

/**
 +----------------------------------------------------------
 * 日志数据处理函数
 +----------------------------------------------------------
 * $log_data		日志记录数据（数组类型、对象类型、字符类型）
 +----------------------------------------------------------
 */
function pack_log($log_data) {
	$data = '';
	if (is_array($log_data)) {
		$data = str_replace(array(" ", "\n", "\r", "\t", "|"), array('', '', '', '', '&#124;'), var_export($log_data, true));
	} else {
		$data = str_replace(array("\n", "\r", "\t", "|"), array('', '', '', '&#124;'), $log_data);
	}
	return $data;
}

/**
 +----------------------------------------------------------
 * 日志读取函数
 +----------------------------------------------------------
 * $log_file_name	日志文件名（不包含文件扩展名）
 * $read_size 		读取字节数
 +----------------------------------------------------------
 */
function read_log($log_file_name, $offset = 1024000) {
	$log_file = __LOGS__ . SERVER_TYPE . '_' . SERVER_AGENT . '_' . SERVER_ID . '_' . $log_file_name . '.log.php';
	$readb = array();
	if (!file_exists($log_file))
	return $readb;
	if ($fp = @fopen($log_file, "rb")) {
		flock($fp, LOCK_SH);
		$size = filesize($log_file);
		$size > $offset ? fseek($fp, -$offset, SEEK_END) : $offset = $size;
		$readb = fread($fp, $offset);
		fclose($fp);
		$readb = str_replace("\n", "\n<:app:>", $readb);
		$readb = explode("<:app:>", $readb);
		$count = count($readb);
		if ($readb[$count - 1] == '' || $readb[$count - 1] == "\r") {
			unset($readb[$count - 1]);
		}
		if (empty($readb)) {
			$readb[0] = "";
		}
	}
	return $readb;
}

/**
 +----------------------------------------------------------
 * 邮件发送函数
 +----------------------------------------------------------
 * $title	邮件标题
 * $to 		发给谁 批量用';'区分开来
 * $content  邮件内容
 +----------------------------------------------------------
 * 	return  true | 邮件发送返回的错误信息
 */
function mailer($title, $to, $content) {
	//包含邮件类库 PHPMailer_5
	include __LIB__ . 'PHPMailer_5.2.1/class.phpmailer.php';
	include __CONFIG__ . 'mail_config.php';
	try {
		$mail = new PHPMailer(true);

		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = MAIL_HOST;
		$mail->Port = MAIL_PORT;
		//登录邮件服务器的账号
		$mail->Username = MAIL_USER;
		$mail->Password = MAIL_PWD;
		//邮件发送者
		$mail->From = defined('MAIL_FROM') ? MAIL_FROM : MAIL_USER;
		$mail->FromName = MAIL_FROM_NAME;

		$to = trim($to, ';');
		$to = explode(';', $to);
		//批量添加邮件发送人
		foreach ($to as $t) {
			$mail->AddAddress($t);
		}
		//邮件主题
		$mail->Subject = $title;
		$mail->WordWrap = 80;
		//邮件内容
		$mail->MsgHTML(preg_replace('/\\\\/', '', $content));
		$mail->IsHTML(true);
		return $mail->Send();
	} catch (phpmailerException $e) {
		return false;
	}
}

/**
 +----------------------------------------------------------
 * 更新服务器列表
 +----------------------------------------------------------
 * $serverType	服务器类型
 * $agent 		代理名
 * $sid  		服务器id
 * $domain		服务器域名
 +----------------------------------------------------------
 *
 */
function update_server_list($sInfo = array()) {
	$agentFile = __CONFIG__ . 'agent_list_config.php';
	$serverFile = __CONFIG__ . 'server_list_config.php';

	$agent_list = file_exists($agentFile) ? require($agentFile) : array();
	//不存在则查询代理名列表更新代理名
	$mysqli = new DbMysqli();
	if (!in_array($sInfo['agent'], $agent_list)) {
		if ($sInfo['agent'] != 'feiyin') {
			$addNewAgentSql = 'insert into `rbac_agent`(`name`) values("' . $sInfo['agent'] . '")';
			$mysqli->query($addNewAgentSql);
		}
		$selectAgentSql = 'select `id`,`name` from `rbac_agent`';
		$selectAgentQuery = $mysqli->query($selectAgentSql);
		$agent_list = array();
		while ($agentQuery = $selectAgentQuery->fetch_assoc()) {
			$agent_list[$agentQuery['id']] = $agentQuery['name'];
		}
		$agent_list = array_merge(array('0' => 'feiyin'), $agent_list);
		//缓存代理列表
		file_put_contents($agentFile, "<?php\n return " . var_export($agent_list, true) . ";\n?>");
	}
	$server_list = file_exists($serverFile) ? require($serverFile) : array();
	$agent_id = array_search($sInfo['agent'], $agent_list);
	//如果不存在此服则添加
	if (!isset($server_list[$sInfo['agent']][$sInfo['sid']])) {
		switch ($sInfo['serverType']) {
			case 'center':
				$type = 0;
				break;
			case 'agent':
				$type = 1;
				break;
			case 'single':
				$type = 2;
				break;
			case 'kuafu':
				$type = 3;
				break;
			default:
				exit('错误');
				break;
		}
		$addNewServerSql = "insert into `server_list`(`type`,`agent_id`,`sid`,`domain`) values (" . $type . "," . $agent_id . ",'" . $sInfo['sid'] . "','" . $sInfo['domain'] . "')";
		$mysqli->query($addNewServerSql);
		//存在代理和服务器但是域名不一致则更新域名
	} elseif ($server_list[$sInfo['agent']][$sInfo['sid']] != $sInfo['domain']) {
		$udpateServerSql = "udpate `server_list` set `domain`='" . $sInfo['domain'] . "' where `agent_id`=" . $agent_id . " and `sid`='" . $sInfo['sid'] . "'";
		$mysqli->query($udpateServerSql);
	}
	//缓存服务器列表
	$serverSql = "select `agent_id` as `agent`,`sid`,`domain` from `server_list` where `status`=1 order by `agent`,`type` asc ,`open_time` asc";
	$serverQuery = $mysqli->query($serverSql);
	$servers = array();
	while ($server = $serverQuery->fetch_assoc()) {
		$servers[$agent_list[$server['agent']]][$server['sid']] = $server['domain'];
	}
	//更新缓存
	file_put_contents($serverFile, "<?php\n return " . var_export($servers, true) . ";\n?>");
}

/**
 +----------------------------------------------------------
 * ajax 数据返回
 +----------------------------------------------------------
 * $status		状态
 * $info 		提示信息
 * $data		返回的附带数据
 +----------------------------------------------------------
 *
 */
function ajax_return($status, $info, $data = '') {
	exit(json_encode(array('status' => $status, 'info' => $info, 'data' => $data)));
}

/**
 +----------------------------------------------------------
 * 生成UUID 单机使用
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function uuid() {
	$charid = md5(uniqid(mt_rand(), true));
	$hyphen = chr(45); // "-"
	$uuid = substr($charid, 0, 8) . $hyphen
	. substr($charid, 8, 4) . $hyphen
	. substr($charid, 12, 4) . $hyphen
	. substr($charid, 16, 4) . $hyphen
	. substr($charid, 20, 12);
	return $uuid;
}

function my_escape_string($string) {
	return addslashes($string); //version_compare(PHP_VERSION,'5.4.0','<')?mysql_escape_string($string):mysql_real_escape_string($string);
}

//全局设置cachefile
function S($name, $value = '', $expire = 0, $options = null) {
	static $_cache = array();
	static $cache = null;
	if (is_null($cache)) {
		//取得缓存对象实例
		include __CLASSES__ . 'CacheFile.class.php';
		$cache = new CacheFile($options);
	}
	if ('' !== $value) {
		if (is_null($value)) {
			// Xóa缓存
			$result = $cache->rm($name);
			if ($result){
				unset($_cache['file_' . $name]);
			}
			return $result;
		}else {
			// 缓存数据
			$cache->set($name, $value, $expire);
			$_cache['file_' . $name] = $value;
		}
		return;
	}
	if (isset($_cache['file_' . $name])){
		return $_cache['file_' . $name];
	}
	// 获取缓存数据
	$value = $cache->get($name);
	$_cache['file_' . $name] = $value;
	return $value;
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists("mb_substr"))
	$slice = mb_substr($str, $start, $length, $charset);
	elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($str, $start, $length, $charset);
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("", array_slice($match[0], $start, $length));
	}
	return $suffix ? $slice . '...' : $slice;
}

/**
 +----------------------------------------------------------
 * 计算给定的秒数距离当前时间多久
 +----------------------------------------------------------
 * @param int $timestamp 秒数
 * @param int $granularity 显示层级，默认为 年 周 天 小时 分钟 秒
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function format_interval($timestamp, $granularity = 5) {
	$units = array(
		31536000 => __('年'),
		//604800 => __('周'),
		86400 => __('天'),
		3600 => __('小时'),
		60 => __('分钟'),
		1 => __('秒'),
	);
	$output = '';
	foreach ($units as $key => $value) {
		if ($timestamp >= $key) {
			$output.=floor($timestamp / $key) . $value;
			$timestamp%=$key;
			$granularity--;
		}
		if ($granularity == 0) {
			break;
		}
	}
	return $output ? $output : $timestamp;
}

//判断玩家是否在线
function is_online($char_id){
	$mysqli=new DbMysqli();
	$login_time=time()-86400*2;//过滤异常在线
	$sql="select logout_time from log_login where char_id=$char_id and login_time>=$login_time order by id desc limit 1";
	$list=$mysqli->findOne($sql);
	return empty($list) || !empty($list['logout_time']) ? false : true;
}

//获取开服时间
function get_open_time(){
	return SERVER_OPEN_TIME;
}

//获取服务端部署版本
function get_server_version(){
	return 'Version3';
}

/*
 * 多线程日志清除
 * $sql_array：sql数组
 * $max_count:进程最大数
 */
function more_thread_logdel($sql_array,$del_time,$max_count=100){
	include_once __CLASSES__.'LogDelThread.class.php';
	$thread_array=$result_array=array();//多线程数组和远程处理结果
	start_thread:
	foreach ($sql_array as $key=>$conf){
		if(count($thread_array)<$max_count){
			$thread_array[$key]=new LogDelThread($key,$conf,$del_time);
			$thread_array[$key]->start();
			unset($sql_array[$key]);//已装载的注销
		}else {
			//装载到最大进程数后，退出循环
			break;
		}
	}

	foreach ($thread_array as $key=>$value){
		if($thread_array[$key]->join()){
			$result=$thread_array[$key]->result;
			//print_r($result);
			$result_array[$key]=$result;

			unset($thread_array[$key]);//注销进程类
		}
	}
	if(!empty($sql_array)&&count($thread_array)<=$max_count){
		goto start_thread;//返回，重新装载线程
	}
	unset($sql_array,$data);
	return $result_array;
}

?>