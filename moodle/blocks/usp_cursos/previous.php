<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

global $CFG;
require_once($CFG->libdir.'/form/submit.php');

/**
 * HTML class for a submit type element
 *
 * @author       Geiser Chalco
 * @access       public
 */
class MoodleQuickForm_previous extends MoodleQuickForm_submit {
    // {{{ constructor

    /**
     * Class constructor
     *
     * @since     1.0
     * @access    public
     * @return    void
     */
    function MoodleQuickForm_previous($elementName=null, $value=null, $attributes=null) {
        if ($elementName==null) {
            $elementName='previous';
        }
        if ($value==null) {
            $value=get_string('previous');
        }
        MoodleQuickForm_submit::MoodleQuickForm_submit($elementName, $value, $attributes);
        $this->updateAttributes(array('onclick'=>'skipClientValidation = true; return true;'));
    } //end constructor
    
    function onQuickFormEvent($event, $arg, &$caller) {
        switch ($event) {
            case 'createElement':
                $className = get_class($this);
                $this->$className($arg[0], $arg[1], $arg[2]);
                $caller->_registerCancelButton($this->getName());
                return true;
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    } // end func onQuickFormEvent
    
    function getFrozenHtml() {
        return HTML_QuickForm_submit::getFrozenHtml();
    }
    
    function freeze() {
        return HTML_QuickForm_submit::freeze();
    }
    // }}}
} //end class MoodleQuickForm_previous

