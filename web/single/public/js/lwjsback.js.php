<?php

/*
 * 乱舞江山后台js项目
 */
define('__ROOT__', str_replace(array('//', '\\'), array('/', '/'), dirname(dirname(__DIR__))));
include __ROOT__.'/config/config.php';
include __AUTH__ . 'lang.php';
//js中的语言包以及中央服配置
$conf_center = CENTER_DOMAIN;
$conf_item = __('道具');
$conf_bind = __('绑定');
$conf_unbind = __('非绑定');
$conf_num = __('数量');
$conf_gold = __('铜币');
$conf_giftGold = __('铜券');
$conf_jade = __('元宝');
$conf_giftJade = __('礼券');

$js = <<<JS
var lwjsback = (function(){
    var _BasePath = _getBasePath(),
    _QUIRKS = document.compatMode != 'CSS1Compat';
    function _getBasePath() {
        var els = document.getElementsByTagName('script'), src;
        for (var i = 0, len = els.length; i < len; i++) {
            src = els[i].src || '';
            if (/lwjsback[\w\-\.]*\.js/.test(src)) {
                return src.substring(0, src.lastIndexOf('/') + 1);
            }
        }
        return '';
    };
    //私有函数自动加载js文件
    function _loadScript(url, fn) {
        var head = document.getElementsByTagName('head')[0] || (_QUIRKS ? document.body : document.documentElement),
        script = document.createElement('script');
        head.appendChild(script);
        script.src = url;
        script.charset = 'utf-8';
        script.onload = script.onreadystatechange = function() {
            if (!this.readyState || this.readyState === 'loaded') {
                if (fn) {
                    fn();
                }
                script.onload = script.onreadystatechange = null;
                head.removeChild(script);
            }
        };
    };
    function reward_check (){
        var itemcount = 0,
        rewardcount = 0;
        $('.reward').each(function(i){
            rewardcount++
            if($(this).val()==='item'){
                itemcount++;
            };
        })
        itemcount += (rewardcount>itemcount&&itemcount!=0?1:0);
        if(itemcount>=11||rewardcount>=13)return false;
        return true;
    };
    _loadScript(_BasePath+'phprpc_js/phprpc_client.min.js');
    //主对象
    return {
        //ajax分页 url:请求的url data:post数据 p:页 callback:success回调函数主要用来刷新分页页面
        ajax_page:function (url,data,p,callback){
            $.ajax({
                url:url+'&p='+p,
                type:'post',
                data:data,
                dataType:'json',
                success:callback
            })
        },
        rewardSet:{
            reward_change:function (obj){
                var reward_type = $(obj).val();
                if(reward_type==='item'){
                    if(reward_check()){
                        var input ='$conf_item'+
                                    '：<input type="text" name="item_info" class="span3" onkeyup="lwjsback.itemSearch.searchItem(this)"> <input type="checkbox" name="item_bind"> '+
                                   '$conf_unbind';
                        $(obj).siblings('.item').html(input);
                    }else{
                        $(obj).val('gold');
                    }
                }else{
                    $(obj).siblings('.item').html('');
                }
            },
            reward_add:function (obj){
                //首先检测奖励数量是否超出
                var li = '<li style="margin:5px 0;"><select class="span1 reward" onchange="lwjsback.rewardSet.reward_change(this)"><option value="item">'+
                         '$conf_item'+
                         '</option><option value="gold">'+
                         '$conf_gold'+
                         '</option><option value="giftGold">'+
                         '$conf_giftGold'+
                         '</option><option value="jade">'+
                         '$conf_jade'+
                         '</option><option value="giftJade">'+
                         '$conf_giftJade'+
                         '</option></select> '+
                         '$conf_num'+
                         '：<input type="text" class="span1" name="num"> <span class="item">'+
                         '$conf_item'+
                         '：<input type="text" name="item_info" class="span3" onkeyup="lwjsback.itemSearch.searchItem(this)"> <input type="checkbox" name="item_bind"> '+
                         '$conf_unbind'+
                         '</span> <button type="button" class="btn btn-mini" onclick="lwjsback.rewardSet.reward_del(this)"><i class="icon-minus"></i></button></li>';
                if(reward_check())$(obj).parents('ul').append(li);
            },
            reward_del:function (obj){
                $(obj).parent('li').detach();
            }
        },
        //道具搜索
        itemSearch:{
            //失去焦点时清除结果
            clearList:function(obj){
                $(obj).siblings('.itemList').remove();
            },
            //添加样式
            addCss:function(obj){
                $(obj).addClass('over');
            },
            //Xóa样式
            delCss:function (obj){
                $(obj).removeClass();
            },
            //选中数据选择id+name
            selectThis:function (obj){
                var name=$(obj).attr('name'),
                id=$(obj).attr('id');
                $(obj).parents('.itemList').siblings('[name$="item_info"]').val(id+'|'+name);
                $(obj).parents('.itemList').remove();
            },
            //搜索道具id+name
            searchItem:function(obj){
                var itemName = $.trim($(obj).val());
                $(obj).siblings('.itemList').remove();
                if(itemName.length>=2){
                    var center_host = 'http://$conf_center/center/app/interface/item_info.php';
                    var lwjsback_client = new PHPRPC_Client(center_host, ['search']);
                    lwjsback_client.search(itemName,'{$_SESSION['__'.SERVER_TYPE.'_LANG']}', function (result, args, output, warning) {
                        if(result==false){
                            $('#result').html(KindEditor.lang('item.itemsearch')).fadeIn();
                        }else{
                            result = eval(result);
                            var  itemList = '<div class="well itemList" style="position:absolute;z-index:10;height:200px; max-width: 340px; padding:8px 0; overflow:scroll;"><ul class="nav nav-list">',
                            nodata = '<li> no data !!!</li>';
                            if(result.length===0){
                                itemList += nodata;
                            }else{
                                $.each(result,function(i){
                                    itemList += '<li onmouseover="lwjsback.itemSearch.addCss(this)" onmouseout="lwjsback.itemSearch.delCss(this)" onmousedown="lwjsback.itemSearch.selectThis(this)" id="'+result[i][0]+'" name="'+result[i][1]+'">'+result[i][1]+result[i][0]+'</li>'
                                })
                            }
                            itemList     += '</ul></div>';
                            $(obj).after(itemList);
                        }
                    }, true);
                }
            }
        }
    }
})();
JS;
echo $js;
?>