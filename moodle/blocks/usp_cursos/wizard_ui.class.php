<?php

defined ('MOODLE_INTERNAL') || die();

require_once('wizard_controller.class.php');
require_once('wizard_ui_stage.class.php');

class wizard_ui {
       
    protected $stage;
    protected $ui_plan;
    protected $controller;
    
    final public static function load_controller($id=false) {
        if ($id) { return wizard_controller::load_controller($id); }
        return false;
    }
    
    public function save_controller() {
        //$this->controller->process_ui_event();
        $this->controller->save_controller();
        return true;
    }
    
    public function __construct($controller, array $params=null) {
        global $GLOBALS;
        $GLOBALS['_HTML_QuickForm_default_renderer'] = new WizardMoodleQuickForm_Renderer();
        
        $this->controller = $controller;
        //$this->progress = self::PROGRESS_INITIAL;
        $this->ui_plan = $this->initialise_ui_plan();
        $this->stage = $this->initialise_stage(null, $params);
    }
    
    protected function initialise_ui_plan() {
        $enrol_potential_users = $this->controller->get_enrol_potential_users();
        $value = 1; $ui_plan['STAGE_COURSE'] = $value;
//        if (isset($enrol_potential_users) && !empty($enrol_potential_users)) {
        $value *= 2; $ui_plan['STAGE_JOIN'] = $value;
//        }
//        $value *= 2; $ui_plan['STAGE_MESSAGE'] = $value;
        $value *= 2; $ui_plan['STAGE_REVIEW'] = $value;
        $value *= 2; $ui_plan['STAGE_FINAL'] = $value;
        $value *= 2; $ui_plan['STAGE_COMPLETE'] = $value;
        
        return $ui_plan;
    }

    public function get_id_from_plan($step) {
        return $this->ui_plan[$step];
    }
    
    public function get_value_from_plan($id) {
        $flip = array_flip($this->ui_plan);
        return $flip[$id];
    }
    
    public function destroy() {
        global $GLOBALS;
        if ($this->controller) {
            $this->controller->destroy();
        }
        unset($this->stage);
        $GLOBALS['_HTML_QuickForm_default_renderer'] = new MoodleQuickForm_Renderer();
    }
    
    public function initialise_stage($stage = null, array $params=null) {
        if ($stage == null) {
            $stage = optional_param('stage', $this->get_first_stage_id(), PARAM_INT);
        }
        /*if (self::$skipcurrentstage) {
            $stage *= 2;
        }*/
        switch ($stage) {
            case $this->ui_plan['STAGE_COURSE']:
                $stage = new wizard_ui_stage_initial($this, $params);
                break;
            case $this->ui_plan['STAGE_JOIN']:
                $stage = new wizard_ui_stage_join($this, $params);
                break;
//            case $this->ui_plan['STAGE_MESSAGE']:
//                $stage = new wizard_ui_stage_message($this, $params);
                break;
            case $this->ui_plan['STAGE_REVIEW']:
                $stage = new wizard_ui_stage_review($this, $params);
                break;
            case $this->ui_plan['STAGE_FINAL']:
                $stage = new wizard_ui_stage_final($this, $params);
                break;
            default:
                $stage = false;
                break;
        }
        return $stage;
    }
    
    public function get_ui_plan() {
        return $this->ui_plan;
    }
    
    public function get_progress_bar() {
        global $PAGE;
        $flip = array_flip($this->ui_plan);
        $stage = $this->ui_plan['STAGE_COMPLETE'];
        $currentstage = $this->stage->get_stage();
        $items = array();
        while ($stage > 0) {
            $classes = array('wizard_stage');
            if (floor($stage/2) == $currentstage) {
                $classes[] = 'wizard_stage_next';
            } else if ($stage == $currentstage) {
                $classes[] = 'wizard_stage_current';
            } else if ($stage < $currentstage) {
                $classes[] = 'wizard_stage_complete';
            }
            $item = array('text'=>strlen(decbin($stage)).'. '.get_string('currentstage'.$flip[$stage], 'block_usp_cursos'),
                          'class'=>implode(' ', $classes));
            if ($stage < $currentstage && $currentstage < $this->ui_plan['STAGE_COMPLETE']) {
                $params = $this->stage->get_params();
                if (empty($params)) {
                    $params = array();
                }
                $params = array_merge($params, array('wizard'=>$this->get_controllerid(), 'stage'=>$stage, 'sesskey'=>sesskey()));
                $item['link'] = new moodle_url($PAGE->url, $params);
            }
            array_unshift($items, $item);
            $stage = floor($stage/2);
        }
        return $items;
    }
    
    public function display() {
        return $this->stage->display();
    }
    
    public function process() {
        if (optional_param('previous', false, PARAM_BOOL) && $this->stage->get_stage() > $this->get_first_stage_id()) {
            $this->stage = $this->initialise_stage($this->stage->get_prev_stage(), $this->stage->get_params());
            return false;
        }
        
        // Process the stage
        $processoutcome = $this->stage->process();
        if ($processoutcome !== false) {
            if ('no_submit' == $processoutcome) {
                $this->stage = $this->initialise_stage($this->stage->get_stage(), $this->stage->get_params());
            } else {
                $this->stage = $this->initialise_stage($this->stage->get_next_stage(), $this->stage->get_params());
            }
        }
        // Process UI event after to check changes are valid
        //$this->controller->process_ui_event();
        return $processoutcome;
    }
    
    public function cancel_process() {
        redirect(new moodle_url('/'));
        die;
    }
    
    public function execute() {
        //$this->controller->finish_ui();
        $this->controller->execute_plan();
        $this->stage = new wizard_ui_stage_complete($this, $this->stage->get_params(), $this->controller->get_results());
        return true;
    }
    
    public function is_final_stage() {
        return $this->ui_plan['STAGE_FINAL'] == $this->get_stage();
    }
    
    public function get_first_stage_id() {
        return $this->ui_plan['STAGE_COURSE'];
    }
    
    public function get_controller() {
        return $this->controller;
    }
    
    public function get_stage() {
        return $this->stage->get_stage();
    }
    
    public function get_stage_name() {
        return $this->stage->get_name();
    }
   
    public function get_controllerid() {
        return $this->controller->get_id();
    }

    public function get_name() {
        return 'wizard';
    }
    
    public function get_uniqueid() {
        return $this->get_controllerid();
    }
    
}

