<?php

!defined('__ROOT__') && exit('forbid');
//smarty配置文件
include __LIB__ . 'Smarty-3.1.7/Smarty.class.php';
include __LIB__ . 'Smarty-3.1.7/plugins/block.t.php';

class MySmarty extends Smarty {

	public function display($template = null, $cache_id = null, $compile_id = null, $parent = null) {
		if (empty($template)) {
			$phpFileName = basename($_SERVER['PHP_SELF']);
			$template = str_replace('.php', '.html', $phpFileName);
		}
		$this->fetch($template, $cache_id, $compile_id, $parent, true);
	}

}

$smarty = new MySmarty();
$smarty->compile_dir = __RUNTIME__ . 'templetes_c';
$smarty->cache_dir = __RUNTIME__ . 'cache';
$smarty->left_delimiter = '<{';
$smarty->right_delimiter = '}>';
$smarty->registerPlugin('block', 't', 'smarty_block_t');
?>