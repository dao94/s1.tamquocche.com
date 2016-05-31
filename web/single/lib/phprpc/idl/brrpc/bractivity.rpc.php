<?php

namespace brrpc{

/*************************************** B2rActivity head*************************************/


/*remote invoke sour class*/
class Sour_B2rActivity extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "brrpc_B2rActivity";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_GM);
	}
	public function b2rUpdateActivity_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
}



/*************************************** B2rActivity end *************************************/

/*************************************** B2rBaiduVip head*************************************/


/*remote invoke sour class*/
class Sour_B2rBaiduVip extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "brrpc_B2rBaiduVip";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_GM);
	}
	public function b2rBaiduVipInfo_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
}



/*************************************** B2rBaiduVip end *************************************/

}
?>
