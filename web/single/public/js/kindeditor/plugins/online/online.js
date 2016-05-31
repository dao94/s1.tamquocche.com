/*
 * @author wangyi
 * @date 2013-05-02 05:10:27
 * 在线规则
 */

KindEditor.plugin('online', function(K) {
    var self = this, name = 'online';
    self.plugin.online = function() {
        var lang = self.lang(name + '.'),
        html = '<div style="padding:20px;">' +
        //在线类型
        '<div class="ke-dialog-row">' +
        lang.type+'：' +
        '<select name="ke_online_type"><option value="1">'+lang.type1+'</option><option value="2">'+lang.type2+'</option></select></div>' +
        //在线时长
        '<div class="ke-dialog-row"">' +
        lang.time+'：' +
        '<input class="ke-input-text span1" type="text" name="ke_online_time" value=""/> min ' +
        '</div>' +
        '</div>',
        dialog = self.createDialog({
            name : name,
            width : 450,
            title : self.lang(name),
            body : html,
            yesBtn : {
                name : self.lang('yes'),
                click : function(e) {
                    var type = K.trim(online_type.val()),
                    time = parseInt(K.trim(online_time.val()));
                    if(time<=0||isNaN(time)){
                        alert(lang.required);
                    }else{
                        self.hideDialog().insertHtml('[online type="'+type+'" time="'+time+'"][/online]<br>').focus();
                    }
                }
            }
        }),
        div = dialog.div,
        online_type = K('select[name="ke_online_type"]', div),
        online_time = K('input[name="ke_online_time"]', div);
    }
    self.clickToolbar(name, self.plugin.online);
});
