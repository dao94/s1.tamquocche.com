<?php

namespace burpc{

	/*************************************** B2uTitleAchieve head*************************************/


	/*remote invoke sour class*/
	class Sour_B2uTitleAchieve extends \ProxyObject
	{
		function __construct()
		{
			$this->_identity = "burpc_B2uTitleAchieve";
			$this->_netNumber = \NetInfo::NET_NUMBER_toStruct(\NetInfo::NET_NUMBER_BG, \NetInfo::NET_NUMBER_USER);
		}
		public function b2uTitleAchieve_async($msg)
		{
			\Operator::invokeAsync($this, 0, NULL, $msg);
		}
	}



	/*************************************** B2uTitleAchieve end *************************************/

}
?>
