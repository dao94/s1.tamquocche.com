<?php
//游戏数值配置
define('MAX_LEVEL', 100);//游戏最高等级
//职业配置
$occ_conf=array(
	11=>__('Phá Thiên'),
	21=>__('Vũ Nguyệt'),
	31=>__('Phi Linh'),
	41=>__('Ảo Tuyết'),
);
//阵营配置
$camp_conf=array(
	0=>__('Không'),
	1=>__('Ngụy'),
	2=>__('Thục'),
	3=>__('Ngô'),
);
//性别配置
$gender_conf=array(
	0=>__('Nữ'),
	1=>__('Nam'),
);
//玩家问题类型
$question_type_conf=array(
1=>__('Báo bug'),
2=>__('Chửi'),
3=>__('Kiến nghị'),
4=>__('Khác'),
);
//玩家建议类型
$advice_type_conf=array(
	1=>__('Kiến Nghị'),
	2=>__('Ý Kiến'),
);
//玩家问题状态
$question_status_conf=array(
	1=>__('Đã xử lý'),
	2=>__('Không xử lý'),
	3=>__('Theo dõi'),
);
//封禁时间(秒=>名称)
$forbid_time_conf=array(
	1800=>__('30 phút'),
	3600=>__('1 giờ'),
	10800=>__('3 giờ'),
	21600=>__('6 giờ'),
	43200=>__('12 giờ'),
	86400=>__('1 ngày'),
	259200=>__('3 ngày'),
	2592000=>__('30 ngày'),
	0=>__('Vĩnh viễn'),
);
//封禁类型
$forbid_type_conf=array(
	1=>__('cấm chat'),
	2=>__('Khóa TK'),
	3=>__('Khóa IP'),
	4=>__('Kick'),
);
//封禁理由(类型=>理由)
$forbid_reason_conf=array(
	1=>__('Thông báo bán NB'),
	2=>__('Spam'),
	3=>__('Kéo người'),
	4=>__('Quấy rối'),
	5=>__('Hack'),
	6=>__('Bug'),
	0=>__('Tự định nghĩa'),
);
//封禁状态
$forbid_status_conf=array(
		1=>__('Đẫ đóng cửa'),
		2=>__('Đã sám hối'),
		3=>__('Đã quá hạn'),
);

//装备部位
$part_conf=array(
	1=>__('Vũ khí'),
	2=>__('Mũ giáp'),
	3=>__('Y phục '),
	4=>__('Áo choàng '),
	5=>__('Đai lưng '),
	6=>__('Quần'),
	7=>__('Giầy'),
	8=>__('Bao cổ tay'),
	9=>__('Cái bao tay'),
	10=>__('Nhẫn'),
	11=>__('Hạng liên'),
	12=>__('Bội sức'),
	13=>__('Võ sức'),
	14=>__('Cánh'),
	15=>__('Thời trang'),
);

$pet_realm_conf=array(
0=>__('Phàm cảnh'),
1=>__('Linh cảnh'),
2=>__('Tiên cảnh'),
3=>__('Thần cảnh'),
4=>__('Thánh cảnh'),
);
$bag_conf=array(
1=>__('Nhân vật'),
2=>__('Trang bị'),
3=>__('Thương khố'),
4=>__('Túi tọa kỵ'),
5=>__('Túi giao dịch'),
6=>__('Túi cẩm nang'),
7=>__('Túi T.bị sủng vật'),
8=>__('Túi Thiên Quan'),
);
//帮派状态
$faction_state_conf=array(
0=>__('Đẫ giải tán'),
1=>__('Bình thường'),
2=>__('Đếm ngược'),
);
//帮派职位
$faction_position_conf=array(
1=>__('BC'),
2=>__('PBC'),
3=>__('Trưởng Lão'),
4=>__('Đường Chủ'),
5=>__('Tinh Anh'),
6=>__('Bang chúng'),
);
//帮派权限
$faction_authority_conf=array(
0=>__('Quản lý thành viên'),
1=>__('Quản lý chức'),
2=>__('Quản lý kiến trúc'),
3=>__('Quản lý kỹ năng'),
4=>__('Quản lý nghĩa quân'),
5=>__('Quản lý hoạt động'),
6=>__('Quản lý ngoại giao'),
);
$ride_type_conf=array(
1=>__('Lục hành hệ'),
);
$skill_type_conf=array(
0=>__('Vô Song-Chiến'),
1=>__('Vô Song-Ngự'),
2=>__('Vô Song-Quần'),
);
$colour_conf=array(
1=>__('Trắng'),
2=>__('Lục'),
3=>__('Lam'),
4=>__('Tím'),
5=>__('Cam'),
);
//结婚类型
$marry_type_conf = array (
1 => __('Cưới'),
2 => __('Ly hôn'),
3 => __('Thăng cấp nhẫn'),
);
//婚礼类型
$wedding_type_conf=array(
1=>__('Thường'),
2=>__('Cao cấp'),
3=>__('Xa hoa'),
);
//婚戒类型
$ring_name_conf = array (
1 => __('Thảo Giới'),
2 => __('Thanh Đồng Giới'),
3 => __('Bạch Ngân Giới'),
4 => __('Thanh Ngọc Giới'),
5 => __('Tượng Nha Giới'),
6 => __('San Hô Giới'),
7 => __('Phượng Vĩ Giới'),
8 => __('Long Lân Giới'),
9 => __('Tâm Tâm Tương Ấn Giới'),
10 => __('Tình Bỉ Kiên Trinh Giới')

);
//Đồng Tâm 类型
$ring_title_conf = array (
1 => __('Tân Hôn'),
2 => __('Chỉ Hôn'),
3 => __('Đồng Hôn'),
4 => __('Thanh Đồng Hôn'),
5 => __('Ngân Hôn'),
6 => __('Bạch Ngân Hôn'),
7 => __('Kim Hôn'),
8 => __('Bạch Kim Hôn'),
9 => __('Bảo Thạch Hôn'),
10 => __('Kim Cương Hôn'),
);

?>