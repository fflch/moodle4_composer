<?php

defined('MOODLE_INTERNAL') || die();

abstract class wizard_moodleform extends base_moodleform {

    public function __construct(wizard_ui_stage $uistage, $action = null, $customdata = null,
                                $method = 'post', $target= '', $attributes = null, $editable = true) {
        parent::__construct($uistage, $action, $customdata, $method, $target, $attributes, $editable);
    }
    
}

class wizard_course_form extends wizard_moodleform{

}

class wizard_join_form extends wizard_moodleform {
    
}

//class wizard_message_form extends wizard_moodleform{
//}

class wizard_confirmation_form extends wizard_moodleform {
         
}


