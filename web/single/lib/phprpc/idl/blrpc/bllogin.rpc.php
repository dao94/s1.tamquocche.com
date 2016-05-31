<?php

namespace blrpc{

/*************************************** B2lLoginCtrl head*************************************/


/*remote invoke sour class*/
class Sour_B2lLoginCtrl extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "blrpc_B2lLoginCtrl";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_LOGIN);
	}
	public function b2lCreateAccount_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function b2lControlLine_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
}



/*************************************** B2lLoginCtrl end *************************************/

}
?>
