/*
 * @author wangyi
 * @date 2013-05-02 05:10:46
 * 等级限制
 */
KindEditor.plugin('level', function(K) {
    var self = this, name = 'level';
    self.plugin.level = function() {
        var lang = self.lang(name + '.'),
        html = '<div style="padding:20px;">' +
        '<div class="ke-dialog-row">' +
        self.lang(name)+'：' +
        '<input class="ke-input-text span1" type="text" name="ke_level_min"/> ~ ' +
        '<input class="ke-input-text span1" type="text" name="ke_level_max"/>' +
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
                    var lv_min = parseInt(K.trim(level_min.val())),
                    lv_max = parseInt(K.trim(level_max.val()));
                    if(lv_min<1||lv_max>200||lv_min>lv_max){
                        alert(lang.required);
                    }else{
                        self.hideDialog().insertHtml('[level min="'+lv_min+'" max="'+lv_max+'"][/level]<br>').focus();
                    }
                }
            }
        }),
        div = dialog.div,
        level_min = K('input[name="ke_level_min"]', div),
        level_max = K('input[name="ke_level_max"]', div);
    }
    self.clickToolbar(name, self.plugin.level);
});
