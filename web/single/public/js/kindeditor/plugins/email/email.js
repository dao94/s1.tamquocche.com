/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//邮件插件
KindEditor.plugin('email', function(K) {
    var self = this, name = 'email';
    self.plugin.email = function() {
        var lang = self.lang(name+'.'),
        html = '<div style="padding:20px;">' +
        //邮件标题
        '<div class="ke-dialog-row">' +
        lang.emailTitle+'<br/>' +
        '<input class="ke-input-text" type="text" name="ke_email_title" value="" style="width:260px;" /><br/>' +
        lang.emailContent +
        '<textarea style="width:270px; height:100px;" id="ke_email_content_text" name="ke_email_content"></textarea>' +
        '</div>' +
        '</div>',
        dialog = self.createDialog({
            name : name,
            width : 800,
            height: 300,
            title : self.lang(name),
            body : html,
            yesBtn : {
                name : self.lang('yes'),
                click : function(e) {
                    var title = K.trim(titleInput.val());
                    var content = e_content_editer.html();
                    if(title===''||content===''){
                        alert(lang.required);
                    }else{
                        self.hideDialog().insertHtml('[email title="'+title+'"]'+content+'[/email]');
                    } 
                }
            }
        }),
        div = dialog.div,
        titleInput = K('input[name="ke_email_title"]',div),
        contentInput = K('textarea[name="ke_email_content"]',div);
        var e_content_editer = K.create('#ke_email_content_text', {
            resizeType : 1,
            items : ['link']
        });
    }
    self.clickToolbar(name, self.plugin.email);
});

