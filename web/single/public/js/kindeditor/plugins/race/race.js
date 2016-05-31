/*
 * @author wangyi
 * @date 2013-05-02 02:15:03
 * 种族
 */

KindEditor.plugin('race', function(K) {
    var self = this, name = 'race';
    self.plugin.race = function() {
        var lang = self.lang(name + '.'),
        html = '<div style="padding:20px;">' +
        //开始时间
        '<div class="ke-dialog-row">' +
        self.lang(name)+'：' +
        '<input type="checkbox" name="ke_race" value="11" id="ke_race_11"><label for="ke_race_11">'+lang.race11+'</label> <input type="checkbox" name="ke_race" value="21" id="ke_race_21"><label for="ke_race_21">'+lang.race21+'</label><input type="checkbox" name="ke_race" value="31" id="ke_race_31"><label for="ke_race_31">'+lang.race31+'</label> <input type="checkbox" name="ke_race" value="41" id="ke_race_41"><label for="ke_race_41">'+lang.race41+'</label> <input type="checkbox" id="ke_race_all"><label for="ke_race_all">'+lang.race_all+'</label> </div>' +
        '</div>',
        dialog = self.createDialog({
            name : name,
            width : 450,
            title : self.lang(name),
            body : html,
            yesBtn : {
                name : self.lang('yes'),
                click : function(e) {
                    var race = [],
                    i = 0;
                    race_box.each(function() {
                        if (this.checked) {
                            race[i] = this.value;
                            i++;
                        }
                    });
                    race = race.join();
                    if(race===''){
                        alert(lang.required);
                    }else{
                        self.insertHtml('[race id="'+race+'"]<br><br>[/race]<br>').hideDialog().focus();
                    }
                }
            }
        }),
        div = dialog.div,
        race_box = K('input[name="ke_race"]', div);
        K('#ke_race_all', div).change(function() {
            var self = this;
            race_box.each(function(){
                this.checked = self.checked;
            })
        });
    }
    self.clickToolbar(name, self.plugin.race);
});
