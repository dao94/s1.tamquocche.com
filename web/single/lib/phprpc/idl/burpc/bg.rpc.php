<?php

namespace burpc{

/*************************************** B2uBgOper head*************************************/


/*remote invoke sour class*/
class Sour_B2uBgOper extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "burpc_B2uBgOper";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_USER);
	}
	public function hotUpdate_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function controlIp_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
	public function controlAccount_async($msg)
	{
		\Operator::invokeAsync($this, 2, NULL, $msg);
	}
	public function kickHuman_async($msg)
	{
		\Operator::invokeAsync($this, 3, NULL, $msg);
	}
}



/*************************************** B2uBgOper end *************************************/

/*************************************** B2uPayOper head*************************************/


/*remote invoke sour class*/
class Sour_B2uPayOper extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "burpc_B2uPayOper";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_USER);
	}
	public function payOrder_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function bgPayOrder_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
}



/*************************************** B2uPayOper end *************************************/

}
?>
