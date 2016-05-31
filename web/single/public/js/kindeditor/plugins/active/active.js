/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//活动容器插件
KindEditor.plugin('active', function(K) {
    var self = this, name = 'active';
    K.loadScript(K.basePath+'../My97DatePicker/WdatePicker.js');
    self.plugin.active = function() {
        var lang = self.lang(name + '.'),
        html = '<div style="padding:20px;">' +
        //活动类型
        '<div class="ke-dialog-row">' +
        lang.activeType+'：' +
        '<select name="ke_active_type">'+
        '<option value="1">'+lang.type1+'</option>'+
        '<option value="2">'+lang.type2+'</option>'+
        '<option value="3">'+lang.type3+'</option>'+
        '</select>'+
        '</div>' +
        '<div class="ke-dialog-row">' +
        lang.start+'：' +
        '<input class="ke-input-text span2" type="text" name="ke_active_start" id="ke_active_start" value="" onfocus="WdatePicker({isShowWeek:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\'})" /></div>' +
        //结束时间
        '<div class="ke-dialog-row"">' +
        lang.over+'：' +
        '<input class="ke-input-text span2" type="text" name="ke_active_over" id="ke_active_over" value="" onfocus="WdatePicker({isShowWeek:true,dateFmt:\'yyyy-MM-dd HH:mm:ss\'})" />' +
        '</div>' +
        '<div class="ke-dialog-row"">' +
        lang.span+'：' +
        '<input type="checkbox" name="ke_active_span" id="ke_active_span1">   '+
        '<label for="ke_active_span1">  '+lang.span1+'</label>'+
        '</select>'+
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
                    var type = K.trim(active_type.val()),
                    start = K.trim(active_start.val()),
                    over = K.trim(active_over.val()),
                    span = 0;
                    active_span.each(function(){
                        if(this.checked){
                            span = 1;
                            return false;
                        }
                    })
                    if(start==''||over==''){
                        alert(lang.required);
                        return;
                    }
                    self.hideDialog().insertHtml('[activity type="'+type+'" start="'+start+'" end="'+over+'" span="'+span+'"]<br>[param]<br><br>[/param]<br>[/activity]<br>').focus();
                }
            }
        }),
        div = dialog.div,
        active_type = K('select[name="ke_active_type"]', div),
        active_start = K('[name="ke_active_start"]', div),
        active_over = K('[name="ke_active_over"]', div),
        active_span = K('[name="ke_active_span"]', div);
    }
    self.clickToolbar(name, self.plugin.active);
});