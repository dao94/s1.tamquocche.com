<?php

define('PAGE_HEADER', __('条记录'));
define('PAGE_PREV', __('上一页'));
define('PAGE_NEXT', __('下一页'));
define('PAGE_FIRST', __('第一页'));
define('PAGE_LAST', __('最后一页'));
define('PAGE_PAGES', __('页'));

class Page {

	public $firstRow;  //起始行数
	public $listRows = 20;  //列表每页显示行数
	public $parameter;  //页数跳转时要带的参数
	public $nowPage;  //当前页数
	protected $totalPages; //分页总页面数// 总行数
	protected $totalRows; //总行数
	protected $rollPage = 6; //分页栏每页显示的页数
	protected $p = 'p';  //分页跳转变量
	// 分页显示定制
	protected $config = array(
        'header' => PAGE_HEADER,
        'prev' => PAGE_PREV,
        'next' => PAGE_NEXT,
        'first' => PAGE_FIRST,
        'last' => PAGE_LAST,
        'pages' => PAGE_PAGES,
        'theme' => '<ul> <li><a href="#" onclick="javascript:return false;">%totalRow% %header% %nowPage% / %totalPage% %pages%</a></li> %first% %upPage% %linkPage% %downPage% %end% %goto% </ul>',
	);

	/**
	 +----------------------------------------------------------
	 * 架构函数
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 * @param array $totalRows  总的记录数
	 * @param array $listRows  每页显示记录数
	 * @param array $parameter  分页跳转的参数
	 +----------------------------------------------------------
	 */
	public function __construct($totalRows, $listRows = 0, $parameter = '') {
		$this->totalRows = intval($totalRows);
		$this->parameter = $parameter;
		$this->listRows = $listRows > 0 ? $listRows : $this->listRows; //分页每页显示记录数
		$this->totalPages = ceil($this->totalRows / $this->listRows);   //总页数
		//对输入的数字进行验证
		$page = !empty($_GET[$this->p]) ? intval($_GET[$this->p]) : 1;
		if ($page <= 1) {
			$page = 1;
		} elseif ($page >= $totalRows) {
			$page = $totalRows;
		}
		$this->nowPage = $page;

		if (!empty($this->totalPages) && $this->nowPage > $this->totalPages) {
			$this->nowPage = $this->totalPages;
		}
		$this->firstRow = $this->listRows * ($this->nowPage - 1);
	}

	public function setConfig($name, $value) {
		if (isset($this->config[$name])) {
			$this->config[$name] = $value;
		}
	}

	/**
	 +----------------------------------------------------------
	 * 分页显示输出
	 +----------------------------------------------------------
	 * @access public
	 +----------------------------------------------------------
	 */
	public function show() {
		if ($this->totalRows == 0)
		return '';

		$upPage = $downPage = $theFirst = $theEnd = $linkPage = $goto = $select = ""; //初始化

		$p = $this->p; //分页跳转变量
		$nowCoolPage = ceil($this->nowPage / $this->rollPage);
		$url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '' : "?") . $this->parameter;
		$parse = parse_url($url);

		if (isset($parse['query'])) {
			parse_str($parse['query'], $params);
			unset($params[$p]);
			$url = $parse['path'] . '?' . http_build_query($params);
		}

		//上下翻页字符串
		$upRow = $this->nowPage - 1;
		$downRow = $this->nowPage + 1;
		if ($upRow > 0) {
			$upPage = "<li><a href='" . $url . "&" . $p . "=$upRow'>" . $this->config['prev'] . "</a></li>";
		}
		if ($downRow <= $this->totalPages) {
			$downPage = "<li><a href='" . $url . "&" . $p . "=$downRow'>" . $this->config['next'] . "</a></li>";
		}

		$nearPage = intval($this->rollPage / 2);
		if ($this->nowPage > ($this->rollPage - $nearPage)) {
			$theFirst = "<li><a href='$url&$p=1' >" . $this->config['first'] . "</a></li>";
		}
		if ($this->nowPage <= ($this->totalPages - $nearPage)) {
			$theEnd = "<li><a href='$url&$p=" . $this->totalPages . "'>" . $this->config['last'] . "</a></li>";
		}
		if ($this->nowPage >= ($this->totalPages - $nearPage) || $this->nowPage > ($this->rollPage - $nearPage)) {
			// 1 2 3 4 5
			$min_page = ($this->nowPage - $nearPage > 0) ? $this->nowPage - $nearPage : 1;
			$max_page = $this->nowPage + $nearPage;
			for ($page = $min_page; $page < $max_page; $page++) {
				if ($page == $this->nowPage) {
					$linkPage .= "<li class='active'><a href='#'>$page</a></li>";
				} else {
					$linkPage .= "<li><a href='$url&$p=$page'>$page</a></li>";
				}
				if ($page == $this->totalPages) {
					break;
				}
			}
		} else {
			if ($this->nowPage <= ($this->rollPage - $nearPage)) {
				// 1 2 3 4 5 6 7 8 9 10 11... 15
				for ($page = 1; $page < ($this->nowPage + $nearPage); $page++) {
					if ($page == $this->nowPage) {
						$linkPage .= "<li class='active'><a href='#'>$page</a></li>";
					} else {
						$linkPage .= "<li><a href='$url&$p=$page'>$page</a></li>";
					}
					if ($page == $this->totalPages) {
						break;
					}
				}
			}
		}


		if ($this->totalPages > 20) {
			//输入框跳转
			$goto = '<li><input class="input-mini-mini" style="text-align:center" type="text" onblur="javascript:window.location=\'' . $url . '&' . $p . '=\'+this.value" name="' . $p . '" maxlength="6"  value="' . $this->nowPage . '"></li>';
		}

		$pageStr = str_replace(
		array('%header%', '%nowPage%', '%totalRow%', '%totalPage%', '%first%', '%upPage%', '%linkPage%', '%downPage%', '%end%', '%goto%', '%select%', '%pages%'), array($this->config['header'], $this->nowPage, $this->totalRows, $this->totalPages, $theFirst, $upPage, $linkPage, $downPage, $theEnd, $goto, $select, $this->config['pages']), $this->config['theme']);
		return $pageStr;
	}

	//ajax分页控制

	public function ajaxShow() {
		if ($this->totalRows == 0)
		return '';

		$upPage = $downPage = $theFirst = $theEnd = $linkPage = $goto = $select = ""; //初始化

		$p = $this->p; //分页跳转变量
		$nowCoolPage = ceil($this->nowPage / $this->rollPage);
		$url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '' : "?") . $this->parameter;
		$parse = parse_url($url);

		if (isset($parse['query'])) {
			parse_str($parse['query'], $params);
			unset($params[$p]);
			$url = $parse['path'] . '?' . http_build_query($params);
		}
		$callback = isset($_REQUEST['callback']) ? trim($_REQUEST['callback']) : 'function (){}';
		$data = json_encode($_POST);
		//上下翻页字符串
		$upRow = $this->nowPage - 1;
		$downRow = $this->nowPage + 1;
		if ($upRow > 0) {
			$upPage = '<li><a class="btn btn-link" onclick=\'lwjsback.ajax_page("' . $url . '",' . $data . ',' . $upRow . ',' . $callback . ')\'>' . $this->config['prev'] . '</a></li>';
		}
		if ($downRow <= $this->totalPages) {
			$downPage = '<li><a class="btn btn-link" onclick=\'lwjsback.ajax_page("' . $url . '",' . $data . ',' . $downRow . ',' . $callback . ')\'>' . $this->config['next'] . '</a></li>';
		}

		$nearPage = intval($this->rollPage / 2);
		if ($this->nowPage > ($this->rollPage - $nearPage)) {
			$theFirst = '<li><a class="btn btn-link" onclick=\'lwjsback.ajax_page("' . $url . '",' . $data . ',1,' . $callback . ')\'>' . $this->config['first'] . '</a></li>';
		}
		if ($this->nowPage <= ($this->totalPages - $nearPage)) {
			$theEnd = '<li><a class="btn btn-link" onclick=\'lwjsback.ajax_page("' . $url . '",' . $data . ',' . $this->totalPages . ',' . $callback . ')\'>' . $this->config['last'] . '</a></li>';
		}
		if ($this->nowPage >= ($this->totalPages - $nearPage) || $this->nowPage > ($this->rollPage - $nearPage)) {
			// 1 2 3 4 5
			$min_page = ($this->nowPage - $nearPage > 0) ? $this->nowPage - $nearPage : 1;
			$max_page = $this->nowPage + $nearPage;
			for ($page = $min_page; $page < $max_page; $page++) {
				if ($page == $this->nowPage) {
					$linkPage .= '<li class="active"><a class="btn btn-link">' . $page . '</a></li>';
				} else {
					$linkPage .= '<li><a class="btn btn-link" onclick=\'lwjsback.ajax_page("' . $url . '",' . $data . ',' . $page . ',' . $callback . ')\'>' . $page . '</a></li>';
				}
				if ($page == $this->totalPages) {
					break;
				}
			}
		} else {
			if ($this->nowPage <= ($this->rollPage - $nearPage)) {
				// 1 2 3 4 5 6 7 8 9 10 11... 15
				for ($page = 1; $page < ($this->nowPage + $nearPage); $page++) {
					if ($page == $this->nowPage) {
						$linkPage .= '<li class="active"><a class="btn btn-link">' . $page . '</a></li>';
					} else {
						$linkPage .= '<li><a class="btn btn-link" onclick=\'lwjsback.ajax_page("' . $url . '",' . $data . ',' . $page . ',' . $callback . ')\'>' . $page . '</a></li>';
					}
					if ($page == $this->totalPages) {
						break;
					}
				}
			}
		}
		$goto = '';

		$pageStr = str_replace(
		array('%header%', '%nowPage%', '%totalRow%', '%totalPage%', '%first%', '%upPage%', '%linkPage%', '%downPage%', '%end%', '%goto%', '%select%', '%pages%'), array($this->config['header'], $this->nowPage, $this->totalRows, $this->totalPages, $theFirst, $upPage, $linkPage, $downPage, $theEnd, $goto, $select, $this->config['pages']), $this->config['theme']);
		return $pageStr;
	}

}

?>