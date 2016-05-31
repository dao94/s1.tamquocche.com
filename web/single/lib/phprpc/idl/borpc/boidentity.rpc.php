<?php

namespace borpc{

/*************************************** B2oIdentity head*************************************/


/*remote invoke sour class*/
class Sour_B2oIdentity extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "borpc_B2oIdentity";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_COMMON);
	}
	public function changeIdentity_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
}



/*************************************** B2oIdentity end *************************************/

}
?>
