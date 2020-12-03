
/**
 * This file contains JS functionality required by block_usp_cursos when required.
 */

// Namespace for the form bits and bobs
M.block_usp_cursos = M.block_usp_cursos || {};

M.block_usp_cursos.initFormHiddens = function(Y, formid, hiddens) {

    // If the hiddens isn't an array or object we don't want to know about it
    if (!Y.Lang.isArray(hiddens) && !Y.Lang.isObject(hiddens)) {
        return false;
    }

    /**
     * Fixes an issue with YUI's processing method of form.elements property
     * in Internet Explorer.
     *     http://yuilibrary.com/projects/yui3/ticket/2528030
     */
    Y.Node.ATTRS.elements = {
        getter: function() {
            return Y.all(new Y.Array(this._node.elements, 0, true));
        }
    };

    // Define the hidden manager if it hasn't already been defined.
    M.form.hiddenManager = M.form.hiddenManager || (function() {
        var hiddenManager = function(config) {
            hiddenManager.superclass.constructor.apply(this, arguments);
        };
        hiddenManager.prototype = {
            _form : null,
            _depElements : [],
            _nameCollections : [],
            initializer : function(config) {
                var i = 0, nodeName;
                this._form = Y.one('#'+formid);
                for (i in hiddens) {
                    this._depElements[i] = this.elementsByName(i);
                    if (this._depElements[i].size() == 0) {
                        continue;
                    }
                    this._depElements[i].each(function(node){
                        nodeName = node.get('nodeName').toUpperCase();
                        if (nodeName == 'INPUT') {
                            if (node.getAttribute('type').match(/^(button|submit|radio|checkbox)$/)) {
                                node.on('click', this.checkDependencies, this);
                            } else {
                                node.on('blur', this.checkDependencies, this);
                            }
                            node.on('change', this.checkDependencies, this);
                        } else if (nodeName == 'SELECT') {
                            node.on('change', this.checkDependencies, this);
                        } else {
                            node.on('click', this.checkDependencies, this);
                            node.on('blur', this.checkDependencies, this);
                            node.on('change', this.checkDependencies, this);
                        }
                    }, this);
                }
                this._form.get('elements').each(function(input){
                    if (input.getAttribute('type')=='reset') {
                        input.on('click', function(){
                            this._form.reset();
                            this.checkDependencies();
                        }, this);
                    }
                }, this);

                return this.checkDependencies(null);
            },
            /**
             * Gets all elements in the form by thier name and returns a YUI NodeList
             * @return Y.NodeList
             */
            elementsByName : function(name) {
                if (!this._nameCollections[name]) {
                    var elements = [];
                    this._form.get('elements').each(function(){
                        if (this.getAttribute('name') == name) {
                            elements.push(this);
                        }
                    });
                    this._nameCollections[name] = new Y.NodeList(elements);
                }
                return this._nameCollections[name];
            },
            /**
             * Checks the hiddens the form has an makes any changes to the form that are required.
             *
             * Changes are made by functions title _hidden_{hiddentype}
             * and more can easily be introduced by defining further functions.
             */
            checkDependencies : function(e) {
                var tolock = [],
                    tohide = [],
                    hiddenon, condition, value,
                    lock, hide, checkfunction, result;
                for (hiddenon in hiddens) {
                    if (this._depElements[hiddenon].size() == 0) {
                        continue;
                    }
                    for (condition in hiddens[hiddenon]) {
                        for (value in hiddens[hiddenon][condition]) {
                            lock = false;
                            hide = false;
                            checkfunction = '_hidden_'+condition;
                            if (Y.Lang.isFunction(this[checkfunction])) {
                                result = this[checkfunction].apply(this, [this._depElements[hiddenon], value, e]);
                            } else {
                                result = this._hidden_default(this._depElements[hiddenon], value, e);
                            }
                            lock = result.lock || false;
                            hide = result.hide || false;
                            for (var ei in hiddens[hiddenon][condition][value]) {
                                var eltolock = hiddens[hiddenon][condition][value][ei];
                                if (hide) {
                                    tohide[eltolock] = true;
                                }
                                if (tolock[eltolock] != null) {
                                    tolock[eltolock] = lock || tolock[eltolock];
                                } else {
                                    tolock[eltolock] = lock;
                                }
                            }
                        }
                    }
                }
                for (var el in tolock) {
                    this._hideElement(el, tolock[el]);
                    /*if (tohide.propertyIsEnumerable(el)) {
                        this._hideElement(el, tohide[el]);
                    }*/
                }
                return true;
            },
            /**
             * Disabled all form elements with the given name
             */
            /*_disableElement : function(name, disabled) {
                var els = this.elementsByName(name);
                var form = this;
                els.each(function(){
                    if (disabled) {
                        this.setAttribute('disabled', 'disabled');
                    } else {
                        this.removeAttribute('disabled');
                    }

                    // Extra code to disable a filepicker
                    if (this.getAttribute('class') == 'filepickerhidden'){
                        var pickerbuttons = form.elementsByName(name + 'choose');
                        pickerbuttons.each(function(){
                            if (disabled){
                                this.setAttribute('disabled','disabled');
                            } else {
                                this.removeAttribute('disabled');
                            }
                        });
                    }
                })
            },*/
            /**
             * Hides all elements with the given name.
             */
            _hideElement : function(name, hidden) {
                var els = this.elementsByName(name);
                els.each(function(){
                    var e = this.ancestor('.fitem');
                    if (e) {
                        e.setStyles({
                            display : (hidden) ? 'none':''
                        })
                    }
                });
            },
            _hidden_notchecked : function(elements, value) {
                var lock = false;
                elements.each(function(){
                    if (this.getAttribute('type').toLowerCase()=='hidden' && !this.siblings('input[type=checkbox][name="' + this.get('name') + '"]').isEmpty()) {
                        // This is the hidden input that is part of an advcheckbox.
                        return;
                    }
                    if (this.getAttribute('type').toLowerCase()=='radio' && this.get('value') != value) {
                        return;
                    }
                    lock = lock || !Y.Node.getDOMNode(this).checked;
                });
                return {
                    lock : lock,
                    hide : false
                }
            },
            _hidden_checked : function(elements, value) {
                var lock = false;
                elements.each(function(){
                    if (this.getAttribute('type').toLowerCase()=='hidden' && !this.siblings('input[type=checkbox][name="' + this.get('name') + '"]').isEmpty()) {
                        // This is the hidden input that is part of an advcheckbox.
                        return;
                    }
                    if (this.getAttribute('type').toLowerCase()=='radio' && this.get('value') != value) {
                        return;
                    }
                    lock = lock || Y.Node.getDOMNode(this).checked;
                });
                return {
                    lock : lock,
                    hide : false
                }
            },
            _hidden_noitemselected : function(elements, value) {
                var lock = false;
                elements.each(function(){
                    lock = lock || this.get('selectedIndex') == -1;
                });
                return {
                    lock : lock,
                    hide : false
                }
            },
            _hidden_eq : function(elements, value) {
                var lock = false;
                var hidden_val = false;
                elements.each(function(){
                    if (this.getAttribute('type').toLowerCase()=='radio' && !Y.Node.getDOMNode(this).checked) {
                        return;
                    } else if (this.getAttribute('type').toLowerCase() == 'hidden' && !this.siblings('input[type=checkbox][name="' + this.get('name') + '"]').isEmpty()) {
                        // This is the hidden input that is part of an advcheckbox.
                        hidden_val = (this.get('value') == value);
                        return;
                    } else if (this.getAttribute('type').toLowerCase() == 'checkbox' && !Y.Node.getDOMNode(this).checked) {
                        lock = lock || hidden_val;
                        return;
                    }
                    //check for filepicker status
                    if (this.getAttribute('class').toLowerCase() == 'filepickerhidden') {
                        var elementname = this.getAttribute('name');
                        if (elementname && M.form_filepicker.instances[elementname].fileadded) {
                            lock = false;
                        } else {
                            lock = true;
                        }
                    } else {
                        lock = lock || this.get('value') == value;
                    }
                });
                return {
                    lock : lock,
                    hide : false
                }
            },
            _hidden_hide : function(elements, value) {
                return {
                    lock : false,
                    hide : true
                }
            },
            _hidden_default : function(elements, value, ev) {
                var lock = false;
                var hidden_val = false;
                elements.each(function(){
                    if (this.getAttribute('type').toLowerCase()=='radio' && !Y.Node.getDOMNode(this).checked) {
                        return;
                    } else if (this.getAttribute('type').toLowerCase() == 'hidden' && !this.siblings('input[type=checkbox][name="' + this.get('name') + '"]').isEmpty()) {
                        // This is the hidden input that is part of an advcheckbox.
                        hidden_val = (this.get('value') != value);
                        return;
                    } else if (this.getAttribute('type').toLowerCase() == 'checkbox' && !Y.Node.getDOMNode(this).checked) {
                        lock = lock || hidden_val;
                        return;
                    }
                    //check for filepicker status
                    if (this.getAttribute('class').toLowerCase() == 'filepickerhidden') {
                        var elementname = this.getAttribute('name');
                        if (elementname && M.form_filepicker.instances[elementname].fileadded) {
                            lock = true;
                        } else {
                            lock = false;
                        }
                    } else {
                        lock = lock || this.get('value') != value;
                    }
                });
                return {
                    lock : lock,
                    hide : false
                }
            }
        };
        Y.extend(hiddenManager, Y.Base, hiddenManager.prototype, {
            NAME : 'mform-hidden-manager'
        });

        return hiddenManager;
    })();

    return new M.form.hiddenManager();
};

