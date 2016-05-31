var lwjs = (function() {
	var _client_api = './single/api/client_api.php';
	return {
		// 客户端调用打开相关链接
		open : function(type) {
			var url = lwjs_c.url[type];
			// 告诉客户端打开面板
			if (url === undefined || url === '') {
				return false
			} else {
				window.open(url)
				return true
			}
		},
		// 防沉迷接口
		fcm : function(account, idcard, truename) {
			var result = 2;
			_ajax(_client_api, function(flag) {
				result = flag
			}, 'POST', {
				account : account,
				card : idcard,
				truename : truename,
				action : 'fcm'
			}, 'text', false);
			return result;
		},
		// 客户端提交玩家问题
		gm : function(account, char_id, char_name, title, content, type) {
			_ajax(_client_api, function() {
			}, 'POST', {
				account : account,
				char_id : char_id,
				char_name : char_name,
				title : title,
				content : content,
				type : type,
				action : 'gm'
			}, 'text');
		},
		// 创号各种加载流程
		loader : function(gevent) {
			_ajax(_client_api, function() {
			}, 'POST', {
				gevent : gevent,
				action : 'loader'
			}, 'text');
		},
		// 游戏保持在线
		online : function() {
			_ajax(_client_api, function() {
			}, 'POST', {
				action : 'online'
			}, 'text');
			setTimeout('lwjs.online()', 600000);
		},
		// flash版本
		flash : function() {
			var fpVersion = deconcept.SWFObjectUtil.getPlayerVersion();
			var version = fpVersion['major'] + '.' + fpVersion['minor'];
			_ajax(_client_api, function() {
			}, 'POST', {
				version : version,
				action : 'flash'
			}, 'text');
		},
		reload : function() {
			return window.location.href = window.location.href;
		},
		// 屏蔽所有客户端的js错误
		disable_error : function() {
			return true;
		}
	};
	// 私有函数自定义ajax方法避免加载jquery等库
	// url请求的地址 fn回调方法 method (get/post) param 参数 dataType返回数据格式 async
	// (true/false)是否异步调用
	function _ajax(url, fn, method, param, dataType, async) {
		method = method || 'GET';
		dataType = dataType || 'json';
		async = async === false ? false : true;
		var xhr = window.XMLHttpRequest ? new window.XMLHttpRequest()
				: new ActiveXObject('Microsoft.XMLHTTP');
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
			_each(param, function(key, val) {
				params.push(encodeURIComponent(key) + '='
						+ encodeURIComponent(val));
			});
			try {
				xhr.setRequestHeader('Content-Type',
						'application/x-www-form-urlencoded');
			} catch (e) {
			}
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
			text = text.replace(cx, function(a) {
				return '\\u'
						+ ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
			});
		}
		if (/^[\],:{}\s]*$/
				.test(text
						.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@')
						.replace(
								/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,
								']').replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
			return eval('(' + text + ')');
		}
		throw 'JSON parse error';
	}
	function _trim(str) {
		return str.replace(/(?:^[ \t\n\r]+)|(?:[ \t\n\r]+$)/g, '');
	}
	function _each(obj, fn) {
		if (_isArray(obj)) {
			for ( var i = 0, len = obj.length; i < len; i++) {
				if (fn.call(obj[i], i, obj[i]) === false) {
					break;
				}
			}
		} else {
			for ( var key in obj) {
				if (obj.hasOwnProperty(key)) {
					if (fn.call(obj[key], key, obj[key]) === false) {
						break;
					}
				}
			}
		}
	}
	function _isArray(val) {
		if (!val)
			return false;
		return Object.prototype.toString.call(val) === '[object Array]';
	}
})();
window.onerror = lwjs.disable_error();
lwjs.loader('loader_page');
lwjs.online();
lwjs.flash();