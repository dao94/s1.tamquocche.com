<?php

//玩家建议
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__ . 'game_config.php';
include __ROOT__.'/config/agent_list_config.php';

$action=empty($_REQUEST['action']) ? '' : trim($_REQUEST['action']);
$start_date=empty($_REQUEST['start_date']) ? '' : my_escape_string(trim($_REQUEST['start_date']));
$end_date=empty($_REQUEST['end_date']) ? '' : my_escape_string(trim($_REQUEST['end_date']));
$start_time=strtotime($start_date);
$end_time=$end_date ? strtotime($end_date)+86400 : '';
switch ($action){
	default:
		$char_id=empty($_REQUEST['char_id']) ? 0 : floatval($_REQUEST['char_id']);
		$char_name=empty($_REQUEST['name']) ? '' : my_escape_string(trim($_REQUEST['name']));
		$account=empty($_REQUEST['account']) ? '' : my_escape_string(trim($_REQUEST['account']));
		$replyer=empty($_REQUEST['replyer']) ? '' : my_escape_string(trim($_REQUEST['replyer']));
		$keyword=empty($_REQUEST['keyword']) ? '' : my_escape_string(trim($_REQUEST['keyword']));
		$status=empty($_REQUEST['status']) ? 0 : intval($_REQUEST['status']);
		$type=empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
		$mysqli=new DbMysqli();
		$where=' where true';
		$where.=$start_time ? " and time>=$start_time" : '';
		$where.=$end_time ? " and time<$end_time" : '';
		$where.=$type ? " and type=$type" : '';
		$where.=$char_id ? " and char_id=$char_id" : '';
		$where.=$char_name ? " and char_name='$char_name'" : '';
		$where.=$account ? " and account='$account'" : '';
		$where.=$replyer ? " and replyer='$replyer'" : '';
		$where.=$status ? " and status='$status'" : '';
		$where.=$keyword ? " and content like '%$keyword%'" : '';
		
		$sql="select count(*) as count from gm_advice $where";
		//echo $sql;
		$list=$mysqli->find($sql);
		//print_r($list);exit;
		$total_rows=intval($list[0]['count']);

		$p=new Page($total_rows);
		$first_row=$p->firstRow;
		$list_rows=$p->listRows;
		$sql="select * from gm_advice $where order by time desc limit $first_row,$list_rows";
		$list=$mysqli->find($sql);
		$data=array();
		foreach ($list as &$row){
			$row['type']=$advice_type_conf[$row['type']];
			//$row['status_name']=$question_status_conf[$row['status']];
			$row['reply_content'] = preg_replace('!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"', $row['reply_content']);
			$row['reply_content']=unserialize($row['reply_content']);
			$row['reply_count']=count($row['reply_content']);
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$data[]=$row;
		}

		$conditions=array(
			'char_id'=>$char_id,
			'char_name'=>$char_name,
			'account'=>$account,
			'replyer'=>$replyer,
			'keyword'=>$keyword,
			'start_date'=>$start_date,
			'end_date'=>$end_date,
			'min_pay'=>'',
			'max_pay'=>'',
			'status'=>$status,
			'type'=>$type,
			'open_date'=>date('Y-m-d',SERVER_OPEN_TIME),
		);
		$smarty->assign('page',$p->show());
		$smarty->assign('conditions',$conditions);
		$smarty->assign('action',$action);
		$smarty->assign('data',$data);
		//$smarty->assign('status_conf',$question_status_conf);
		$smarty->assign('type_conf',$advice_type_conf);
		//$smarty->assign('check_list',$check_list);
		//$smarty->assign('check',$check);
		$smarty->display();
		break;

	case 'reply':
		include __CLASSES__ . '/Gm.class.php';
		$id=empty($_POST['id']) ? '' : intval($_POST['id']);
		$content=empty($_POST['content']) ? '' : my_escape_string(trim($_POST['content']));
		$status=empty($_POST['status']) ? '' : intval($_POST['status']);
		if(empty($id) || empty($content)){
			ajax_return(0, __('回复内容不能为空'));
		}
		$mysqli=new DbMysqli();
		$sql="select char_id,reply_content from gm_advice where id=$id";
		$list=$mysqli->findOne($sql);
		if($list){
			$email_id=uuid();
			$time=time();
			$reply_content=unserialize($list['reply_content']);
			$arr=array('content'=>$content,'time'=>date('Y-m-d H:i:s',$time));
			$reply_content[]=$arr;
			$reply_content=serialize($reply_content);
			$replyer=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
			$sql="update gm_advice set reply_content='$reply_content',email_id='$email_id',
				replyer='$replyer',reply_time=$time where id=$id";
			if($mysqli->query($sql)){
				$gm=new Gm();
				$rpc='borpc/boemail.rpc';
				$rpc_obj='borpc\Sour_B2oEmail';
				$async='b2ocreateEmail_async';
				$msg_data=array(
					'title'=>__('GM回复的一封信'),
					'content'=>$content,
					'list'=>array(array('charId'=>floatval($list['char_id']),'emailId'=>$email_id)),
				);
				$gm->async($rpc, $rpc_obj, $async, $msg_data);
				$data=array(
					'id'=>$id,
					'content'=>msubstr($content,0,10),
					'time'=>date('Y-m-d H:i:s',$time),
					//'status'=>$question_status_conf[$status],
					'replyer'=>$replyer
				);
				ajax_return(1, __('回复成功'),$data);
			}else{
				ajax_return(0, __('数据记录更新失败'));
			}
		}else{
			ajax_return(0, __('数据记录已不存在'));
		}
		break;

	case 'output':
		$char_id=empty($_REQUEST['char_id']) ? 0 : floatval($_REQUEST['char_id']);
		$char_name=empty($_REQUEST['name']) ? '' : my_escape_string(trim($_REQUEST['name']));
		$account=empty($_REQUEST['account']) ? '' : my_escape_string(trim($_REQUEST['account']));
		$replyer=empty($_REQUEST['replyer']) ? '' : my_escape_string(trim($_REQUEST['replyer']));
		$keyword=empty($_REQUEST['keyword']) ? '' : my_escape_string(trim($_REQUEST['keyword']));
		$status=empty($_REQUEST['status']) ? 0 : intval($_REQUEST['status']);
		$type=empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
		$mysqli=new DbMysqli();
		$where=' where true';
		$where.=$start_time ? " and time>=$start_time" : '';
		$where.=$end_time ? " and time<$end_time" : '';
		$where.=$type ? " and type=$type" : '';
		$where.=$char_id ? " and char_id=$char_id" : '';
		$where.=$char_name ? " and char_name='$char_name'" : '';
		$where.=$account ? " and account='$account'" : '';
		$where.=$replyer ? " and replyer='$replyer'" : '';
		$where.=$status ? " and status='$status'" : '';
		$where.=$keyword ? " and content like '%$keyword%'" : '';
		
		$sql="select count(*) as count from gm_advice $where";
		//echo $sql;
		$list=$mysqli->find($sql);
		//print_r($list);exit;
		$total_rows=intval($list[0]['count']);

		$p=new Page($total_rows);
		$first_row=$p->firstRow;
		$list_rows=$p->listRows;
		$sql="select `type`,`char_id`,`account`,`char_name`,`title`,`content`,`time` from gm_advice $where order by time desc ";
		$advice_log_query = $mysqli->query($sql);
		$output="类型\t玩家ID\t账号\t角色名\t标题\t内容\t提交时间\n";
		while ($row = $advice_log_query->fetch_assoc()) {
			$row['char_id']=$row['char_id'];
			$row['type']=$advice_type_conf[$row['type']];
			$row['time']=date('Y-m-d H:i:s',$row['time']);
			$output .= implode("\t",$row);
			$output .="\n";
		}
		header("Pragma:public");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl;charset=gb2312");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename=player_advice.xls');
		header("Content-Transfer-Encoding:binary");

		echo $output;

		break;
}