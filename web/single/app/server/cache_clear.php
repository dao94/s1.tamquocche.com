<?php
//缓存清理
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action=empty($_GET['action']) ? '' : trim($_GET['action']);
$type_config=array(
	'cache'=>__('缓存文件'),
	'data'=>__('数据文件'),
	'images'=>__('图片缓存'),
	'templetes_c'=>__('编译文件'),
);
switch ($action){
	case 'clear':
		set_time_limit(180);
		$type=empty($_GET['type']) ? '' : trim($_GET['type']);
		foreach ($type_config as $key=>$item){
			if($type!='all' && $type!=$key) continue;
			$dir=__RUNTIME__.$key.'/';
			if(is_dir($dir)){
				if ($dh = opendir($dir)){
					while (($file = readdir($dh)) !== false){
						if ($file == '.' || $file == '..' || $file=='.svn')	continue;
						$result=$item.'：'.iconv('','UTF-8',$file).' ';
						if(unlink($dir.$file)){
							$result.=__('Xóa成功');
						}else{
							$result='<b style="color:red">'.$result.__('Xóa失败').'</b>';
						}
						$result.='<br/>';
						echo str_pad($result,1024*256*10);
						ob_flush();
						flush();
						usleep(100000);
					}
					closedir($dh);
				}
				$result=$item.'：'.__('清理完毕').'<br/>';
				echo str_pad($result,4096);
			}
		}
		exit;
		break;
}
$smarty->assign('type_config',$type_config);
$smarty->display();