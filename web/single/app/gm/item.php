<?php
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__.'smarty_config.php';
include __AUTH__.'auth.php';
include __CLASSES__.'Page.class.php';
include __LIB__.'phprpc_php/phprpc_client.php';

$name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));
$items=array();
$count=0;
//获取json数据
function getItemsData($name){
	$array = array();
	$phprpc_client = new PHPRPC_Client();
	$phprpc_client->useService('http://' . CENTER_DOMAIN . '/center/app/interface/item_info.php');
	$phprpc_client->setCharset('UTF-8');
	$phprpc_client->setEncryptMode(1);
	$phprpc_client->setEnableGZIP(true);
	$phprpc_client->setTimeout(30);
	$result=$phprpc_client->search($name,$_SESSION['__'.SERVER_TYPE.'_LANG']);
	$array=(json_decode($result,true));
	return $array;
}
$cache_id='gm/item.php';//缓存id
if(!S($cache_id)){
	$items=getItemsData($name);
	S($cache_id,$items,3600);
}else{
	//为空时执行缓存 不为空时 getItemsData
	$items=$name=='' ? S($cache_id) : getItemsData($name);
}

$type=empty($_GET['type']) ? '' : trim($_GET['type']);
$data=$arr=array();
$page='';


if($type=='export'){
	//导出数据
	include __LIB__.'PHPExcel/PHPExcel.php';
	$objExcel = new PHPExcel();
	$objWriter = new PHPExcel_Writer_Excel5($objExcel);
	$objExcel->setActiveSheetIndex(0);
	$objActSheet = $objExcel->getActiveSheet();
	$objActSheet->setTitle('道具列表');
	$objActSheet->setCellValue('A1', 'ID');
	$objActSheet->setCellValue('B1', '道具名称');

	foreach($items as $key=>$item){
		if(($name && strpos($item[1], $name)!== false) || !$name){
			$count++;
			$cell1 = 'A' . ($count+1);
			$cell2 = 'B' . ($count+1);
			$objActSheet->setCellValue($cell1, $item[0]);
			$objActSheet->setCellValue($cell2, $item[1]);
		}
	}

		$objActSheet->getColumnDimension('A')->setWidth(20);
		$objActSheet->getColumnDimension('B')->setWidth(50);
		$objActSheet->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment :: HORIZONTAL_CENTER);

		$outputFileName = "export_item_" . date('Ymd') . ".xls";
		header("Content-Type: application/vnd.ms-excel");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Content-Disposition:inline;filename="' . $outputFileName . '"');
		header("Content-Transfer-Encoding: binary");
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: no-cache");
		$objWriter->save('php://output');
		exit ();

}else{
	$p=new Page(count($items));
	$count=count($items);
    $list = array();
	$list=array_splice($items,$p->firstRow,$p->listRows);
	foreach ($list as $key=>$row){
		$row['num']=($p->nowPage-1)*20+$key+1;//序号
		$data[]=$row;
	}
	$page=$p->show();

}

$smarty->assign('count',$count);
$smarty->assign('name',$name);
$smarty->assign('data',$data);
$smarty->assign('page',$page);
$smarty->display();