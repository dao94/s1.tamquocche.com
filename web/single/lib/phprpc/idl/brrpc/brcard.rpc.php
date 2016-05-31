<?php

namespace brrpc{

/*************************************** B2rCardReward head*************************************/


/*remote invoke sour class*/
class Sour_B2rCardReward extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "brrpc_B2rCardReward";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_GM);
	}
	public function b2rUpdateCardStatus_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function b2rNotifyCardReward_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
}



/*************************************** B2rCardReward end *************************************/

}
?>
