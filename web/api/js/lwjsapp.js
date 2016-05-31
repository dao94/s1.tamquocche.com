window.onerror = function(){
	return true;	
};
window.onbeforeunload=function(){
	if(typeof(task_list)!='undefined'&&task_list){
		return task_list;
	}
};
var lwjs = (function() {
	var _client_api = './api/client_api.php';
	return {
		open: function(type) {
			var url = lwjs_c.url[type];
			if (url === undefined || url === '') {
				return false;
			} else {
				window.open(url);
				return true;
			}
		},
		fcm: function(account, idcard, truename) {
			var result = 2;
			_ajax(_client_api,function(flag) {
				result = flag;
			},'POST', {
				account: account,
				card: idcard,
				truename: truename,
				action: 'fcm'
			},'text', false);
			return result;
		},
		gm: function(account, char_id, char_name, title, content, type) {
			_ajax(_client_api,function() {},'POST', {
				account: account,
				char_id: parseInt(char_id,16),
				char_name: char_name,
				title: title,
				content: content,
				type: type,
				action: 'gm'
			},'text');
		},
		advice: function(account, char_id, char_name, title, content, type) {
			_ajax(_client_api,function() {},'POST', {
				account: account,
				char_id: parseInt(char_id,16),
				char_name: char_name,
				title: title,
				content: content,
				type: type,
				action: 'advice'
			},'text');
		},
		loader: function(gevent) {
			_ajax(_client_api,
			function() {},'POST', {
				gevent: gevent,
				action: 'loader'
			},'text');
		},
		online: function() {
			_ajax(_client_api,function() {},'POST', {
				action: 'online'
			},'text');
			setTimeout('lwjs.online()', 600000);
		},
		flash: function() {
			var fpVersion = deconcept.SWFObjectUtil.getPlayerVersion();
			var version = fpVersion['major'] + '.' + fpVersion['minor'];
			_ajax(_client_api,function() {},'POST', {
				version: version,
				action: 'flash'
			},'text');
		},
		log_client:function(char_name,content){
			_ajax(_client_api,function() {},'POST', {
				action: 'log_client',
				char_name: char_name,
				content: content
			},'text');
		},
		reload: function(text) {
			if(typeof(text)!='undefined' && text!='') {
				if(text=='reload'){
					task_list='';
				}else{
					alert(text);
				}
			}
			return window.location.href = window.location.href;
		},
		card: function(action, char_name, verify, card_type, card_num) {
			var result = null;
			_ajax(_client_api,function(msg) {
				result = msg;
			},'POST', {
				char_name: char_name,
				account: lwjs_c.account,
				server_id: lwjs_c.server_id,
				verify: verify,
				card_type: card_type,
				card_num: card_num,
				action: action
			},'text',false);
			return result;
		},
		addFavorite: function(shortcut) {
			if(shortcut===true){
				task_list=null;
				return window.location=_client_api+'?action=favorite';
			}else{
				var tips = '您可以通过 Ctrl+D 组合键添加【乱舞江山】到您的收藏夹中。',
				title = document.title,
				url = lwjs_c.url['guan'];
				if (isFirefox = navigator.userAgent.indexOf('Firefox') > 0 || navigator.userAgent.indexOf('Opera') > 0 || navigator.userAgent.indexOf('Chrome') > 0) {
					alert(tips);
					return true;
				}
				try {
					window.external.addFavorite(url, title);
				} catch(e) {
					try {
						window.sidebar.addPanel(title, url, '');
					} catch(e) {
						alert(tips);
					}
				}
			}
			return true;
		},
		setTaskList:function(task_list_str){
			return task_list=task_list_str;
		},
		chat:function(char_name,type,content){
			_ajax('./api/chat.php',function(msg) {
				result = msg;
			},'POST', {
				char_name: char_name,
				account: lwjs_c.account,
				server_id: lwjs_c.server_id,
				type:type,
				content: content
			},'text');
			return false;
		},
		click_box:function(char_id,type,level){
			_ajax(_client_api,function(msg) {
				result = msg;
			},'POST', {
				action: 'click_box',
				char_id: parseInt(char_id,16),
				type:type,
				level: level
			},'text');
			return false;
		},
		push_player:function(char_id,char_name,level){
			var is_push=false;
			switch(lwjs_c.agent){
				case 'baidu':
					if(level==1||level%5==1){
						is_push=true;
					}
					break;
				case '360':
					if(level==1||level==2||(level<30&&level%5==1)||level>30){
						is_push=true;	
					}
					break;
				case 'sougou':
				case 'cmw':
					if(level==1){
						is_push=true;
						if (typeof(eval('createRoleOK')) == "function") {
							createRoleOK();
						}
					}
					break;
			}
			if(is_push===true){
				_ajax('./api/push.php',function(msg) {
					result = msg;
				},'POST', {
					action: 'post_player',
					account:lwjs_c.account,
					server_id: lwjs_c.server_id,
					char_id: parseInt(char_id,16),
					char_name:char_name,
					level: level
				},'text');
			}
			return false;
		},
		get_explorer:function(){
			var explorer=window.navigator.userAgent,browser='';
			if (explorer.indexOf('MSIE')>=0){
				browser='MSIE';
			}
			if(explorer.indexOf('Firefox')>=0){
				browser='Firefox';
			}
			if(explorer.indexOf('Chrome')>=0){
				browser='Chrome';
			}
			if(explorer.indexOf('Opera')>=0){
				browser='Opera';
			}
			if(explorer.indexOf('Safari')>=0){
				browser='Safari';
			}
			return browser;
		},
		
		disable_error: function() {
			return true;
		}
	};
	// url请求的地址 fn回调方法 method (get/post) param 参数 dataType返回数据格式 async
	// (true/false)是否异步调用
	function _ajax(url, fn, method, param, dataType, async) {
		method = method || 'GET';
		dataType = dataType || 'json';
		async = async === false ? false: true;
		var xhr = window.XMLHttpRequest ? new window.XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
		xhr.open(method, url, async);
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status == 200) {
				if (fn) {
					var data = _trim(xhr.responseText);
					if (dataType == 'json') {
						data = _json(data);
					}
					fn(data);
				}
			}
		};
		if (method == 'POST') {
			var params = [];
			_each(param,
			function(key, val) {
				params.push(encodeURIComponent(key) + '=' + encodeURIComponent(val));
			});
			try {
				xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=utf-8');
			} catch(e) {}
			xhr.send(params.join('&'));
		} else {
			xhr.send(null);
		}
	}
	function _json(text) {
		var match;
		if ((match = /\{[\s\S]*\}|\[[\s\S]*\]/.exec(text))) {
			text = match[0];
		}
		var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
		cx.lastIndex = 0;
		if (cx.test(text)) {
			text = text.replace(cx,
			function(a) {
				return '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice( - 4);
			});
		}
		if (/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
			return eval('(' + text + ')');
		}
		throw 'JSON parse error';
	}
	function _trim(str) {
		return str.replace(/(?:^[ \t\n\r]+)|(?:[ \t\n\r]+$)/g, '');
	}
	function _each(obj, fn) {
		if (_isArray(obj)) {
			for (var i = 0,len = obj.length; i < len; i++) {
				if (fn.call(obj[i], i, obj[i]) === false) {
					break;
				}
			}
		} else {
			for (var key in obj) {
				if (obj.hasOwnProperty(key)) {
					if (fn.call(obj[key], key, obj[key]) === false) {
						break;
					}
				}
			}
		}
	}
	function _isArray(val) {
		if (!val) return false;
		return Object.prototype.toString.call(val) === '[object Array]';
	}
})();
lwjs.loader('loader_page');
lwjs.flash();
lwjs.online();