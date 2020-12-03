<?php

defined ('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/forms.lib.php');

abstract class base_moodleform extends moodleform {
    
    protected $uistage = null;
    
    protected $coursediv = false;
    protected $sectiondiv = false;
    protected $activitydiv = false;
    
    function __construct(base_ui_stage $uistage, $action=null, $customdata=null,
                         $method='post', $target='', $attributes=null, $editable=true) {
        $this->uistage = $uistage;
        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }
    
    function definition() {
        $ui = $this->uistage->get_ui();
        $mform = $this->_form;
        $stage = $mform->addElement('hidden', 'stage', $this->uistage->get_stage());
        $stage = $mform->addElement('hidden', $ui->get_name(), $ui->get_uniqueid());
        $params = $this->uistage->get_params();
        if (is_array($params) && count($params) > 0) {
            foreach ($params as $name=>$value) {
                $stage = $mform->addElement('hidden', $name, $value);
            }
        }
    }
    
    function definition_after_data() {
        global $PAGE;
        $buttonarray = array();
        $buttonarray[] = $this->_form->createElement('submit', 'submitbutton',
                            get_string($this->uistage->get_ui()->get_name().'stage'.
                                $this->uistage->get_stage().'action', 'block_usp_cursos'),
                            array('class'=>'proceedbutton'));
        if (!$this->uistage->is_first_stage()) {
            $buttonarray[] = $this->_form->createElement('submit', 'previous',
                                get_string('previousstage','block_usp_cursos'));
        }
        $buttonarray[] = $this->_form->createElement('cancel', 'cancel',
                            get_string('cancel'), array('class'=>'confirmcancel'));
        $this->_form->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $this->_form->closeHeaderBefore('buttonar');
        
        $config = new stdClass();
        $config->title = get_string('confirmcancel', 'block_usp_cursos');
        $config->question = get_string('confirmcancelquestion', 'block_usp_cursos');
        $config->yesLabel = get_string('confirmcancelyes', 'block_usp_cursos');
        $config->noLabel = get_string('confirmcancelno', 'block_usp_cursos');
        $PAGE->requires->yui_module('moodle-usp_cursos-confirmcancel',
                            'M.block_usp_cursos.watch_cancel_button', array($config));
    }
    
    function close_task_divs() {
        if ($this->activitydiv) {
            $this->_form->addElement('html', html_writer::end_tag('div'));
            $this->activitydiv = false;
        }
        if ($this->sectiondiv) {
            $this->_form->addElement('html', html_writer::end_tag('div'));
            $this->sectiondiv = false;
        }
        if ($this->coursediv) {
            $this->_form->addElement('html', html_writer::end_tag('div'));
            $this->coursediv = false;
        }
    }
    
    function add_setting(wizard_setting $setting, base_task $task=null) {
        global $OUTPUT;
        // If the setting cant be changed
        if (!$setting->get_ui()->is_changeable() ||
            $setting->get_visibility()!=backup_setting::VISIBLE) {
            return $this->add_fixed_setting($setting, $task);
        }
        // First add the formatting for this setting
        $this->add_html_formatting($setting);
        // The call the add method with the get_element_properties array
        call_user_func_array(array($this->_form, 'addElement'),
                             $setting->get_ui()->get_element_properties($task, $OUTPUT));
        $this->_form->setDefault($setting->get_ui_name(), $setting->get_value());
        if ($setting->has_help()) {
            list($identifier, $component) = $setting->get_help(); 
            $this->_form->addHelpButton($setting->get_ui_name(), $identifier, $component);
        }
        $this->_form->addElement('html', html_writer::end_tag('div'));
        return true;
    }
    
    function add_heading($name, $text) {
        $this->_form->addElement('header', $name, $text);
    }
    
    protected function add_html_formatting(wizard_setting $setting) {
        $mform = $this->_form;
        $isincludesetting = (strpos($setting->get_name(), '_include')!==false);
        if ($isincludesetting && $setting->get_level() != wizard_setting::ROOT_LEVEL) {
            switch ($setting->get_level()) {
                case wizard_setting::COURSE_LEVEL:
                    if ($this->activitydiv) {
                        $mform->addElement('html', html_writer::end_tag('div'));
                        $this->activitydiv = false;
                    }
                    if ($this->sectiondiv) {
                        $mform->addElement('html', html_writer::end_tag('div'));
                        $this->sectiondiv = false;
                    }
                    if ($this->coursediv) {
                        $this->addElement('html', html_writer::end_tag('div'));
                    }
                    $mform->addElement('html', html_writer::start_tag('div',
                                array('class'=>'grouped_settings course_level')));
                    $mform->addElement('html', html_writer::start_tag('div',
                                array('class'=>'include_setting course_level')));
                    $this->coursediv = true;
                    break;
                case wizard_setting::SECTION_LEVEL:
                    if ($this->activitydiv) {
                        $mform->addElement('html', html_writer::end_tag('div'));
                        $this->activitydiv = false;
                    }
                    if ($this->sectiondiv) {
                        $mform->addElement('html', html_writer::end_tag('div'));
                    }
                    $mform->addElement('html', html_writer::start_tag('div',
                                array('class'=>'grouped_settings section_level')));
                    $mform->addElement('html', html_writer::start_tag('div',
                                array('class'=>'include_setting section_level')));
                    $this->sectiondiv = true;
                    break;
                case wizard_setting::ACTIVITY_LEVEL:
                    if ($this->activitydiv) {
                        $mform->addElement('html', html_writer::end_tag('div'));
                    }
                    $mform->addElement('html', html_writer::start_tag('div',
                                array('class'=>'grouped_settings activity_level')));
                    $mform->addElement('html', html_writer::start_tag('div',
                                array('class'=>'include_setting activity_level')));
                    $this->activitydiv = true;
                    break;
                default:
                    $mform->addElement('html', html_writer::start_tag('div',
                                array('class'=>'normal_setting')));
                    break;
            }
        } else if ($setting->get_level() == wizard_setting::ROOT_LEVEL) {
            $mform->addElement('html', html_writer::start_tag('div',
                        array('class'=>'root_setting')));
        } else {
            $mform->addElement('html', html_writer::start_tag('div',
                        array('class'=>'normal_setting')));
        }
    }
    
    function add_fixed_setting(wizard_setting $setting, base_task $task) {
        global $OUTPUT;
        $settingui = $setting->get_ui();
        if ($setting->get_visibility() == backup_setting::VISIBLE) {
            $this->add_html_formatting($setting);
            switch ($setting->get_status()) {
                case wizard_setting::LOCKED_BY_PERMISSION:
                    $icon = ' '.$OUTPUT->pix_icon('i/permissionlock',
                            get_string('lockedbypermission', 'block_usp_cursos'),
                            'moodle', array('class'=>'smallicon lockedicon permissionlock'));
                    break;
                case wizard_setting::LOCKED_BY_CONFIG:
                    $icon = ' '.$OUTPUT->pix_icon('i/configlock',
                            get_string('lockedbyconfig', 'block_usp_cursos'),
                            'moodle', array('class'=>'smallicon lockedicon configlock'));
                    break;
                case wizard_setting::LOCKED_BY_HIERARCHY:
                    $icon = ' '.$OUTPUT->pix_icon('i/hierarchylock',
                            get_string('lockedbyhierarchy', 'block_usp_cursos'),
                            'moodle', array('class'=>'smallicon lockedicon configlock'));
                    break;
                default:
                    $icon = '';
                    break;
            }
            $label = $settingui->get_label($task);
            $labelicon = $settingui->get_icon();
            if (!empty($labelicon)) {
                $label .= '&nbsp;'.$OUTPUT->render($labelicon);
            }
            $this->_form->addElement('static', 'static_'.$settingui->get_name(),
                                     $label, $settingui->get_static_value().$icon);
            $this->_form->addElement('html', html_writer::end_tag('div'));
        }
        $this->_form->addElement('hidden', $settingui->get_name(), $settingui->get_value());
    }
    
    function add_dependencies(wizard_setting $setting) {
        $mform = $this->_form;
        // Apply all dependencies for backup
        foreach ($setting->get_my_dependency_properties() as $key=>$dependency) {
            call_user_func_array(array($mform, 'disabledIf'), $dependency);
        }
    }
    
    /**
     * Returns true if the form was cancelled, false otherwise
     * @return bool
     */
    public function is_cancelled() {
        return (optional_param('cancel', false, PARAM_BOOL) || parent::is_cancelled());
    }

    /**
     * Removes an element from the form if it exists
     * @param string $elementname
     * @return bool
     */
    public function remove_element($elementname) {
        if ($this->_form->elementExists($elementname)) {
            return $this->_form->removeElement($elementname);
        } else {
            return false;
        }
    }

    /**
     * Gets an element from the form if it exists
     *
     * @param string $elementname
     * @return HTML_QuickForm_input|MoodleQuickForm_group
     */
    public function get_element($elementname) {
        if ($this->_form->elementExists($elementname)) {
            return $this->_form->getElement($elementname);
        } else {
            return false;
        }
    }

    /**
     * Displays the form
     */
    public function display() {
        $this->require_definition_after_data();
        parent::display();
    }
    
    /**
     * Ensures the the definition after data is loaded
     */
    public function require_definition_after_data() {
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
    }
    
}

