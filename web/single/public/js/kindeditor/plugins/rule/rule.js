/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


//规则容器             
KindEditor.plugin('rule', function(K) {
    var self = this, name = 'rule';
    self.clickToolbar(name, function() {
        self.insertHtml('[rule]<br><br>[/rule]').focus();
    })
});