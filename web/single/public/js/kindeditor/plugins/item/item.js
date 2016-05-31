/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

//添加道具插件
KindEditor.plugin('item', function(K) {
    var self = this, name = 'item';
    K.loadScript(K.basePath+'../lwjsback.js.php');
    self.plugin.item = function() {
        //获取编辑器的内容匹配是否有10个item道具了    
        var lang = self.lang(name+'.'),
        html = '<div style="padding:20px;">' +
        '<div class="ke-dialog-row">' +
        lang.iteminfo+'：' +
        '<input type="text" name="ke_item_item_info" onkeyup="lwjsback.itemSearch.searchItem(this)"><br/>'+
        lang.itemnum+'：<input type="text" name="ke_item_itemnum" class="span1"/>   '+
        '<input type="checkbox" name="ke_item_itembind" id="ke_item_itembind"><label for="ke_item_itembind">'+
        lang.itemunbind+'</label>'+
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
                    var items = K.trim(item_info.val()),
                    item = items.split('|'),
                    id = item[0],
                    name = item[1],
                    num = parseInt(K.trim(item_num.val())),
                    bind = 0;
                    item_bind.each(function(){
                        if(this.checked){
                            bind = 1;
                            return false;
                        }
                    })
                    if(id===''||id===undefined||name===''||name===undefined||num<=0||isNaN(num)){
                        alert(lang.required);
                    }else{
                        self.hideDialog().insertHtml('[item id="'+id+'" num="'+num+'" bind="'+bind+'" name="'+name+'"][/item]<br>');
                    } 
                }
            }
        }),
        div = dialog.div,
        item_info = K('input[name="ke_item_item_info"]', div),
        item_num = K('input[name="ke_item_itemnum"]', div),
        item_bind = K('input[name="ke_item_itembind"]',div);
    }
    self.clickToolbar(name,self.plugin.item);
});  
