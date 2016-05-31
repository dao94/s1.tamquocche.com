<?php

/*
 * @author wangyi
 * @date 2013-04-18 03:42:37
 * 奖励编辑器解析预览--  rewardview插件调用
 */
include str_replace(array('//', '\\', 'public/js/kindeditor/plugins/rewardview'), array('/', '/', ''), __DIR__) . '/config/config.php';
include __AUTH__ . 'lang.php';

$reward_content = strip_tags($_POST['reward'], '<a>');

$activity_pattern = '#\[activity\s+type\s*=\s*"(\d+)"\s+start\s*=\s*"(.*)"\s+end\s*=\s*"(.*)"\s+span\s*=\s*"(\d+)"\s*\](.*)\[/activity\]#Us';
$matchs = preg_match_all($activity_pattern, $reward_content, $match_activitys, PREG_SET_ORDER);
include __CLASSES__ . 'Activity.class.php';
$act = new Activity();
//如果不为空则直接进入单一解析
if ($matchs == 0) {
//匹配道具奖励
    preg_match_all('#\[item\s+id\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"\s+bind\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/item\]#Us', $reward_content, $reward_item, PREG_SET_ORDER);
//匹配货币奖励
    preg_match_all('#\[money\s+type\s*=\s*"(\d+)"\s+num\s*=\s*"(\d+)"(\s+name\s*=\s*"(.*)"){0,1}\](.*)\[/money\]#Us', $reward_content, $reward_money, PREG_SET_ORDER);
    count($reward_item) + (empty($reward_money) ? 0 : 1) > 10 && ajax_return('error', __('奖励数量超出10个限制'));
//匹配奖励邮件
  /*  preg_match('#\[email\s+title\s*=\s*"(.*)"\s*\](.*)\[\/email\]#U', $reward_content, $reward_email);
    empty($reward_email) && ajax_return('error', __('没有设置奖励邮件'));
    $viewhtml = __('邮件标题') . ':' . $reward_email[1] . '<br/>';
    $viewhtml .= __('邮件内容') . ':' . $reward_email[2] . '<br/>';
   *
   */
    $viewhtml .= $act->showReward($reward_content);
    ajax_return('ok', $viewhtml);
} else {
    $viewhtml = '';

    $activity_pattern = '#\[activity\s+type\s*=\s*"(\d+)"\s+start\s*=\s*"(.*)"\s+end\s*=\s*"(.*)"\s+span\s*=\s*"(\d+)"\s*\](.*)\[/activity\]#Us';
    $matchs = preg_match_all($activity_pattern, $reward_content, $match_activitys, PREG_SET_ORDER);
    $act_info = Activity::getInfo();
    foreach ($match_activitys as $match_activity) {
        $viewhtml .= $act_info[intval($match_activity[1])]['name'] . '<br/>';
        $viewhtml .= __('Thời gian') . ':' . $match_activity[2] . '~' . $match_activity[3];
        $viewhtml .= intval($match_activity[4]) == 1 ? '【' . __('分天') . '】' : '';
        $viewhtml .= '<br/>';
        $viewhtml .= $act->showActivity($match_activity[0]);
    }
    ajax_return('ok', $viewhtml);
}
?>
