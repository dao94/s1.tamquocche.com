<?php

namespace borpc{

/*************************************** B2oEmail head*************************************/


/*remote invoke sour class*/
class Sour_B2oEmail extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "borpc_B2oEmail";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_COMMON);
	}
	public function b2ocreateEmail_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function b2ocreateEmailList_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
	public function b2ocreateEmailListAll_async($msg)
	{
		\Operator::invokeAsync($this, 2, NULL, $msg);
	}
}



/*************************************** B2oEmail end *************************************/

}
?>
