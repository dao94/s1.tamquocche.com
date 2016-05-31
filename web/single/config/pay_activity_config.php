<?php
//充值活动配置
/* 活动配置只生效最前头的列表项
 * open_return:按开服时间NB赠送
 * time_return:按时间间隔返利，支持多时间点返利
 *
 */
$pay_activity_conf=array(
	'open_return' => array(
		'end_day'			=> 3,//开服后第几天结束
		'ratio'				=> 10,//赠送返还Tỷ lệ
		'email_title'		=> 'Quà Nạp NB Server mới từ Code Web',
		'email_content'		=> 'Quà Nạp NB Server mới từ Code Web',
	),

	'time_return' => array(
		array(
			'start_time'	=>'2015-01-12 00:00:00',//开始时间
			'end_time'		=>'2015-01-17 00:00:00',//结束时间
			'ratio'			=> 50,//赠送返还Tỷ lệ
			'email_title'	=> 'Quà Nạp NB Server mới từ Code Web',
			'email_content'	=> 'Quà Nạp NB Server mới từ Code Web',
		)
	),
);