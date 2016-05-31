<?php

//玩家问题
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __CONFIG__ . 'smarty_config.php';
include __AUTH__ . 'auth.php';
include __CLASSES__. 'Page.class.php';
include __CONFIG__ . 'game_config.php';
include __ROOT__.'/config/agent_list_config.php';
include __ROOT__.'/config/lianyun_list_config.php';

$action=empty($_GET['action']) ? '' : trim($_GET['action']);
$start_date=empty($_GET['start_date']) ? '' : my_escape_string(trim($_GET['start_date']));
$end_date=empty($_GET['end_date']) ? '' : my_escape_string(trim($_GET['end_date']));
$start_time=strtotime($start_date);
$end_time=$end_date ? strtotime($end_date)+86400 : '';
switch ($action){
	default:
		$char_id=empty($_GET['char_id']) ? 0 : floatval($_GET['char_id']);
		$char_name=empty($_GET['name']) ? '' : my_escape_string(trim($_GET['name']));
		$account=empty($_GET['account']) ? '' : my_escape_string(trim($_GET['account']));
		$replyer=empty($_GET['replyer']) ? '' : my_escape_string(trim($_GET['replyer']));
		$keyword=empty($_GET['keyword']) ? '' : my_escape_string(trim($_GET['keyword']));
		$status=empty($_GET['status']) ? 0 : intval($_GET['status']);
		$type=empty($_GET['type']) ? 0 : intval($_GET['type']);
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
		
		
		//取得联运方设置，权限设置
		$ragent_list = array_flip($agent_list);
		$agent_id=$ragent_list[SERVER_AGENT];
		$lianyun_power_list=$_SESSION['__' . SERVER_TYPE . '__LIANYUN_POWER'][$agent_id];
		$lianyun_list_temp=empty($LianYun_List)?array():$LianYun_List;
		
		//去掉无效的权限设置
		if(!empty($lianyun_power_list)){
				foreach($lianyun_power_list as $key => $value){
						if(!isset($lianyun_list_temp[$key])){
								unset($lianyun_power_list[$key]);
						}
				}
		}
		
		if(is_array($lianyun_list_temp)&&count($lianyun_list_temp)){
				if(empty($_POST['lid'])){
						if(is_array($lianyun_power_list)&&count($lianyun_power_list)&&count($lianyun_power_list)!=count($lianyun_list_temp)){
								$d_flag=false;
								foreach($lianyun_power_list as $key => $value){
										if($lianyun_list_temp[$key]['query_key']==''){
												$d_flag=true;
												unset($lianyun_list_temp[$key]);
										}
								}
								if($d_flag){
										foreach($lianyun_power_list as $key => $value){
												unset($lianyun_list_temp[$key]);
										}
										foreach($lianyun_list_temp as $lianyun_temp){
												$where.=" and account not like '%".$lianyun_temp['query_key']."'";
										}
								}
								else{
										$where_add=" and ( false  ";
										$lianyun_power=$lianyun_power_list;
										foreach($lianyun_power as $key => $value){
												$where_add.=" or account like '%".$lianyun_list_temp[$key]['query_key']."'";
										}
										$where_add.=" )";
										$where.=$where_add;
								}
						}
						else{
								//do nothing
						}
				}
				else{
						if(is_array($_POST['lid'])&&count($_POST['lid'])&&count($_POST['lid'])!=count($lianyun_list_temp)){
								$d_flag=false;
								foreach($_POST['lid'] as $lid){
										if($lianyun_list_temp[$lid]['query_key']==''){
												$d_flag=true;
												unset($lianyun_list_temp[$lid]);
										}
								}
								if($d_flag){
										foreach($_POST['lid'] as $lid){
												unset($lianyun_list_temp[$lid]);
										}
										foreach($lianyun_list_temp as $lianyun_temp){
												$where.=" and account not like '%".$lianyun_temp['query_key']."'";
										}
								}
								else{
										$where_add=" and ( false  ";
										$lianyun_power=$lianyun_power_list;
										foreach($_POST['lid'] as  $lid){
												$where_add.=" or account like '%".$lianyun_list_temp[$lid]['query_key']."'";
										}
										$where_add.=" )";
										$where.=$where_add;
								}
						}
						else{
								//do nothing
						}
				}
		}
		else{
				//do nothing
		}
		$check_list=array();
		$check=0;
		$lianyun_list_temp=empty($LianYun_List)?array():$LianYun_List;
		if(!empty($lianyun_list_temp)){
				if(is_array($lianyun_power_list)&&count($lianyun_power_list)!=count($lianyun_list_temp)){
						foreach($lianyun_power_list as $lid => $value){
								$check_list[$lid]=0;
						}
				}
				else{
						foreach($lianyun_list_temp as $lid => $value){
								$check_list[$lid]=0;
						}
				}
				if(!empty($_POST['lid'])&&is_array($_POST['lid'])&&count($_POST['lid'])){
						foreach($_POST['lid'] as $lid){
								$check_list[$lid]=1;
								$check=1;
						}
				}
		}
		
		$sql="select count(*) as count from gm_question $where";
		//echo $sql;
		$list=$mysqli->find($sql);
		//print_r($list);exit;
		$total_rows=intval($list[0]['count']);

		$p=new Page($total_rows);
		$first_row=$p->firstRow;
		$list_rows=$p->listRows;
		$sql="select * from gm_question $where order by time desc limit $first_row,$list_rows";
		$list=$mysqli->find($sql);
		$data=array();
		foreach ($list as &$row){
			$row['type']=$question_type_conf[$row['type']];
			$row['status_name']=$question_status_conf[$row['status']];
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
		$smarty->assign('status_conf',$question_status_conf);
		$smarty->assign('type_conf',$question_type_conf);
		$smarty->assign('check_list',$check_list);
		$smarty->assign('check',$check);
		$smarty->display();
		break;

	case 'reply':
		include __CLASSES__ . '/Gm.class.php';
		$id=empty($_POST['id']) ? '' : intval($_POST['id']);
		$content=empty($_POST['content']) ? '' : my_escape_string(trim($_POST['content']));
		$status=empty($_POST['status']) ? '' : intval($_POST['status']);
		if(empty($id) || empty($content) || empty($status)){
			ajax_return(0, __('回复内容不能为空'));
		}
		$mysqli=new DbMysqli();
		$sql="select char_id,reply_content from gm_question where id=$id";
		$list=$mysqli->findOne($sql);
		if($list){
			$email_id=uuid();
			$time=time();
			$reply_content=unserialize($list['reply_content']);
			$arr=array('content'=>$content,'time'=>date('Y-m-d H:i:s',$time));
			$reply_content[]=$arr;
			$reply_content=serialize($reply_content);
			$replyer=$_SESSION['__'.SERVER_TYPE.'_USER_ACCOUNT'];
			$sql="update gm_question set reply_content='$reply_content',status=$status,email_id='$email_id',
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
					'status'=>$question_status_conf[$status],
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
}