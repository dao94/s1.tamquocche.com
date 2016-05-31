<?php

namespace borpc{

/*************************************** B2oBgOper head*************************************/


/*remote invoke sour class*/
class Sour_B2oBgOper extends \ProxyObject
{
	function __construct()
	{
		$this->_identity = "borpc_B2oBgOper";
		$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_COMMON);
	}
	public function controlSpeak_async($msg)
	{
		\Operator::invokeAsync($this, 0, NULL, $msg);
	}
	public function saveHumanData_async($msg)
	{
		\Operator::invokeAsync($this, 1, NULL, $msg);
	}
	public function gmRepairPet_async($msg)
	{
		\Operator::invokeAsync($this, 2, NULL, $msg);
	}
}



/*************************************** B2oBgOper end *************************************/

}
?>
