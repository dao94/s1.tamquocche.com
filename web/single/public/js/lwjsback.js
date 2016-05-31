/*
 * 乱舞江山后台js项目
 */
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
        if(itemcount>=10||rewardcount>=13)return false;
        return true;
    };
    _loadScript(_BasePath+'phprpc_js/phprpc_client.min.js');
    //主对象
    return {
        rewardSet:{
            reward_change:function (obj){
                var reward_type = $(obj).val();
                if(reward_type==='item'){
                    if(reward_check()){
                        $(obj).siblings('.item').html('Đạo cụ：<input type="text" name="item_info" class="span2" onkeyup="lwjsback.itemSearch.searchItem(this)"> <input type="checkbox" name="item_bind"> 非绑定');
                    }else{
                        $(obj).val('gold');
                    }
                }else{
                    $(obj).siblings('.item').html('');
                }
            },
            reward_add:function (obj){
                //首先检测奖励数量是否超出
                var li = '<li><select class="span1 reward" onchange="lwjsback.rewardSet.reward_change(this)"><option value="item">Đạo cụ</option><option value="gold">Đồng</option><option value="giftGold">Đồng khóa</option><option value="jade">NB</option><option value="giftJade">NB khóa</option></select>SL:<input type="text" class="span1" name="num"> <span class="item">Đạo cụ：<input type="text" name="item_info" class="span2" onkeyup="lwjsback.itemSearch.searchItem(this)"> <input type="checkbox" name="item_bind"> 非绑定</span> <input type="button" value="-" onclick="lwjsback.rewardSet.reward_del(this)"></li>';
                if(reward_check())$(obj).parent().append(li);
            },
            reward_del:function (obj){
                $(obj).parent('li').detach();
            }
        },
        //Đạo cụ搜索
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
            //选中数据
            selectThis:function (obj){
                var name=$(obj).attr('name'),
                id=$(obj).attr('id');
                $(obj).parents('.itemList').siblings('[name$="item_info"]').val(id+'|'+name);
                $(obj).parents('.itemList').remove();
            },
            searchItem:function(obj){
                var itemName = $.trim($(obj).val());
                $(obj).siblings('.itemList').remove();
                if(itemName.length>=3){
                    var lwjsback_client = new PHPRPC_Client('http://192.168.8.223:8102/center/app/interface/item_info.php', ['search']);
                    lwjsback_client.search(itemName,,'<{$smarty.session.__single_LANG}>', function (result, args, output, warning) {
                        if(result==false){
                            $('#result').html(KindEditor.lang('item.itemsearch')).fadeIn();
                        }else{
                            result = eval(result);
                            var  itemList = '<div class="well itemList" style="position:absolute;height:200px; max-width: 340px; padding:8px 0; overflow:scroll;"><ul class="nav nav-list">',
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

