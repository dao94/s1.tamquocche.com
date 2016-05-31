<?php

/* 	Session		会话对象
 SessionMgr	会话对象管理
 */

//会话对象
final class Session {

	const min = 1;
	const max = 1073741824;

	private $curId = 1;
	private $map = array();
	private $_rpcBridging;

	public function __construct($bridging) {
		$this->_rpcBridging = $bridging;
	}

	public function getId() {
		return 0;
	}

	public function rpcBridging() {
		return $this->_rpcBridging;
	}

	public function handleTimeout() {
		$now = time();
		foreach ($this->map as $key => $tobj) {
			if ($tobj->time() <= $now) {
				$cont = new Context();
				$cont->_messageId = $key;
				$cont->_session = $this;
				$cont->_dispatchStatus = DISPATCH_TIMEOUT;

				$tobj->__back($cont, NULL);

				unset($this->map[$key]);
			}
		}
	}

	public function insertBackObject($backObj) {
		while (true) {
			$this->curId++;
			if ($this->curId > self::max) {
				$this->curId = self::min;
			}

			if (!isset($this->map[$this->curId])) {
				$this->map[$this->curId] = $backObj;
				return $this->curId;
			}
		}
		return -1;
	}

	public function findRemoveBackObject($messageId) {
		$arr = array();
		if (isset($this->map[$this->curId])) {
			$arr[0] = true;
			$arr[1] = $this->map[$messageId];
		} else {
			$arr[0] = false;
		}
		return $arr;
	}

}

//会话管理
class SessionMgr {

	private static $sessionMap = array();
	private static $single = NULL;

	// $SOCK IS socket 实现的对象
	public static function createSession($bridging, $sock) {
		$tssion = new Session($bridging);

		$bridging->bindSession($tssion);
		$bridging->bindSocket($sock);

		self::$sessionMap[$tssion->getId()] = $tssion;

		return $tssion;
	}

	public function getSession($id) {

		return self::$sessionMap[$id];
	}

	public function delSession($id) {

		self::$sessionMap[$id] == NULL;
	}

	public static function getSingle() {
		if (self::$single == NULL) {
			return new SessionMgr();
		}
		return self::$single;
	}

}

?>