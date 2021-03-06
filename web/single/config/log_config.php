<?php
//流水各种配置
$money_io_conf = array(
  0 => __('Tiêu'),
  1 => __('Thu nhập'),
  2 => __('Update')
);
//货币种类
$money_class_conf = array(
  1 => __('Đồng'),
  2 => __('Đồng Khóa'),
  3 => __('NB'),
  4 => __('NB Khóa'),
  //5 => __('Giao dịch背包上的Đồng'),
  //6 => __('Giao dịch背包上的NB'),
  8=>__('Công Huân'),
  10=>__('Cạnh Kỹ'),
  11=>__('Điểm Tẩy Luyện'),
  12=>__('Giao tình'),
  13=>__('Điểm Nạp Nb'),
  14=>__('Điểm tiêu phí'),
  15=>__('Tiền lôi đài'),
);
//道具IO
$item_io_conf = array(
  0 => __('Ra'),
  1 => __('Vào'),
  2 => __('Update')
);
$mail_type_conf=array(
  1=>__('Hệ Thống'),
  2=>__('Cá nhân'),
  3=>'GM',
  4=>__('Thiếp cưới'),
);
//Shop类型
$mall_type_conf = array(
  1 => __('Shop'),
  2 => __('Từ NPC'),
  3 => __('Giới hạn mua'),
  4 => __('Trân Bảo Các'),
);
//Shop流水货币类型
$mall_money_conf = array(
  0 => __('Đổi vật phẩm'),
  1 => __('Đồng'),
  2 => __('Đồng Khóa'),
  3 => __('NB'),
  4 => __('NB Khóa'),
  5 => __('Ưu tiên NB Khóa rồi đến NB'),
  6 => __('Ưu tiên Đồng Khóa rồi đến Đồng'),
  7 => __('Chân khí'),
  8 => __('Công Huân'),
  9 => __('Bang cống'),
  10 => __('Cạnh Kỹ'),
  11 => __('Điểm Tẩy Luyện'),
);

$item_type_conf = array (
  0 => __('Rơi xuống'),
  1 => __('Hủy vật phẩm'),
  2 => __('Xác nhập'),
  3 => __('Dùng vật phẩm'),
  4 => __('Chỉnh lý'),
  5 => __('Tăng thêm'),
  6 => __('Trao đổi'),
  7 => __('Nhiệm vụ'),
  8 => __('Nhận bưu kiện'),
  9 => __('Lễ túi'),
  10 => __('Túi trao đổi'),
  11 => __('Giao dịch'),
  12 => __('Hủy giao dịch'),
  14 => __('Thu thập'),
  15 => __('Shop mua '),
  16 => __('Lần đầu vượt fb'),
  17 => __('Học kỹ năng'),
  18 => __('Từ NPC'),
  19 => __('NPC bán'),
  20 => __('Cường hóa'),
  21 => __('Thăng cấp'),
  22 => __('Làm mới NV'),
  23 => __('Mở rương'),
  24 => __('Hàng yêu nhận'),
  25 => __('VIP từ thiện '),
  26 => __('Kỹ năng pet'),
  27 => __('Cảnh giới pet'),
  28 => __('Luyện trang bị'),
  29 => __('Phân giải'),
  30 => __('Tư chất pet'),
  31 => __('Cảnh giới pet tăng'),
  32 => __('Tạo bang'),
  33 => __('Luyện hóa thường'),
  34 => __('Luyện hóa đặc thù'),
  35 => __('Thay thế luyện'),
  36 => __('Làm mới tiêu'),
  37 => __('Hồi sinh'),
  38 => __('Dùng đạo cụ dịch chuyển'),
  39 => __('Tặng hoa'),
  40 => __('Giới hạn mua'),
  41 => __('Kích hoạt bảo thạch'),
  42 => __('Sung năng bảo thạch'),
  43 => __('Thần luyện bảo thạch'),
  44 => __('Thưởng fb'),
  45 => __('Càn quét fb'),
  46 => __('Thưởng tướng quân ngày'),
  47 => __('Thưởng tướng quân ngày'),
  48 => __('Trứng pet random'),
  49 => __('Sách pet'),
  50 => __('Ký mỗi ngày'),
  51 => __('Quà online tân thủ'),
  52 => __('Quà trưởng thành'),
  53 => __('Giftcode'),
  54 => __('Thưởng 7 ngày login'),
  55 => __('Rương công trận'),
  56 => __('Thưởng sôi nổi'),
  57 => __('Tặng offline'),
  58 => __('Vật phẩm ướng quân'),
  59 => __('Đạn bang'),
  60 => __('Đại lạt bá'),
  61 => __('Hệ thống pet'),
  62 => __('Kỳ cọ'),
  63 => __('Rút thưởng bang'),
  64 => __('Thu thập bộ đồ'),
  65 => __('Thời trang tân thủ'),
  66 => __('Đào rương ôn tuyền'),
  67 => __('Luyện hóa nguyên hồn pet'),
  68 => __('Gửi bán'),
  69 => __('Túi quà nạp'),
  70 => __('Thăng cấp kỹ năng'),
  71 => __('Mục tiêu'),
  72 => __('Túi đồ'),
  73 => __('Kho'),
  74 => __('Túi hàng yêu'),
  75 => __('Túi vũ khí'),
  76 => __('Túi tọa kỵ'),
  77 => __('Mục tiêu mở sv'),
  78 => __('Nv chân khí'),
  79 => __('Đổi'),
  80 => __('Dung hợp'),
  81 => __('Kết hôn'),
  82 => __('Thâm hải'),
  83 => __('Thưởng càn khôn'),
  84 => __('Tẩy đặc thù'),
  85 => __('Đập trứng'),
  86 => __('Thu thập đan'),
  87 => __('Vòng quay thập diện'),
  88 => __('Thưởng mục tiêu chương'),
  89 => __('Thưởng nv tân thủ đặc thù'),
  90 => __('Học kỹ năng'),
  91 => __('Quà ngẫu nhiên'),
  92 => __('Triệu tập đội'),
  93 => __('Mua nhanh'),
  94 => __('Quà chiến lực mở sv'),
  95 => __(' Boss TG hòa'),
  96 => __('Tự động xóa quá hạn'),
  97 => __('Nhận Thẻ binh pháp'),
  98 => __('Đấu offline'),
  99 => __('Đạo cụ c.hóa đặc thù'),
  100 => __('Đạo cụ t.cấp đặc thù'),
  101 => __('Kích hoạt cánh'),
  102 => __('Thăng cấp cánh'),
  103 => __('Luyện chế trứng pet'),
  104 => __('Tăng thuần thục kỹ cánh'),
  105 => __('V.phấm n.vụ nguyên lực'),
  106 => __('V.phấm n.vụ nguyên lực cuối'),
  107 => __('Trang bị thần hóa'),
  108 => __('Điêu khắc'),
  109 => __('Thập diện vip'),
  110 => __('Càn khôn vip'),
  111 => __('Tàng bảo các'),
  112 => __('Hoạt động'),
  113 => __('Nạp toàn server'),
  114 => __('Chinh chiến 4 phía'),
  115 => __('Đổi GMT'),
  116 => __('Gia viên'),
  117 => __('Đồng đội mặc/cởi t.bị'),
  118 => __('Trang bị đ.đội ăn exp'),
  119 => __('Thăng cấp t.bị đ.đội'),
  120 => __('Thay thế kỹ đ.đội'),
  121 => __('Nạp quay'),
  122 => __('Thưởng đ.đội thiên quan'),
  123 => __('Thăng cấp trận hồn'),
  124 => __('Thủ hướng'),
  125 => __('Thử giả'),
  126 => __('Đại diện đồng khóa'),
  127 => __('Đội phát võ hồn'),
  128 => __('Bồi dưỡng võ hồn'),
  129 => __('Nhận nhanh thông quan'),
  130 => __('Giết boss liên sv'),
  131 => __('Tế luyện võ hồn'),
  132 => __('Đ.đội chiến liên sv'),
  133 => __('Điểm tiêu phí'),
  134 => __('Thưởng đ.đội chiến liên sv'),
  135 => __('Thăng bảo vệ đ.đội'),
  136 => __('Dung hợp T.bị'),
  137 => __('Đào hoa liên sv'),
  138 => __('Trồng trọt liên sv'),
  139 => __('Tiệm thần bí'),
  140 => __('Cược chiến liên sv'),
  141 => __('Thăng cấp thiên phú'),
  142 => __('Phù văn t.bị'),
  143 => __('Phụ hồn'),
  144 => __('C.hóa nguyên thần'),
  145 => __('Thần hóa linh hồn'),
  146 => __('Điêu khắc linh hồn'),
  147 => __('Quán chú tài liệu'),
  148 => __('Thức ăn tọa kỵ'),
  149 => __('Vòng quay GMT'),
  150 => __('Thử luyện danh tướng'),
  151 => __('Tàng Bảo Đồ'),
  152 => __('Cường hóa cánh'),
  153 => __('Quá hạn'),
);

$money_type_conf = array (
  0 => __('Rơi xuống'),
  1 => __('Nhiệm vụ nhận '),
  2 => __('Túi cách tử'),
  3 => __('Phí gửi thư'),
  4 => __('Lễ túi'),
  5 => __('Giao dịch'),
  6 => __('Giao dịchi'),
  7 => __('Shop mua '),
  8 => __('Lần đầu vượt fb'),
  9 => __('Học kỹ năng'),
  10 => __('Từ NPC'),
  11 => __('Quay lại túi đồ'),
  12 => __('Cây rung tiền'),
  13 => __('Cường hóa'),
  14 => __('Thăng cấp'),
  15 => __(' Mở rương'),
  16 => __('Luyện trang bị'),
  17 => __('Hoàn thành nhanh nv'),
  18 => __('Mua làm mới nv'),
  19 => __('Phân giải'),
  20 => __('Tư chất pet'),
  21 => __('Cảnh giới pet'),
  22 => __('Kỹ năng pet'),
  23 => __('Cảnh giới pet tăng'),
  24 => __('Học bang kỹ'),
  25 => __('Luyện hóa thường'),
  26 => __('Luyện hóa đặc thù'),
  27 => __('EXP fb( binh trước khi dưới thành )'),
  28 => __('Làm mới tiêu'),
  29 => __('Áp tiêu'),
  30 => __('Thăng cấp kỹ năng '),
  31 => __('Tặng hoa'),
  32 => __('Mượn tên'),
  33 => __('Hồi sinh'),
  34 => __('Giới hạn mua'),
  35 => __('Kích hoạt bảo thạch'),
  36 => __('Sung năng bảo thạch'),
  37 => __('Thần luyện bảo thạch'),
  38 => __('Tham bái'),
  39 => __('Thưởng fb'),
  40 => __('Càn quét fb'),
  41 => __('Mua lần fb'),
  42 => __('Mua gói thuốc'),
  43 => __('Sách pet'),
  44 => __('Trứng pet random'),
  45 => __('Mở rương công trận'),
  46 => __('Triệu tập đội'),
  47 => __('Đạo cụ tiền'),
  48 => __('Quả vàng bạc(Cây rung tiền)'),
  49 => __('Xóa CD Cây rung tiền'),
  50 => __('Giftcode tân thủ'),
  51 => __('Giftcode'),
  52 => __('Thưởng 7 ngày login'),
  53 => __('Sôi nổi ngày'),
  54 => __('Tặng offline'),
  55 => __('Thăng cấp bang'),
  56 => __('Đại lạt bá'),
  57 => __('Hệ thống pet'),
  58 => __('Cống hiến bang'),
  59 => __('Đổi treo máy bang'),
  60 => __('Thu thập bộ đồ'),
  61 => __('Mua cạnh kỹ'),
  62 => __('Nạp NB'),
  63 => __('Gửi bán'),
  64 => __('Nguyên thần'),
  65 => __('Túi quà nạp'),
  66 => __('Lời kết hoạch đầu tư'),
  67 => __('Mục tiêu'),
  68 => __('Xóa CD NV Chân Khí'),
  69 => __('Buff bang phái'),
  70 => __('Đổi '),
  71 => __('Kết hôn'),
  72 => __('Sự kiện thần bí'),
  73 => __('Tiền mừng hôn lễ'),
  74 => __('Đập trứng'),
  75 => __('Mở túi đồ'),
  76 => __('Đưa tiền'),
  77 => __('Kích hoạt danh tướng'),
  78 => __('Gặp cách khai tử'),
  79 => __('Gặp danh tướng trắng'),
  80 => __('Gặp danh tướng lụcg'),
  81 => __('Gặp danh tướng la'),
  82 => __('Gặp danh tướng tím'),
  83 => __('Gặp danh tướng cam'),
  85 => __('Học kỹ năng hiệu quả '),
  86 => __('Bán phế linh'),
  87 => __('Trừ NB ký thêm'),
  88 => __('Thâm hải'),
  89 => __('Khích lệ boss mạnh hoạch'),
  90 => __('Anh Linh Điện'),
  93 => __('Mua nhanh'),
  94 => __('Quà chiến lực mở sv'),
  95 => __('Nhận Thẻ binh pháp'),
  96 => __('Đấu offline'),
  97 => __('Luyện hóa nguyên hồn pet'),
  98 => __('Thăng cấp cánh'),
  99 => __('Đấu trường'),
  100 => __('Mua cạnh kỹ'),
  101 => __('Tiệm cạnh kỹ'),
  102 => __('Bang chiến'),
  103 => __('Bồi thường c.huân offline'),
  104 => __(' chiến trường '),
  105 => __('Công HuânTừ NPC'),
  106 => __('Mục tiêuđạt thành tưởng thưởng '),
  107 => __('Test tiếp lời '),
  108 => __('Cướp tiêu'),
  109 => __('Luyện chế trứng pet'),
  110 => __('Mua binh phù'),
  111 => __('Tăng thuần thục kỹ cánh'),
  112 => __('Mua lần nv binh lực'),
  113 => __('Đi lại nv nguyên lực'),
  114 => __('Cuối cùng NV Nguyên lực'),
  115 => __('Gặp mặt'),
  116 => __('Đổi vp'),
  117 => __('Dùng tiền mua nguyên lực'),
  118 => __('Trang bị thần hóa'),
  119 => __('Điêu khắc'),
  120 => __('Mua Công Huân'),
  121 => __('chuyển phe'),
  122 => __('NV Bang'),
  123 => __('Tàng bảo các'),
  124 => __('Quần anh'),
  125 => __('Nạp toàn server'),
  126 => __('Đổi GMT'),
  127 => __('Mượn tên 60'),
  128 => __('Gia viên'),
  129 => __('Nạp NB GMT'),
  130 => __('Trang bị đ.đội ăn exp'),
  131 => __('Nạp quay'),
  132 => __('Lãnh địa chiến'),
  133 => __('Thưởng đ.đội thiên quan'),
  134 => __('Worldcup 2014'),
  135 => __('Thăng cấp t.bị đ.đội'),
  136 => __('Thăng cấp trận hồn'),
  137 => __('Thủ hướng'),
  138 => __('Thử giả'),
  139 => __('Đại diện đồng khóa'),
  140 => __('Đột phá võ hồn'),
  141 => __('Bồi dưỡng võ hồn'),
  142 => __('Tế luyện võ hồn'),
  143 => __('Đồng đội chiến'),
  144 => __('Điểm hoạt động tiêu phí'),
  145 => __('Sửa GMT'),
  146 => __('Thăng bảo vệ đ.đội'),
  147 => __('Đồng đội lôi đài khiêu chiến '),
  148 => __('Thưởng đ.đội chiến liên sv'),
  149 => __('Dung hợp'),
  150 => __('Đào hoa liên sv'),
  151 => __('Tiệm thần bí'),
  152 => __('Làm mới NB tiệm thần bí'),
  153 => __('Cược chiến liên sv'),
  154 => __('Nguyên Lực Mê Trận'),
  155 => __('Thăng cấp thiên phú'),
  156 => __('Phù văn T.bị'),
  157 => __('Phụ hồn t.bị'),
  158 => __('C.Hóa nguyên thần'),
  159 => __('Tẩy thuộc tính nguyên thần'),
  160 => __('Thần hóa linh hồn t.bị'),
  161 => __('Điêu khắc linh hồn t.bị'),
  162 => __('Thăng cấp NB bảo vệ hồn'),
  163 => __('Quán chú NB bảo vệ hồn'),
  164 => __('Vòng quay GMT'),
  165 => __('Danh tướng thử luyện'),
  166 => __('Thu thập avatar'),
  167 => __('Đổi binh phù cao cấp(trừ Giao tình)'),
  168 => __('Tàng Bảo Đồ'),
  169 => __('Cánh cường hóa'),
170 => __('头衔挑战'),
);

$exp_type_conf = array (
  1 => __('HH chúc phúc'),
  2 => __('Làm NV nhận'),
  3 => __('Giết quái nhận '),
  4 => __('Lần đầu vượt fb'),
  5 => __('Học kỹ năng'),
  6 => __('EXP fb( binh trước khi dưới thành )'),
  7 => __('NV Ngày'),
  8 => __('Cấp thế giới'),
  9 => __('EXP quà '),
  10 => __('Nhân vật EXP đan'),
  11 => __('Thưởng fb'),
  12 => __('Quét fb'),
  13 => __('NV Bang lấy được '),
  14 => __('Tĩnh tọa'),
  15 => __('Pet EXP đan '),
  16 => __('Thưởng offline ngày'),
  17 => __('Test API'),
  18 => __('Treo máy bang'),
  19 => __('Ôn tuyền'),
  20 => __('Mục tiêu'),
  21 => __('Sự kiện thần bí'),
  22 => __('Hôn lễ'),
  23 => __('Thâm hải'),
  24 => __('Tăng cấp'),
  25 => __('Nhận Thẻ binh pháp'),
  26 => __('Đấu offline'),
  27 => __('Nhân vậtThăng cấpđan'),
  28 => __('Đồng độiThăng cấpđan'),
  29 => __('Nv Nguyên Lực nhận exp'),
  30 => __('Nv nguyên lực thông qua thời điểm nhận exp'),
  31 => __('NV Bang'),
  32 => __('Treo fb'),
  33 => __('Lãnh địa chiến'),
  34 => __('Đồng đội xông quan'),
  35 => __('Đồng đội chiến liên server'),
  36 => __('Hợp server hoa đào được'),
  37 => __('Nguyên Lực Mê Trận'),
  38 => __('Thăng cấp bảo vệ hồn nv giảm exp'),
  39 => __('Tàng Bảo Đồ'),
);

$faction_type_conf = array (
  1 => __('Thăng kiến trúc'),
  2 => __('Nghiên cứu/Thăng cấp kỹ'),
  3 => __('Đổi chức'),
  4 => __('Mở phụ bản'),
  5 => __('Nhiệm vụ'),
  6 => __('Dùng đạo cụ bang'),
  7 => __('BXH'),
  8 => __('Cống hiến bang'),
  9 => __('Lịch sử c.hiến'),
  10 => __('NV bang mới'),
);
?>