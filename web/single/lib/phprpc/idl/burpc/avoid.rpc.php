<?php

namespace burpc{

/*************************************** B2uAvoidCtrl head*************************************/


/*remote invoke sour class*/
class Sour_B2uAvoidCtrl extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "burpc_B2uAvoidCtrl";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_USER);
	}
	public function b2uAvoid_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
}



/*************************************** B2uAvoidCtrl end *************************************/

/*************************************** B2uResetProtectLock head*************************************/


/*remote invoke sour class*/
class Sour_B2uResetProtectLock extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "burpc_B2uResetProtectLock";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_USER);
	}
	public function b2uReset_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
}



/*************************************** B2uResetProtectLock end *************************************/

}
?>
