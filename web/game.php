<script type="text/javascript">
var flashContainer = "flashconent";
//相关参数初始化
var mapResURL = "<?php echo $dir; ?>";
var loaderURL = mapResURL + "/LoaderMain.swf?090115";
var loginURL = "LoginModule.swf?090115";
var gameURL = "Main.swf?090115";
var configURL = "assets/data/in_cfg.o?090115";
var logoURL = "assets/ui/logo_jtsg.swf?090115";
var installerURL = "./expressInstall.swf";
//参数设置
var gamevars = {
	version:"v1.12.311719",
	gameURL:gameURL,
	loginURL:loginURL,
	logoURL:logoURL,
	configURL:configURL,
	mapResURL:mapResURL,
	host:"<?php echo $host; ?>",
	eventURL:"<?php echo $_SERVER['SERVER_NAME'];?>",
	port:<?php echo $port; ?>,
	account:"<?php echo $account; ?>",
	sid:<?php echo $sid; ?>,
	time:<?php echo $time; ?>,
	sign:"<?php echo $sign; ?>",
	cm_first:<?php echo $cm_first; ?>,
	cm_flag:<?php echo $cm; ?>,
	chat_flag:"<?php echo $chat_flag; ?>",
	platform:"<?php echo SERVER_AGENT;?>",
	auto_flag:"<?php echo $auto_flag; ?>",
	from_flag:"<?php echo $from_flag;?>",
	debug:1
};
//插件参数
var param = {
	bgColor:"#000000",
	align:"middle",
	allowScriptAccess:"always",
	allowFullScreen:true,
	menu:"false",
	hasPriority:"true",
	wmode:"direct"
};
//初始化swf
function initSwf(){
	enterFullscreen();
	var fpVersion = deconcept.SWFObjectUtil.getPlayerVersion();
	var version = fpVersion['major'] + '.' + fpVersion['minor'];
	var min_version = 11.0;//最低版本要求
	//版本检查
	if(version<min_version){
		var txt = "Phiên bản Adobe Flash Player không phù hợp. <a href='http://www.adobe.com/go/getflash' target='_blank'>Tải bản mới tại đây.</a>";
		document.getElementById('playerdl').innerHTML=txt;
	}
	//嵌入网页
	var so = new SWFObject(loaderURL, "gameSwf", "100%", "100%", version, "#000000");
	so.useExpressInstall(installerURL);
	for(var i in gamevars){
		so.addVariable(i, gamevars[i]);
	}
	for(var i in param){
		so.addParam(i, param[i]);
	}
	so.write(flashContainer);
};
//进入全屏
function enterFullscreen(){
		document.getElementById(flashContainer).style.width = "100%";
		document.getElementById(flashContainer).style.height = "100%";
		document.getElementById(flashContainer).style.left = 0;
		document.getElementById(flashContainer).style.top = 0;
		document.getElementById(flashContainer).style.marginTop = "0px";
		document.getElementById(flashContainer).style.marginLeft = "0px";
};
//退出全屏
function exitFullscreen(){
		document.getElementById(flashContainer).style.width = "1030px";
		document.getElementById(flashContainer).style.height = "605px";
		document.getElementById(flashContainer).style.left = "50%";
		document.getElementById(flashContainer).style.top = "50%";
		document.getElementById(flashContainer).style.marginTop = "-303px";
		document.getElementById(flashContainer).style.marginLeft = "-516px";
};
//设置事件函数
window.onload = initSwf;
</script>