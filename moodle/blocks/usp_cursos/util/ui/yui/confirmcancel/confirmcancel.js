YUI.add('moodle-usp_cursos', function(Y) {

M.block_usp_cursos.watch_cancel_buttons = function(config) {
    Y.all('.confirmcancel').each(function() {
        this._confirmationListener = this._confirmationListener || this.on('click', function(e) {
            e.preventDefault();
            var confirm = new M.core.confirm(config);
            confirm.on('complete-yes', function(e) {
                this._confirmationListener.detach();
                this.simulate('click');
            }, this);
            confirm.show();
        });
    });
};

}, '@VERSION@', {'requires':['base','node','node-event-simulate','moodle-enrol-notification']});

