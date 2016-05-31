//diy奖励预览
KindEditor.plugin('rewardview', function(K) {
    var self = this, name = 'rewardview';
    self.clickToolbar(name,function(){
        var reward_conent = K.trim(self.html());
        if(reward_conent===''){
            return;
        }
        K.ajax(
            K.options.pluginsPath+name+'/'+name+'.php',
            function(result){
                var lang = self.lang(name + '.'),
                html = '<div style="padding:10px 20px;"><div style="width:600px; height:400px; overflow:scroll;">' +
                result.info +
                '</div></div>',
                dialog = self.createDialog({
                    name : name,
                    width : 700,
                    height: 500,
                    title : self.lang(name),
                    body : html
                })
            },'POST',{
                reward:reward_conent
            });
    });    
});
