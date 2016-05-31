<?php

//充值记录
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__ . 'Page.class.php';

$type = (empty($_GET['type'])) ? 'action' : $_GET['type'];
$keyword = (empty($_GET['keyword'])) ? '' : $_GET['keyword'];
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$date_start=(empty($_GET['date_start'])) ? date('Ymd',strtotime(date('Ymd'))-9*3600*24) : $_GET['date_start'];
$date_end=(empty($_GET['date_end'])) ? date('Ymd') : $_GET['date_end'];

$logfiledata=array();
for($date=$date_start;$date<=$date_end;){
	$logfiledata_temp = read_log($type.'_'.$date);
	$logfiledata=array_merge($logfiledata,$logfiledata_temp);
	$date=date('Ymd',(strtotime($date)+3600*24));
}
$logfiledata = array_reverse($logfiledata);

if ($action == 'output') {
	$output = $type == 'mysql' ? "库名\tSQL\t时间\n" : "账号\t路径\t参数\tIP\t时间\t备注\n";
	foreach ($logfiledata as $value) {
		if (!empty($keyword) && strpos($value, $keyword) !== false) {
			$detail = explode("|", $value);
			$detail[2]=urldecode($detail[2]);
			$detail[3]=urldecode($detail[3]);
			unset($detail[0]);
			$output .= implode("\t", $detail);
		} else {
			$detail = explode("|", $value);
			$detail[2]=urldecode($detail[2]);
			$detail[3]=urldecode($detail[3]);
			unset($detail[0]);
			$output .= implode("\t", $detail);
		}
	}
	header("Pragma:public");
	header("Expires:0");
	header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
	header("Content-Type:application/force-download");
	header("Content-Type:application/vnd.ms-execl;charset=gb2312");
	header("Content-Type:application/octet-stream");
	header("Content-Type:application/download");
	header('Content-Disposition:attachment;filename="' . $type . '.xls"');
	header("Content-Transfer-Encoding:binary");
	echo $output;
	exit;
}
//导出结束

$count = count($logfiledata);
$yl_perpage = 50; //定义每页显示数目
$adlogfor = '';
$data=array();
if (!empty($keyword)) {
	$num = 0;
	$p = new Page(100, $yl_perpage);
	$start = $p->firstRow;
	$id = 0;
	foreach ($logfiledata as $value) {
		if (strpos($value, $keyword) !== false) {
			if ($num >= $start && $num < $start + $yl_perpage) {
				$detail = explode("|", $value);
				$detail[2]=urldecode($detail[2]);
				$detail[3]=urldecode($detail[3]);
				$data[]=$detail;
				$adlogfor.="<tr>";
				foreach ($detail as $key => $val) {
					if ($key != 0) {
						$adlogfor .= '<td style="width:100px; word-wrap:break-word; word-break:break-all;">';
						/*
						if ($type == 'action' && $key == 3) {
							$adlogfor .='<a class="accordion-toggle btn" data-toggle="collapse" href="#id' . $id . '"><i class="icon-search"></i></a><div id="id' . $id . '" class="accordion-body collapse">' . $val . '</div>';
							$id++;
						} else {
						*/
							$adlogfor .=$val;
						//}
						$adlogfor .='</td>';
					}
				}
				count($detail)==6&&$adlogfor .= '<td style="width:100px;"></td>';
				$adlogfor .= "</tr>";
			}
			++$num;
		}
	}
	$p = new Page($num, $yl_perpage);
} else {
	$p = new Page($count, $yl_perpage);
	$pagemin = $p->firstRow;
	$pagemax = min($pagemin + $yl_perpage, $count);
	$id = 0;
	for ($i = $pagemin; $i < $pagemax; $i++) {
		$detail = explode("|", $logfiledata[$i]);
		$detail[2]=urldecode($detail[2]);
		$detail[3]=urldecode($detail[3]);
		$data[]=$detail;
		$adlogfor.="<tr>";
		foreach ($detail as $key => $val) {
			if ($key != 0) {
				$adlogfor .= '<td style="width:100px; word-wrap:break-word; word-break:break-all;">';
				/*
				if ($type == 'action' && $key == 3) {
					$adlogfor .='<a class="accordion-toggle btn" data-toggle="collapse" href="#id' . $id . '"><i class="icon-search"></i></a><div id="id' . $id . '" class="accordion-body collapse">' . $val . '</div>';
					$id++;
				} else {
				*/
					$adlogfor .=$val;
				//}
				$adlogfor .='</td>';
			}
		}
		count($detail)==6&&$adlogfor .= '<td style="width:100px;"></td>';
		$adlogfor .= "</tr>";
	}
}
$smarty->assign('date_start', $date_start);
$smarty->assign('date_end', $date_end);
$smarty->assign('data', $data);
$smarty->assign('type', $type);
$smarty->assign('keyword', $keyword);
$smarty->assign('adlogfor', $adlogfor);
$smarty->assign('page', $p->show());
$smarty->display('rbac_log.html');
?>
