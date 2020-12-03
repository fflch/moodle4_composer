<?php

defined ('MOODLE_INTERNAL') || die();

require_once('wizard_moodleform.class.php');
//require_once('lib.php');

abstract class wizard_ui_stage {
    
    protected $ui;
    protected $params = null;
    protected $stage = 1;
    
    protected $stageform = null;
    
    public function __construct(wizard_ui $ui, array $params=null) {
        $this->ui = $ui;
        $this->params = $params;
    }
    
    public function get_next_stage() {
        return floor($this->stage*2);
    }
    
    public function get_prev_stage() {
        return floor($this->stage/2);
    }
    
    public function get_ui() {
        return $this->ui;
    }
    
    public function get_stage() {
        return $this->stage;
    }
    
    public function get_stage_value() {
        return $this->ui->get_value_from_plan($this->stage);
    }
    
    public function get_params() {
        return $this->params;
    }
    
    public function get_name() {
        return get_string('currentstage'.$this->get_stage_value($this->stage), 'block_usp_cursos');
    }
    
    public function display() {
        $form = $this->initialise_stage_form();
        flush();
        ob_start();
        $form->display();
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
    
    public function is_first_stage() {
        return $this->stage == 1;
    }
        
    abstract protected function initialise_stage_form();
    
    // wizard_moodleform extends moodleform
    abstract public function process(wizard_moodleform $form=null);
    
}

/**
 * Class to show course information
 **/
class wizard_ui_stage_initial extends wizard_ui_stage {
    
    public function __construct(wizard_ui $ui, array $params=null) {
        $this->stage = $ui->get_id_from_plan('STAGE_COURSE');
        parent::__construct($ui, $params);
    }
    
    protected function initialise_stage_form() {
        global $PAGE;
        if ($this->stageform === null) {
            $controller = $this->ui->get_controller();
            $form = new wizard_course_form($this, $PAGE->url, $controller->get_courseinfo());
            // TODO add store as a variable and iterate all tasks by reference
            $this->stageform = $form;
        }
        return $this->stageform;
    }
    
    public function process(wizard_moodleform $form=null) {
        global $CFG, $DB;
        $form = $this->initialise_stage_form();
        if ($form->is_cancelled()) {
            $this->ui->cancel_process();
        }
        // no submit
        if ($courseid = optional_param('joinid', false, PARAM_INT)) {
            $config = get_config('block_usp_cursos');
            
            $controller = $this->ui->get_controller();
            $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
            $course->{$config->localcoursefield} = $course->{$config->localcoursefield}.', '.$controller->get_codmoodle();
            
            $category =  $DB->get_record('course_categories', array('id'=>$course->category), '*', MUST_EXIST);
            $editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true);
            $editoroptions['context'] = context_coursecat::instance($category->id);
            $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
            
            $controller->set_data($course, 'course');
            $controller->set_data($category, 'category');
            $controller->set_data($editoroptions, 'editoroptions');
            return 'no_submit';
        }
        // submit
        if (confirm_sesskey() && $form->is_submitted() && $data = $form->get_data()) {
            $category =  $DB->get_record('course_categories', array('id'=>$data->category), '*', MUST_EXIST);
            $editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true);
            $editoroptions['context'] = context_coursecat::instance($category->id);
            
            $controller = $this->ui->get_controller();
            $controller->set_data($data, 'course');
            $controller->set_data($category, 'category');
            $controller->set_data($editoroptions, 'editoroptions');
            return $data;
        } else {
            return false;
        }
    }
    
}

/**
 * Class to show users enrolments
 **/
class wizard_ui_stage_join extends wizard_ui_stage {
    
    public function __construct(wizard_ui $ui, array $params=null) {
        $this->stage = $ui->get_id_from_plan('STAGE_JOIN');
        parent::__construct($ui, $params);
    }
    
    protected function initialise_stage_form() {
        global $PAGE;
        if ($this->stageform === null) {
            $controller = $this->ui->get_controller();
            $course = $controller->get_data('course');
            $codmoodle= $controller->get_codmoodle();
            $form = new wizard_join_form($this, $PAGE->url, Array('course' => $course, 'codmoodle' => $codmoodle, 'stage' => $this->stage));
            // TODO add store as a variable and iterate all tasks by reference
            $this->stageform = $form;
        }
        return $this->stageform;
    }
    
    public function process(wizard_moodleform $form=null) {
        global $CFG, $DB;
        $form = $this->initialise_stage_form();
        if ($form->is_cancelled()) {
            $this->ui->cancel_process();
        }
        // submit
        if (confirm_sesskey() && $form->is_submitted() && $data = $form->get_data()) {
            $controller = $this->ui->get_controller();
            $controller->set_data($data, 'joincourses');
            if(sizeof($data->joincourses) > 1) {
                $coursedata = $controller->get_data('course');
                $shortname = $coursedata->shortname;
                $parts = explode("-",$shortname);
                if(sizeof($parts) > 2) {
                    $newshortname = $parts[0]."-".$parts[2];
                    if(!$DB->record_exists('course',array('shortname'=>$newshortname))){
                        $coursedata->shortname = $newshortname;
                        $controller->set_data($coursedata,'course');
                    }
                }
            }
            return $data;
        } else {
            return false;
        }
    }
    
}

/**
 * Class to Review Stages
 **/
class wizard_ui_stage_review extends wizard_ui_stage {

    public function __construct(wizard_ui $ui, array $params=null) {
        $this->stage = $ui->get_id_from_plan('STAGE_REVIEW');
        parent::__construct($ui, $params);
    }

    protected function initialise_stage_form() {
        global $PAGE;
        if ($this->stageform === null) {
            $controller = $this->ui->get_controller();
            $course = $controller->get_data('course');
            $joincourses= $controller->get_data('joincourses');
            $form = new wizard_review_form($this, $PAGE->url, Array('course' => $course, 'joincourses' => $joincourses, 'stage' => $this->stage));
            // TODO add store as a variable and iterate all tasks by reference
            $this->stageform = $form;
        }
        return $this->stageform;
    }

    public function process(wizard_moodleform $form=null) {
        $form = $this->initialise_stage_form();
        if ($form->is_cancelled()) {
            $this->ui->cancel_process();
        }
        // no submit
        if ($form->no_submit_button_pressed()) {
            $controller = $this->ui->get_controller();
            return 'no_submit';
        }
        // submit
        if (confirm_sesskey() && $form->is_submitted() && $data = $form->get_data()) {
            $controller = $this->ui->get_controller();
            return $data;
        } else {
            return false;
        }
    }

}

/**
 * Class to final stage dialog
 **/
class wizard_ui_stage_final extends wizard_ui_stage {
    
    public function __construct(wizard_ui $ui, array $params=null) {
        $this->stage = $ui->get_id_from_plan('STAGE_FINAL');
        parent::__construct($ui, $params);
    }

    protected function initialise_stage_form() {
        throw new moodle_exception('wizard_ui_must_execute_first');
    }

    public function process(wizard_moodleform $form=null) {
        return true;
    }

    public function display() {
        throw new moodle_exception('wizard_ui_must_execute_first');
    }
    
}

/**
 * Class to complete users messages
 **/
class wizard_ui_stage_complete extends wizard_ui_stage_final {
    
    protected $results;
    
    public function __construct(wizard_ui $ui, array $params=null, array $results=null) {
        $this->results = $results;
        parent::__construct($ui, $params);
        $this->stage = $ui->get_id_from_plan('STAGE_COMPLETE');
    }

    //TODO working in this part 
    public function display() {
        global $OUTPUT;
        $controller = $this->ui->get_controller();
        $course = $controller->get_data('course');
        echo $OUTPUT->box_start();
        echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id'=>$course->id)));
        echo $OUTPUT->box_end();
    }
    
}

