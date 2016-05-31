<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';

$action=empty($_GET['action']) ? 1 : trim($_GET['action']);
$action_conf=array(
	1=>__('更新公告'),
);
$xml_file=__XML__.'notice.xml';
switch ($action) {
	case 'save':
		file_exists($xml_file) ? '' : ajax_return(0, $notice_xml.__('文件不存在'));
		is_writable($xml_file) ? '' : ajax_return(0, $notice_xml.__('文件不可写'));
		$xml_content=file_get_contents($xml_file);
		$notice_id=isset($_POST['notice_id']) ? intval($_POST['notice_id']) : '';
		$content=isset($_POST['content']) ? $_POST['content'] : array();
		$xml=new DOMDocument();
		$xml->load($xml_file);
		$notices=$xml->getElementsByTagName('notice');
		foreach ($notices as $notice){
			$id=$notice->getAttribute('id');
			if($id==$notice_id){
				$sub_notices=$notice->getElementsByTagName('subNotice');
				foreach ($sub_notices as $sub_notice){
					$sub_id=$sub_notice->getAttribute('id');
					$contents=$sub_notice->getElementsByTagName('content');
					$new_data=$xml->createCDATASection($content[$sub_id]);
					$contents->item(0)->nodeValue='';
					$contents->item(0)->appendChild($new_data);
				}
			}
		}
		if($xml->save($xml_file)){
			ajax_return(1, __('Sửa成功'));
		}else{
			ajax_return(0, __('Sửa失败'));
		}
		exit;
		break;
}
$data=array();
if(file_exists($xml_file)){
	$xml=new DOMDocument();
	$xml->load($xml_file);
	$notices=$xml->getElementsByTagName('notice');
	foreach ($notices as $notice){
		$id=$notice->getAttribute('id');
		if($id==$action){
			$sub_notices=$notice->getElementsByTagName('subNotice');
			foreach ($sub_notices as $sub_notice){
				$sub_id=$sub_notice->getAttribute('id');
				$titles=$sub_notice->getElementsByTagName('title');
				$title=$titles->item(0)->nodeValue;
				$contents=$sub_notice->getElementsByTagName('content');
				$content=$contents->item(0)->nodeValue;
				$data[$sub_id]=array('title'=>$title,'content'=>$content);
			}
		}
	}
}
$conditions=array('action'=>$action);
$smarty->assign('action_conf', $action_conf);
$smarty->assign('conditions', $conditions);
$smarty->assign('data', $data);
$smarty->display();
?>
