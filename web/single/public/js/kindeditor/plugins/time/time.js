/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//时间容器
KindEditor.plugin('time', function(K) {
    var self = this, name = 'time';
    K.loadScript(K.basePath+'../My97DatePicker/WdatePicker.js');
    self.plugin.time = function() {
        var lang = self.lang(name + '.'),
        html = '<div style="padding:20px;">' +
        //开始时间
        '<div class="ke-dialog-row">' +
        lang.start+'：' +
        '<input class="ke-input-text" type="text" name="ke_time_start" value="" style="width:260px;" onfocus="WdatePicker({isShowWeek:true})" /></div>' +
        //结束时间
        '<div class="ke-dialog-row"">' +
        lang.end+'：' +
        '<input class="ke-input-text" type="text" name="ke_time_end" value="" style="width:260px;" onfocus="WdatePicker({isShowWeek:true})" />' +
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
                    var start = K.trim(time_start.val());
                    var end = K.trim(time_end.val());
                    if(start===''||end===''){
                        alert(lang.required);
                    }else{
                        self.hideDialog().insertHtml('[time start="'+start+'" end="'+end+'"][/time]').focus();
                    }
                }
            }
        }),
        div = dialog.div,
        time_start = K('input[name="ke_time_start"]', div),
        time_end = K('input[name="ke_time_end"]', div);
    }
    self.clickToolbar(name, self.plugin.time);
});
