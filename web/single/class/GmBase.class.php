<?php

/**
 * @author wangyi
 * @date 2013-04-22 10:22::16
 * gm基础类 主要是gm的一下配置
 */
class GmBase {

    //自动加载配置
    protected $_loadconfig = array (
  'berpc/bg.rpc' => 
  array (
    'berpc/bg.rpc' => 'berpc/bg.rpc.php',
    'utilpb/bgutil.pb' => 'utilpb/bgutil.php',
    'utilpb/util.proto' => 'utilpb/util.php',
    'brpb/bractivity.pb' => 'brpb/bractivity.php',
  ),
  'blrpc/bllogin.rpc' => 
  array (
    'blrpc/bllogin.rpc' => 'blrpc/bllogin.rpc.php',
    'blpb/bllogin.pb' => 'blpb/bllogin.php',
    'utilpb/util.pb' => 'utilpb/util.php',
  ),
  'borpc/bo_control.rpc' => 
  array (
    'borpc/bo_control.rpc' => 'borpc/bo_control.rpc.php',
    'utilpb/bgutil.pb' => 'utilpb/bgutil.php',
    'utilpb/util.proto' => 'utilpb/util.php',
    'utilpb/util.pb' => 'utilpb/util.php',
  ),
  'borpc/boemail.rpc' => 
  array (
    'borpc/boemail.rpc' => 'borpc/boemail.rpc.php',
    'utilpb/utilemail.pb' => 'utilpb/utilemail.php',
    'utilpb/util.proto' => 'utilpb/util.php',
    'utilpb/item.proto' => 'utilpb/item.php',
  ),
  'borpc/boidentity.rpc' => 
  array (
    'borpc/boidentity.rpc' => 'borpc/boidentity.rpc.php',
    'bopb/boidentity.pb' => 'bopb/boidentity.php',
    'utilpb/util.proto' => 'utilpb/util.php',
  ),
  'brrpc/bg.rpc' => 
  array (
    'brrpc/bg.rpc' => 'brrpc/bg.rpc.php',
    'utilpb/bgutil.pb' => 'utilpb/bgutil.php',
    'utilpb/util.proto' => 'utilpb/util.php',
  ),
  'brrpc/bractivity.rpc' => 
  array (
    'brrpc/bractivity.rpc' => 'brrpc/bractivity.rpc.php',
    'brpb/bractivity.pb' => 'brpb/bractivity.php',
    'utilpb/util.proto' => 'utilpb/util.php',
    'utilpb/bgutil.pb' => 'utilpb/bgutil.php',
  ),
  'brrpc/brcard.rpc' => 
  array (
    'brrpc/brcard.rpc' => 'brrpc/brcard.rpc.php',
    'brpb/brcard.pb' => 'brpb/brcard.php',
    'utilpb/util.proto' => 'utilpb/util.php',
  ),
  'brrpc/broadcast.rpc' => 
  array (
    'brrpc/broadcast.rpc' => 'brrpc/broadcast.rpc.php',
    'brpb/broadcast.pb' => 'brpb/broadcast.php',
    'utilpb/util.proto' => 'utilpb/util.php',
  ),
  'burpc/avoid.rpc' => 
  array (
    'burpc/avoid.rpc' => 'burpc/avoid.rpc.php',
    'bupb/avoid.pb' => 'bupb/avoid.php',
    'utilpb/util.pb' => 'utilpb/util.php',
  ),
  'burpc/bg.rpc' => 
  array (
    'burpc/bg.rpc' => 'burpc/bg.rpc.php',
    'utilpb/bgutil.pb' => 'utilpb/bgutil.php',
    'utilpb/util.proto' => 'utilpb/util.php',
    'utilpb/util.pb' => 'utilpb/util.php',
  ),
  'burpc/title_achieve.rpc' => 
  array (
    'burpc/title_achieve.rpc' => 'burpc/title_achieve.rpc.php',
    'bupb/title_achieve.pb' => 'bupb/title_achieve.php',
  ),
);
    //$msg_data解析引导配置
    protected $_analyzeconfig = array (
  'blpb\\CreataAccount' => 
  array (
    'account' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'blpb\\ControlLine' => 
  array (
    'opt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bopb\\UpdateIdentity' => 
  array (
    'items' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdDouble',
    ),
  ),
  'brpb\\UpdateActivity' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'brpb\\CardStatus' => 
  array (
    'type' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'brpb\\NotifyCardGift' => 
  array (
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'cardId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'brpb\\BdcInfo' => 
  array (
    'msgId' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'state' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bupb\\Avoid' => 
  array (
    'srcList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bupb\\TitleAchieve' => 
  array (
    'charId' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'title' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'achieve' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\PavilionItem' => 
  array (
    'start' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'over' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bind' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'old' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'price' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'currency' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'total' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'limit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'limittype' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'recommend' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Pavilion' => 
  array (
    'items' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PavilionItem',
    ),
  ),
  'utilpb\\ActivityStatus' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\AllBgActivity' => 
  array (
    'status' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ActivityStatus',
    ),
  ),
  'utilpb\\RealTimeAttr' => 
  array (
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hpMax' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mpMax' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vigor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vigorMax' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\VariableAttr' => 
  array (
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vigor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'anger' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CombatAttrCombat' => 
  array (
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vigor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'anger' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'attack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'point' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'dodge' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'critical' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'criticalDf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'criticalEffect' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'criticalEffectdf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'iceAttack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'iceDefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'poisonAttack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'poisonDefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fireAttack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fireDefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'speed' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'addhp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'addmp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fighting' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'stopdf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'pulldf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'decSpeeddf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'retreatdf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'silencedf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'dizzydf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defhpper' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'damageaddper' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ignoredefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CombatAttrAll' => 
  array (
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxHp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxMp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'anger' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxAnger' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vigor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'attack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'point' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'dodge' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'critical' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'criticalDf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'criticalEffect' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'criticalEffectdf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'iceAttack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'iceDefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'poisonAttack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'poisonDefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fireAttack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fireDefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'speed' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'addhp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'addmp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxVigor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'stopdf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'pulldf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'decSpeeddf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'retreatdf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'silencedf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'dizzydf' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defhpper' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'damageaddper' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ignoredefense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BaseAttr' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'exp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxexp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\AdvancedAttr' => 
  array (
    'faction' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'position' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lover' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'teacher' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vip' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'camp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ringLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SitDownInfo' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\AdvancedStatus' => 
  array (
    'escort' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'combatStatus' => 
    array (
      0 => 'set',
      1 => 'utilpb\\IntDouble',
    ),
    'ride' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sitdown' => 
    array (
      0 => 'set',
      1 => 'utilpb\\SitDownInfo',
    ),
    'fly' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bgutilpb\\ServerInfo' => 
  array (
    'serverId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverIdx' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bgutilpb\\HotUpate' => 
  array (
    'svInfo' => 
    array (
      0 => 'set',
      1 => 'bgutilpb\\ServerInfo',
    ),
    'pathList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'bgutilpb\\ControlTime' => 
  array (
    'charId' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bgutilpb\\PayOrder' => 
  array (
    'orderId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'state' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'jade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bgutilpb\\BaiduVipInfo' => 
  array (
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vipInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdInt',
    ),
  ),
  'bgutilpb\\BgPayOrder' => 
  array (
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'jade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bgutilpb\\ResPetSkill' => 
  array (
    'skillId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'bgutilpb\\ResPullList' => 
  array (
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'attack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hisHp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hisAtack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hisDenense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bgutilpb\\ResRealm' => 
  array (
    'realmLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'blessing' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'bgutilpb\\ResPetData' => 
  array (
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'protential' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'skillList' => 
    array (
      0 => 'add',
      1 => 'bgutilpb\\ResPetSkill',
    ),
    'pullList' => 
    array (
      0 => 'set',
      1 => 'bgutilpb\\ResPullList',
    ),
    'realm' => 
    array (
      0 => 'set',
      1 => 'bgutilpb\\ResRealm',
    ),
    'modelList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'bgutilpb\\RepairPetInfo' => 
  array (
    'remark' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BoxStart' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Broadcast' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'item' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilItem',
    ),
  ),
  'utilpb\\BroadcastList' => 
  array (
    'bd' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Broadcast',
    ),
  ),
  'utilpb\\MyStoreBroadcast' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'moneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MyStoreBroadcastList' => 
  array (
    'bd' => 
    array (
      0 => 'add',
      1 => 'utilpb\\MyStoreBroadcast',
    ),
  ),
  'utilpb\\Content' => 
  array (
    'content' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PlayerInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gender' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'camp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vipLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'teamid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'position' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'identity' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'account' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'platName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Goods' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'content' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MsgClient' => 
  array (
    'channel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'content' => 
    array (
      0 => 'set',
      1 => 'utilpb\\Content',
    ),
    'goodsList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Goods',
    ),
    'otherId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'playerInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PlayerInfo',
    ),
  ),
  'utilpb\\Msg' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'channel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'playerInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PlayerInfo',
    ),
    'content' => 
    array (
      0 => 'set',
      1 => 'utilpb\\Content',
    ),
    'goodsList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Goods',
    ),
  ),
  'utilpb\\GoodUp' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gold' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'jade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'needMoneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'needMoney' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bdcFlag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\GoodDown' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\GoodBuy' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\GoodSearch' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'indexId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sort' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'page' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'pageSize' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\GoodList' => 
  array (
    'retCode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'good' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Good',
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'page' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'pageSize' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Good' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'item' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilItem',
    ),
    'gold' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'jade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'needMoneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'needMoney' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\GoodUpList' => 
  array (
    'good' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Good',
    ),
    'gold' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'jade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'record' => 
    array (
      0 => 'add',
      1 => 'utilpb\\GoodRecord',
    ),
  ),
  'utilpb\\GoodCheck' => 
  array (
    'retCode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gold' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'jade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'needMoneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'needMoney' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'item' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilItem',
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bdcFlag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\GoodRecord' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'moneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'item' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilItem',
    ),
  ),
  'utilpb\\GoodDownResult' => 
  array (
    'retCode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBMemberInfo' => 
  array (
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ready' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBTeamInfo' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'leader' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'num' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'isConfirm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'point' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'member' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CBMemberInfo',
    ),
    'score' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'platName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'session' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'honor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBCoSignUp' => 
  array (
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBSvSignUp' => 
  array (
    'member' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBMemberInfo',
    ),
    'info' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBCoSignUp',
    ),
  ),
  'utilpb\\CBRqCaptainOper' => 
  array (
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBRtCaptainOper' => 
  array (
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBSignUpInfo' => 
  array (
    'team' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'other' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'total' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'page' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'deadline' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBRecordItem' => 
  array (
    'ta' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'tb' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'res' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBPointsRace' => 
  array (
    'team' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'other' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'record' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CBRecordItem',
    ),
    'deadline' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'total' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'page' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'point' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBFinalsInfo' => 
  array (
    'stage1' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'stage2' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'stage3' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'stage4' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'stage5' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'team' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'stage' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'deadline' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'session' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'betflag' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBBetInfo',
    ),
  ),
  'utilpb\\CBChampionList' => 
  array (
    'team' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CBTeamInfo',
    ),
  ),
  'utilpb\\CBRqGetInfo' => 
  array (
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'page' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBPanelInfo' => 
  array (
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'signup' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBSignUpInfo',
    ),
    'pointrace' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBPointsRace',
    ),
    'final' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBFinalsInfo',
    ),
    'champion' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBChampionList',
    ),
  ),
  'utilpb\\CBResult' => 
  array (
    'battleId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'result' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBBattleTips' => 
  array (
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'teamA' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'teamB' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'result' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBRqGetPage' => 
  array (
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'page' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBPageInfo' => 
  array (
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'other' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CBTeamInfo',
    ),
    'total' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'page' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBRtEnterPrepare' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'deadline' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CBBetInfo' => 
  array (
    'groupId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'betType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'betIdx' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbCrossRankInfo' => 
  array (
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'honor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'userName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'platName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'killNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'platId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'objId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PbCrossTerritory' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'platform' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'platId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lastOccupyTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'state' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lastInspireCnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ownerId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'topPresidentName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'topFaction' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbCrossRankInfo',
    ),
    'rankFaction' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbCrossRankInfo',
    ),
    'rankPerson' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbCrossRankInfo',
    ),
  ),
  'utilpb\\PbCrossTerritoryInfo' => 
  array (
    'territory' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbCrossTerritory',
    ),
    'topFactionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'topObjId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PbInspireInfo' => 
  array (
    'platId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'totalNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbCrossOccupyInfo' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ownerId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PbCrossFinalRewardInfo' => 
  array (
    'rewardType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occupyInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbCrossOccupyInfo',
    ),
    'topFactionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'topObjId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PbTopPresidentInfo' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'topPresidentName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbTopPresidentInfolist' => 
  array (
    'topPresident' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbTopPresidentInfo',
    ),
  ),
  'utilpb\\OccupyInfo' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\AllOccupyInfo' => 
  array (
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\OccupyInfo',
    ),
  ),
  'utilpb\\Strong' => 
  array (
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PartListInfo' => 
  array (
    'partInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PartInfo',
    ),
    'suit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'yfSuit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'wing' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PartInfo' => 
  array (
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'strInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\StrongInfo',
    ),
    'item' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilItem',
    ),
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gemInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\GemInfo',
    ),
    'energyExp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'practiceExp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'deilyLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'carveLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fuwenIndex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fuHun' => 
    array (
      0 => 'add',
      1 => 'utilpb\\FuhunInfo',
    ),
    'deilySoul' => 
    array (
      0 => 'set',
      1 => 'utilpb\\EquipSoulInfo',
    ),
    'carveSoul' => 
    array (
      0 => 'set',
      1 => 'utilpb\\EquipSoulInfo',
    ),
  ),
  'utilpb\\GemInfo' => 
  array (
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'color' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\EquipSoulInfo' => 
  array (
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'soul' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\StrongInfo' => 
  array (
    'starLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'perfect' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BackStrong' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Upgrade' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sort' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Disintegration' => 
  array (
    'uuid' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\XiLian' => 
  array (
    'bagId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'xiLianFlag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\specialXiLian' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'materialUid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\BackXiLian' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Replace' => 
  array (
    'bagId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Refinery' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Gem' => 
  array (
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SpecialStrong' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'part' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SpecialUpgrade' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'part' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\EquipMerge' => 
  array (
    'mainUuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mainBagId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'secondUuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'secondBagId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\EquipTest' => 
  array (
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gemEnergy' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gemPractice' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Deily' => 
  array (
    'part' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flagSh' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flagBd' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flagTs' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'useBd' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'useTs' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BackDeily' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Carve' => 
  array (
    'part' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flagDk' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flagBd' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flagTs' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'useBd' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'useTs' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BackCarve' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HomeMake' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FuwenMake' => 
  array (
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FuHun' => 
  array (
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FuhunInfo' => 
  array (
    'starLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'perfect' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BackFuhun' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'oldCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'newCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'oldLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'newLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SoulMes' => 
  array (
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'autoFlag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BackSoulMessage' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'addSoul' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sClass' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbMemberInfo' => 
  array (
    'objId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    ' title' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'userName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fightScore' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'loginTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbMyFactionInfo' => 
  array (
    'members' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbMemberInfo',
    ),
    'fightScore' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'signTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'masterId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PbChangePanel' => 
  array (
    'player' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbMemberInfo',
    ),
    'member' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbMemberInfo',
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbFactionInfo' => 
  array (
    'factionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fightScore' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'cnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'winNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'loseNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'signTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'masterId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PbCombatInfo' => 
  array (
    'factions' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbFactionInfo',
    ),
  ),
  'utilpb\\PbFinalInfo' => 
  array (
    'term' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'faction1' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PbFactionInfo',
    ),
    'faction2' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PbFactionInfo',
    ),
  ),
  'utilpb\\PbCombatFactionInfo' => 
  array (
    'factionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fightScore' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'cnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'leftCnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'isWin' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbCombatingInfo' => 
  array (
    'faction1' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PbCombatFactionInfo',
    ),
    'faction2' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PbCombatFactionInfo',
    ),
  ),
  'utilpb\\ChallengeRank' => 
  array (
    'objId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HeartDevil' => 
  array (
    'lv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'list' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ChallengeRank',
    ),
  ),
  'utilpb\\PbHotSpringOp' => 
  array (
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'srcId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'desId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'opDir' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ret' => 
    array (
      0 => 'set',
      1 => 'utilpb\\RetCode',
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbHotSpringInfo' => 
  array (
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'opCnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'beOpCnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbHotSpringInfoList' => 
  array (
    'ret' => 
    array (
      0 => 'set',
      1 => 'utilpb\\RetCode',
    ),
    'hotSpringInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbHotSpringInfo',
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PbHotSpringEnd' => 
  array (
    'totalExp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'cuobeiAddition' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'teamAddition' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HumanAttr' => 
  array (
    'base' => 
    array (
      0 => 'set',
      1 => 'utilpb\\BaseAttr',
    ),
    'advanced' => 
    array (
      0 => 'set',
      1 => 'utilpb\\AdvancedAttr',
    ),
    'constattr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrAll',
    ),
    'tempattr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrAll',
    ),
  ),
  'utilpb\\HumanOfflineInfo' => 
  array (
    'equips' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PartListInfo',
    ),
    'advanced' => 
    array (
      0 => 'set',
      1 => 'utilpb\\HumanAttr',
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'idR' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'showInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\HumanShow',
    ),
    'skillInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\U2mPbSkillList',
    ),
    'title' => 
    array (
      0 => 'set',
      1 => 'utilpb\\U2mDesignation',
    ),
    'challengeLvl' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'drug' => 
    array (
      0 => 'set',
      1 => 'utilpb\\SoulDrugOffline',
    ),
  ),
  'utilpb\\U2mDesignation' => 
  array (
    'faction' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'position' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'vip' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'camp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lover' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'titles' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'titleId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SkillObj' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lastUsedTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'effects' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\U2mPbSkillList' => 
  array (
    'list' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SkillObj',
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RealtimeHumanOfflineInfo' => 
  array (
    'idR' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'idN' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RealtimeSvHumanOfflineInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'param' => 
    array (
      0 => 'set',
      1 => 'utilpb\\TableInfo',
    ),
  ),
  'utilpb\\CsHumanInfo' => 
  array (
    'humanInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\HumanOfflineInfo',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'idR' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'param' => 
    array (
      0 => 'set',
      1 => 'utilpb\\TableInfo',
    ),
  ),
  'utilpb\\SvGetHumanOffLine' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lvl' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SvSendHumanOffLine' => 
  array (
    'humanInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\HumanOfflineInfo',
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\U2cHumanOptAttr' => 
  array (
    'spirit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'contribution' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'exploit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hunsoul' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'xilianpoint' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'prestige' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'exploitExp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'leitaibi' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'totaljade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'honor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\S2cTeamInfo' => 
  array (
    'humanInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\HumanOfflineInfo',
    ),
  ),
  'utilpb\\DrugInfo' => 
  array (
    'drugId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'num' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'daylimit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SoulDrugOffline' => 
  array (
    'attr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrCombat',
    ),
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\DrugInfo',
    ),
    'soulLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Vvip' => 
  array (
    'lvl1' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lvl2' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lvl3' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FashionOpenList' => 
  array (
    'swichList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdInt',
    ),
  ),
  'utilpb\\MagicArmorInfo' => 
  array (
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\MagicArmorPart',
    ),
    'exteriorId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MagicArmorPart' => 
  array (
    'star' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'part' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'tm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'objId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\UtilItem' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bind' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'data' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'startTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'endTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\UtilMoney' => 
  array (
    'gold' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'giftGold' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'jade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'giftJade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'tradeGold' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'tradeJade' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SimpleItem' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bind' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SimpleMoney' => 
  array (
    'moneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\courtship' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\yesorno' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\divorce' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\courtshipRet' => 
  array (
    'retCode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'faction' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    ' sex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    ' level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ringType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\checkCourtShip' => 
  array (
    'ringType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\weddingToUser' => 
  array (
    ' otherId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'state' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ringLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    ' type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'commonHeart' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'otherName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'weddingLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sponsor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\weddingConInfo' => 
  array (
    'commonHeart' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'otherName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'weddingState' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ringLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'weddingTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sponsor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    ' weddingLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gifeCnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\commonUse' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\startCruise' => 
  array (
    'retCode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'giftCnt' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\mutualInfo' => 
  array (
    'toast' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flower' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bless' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fireworks' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\sendWeddingCard' => 
  array (
    'id' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\goldRankListInfo' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    ' charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\goldRankList' => 
  array (
    'list' => 
    array (
      0 => 'add',
      1 => 'utilpb\\goldRankListInfo',
    ),
    'idOne' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'idTwo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\sendWeddingGold' => 
  array (
    'moneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'moneyNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\sendWeddingGoldRet' => 
  array (
    'retCode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'moneyNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\invite' => 
  array (
    'playOne' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'playTwo' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    '	weddingLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\createScene' => 
  array (
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'playNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'man' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'woman' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'weddingLv' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\enterScene' => 
  array (
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\C2mFlyCould' => 
  array (
    'sceneName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'posX' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'posY' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MultiPkInfo' => 
  array (
    'stage' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'info' => 
    array (
      0 => 'set',
      1 => 'utilpb\\StageInfo',
    ),
  ),
  'utilpb\\StageInfo' => 
  array (
    'signUp' => 
    array (
      0 => 'set',
      1 => 'utilpb\\SignUpInfo',
    ),
    'knockout' => 
    array (
      0 => 'set',
      1 => 'utilpb\\KnockoutInfo',
    ),
    'challenge' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ChallengeInfo',
    ),
  ),
  'utilpb\\SignUpInfo' => 
  array (
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'overTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\RtSignUp' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CompetitorInfo' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'platId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\KnockoutInfo' => 
  array (
    'round' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'overTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PartitionInfo',
    ),
    'winner' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CompetitorInfo',
    ),
    'loser' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CompetitorInfo',
    ),
    'log' => 
    array (
      0 => 'set',
      1 => 'utilpb\\LogList',
    ),
  ),
  'utilpb\\PkGroup' => 
  array (
    'a' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'b' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RoundInfo' => 
  array (
    'roundIdx' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'group' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PkGroup',
    ),
  ),
  'utilpb\\PartitionInfo' => 
  array (
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\RoundInfo',
    ),
    'king' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ChallengeInfo' => 
  array (
    'stage' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'overTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'group' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ChallengeGroup',
    ),
    'winner' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CompetitorInfo',
    ),
    'loser' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CompetitorInfo',
    ),
    'log' => 
    array (
      0 => 'set',
      1 => 'utilpb\\LogList',
    ),
    'betFlag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'watchFlag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ChallengeGroup' => 
  array (
    'a' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ChallengeBet' => 
  array (
    'groupId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RankInfo' => 
  array (
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gender' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'record' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'platId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'serverId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MultiPkRank' => 
  array (
    'rank' => 
    array (
      0 => 'add',
      1 => 'utilpb\\RankInfo',
    ),
  ),
  'utilpb\\LogItem' => 
  array (
    'winner' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'loser' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\LogList' => 
  array (
    'log' => 
    array (
      0 => 'add',
      1 => 'utilpb\\LogItem',
    ),
  ),
  'utilpb\\CombatResult' => 
  array (
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'schedule' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'stage' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'competitor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'nextSchedule' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'nextStage' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'rise' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PkGroupTips' => 
  array (
    'schedule' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'stage' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'cmpA' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CompetitorInfo',
    ),
    'cmpBList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CompetitorInfo',
    ),
  ),
  'utilpb\\WatchingPk' => 
  array (
    'groupId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\EnterWatch' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'watch' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Pullulate' => 
  array (
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'attack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hpMax' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'attackMax' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defenseMax' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\BaseAttrPet' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'ownerId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'exp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxexp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'model' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'pullulate' => 
    array (
      0 => 'set',
      1 => 'utilpb\\Pullulate',
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ownerName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'protential' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'volume' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'extraProtential' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxProtential' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'isCollect' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RealmPet' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'blessing' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SkillPet' => 
  array (
    'skillId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'levelTotal' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'levelBook' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'mijiLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'slot' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'canAdd' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SkillListPet' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'skillList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SkillPet',
    ),
  ),
  'utilpb\\PetRetCode' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ClientPetInfo' => 
  array (
    'humanId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PetInfo' => 
  array (
    'base' => 
    array (
      0 => 'set',
      1 => 'utilpb\\BaseAttrPet',
    ),
    'constAttr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrAll',
    ),
    'skill' => 
    array (
      0 => 'set',
      1 => 'utilpb\\SkillListPet',
    ),
    'modelBag' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PetModelBag',
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'huntItemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ItemInfo',
    ),
    'huamnAttr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrAll',
    ),
    'newRealm' => 
    array (
      0 => 'set',
      1 => 'utilpb\\RealmPet',
    ),
    'equipBag' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PetEquipBag',
    ),
    'firstHand' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetInfoList' => 
  array (
    'ownerId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'warPetId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'petList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PetInfo',
    ),
    'requesterId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'merge' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\WarInfo' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'state' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'eCode' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'force' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\UpdateExpPet' => 
  array (
    'exp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxexp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'addexp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PetVariableAttr' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetName' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetAddPullulate' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'buy' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'protect' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'protectCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetCommonSkill' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'skillId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetCommonSkillMiji' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'skillId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetAddRealm' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'buy' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\UpdatePetRealm' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'newRealm' => 
    array (
      0 => 'set',
      1 => 'utilpb\\RealmPet',
    ),
  ),
  'utilpb\\PetAttribute' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'constattr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrAll',
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'humanAttr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrAll',
    ),
  ),
  'utilpb\\PetModelBag' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'modelList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'model' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SelectModel' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'model' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RefineModel' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'refinePetId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'skillList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'replaceGift' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SelectSkill' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'skill' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\DebugAddRealm' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RealtimePetOfflineInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'ownerId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ClientOperEgg' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'moneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'star' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\OperEggRet' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\DeletePetModel' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SoulInfo' => 
  array (
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'proficiency' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'idList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'refineCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'doubleCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'refineList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdInt',
    ),
  ),
  'utilpb\\SoulRefine' => 
  array (
    'formulaId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'moneyType' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SourceItemGroup' => 
  array (
    'group' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SoulItem',
    ),
  ),
  'utilpb\\SoulItem' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetPointList' => 
  array (
    'pointList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\petPointInfo',
    ),
  ),
  'utilpb\\petPointInfo' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'point' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MergePetSkill' => 
  array (
    'skillTy' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MergeCompareSkill' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'skillInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\MergePetSkill',
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\ReslovePetEgg' => 
  array (
    'uuid' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\ResloveEggBack' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bind' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\UtilPetInfo' => 
  array (
    'petInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\PetInfo',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CsPetFightOtherForm' => 
  array (
    'pos' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'model' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'defense' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'attack' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'skill' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'zizhi' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CsPetFightOtherInfo' => 
  array (
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'point' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'win' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fail' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'otherForm' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CsPetFightOtherForm',
    ),
    'firstHand' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ownerName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'objId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\CsPetFightOtherInfoList' => 
  array (
    'topList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CsPetFightOtherInfo',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'posterList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CsPetFigthStr',
    ),
  ),
  'utilpb\\CsPetFightTopList' => 
  array (
    'idList' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CsPetFightSimplaInfo' => 
  array (
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'objId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'petModel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CsPetFightSimplaInfoList' => 
  array (
    'infoList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CsPetFightSimplaInfo',
    ),
    'oldRank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'newRank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CsPetFigthStr' => 
  array (
    'list' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetBeChallangeRank' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'win' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetGuardInfo' => 
  array (
    'guardLevel' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bless' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IntComposeID',
    ),
  ),
  'utilpb\\ModelCollectInfo' => 
  array (
    'totalPotential' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'remainPotential' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'modelList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ModelCollect',
    ),
  ),
  'utilpb\\ModelCollect' => 
  array (
    'modelId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CommonClientPetInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'modelId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetEquipItem' => 
  array (
    'slot' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'item' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilItem',
    ),
    'useCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetEquipBag' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'itemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PetEquipItem',
    ),
  ),
  'utilpb\\PetEquipExp' => 
  array (
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'slot' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PetReplaceSkill' => 
  array (
    'slot' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bagItemUuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'replaceSkill' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\DressPetEquip' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\PetAddRank' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'slot' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'protect' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'autoBuy' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PetBagReturn' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ItemInfo' => 
  array (
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'exp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lock' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HuntingInfo' => 
  array (
    'typeList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdInt',
    ),
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'doubleCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'point' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ItemInfo',
    ),
    'lookCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'autoHuntCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HuntBagInfo' => 
  array (
    'publicBag' => 
    array (
      0 => 'set',
      1 => 'utilpb\\HuntPublicBag',
    ),
    'petList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\HuntPetBag',
    ),
  ),
  'utilpb\\HuntPublicBag' => 
  array (
    'itemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ItemInfo',
    ),
    'bagSize' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HuntPetBag' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'petItemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ItemInfo',
    ),
  ),
  'utilpb\\HuntExchangeItem' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bagIndex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'petIndex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HuntReturn' => 
  array (
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HuntRetCode' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\HuntReturn',
    ),
    'item' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdInt',
    ),
  ),
  'utilpb\\HuntPetMove' => 
  array (
    'petId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'souIndex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'desIndex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BuyHigherItem' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\HigherItemNeedInfo',
    ),
  ),
  'utilpb\\HigherItemNeedInfo' => 
  array (
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'index' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RideEquip' => 
  array (
    'uuid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'number' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'bind' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'iMsg' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'startTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lastTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RideInfo' => 
  array (
    'model' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'attr' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CombatAttrCombat',
    ),
    'skillId' => 
    array (
      0 => 'add',
      1 => 1,
    ),
    'equip' => 
    array (
      0 => 'add',
      1 => 'utilpb\\RideEquip',
    ),
  ),
  'utilpb\\RealtimeRideOfflineInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'ownerId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SvRideInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\RealtimeRideOfflineInfo',
    ),
    'info' => 
    array (
      0 => 'set',
      1 => 'utilpb\\RideInfo',
    ),
  ),
  'utilpb\\ScenePos' => 
  array (
    'x' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'y' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ScenePosVideo' => 
  array (
    'x' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'y' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SceneID' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'copyId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mapId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SceneInfo' => 
  array (
    'sceneId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\SceneID',
    ),
    'x' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'y' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SceneObjInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\SceneID',
    ),
    'x' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'y' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SceneObjInfoList' => 
  array (
    'sceneObj' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SceneObjInfo',
    ),
  ),
  'utilpb\\WalkerReport' => 
  array (
    'walkerOcc' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'left' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'total' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FbTimesReq' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FbTimesListReq' => 
  array (
    'querys' => 
    array (
      0 => 'add',
      1 => 'utilpb\\FbTimesReq',
    ),
  ),
  'utilpb\\FbTimes' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'times' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'tm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CrossBossStatus' => 
  array (
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'entryid' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CrossBossStatuses' => 
  array (
    'status' => 
    array (
      0 => 'add',
      1 => 'utilpb\\CrossBossStatus',
    ),
  ),
  'utilpb\\FbExitInfo' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'copyId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mapId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\C2oPlantOper' => 
  array (
    'landId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'oper' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'seedId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\O2cPlantFlower' => 
  array (
    'ownerId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'landId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'stealCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'totalCollectCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'plantCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'plantTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'isAberrance' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'isTeam' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'seedId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\O2cPlantFlowerList' => 
  array (
    'landList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\O2cPlantFlower',
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SelfFlowerInfo' => 
  array (
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'landId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'plantTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'seedId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\O2cSelfFlowerList' => 
  array (
    'flowerList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SelfFlowerInfo',
    ),
    'plantCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'stealCount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TrySitDownBack' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'startTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TrySitDown' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\SitDownRewards' => 
  array (
    'exp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'spirit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'time' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\OthersSitDown' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'isSit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SitDownStatus' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'count' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SvSitDownBack' => 
  array (
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CsLastMember' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'last' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\Team' => 
  array (
    'teamId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Member',
    ),
    'leader' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'applySet' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'inviteSet' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Member' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'status' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'camp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'occ' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gender' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fighting' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ready' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxHp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TeamUpdate' => 
  array (
    'tList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Team',
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MemberUpdate' => 
  array (
    'teamId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Member',
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TeamClient' => 
  array (
    'team' => 
    array (
      0 => 'set',
      1 => 'utilpb\\Team',
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ApplyTeam' => 
  array (
    'teamId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ApplySend' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\InviteSend' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\LimitSet' => 
  array (
    'applySet' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'inviteSet' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Gather' => 
  array (
    'leaderName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sceneId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sceneName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'schedule' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'x' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'y' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MemberInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'x' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'y' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mapId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'copyId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'hp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxHp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\MemberInfoList' => 
  array (
    'list' => 
    array (
      0 => 'add',
      1 => 'utilpb\\MemberInfo',
    ),
  ),
  'utilpb\\CrossTeam' => 
  array (
    'teamId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\Member',
    ),
    'leader' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'fighting' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\SimpleTeamInfo' => 
  array (
    'teamId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'leaderName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'leader' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'num' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fighting' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FbCrossLobbyInfo' => 
  array (
    'ret' => 
    array (
      0 => 'set',
      1 => 'utilpb\\RetCode',
    ),
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lobbyInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SimpleTeamInfo',
    ),
    'teamInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\CrossTeam',
    ),
  ),
  'utilpb\\FbCrossTeamSetting' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fighting' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\FbCrossChooseInfo' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'teamId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'member' => 
    array (
      0 => 'set',
      1 => 'utilpb\\Member',
    ),
  ),
  'utilpb\\FbCrossTeamCreate' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'member' => 
    array (
      0 => 'set',
      1 => 'utilpb\\Member',
    ),
    'fighting' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'minNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'maxNum' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FbCrossReplyIn' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'type' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FBCrossTeamLobbyQueryIn' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'leaderName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FbCrossInvite' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'teamId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name1' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id1' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name2' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id2' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'copyName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbTerritory' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lastFactionName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'presidentName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'lastFactionId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'presidentId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'lastOccupyTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'state' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbTerritoryInfo' => 
  array (
    'territory' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbTerritory',
    ),
    'nextOpenTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbTerritoryInside' => 
  array (
    'entryId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'endTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'curFactionName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'pos' => 
    array (
      0 => 'set',
      1 => 'utilpb\\Pos',
    ),
    'exp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'liveTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'curOccupyTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbTerritoryLiveTm' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'liveTm' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\PbTerritoryLiveTmList' => 
  array (
    'territoryLiveTm' => 
    array (
      0 => 'add',
      1 => 'utilpb\\PbTerritoryLiveTm',
    ),
  ),
  'utilpb\\WholeTitleInfo' => 
  array (
    'bossInfo' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SingleBossInfo',
    ),
    'playerInfo' => 
    array (
      0 => 'set',
      1 => 'utilpb\\SelfPlayerInfo',
    ),
  ),
  'utilpb\\SelfPlayerInfo' => 
  array (
    'coolTime' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'encourageList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\EncourageList',
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\EncourageList' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SingleBossInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'haveKill' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'owner' => 
    array (
      0 => 'set',
      1 => 'utilpb\\TitleOwner',
    ),
    'killList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\BeKillInfo',
    ),
    'encouageList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\EncourageInfo',
    ),
  ),
  'utilpb\\TitleOwner' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionName' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'factionPro' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gender' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'clothes' => 
    array (
      0 => 'set',
      1 => 'utilpb\\OwnerClothes',
    ),
    'personOcc' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\OwnerClothes' => 
  array (
    'weapon' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'trinket' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fashion' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BeKillInfo' => 
  array (
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fight' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\EncourageInfo' => 
  array (
    'rank' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'killHp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TitleChangeInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\encourageClientInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TitleCharInfo' => 
  array (
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\encourageClientInfo',
    ),
  ),
  'utilpb\\TradeToSv' => 
  array (
    'uItem' => 
    array (
      0 => 'add',
      1 => 'utilpb\\UtilItem',
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilMoney',
    ),
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TradeToClient' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'uItem' => 
    array (
      0 => 'add',
      1 => 'utilpb\\UtilItem',
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilMoney',
    ),
    'flag' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'gender' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TradeToClientList' => 
  array (
    'list' => 
    array (
      0 => 'add',
      1 => 'utilpb\\TradeList',
    ),
  ),
  'utilpb\\TradeList' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
    'name' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'level' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sex' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TradeCheck' => 
  array (
    'myUitem' => 
    array (
      0 => 'add',
      1 => 'utilpb\\UtilItem',
    ),
    'money' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilMoney',
    ),
    'otherUitem' => 
    array (
      0 => 'add',
      1 => 'utilpb\\UtilItem',
    ),
    'otherMoney' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilMoney',
    ),
  ),
  'utilpb\\SysTime' => 
  array (
    't1' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    't2' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\RetCode' => 
  array (
    'code' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CommonStr' => 
  array (
    'stringinfo' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CommonInt' => 
  array (
    'intinfo' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CommonIntArray' => 
  array (
    'ints' => 
    array (
      0 => 'add',
      1 => 1,
    ),
  ),
  'utilpb\\CommonUInt' => 
  array (
    'intinfo' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\CommonDouble' => 
  array (
    'doubleinfo' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ComposeID' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\ComposeObjID' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\KeyValue' => 
  array (
    'key' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'value' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\IntDouble' => 
  array (
    'key' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'value' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\IdValue' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'value' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\IdInt' => 
  array (
    'key' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'value' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\KeyString' => 
  array (
    'key' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'value' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\IdDouble' => 
  array (
    'key' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'value' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\KeyDouble' => 
  array (
    'key' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'value' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\TableInfo' => 
  array (
    'sname' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'iname' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'kvalue' => 
    array (
      0 => 'add',
      1 => 'utilpb\\KeyDouble',
    ),
    'kstring' => 
    array (
      0 => 'add',
      1 => 'utilpb\\KeyString',
    ),
    'iint' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdDouble',
    ),
    'ivalue' => 
    array (
      0 => 'add',
      1 => 'utilpb\\IdValue',
    ),
    't' => 
    array (
      0 => 'add',
      1 => 'utilpb\\TableInfo',
    ),
  ),
  'utilpb\\TableDefind' => 
  array (
    'svalue' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'ivalue' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    't' => 
    array (
      0 => 'set',
      1 => 'utilpb\\TableInfo',
    ),
  ),
  'utilpb\\ModifyInfo' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'info' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ModifySingle',
    ),
  ),
  'utilpb\\ModifySingle' => 
  array (
    'name' => 
    array (
      0 => 'add',
      1 => 'utilpb\\FuzzyType',
    ),
    'tvalue' => 
    array (
      0 => 'set',
      1 => 'utilpb\\TableInfo',
    ),
    'ivalue' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'svalue' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'empty' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\FuzzyType' => 
  array (
    'ivalue' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'svalue' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\HumanShow' => 
  array (
    'weapon' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'cloth' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mount' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'wing' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'fashion' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'trinket' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'sorcery' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'suit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'yfSuit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'escort' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'mSuit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\Pos' => 
  array (
    'x' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'y' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\IntComposeID' => 
  array (
    'key' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'objId' => 
    array (
      0 => 'set',
      1 => 'utilpb\\ComposeID',
    ),
  ),
  'utilpb\\EmailAddition' => 
  array (
    'contribution' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'exp' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'exploit' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'arenaPoint' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'leitaibi' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'honor' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SvEmail' => 
  array (
    'recvId' => 
    array (
      0 => 'add',
      1 => 'utilpb\\ComposeID',
    ),
    'title' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'content' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\UtilItem',
    ),
    'moneyList' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilMoney',
    ),
    'other' => 
    array (
      0 => 'set',
      1 => 'utilpb\\EmailAddition',
    ),
    'emailtype' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\SvEmailList' => 
  array (
    'email' => 
    array (
      0 => 'add',
      1 => 'utilpb\\SvEmail',
    ),
  ),
  'utilpb\\BackStageEmail' => 
  array (
    'list' => 
    array (
      0 => 'add',
      1 => 'utilpb\\charList',
    ),
    'title' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'content' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'itemList' => 
    array (
      0 => 'add',
      1 => 'utilpb\\UtilItem',
    ),
    'moneyList' => 
    array (
      0 => 'set',
      1 => 'utilpb\\UtilMoney',
    ),
    'other' => 
    array (
      0 => 'set',
      1 => 'utilpb\\EmailAddition',
    ),
  ),
  'utilpb\\charList' => 
  array (
    'charId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'emailId' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
  'utilpb\\BackStageEmailList' => 
  array (
    'email' => 
    array (
      0 => 'add',
      1 => 'utilpb\\BackStageEmail',
    ),
  ),
  'utilpb\\BackSendEmail' => 
  array (
    'id' => 
    array (
      0 => 'set',
      1 => 1,
    ),
    'state' => 
    array (
      0 => 'set',
      1 => 1,
    ),
  ),
);
    //接口配置
    protected $_interfaceconfig = array (
  'berpc/bg.rpc' => 
  array (
    'berpc\\Sour_B2eBgOper' => 
    array (
      'hotUpdate_async' => 
      array (
        0 => 'bgutilpb\\HotUpate',
      ),
      'flushPrint_async' => 
      array (
        0 => 'bgutilpb\\HotUpate',
      ),
      'b2eUpdateActivity_async' => 
      array (
        0 => 'brpb\\UpdateActivity',
      ),
    ),
  ),
  'blrpc/bllogin.rpc' => 
  array (
    'blrpc\\Sour_B2lLoginCtrl' => 
    array (
      'b2lCreateAccount_async' => 
      array (
        0 => 'blpb\\CreataAccount',
      ),
      'b2lControlLine_async' => 
      array (
        0 => 'blpb\\ControlLine',
      ),
    ),
  ),
  'borpc/bo_control.rpc' => 
  array (
    'borpc\\Sour_B2oBgOper' => 
    array (
      'controlSpeak_async' => 
      array (
        0 => 'bgutilpb\\ControlTime',
      ),
      'saveHumanData_async' => 
      array (
        0 => 'utilpb\\ComposeID',
      ),
      'gmRepairPet_async' => 
      array (
        0 => 'bgutilpb\\RepairPetInfo',
      ),
    ),
  ),
  'borpc/boemail.rpc' => 
  array (
    'borpc\\Sour_B2oEmail' => 
    array (
      'b2ocreateEmail_async' => 
      array (
        0 => 'utilpb\\BackStageEmail',
      ),
      'b2ocreateEmailList_async' => 
      array (
        0 => 'utilpb\\BackStageEmailList',
      ),
      'b2ocreateEmailListAll_async' => 
      array (
        0 => 'utilpb\\BackSendEmail',
      ),
    ),
  ),
  'borpc/boidentity.rpc' => 
  array (
    'borpc\\Sour_B2oIdentity' => 
    array (
      'changeIdentity_async' => 
      array (
        0 => 'bopb\\UpdateIdentity',
      ),
    ),
  ),
  'brrpc/bg.rpc' => 
  array (
    'brrpc\\Sour_B2rBgOper' => 
    array (
      'hotUpdate_async' => 
      array (
        0 => 'bgutilpb\\HotUpate',
      ),
      'flushPrint_async' => 
      array (
        0 => 'bgutilpb\\HotUpate',
      ),
    ),
  ),
  'brrpc/bractivity.rpc' => 
  array (
    'brrpc\\Sour_B2rActivity' => 
    array (
      'b2rUpdateActivity_async' => 
      array (
        0 => 'brpb\\UpdateActivity',
      ),
    ),
    'brrpc\\Sour_B2rBaiduVip' => 
    array (
      'b2rBaiduVipInfo_async' => 
      array (
        0 => 'bgutilpb\\BaiduVipInfo',
      ),
    ),
  ),
  'brrpc/brcard.rpc' => 
  array (
    'brrpc\\Sour_B2rCardReward' => 
    array (
      'b2rUpdateCardStatus_async' => 
      array (
        0 => 'brpb\\CardStatus',
      ),
      'b2rNotifyCardReward_async' => 
      array (
        0 => 'brpb\\NotifyCardGift',
      ),
    ),
  ),
  'brrpc/broadcast.rpc' => 
  array (
    'brrpc\\Sour_B2rBroadcast' => 
    array (
      'b2rUpdateBroadcast_async' => 
      array (
        0 => 'brpb\\BdcInfo',
      ),
    ),
  ),
  'burpc/avoid.rpc' => 
  array (
    'burpc\\Sour_B2uAvoidCtrl' => 
    array (
      'b2uAvoid_async' => 
      array (
        0 => 'bupb\\Avoid',
      ),
    ),
    'burpc\\Sour_B2uResetProtectLock' => 
    array (
      'b2uReset_async' => 
      array (
        0 => 'utilpb\\CommonDouble',
      ),
    ),
  ),
  'burpc/bg.rpc' => 
  array (
    'burpc\\Sour_B2uBgOper' => 
    array (
      'hotUpdate_async' => 
      array (
        0 => 'bgutilpb\\HotUpate',
      ),
      'controlIp_async' => 
      array (
        0 => 'utilpb\\KeyValue',
      ),
      'controlAccount_async' => 
      array (
        0 => 'bgutilpb\\ControlTime',
      ),
      'kickHuman_async' => 
      array (
        0 => 'utilpb\\CommonDouble',
      ),
    ),
    'burpc\\Sour_B2uPayOper' => 
    array (
      'payOrder_async' => 
      array (
        0 => 'bgutilpb\\PayOrder',
      ),
      'bgPayOrder_async' => 
      array (
        0 => 'bgutilpb\\BgPayOrder',
      ),
    ),
  ),
  'burpc/title_achieve.rpc' => 
  array (
    'burpc\\Sour_B2uTitleAchieve' => 
    array (
      'b2uTitleAchieve_async' => 
      array (
        0 => 'bupb\\TitleAchieve',
      ),
    ),
  ),
);
    protected $_autoload = array(
        'client.class.php' => 'Client.class.php',
        'rpclib/RpcConst.php' => 'rpclib/RpcConst.php',
        'DrSlump/Protobuf.php' => 'DrSlump/Protobuf.php'
    );

}

?>
