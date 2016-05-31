<?php

namespace brrpc{

/*************************************** B2rBroadcast head*************************************/


/*remote invoke sour class*/
class Sour_B2rBroadcast extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "brrpc_B2rBroadcast";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_GM);
	}
	public function b2rUpdateBroadcast_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
}



/*************************************** B2rBroadcast end *************************************/

}
?>
