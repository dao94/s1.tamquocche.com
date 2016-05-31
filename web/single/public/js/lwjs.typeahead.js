$(function($){
	var name_search=false;
	$("input[name='name']").typeahead({
		items : 100,
		source: function(value, process) {
			if(name_search==false) return;
			name_search=false;
			var client = new PHPRPC_Client('../interface/player.php', ['getNameList']);
			client.getNameList(value, function (result, args, output, warning) {
				var data=new Array();
				result=jQuery.parseJSON(result);
				$(result).each(function(i,obj){
					data.push(obj.name);
				});
				process(data);
			}); 
		},
	});
	
	$("input[name='name']").next().click(function(){
		name_search=true;
		$(this).prevAll(':input').keyup();
	});
	
	var account_search=false,separator=' <i class="icon-user"></i> ';
	$("input[name='account']").typeahead({
		items : 100,
		source: function(value, process) {
			if(account_search==false) return;
			account_search=false;
			var client = new PHPRPC_Client('../interface/player.php', ['getAccountList']);
			client.getAccountList(value, function (result, args, output, warning) {
				var data=new Array();
				result=jQuery.parseJSON(result);
				$(result).each(function(i,obj){
					data.push(obj.serverId+separator+obj.account);
				});
				process(data);
			});
		},
		updater:function(item){
			var arr=item.split(separator,2);
			$('input[name="sid"]').val(typeof(arr[0])=='undefined' ? '' : arr[0]);
			return typeof(arr[1])=='undefined' ? '' : arr[1];
		},
		highlighter:function(item){
			var arr=item.split(separator,2);
			var account=typeof(arr[1])=='undefined' ? '' : arr[1];
			var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
      return arr[0]+separator+account.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
        return '<strong>' + match + '</strong>';
      })
		},
	});
	
	$("input[name='account']").next().click(function(){
		account_search=true;
		$(this).prevAll(':input').keyup();
	});
	
	var faction_search=false;
	$("input[name='faction_name']").typeahead({
		items : 100,
		source: function(value, process) {
			if(faction_search==false) return;
			faction_search=false;
			var client = new PHPRPC_Client('../interface/player.php', ['getFactionList']);
			client.getFactionList(value, function (result, args, output, warning) {
				var data=new Array();
				result=jQuery.parseJSON(result);
				$(result).each(function(i,obj){
					data.push(obj.name);
				});
				process(data);
			}); 
		},
	});
	
	$("input[name='faction_name']").next().click(function(){
		faction_search=true;
		$(this).prevAll(':input').keyup();
	});
});