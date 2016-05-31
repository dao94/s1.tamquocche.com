<?php
class FcmTQC extends Fcm {

    //接口api
    private $url = 'http://web.4399.com/api/reg/fcm_api.php?';
    public function __construct() {
        parent::__construct();
    }

    /*
     * 从接口获取
     * 1	成功	成功登记并且年龄超过18岁
     * 2	成功	成功登记但年龄没有超过18岁
     * -1	失败	参数缺失
     * -2	失败	验证失败
     * -3	失败	身份证号码无效
     * -4	失败	不允许重复登记，每个玩家的帐号只要登记成功了就不允许再做变动了
     * -5	失败	登记失败
     * -6	失败	用户不存在
     */
    public function run() {
        $params['account'] = $this->account;
        $params['card'] = $this->card;
        $params['truename'] = $this->truename;
        $params['sign'] = md5($this->truename . $this->account . FCM_KEY . $this->card);
        $res = http_post($this->url . http_build_query($params));
        switch ($res) {
            case 1:
                $flag = 1;
                break;
            case 2:
                $flag = 0;
                break;
            case -4:
                $flag = $this->age >= 18 ? 1 : 0;
                break;
            default:
                $flag = 2;
                break;
        }
        return $flag;
    }
}

?>
