M.mod_datac = {};

M.mod_datac.init_view = function(Y) {
    Y.on('click', function(e) {
        Y.all('input.recordcheckbox').each(function() {
            this.set('checked', 'checked');
        });
    }, '#checkall');

    Y.on('click', function(e) {
        Y.all('input.recordcheckbox').each(function() {
            this.set('checked', '');
        });
    }, '#checknone');
};