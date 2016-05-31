/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//添加货币插件
KindEditor.plugin('money', function(K) {
    var self = this, name = 'money';
    self.plugin.money = function() {
        var lang = self.lang(name+'.'),
        html = '<div style="padding:20px;">' +
        //货币类型列表
        '<div class="ke-dialog-row">' +
        lang.moneytype+'：' +
        '<select name="ke_money_type">'+
        '<option value="1">'+lang.money1+'</option>'+
        '<option value="2">'+lang.money2+'</option>'+
        '<option value="3">'+lang.money3+'</option>'+
        '<option value="4">'+lang.money4+'</option>'+
        '</select><br/>'+
        lang.moneynum+'：' +
        '<input type="text" name="ke_money_num" style="width:100px;"/>'+
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
                    var type = K.trim(money_type.val()),
                    num = parseInt(K.trim(money_num.val()));
                    if(num<=0||isNaN(num)){
                        alert(lang.numrequired);
                    }else{
                        self.hideDialog().insertHtml('[money type="'+type+'" num="'+num+'" name="'+lang['money'+type]+'"][/money]<br>');
                    } 
                }
            }
        }),
        div = dialog.div,
        money_type = K('select[name="ke_money_type"]', div),
        money_num = K('input[name="ke_money_num"]', div);
    }
    self.clickToolbar(name,self.plugin.money);
});  
