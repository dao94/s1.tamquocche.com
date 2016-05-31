<?php
//Quá quanboss_id
$copy_id_config=array(
5801,5802,5803,5804,5805,5806,5807,5808,5809,5810,//少尉
5811,5812,5813,5814,5815,5816,5817,5818,5819,5820,//中尉
5821,5822,5823,5824,5825,5826,5827,5828,5829,5830,//上尉
5831,5832,5833,5834,5835,5836,5837,5838,5839,5840,//少校
5841,5842,5843,5844,5845,5846,5847,5848,5849,5850,//中校
5851,5852,5853,5854,5855,5856,5857,5858,5859,5860,//上校
5861,5862,5863,5864,5865,5866,5867,5868,5869,5870,//少将
5871,5872,5873,5874,5875,5876,5877,5878,5879,5880,//中将
5881,5882,5883,5884,5885,5886,5887,5888,5889,5890,//上将

//5961,5962,5963,5964,5965,5966,5967,5968,5969,5970,
//5971,5972,5973,5974,5975,5976,5977,5978,5979,5980,
//5981,5982,5983,5984,5985,5986,5987,5988,5989,5990
);

//Quá quanboss_id
$copy_id_jy_config=array(
5951,5952,5953,5954,5955,5956,5957,5958//曹魏八骑
);

$copy_id_jy_2_config=array(
5959,5960,5961,5962,5963//五子良将
);

$copy_config=array(
//策划数据--副本分析
	//副本类
	'copy'=>array(
//副本id
array(
			'name'=>'Càn khôn bát quái',//副本名称
			'start_entry_id'=>'304000',//起始副本id
			'end_entry_id'=>'305000',//结束副本id
			'level_start'=>50,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',//开启时间
			'end_time'=>'',//结束时间
			'type'=>'0',//总
),
/*
array(
			'name'=>'Càn khôn bát quái',//副本名称
			'start_entry_id'=>'304000',//起始副本id
			'end_entry_id'=>'304000',//结束副本id
			'level_start'=>37,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',//开启时间
			'end_time'=>'',//结束时间
			'type'=>'1',//分
),
*/
array(
			'name'=>'Càn khôn bát quái',//副本名称
			'start_entry_id'=>'305000',//起始副本id
			'end_entry_id'=>'305000',//结束副本id
			'level_start'=>50,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',//开启时间
			'end_time'=>'',//结束时间
			'type'=>'11',//分
),
//Bình lâm thiên hạ
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600100',
			'end_entry_id'=>'600100',
			'level_start'=>32,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,//总
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600100',
			'end_entry_id'=>'600100',
			'level_start'=>32,//开启等级最小
			'level_end'=>39,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>1,//分
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600100',
			'end_entry_id'=>'600100',
			'level_start'=>40,//开启等级最小
			'level_end'=>49,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>2,//分
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600100',
			'end_entry_id'=>'600100',
			'level_start'=>50,//开启等级最小
			'level_end'=>59,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>3,//分
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600100',
			'end_entry_id'=>'600100',
			'level_start'=>60,//开启等级最小
			'level_end'=>69,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>4,//分
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600100',
			'end_entry_id'=>'600100',
			'level_start'=>70,//开启等级最小
			'level_end'=>79,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>5,//分
),
//Bình lâm thiên hạ精英
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600200',
			'end_entry_id'=>'600200',
			'level_start'=>40,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>10,//总
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600200',
			'end_entry_id'=>'600200',
			'level_start'=>40,//开启等级最小
			'level_end'=>49,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>11,//分
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600200',
			'end_entry_id'=>'600200',
			'level_start'=>50,//开启等级最小
			'level_end'=>59,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>12,//分
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600200',
			'end_entry_id'=>'600200',
			'level_start'=>60,//开启等级最小
			'level_end'=>69,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>13,//分
),
array(
			'name'=>'Bình lâm thiên hạ',
			'start_entry_id'=>'600200',
			'end_entry_id'=>'600200',
			'level_start'=>70,//开启等级最小
			'level_end'=>79,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>14,//分
),

//Mượn tên
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700100',
			'end_entry_id'=>'700100',
			'level_start'=>35,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,//总
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700100',
			'end_entry_id'=>'700100',
			'level_start'=>35,//开启等级最小
			'level_end'=>39,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>1,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700100',
			'end_entry_id'=>'700100',
			'level_start'=>40,//开启等级最小
			'level_end'=>49,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>2,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700100',
			'end_entry_id'=>'700100',
			'level_start'=>50,//开启等级最小
			'level_end'=>59,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>3,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700100',
			'end_entry_id'=>'700100',
			'level_start'=>60,//开启等级最小
			'level_end'=>69,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>4,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700100',
			'end_entry_id'=>'700100',
			'level_start'=>70,//开启等级最小
			'level_end'=>79,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>5,//分
),
//Mượn tên精英
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700200',
			'end_entry_id'=>'700200',
			'level_start'=>35,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>10,//总
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700200',
			'end_entry_id'=>'700200',
			'level_start'=>35,//开启等级最小
			'level_end'=>39,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>11,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700200',
			'end_entry_id'=>'700200',
			'level_start'=>40,//开启等级最小
			'level_end'=>49,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>12,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700200',
			'end_entry_id'=>'700200',
			'level_start'=>50,//开启等级最小
			'level_end'=>59,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>13,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700200',
			'end_entry_id'=>'700200',
			'level_start'=>60,//开启等级最小
			'level_end'=>69,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>14,//分
),
array(
			'name'=>'Mượn tên',
			'start_entry_id'=>'700200',
			'end_entry_id'=>'700200',
			'level_start'=>70,//开启等级最小
			'level_end'=>79,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>15,//分
),

array(
			'name'=>'Lạc Dương攻防战',
			'start_entry_id'=>'900300',
			'end_entry_id'=>'900300',
			'level_start'=>41,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,
),

array(
			'name'=>'Thập diện mai phục',
			'start_entry_id'=>'500100',
			'end_entry_id'=>'500100',
			'level_start'=>41,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,
),

array(
			'name'=>'Quá quan',
			'start_entry_id'=>'800100',
			'end_entry_id'=>'822000',
			'level_start'=>31,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,
),

//Thục đạo
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560100',
			'end_entry_id'=>'560100',
			'level_start'=>39,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,//总
),
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560100',
			'end_entry_id'=>'560100',
			'level_start'=>39,//开启等级最小
			'level_end'=>49,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>1,//分
),
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560100',
			'end_entry_id'=>'560100',
			'level_start'=>50,//开启等级最小
			'level_end'=>59,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>2,//分
),
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560100',
			'end_entry_id'=>'560100',
			'level_start'=>60,//开启等级最小
			'level_end'=>69,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>3,//分
),
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560200',
			'end_entry_id'=>'560200',
			'level_start'=>39,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>10,//总
),
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560200',
			'end_entry_id'=>'560200',
			'level_start'=>39,//开启等级最小
			'level_end'=>49,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>11,//分
),
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560200',
			'end_entry_id'=>'560200',
			'level_start'=>50,//开启等级最小
			'level_end'=>59,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>12,//分
),
array(
			'name'=>'Thục đạo',
			'start_entry_id'=>'560200',
			'end_entry_id'=>'560200',
			'level_start'=>60,//开启等级最小
			'level_end'=>69,//开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>13,//分
),
array(
			'name'=>'Hải đế',
			'start_entry_id'=>'630100',
			'end_entry_id'=>'630100',
			'level_start'=>38,//开启等级最小
			'level_end'=>100,//开启等级最大
			'start_time'=>'15:00',
			'end_time'=>'15:30',
			'type'=>0,//总
),
array(
			'name'=>'Thất Cầm Mạnh Hoạch',
			'start_entry_id'=>'205500',
			'end_entry_id'=>'205500',
			'level_start'=>40,//开启等级最小
			'level_end'=>100,//默认 开启等级最大
			'start_time'=>'16:00',
			'end_time'=>'16:30',
			'type'=>0,//总
),
array(
			'name'=>'Phượng Sồ Bí Cảnh',
			'start_entry_id'=>'510100',
			'end_entry_id'=>'510100',
			'level_start'=>50,//开启等级最小
			'level_end'=>100,//默认 开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,//总
),
array(
			'name'=>'Quần Anh Hội',
			'start_entry_id'=>'330300',
			'end_entry_id'=>'330300',
			'level_start'=>40,//开启等级最小
			'level_end'=>100,//默认 开启等级最大
			'start_time'=>'',
			'end_time'=>'',
			'type'=>0,//总
),

),
);
?>