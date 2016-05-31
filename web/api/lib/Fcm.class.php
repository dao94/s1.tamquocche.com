<?php
class Fcm {

    protected $card = '';
    protected $truename = '';
    protected $account = '';
    protected $age = 0;
    protected $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    protected $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');

    function __construct() {
        $this->card = isset($_POST['card']) ? strtoupper(trim($_POST['card'])) : '';
        $this->truename = isset($_POST['truename']) ? urldecode($_POST['truename']) : '';
        $this->account = isset($_POST['account']) ? urldecode($_POST['account']) : '';
        //截取出生日期
        $date = substr($this->card, 6, -4);
        $year = substr($date, 0, -4);
        $month = substr($date, 4, -2);
        $day = substr($date, -2);
        //获取年龄
        $span_time = time() - strtotime($date);
        $this->age = $span_time / (365 * 24 * 60 * 60);
    }

    //工厂方法
    final static function factory() {
    	static $obj = null;
		if (!is_null($obj)){
			return $obj;
		}
		$className = __CLASS__ . SERVER_AGENT;
		$classFile = __API__ . '/lib/' . SERVER_AGENT . '/' . $className . '.class.php';
		if (is_file($classFile)) {
			include $classFile;
			$obj = new $className();
		} else {
			$obj = new self();
		}
		return $obj;
    }

    /*
     * 返回值处理
     */
    public function run() {
        $sigma = 0;
        for ($i = 0; $i < 17; $i++) {
            $sigma += ((int) $this->card{$i}) * $this->wi[$i];
        }
        //截取校验码
        $sign = substr($this->card, -1);
        if ($this->ai[($sigma % 11)] == $sign) {
            $flag = $this->age >= 18 ? 1 : 0;
        } else {
            $flag = 2;
        }
        return $flag;
    }

}

?>
