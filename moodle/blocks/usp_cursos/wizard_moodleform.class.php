<?php

defined('MOODLE_INTERNAL') || die;

require_once('../../course/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');

class WizardMoodleQuickForm_Renderer extends MoodleQuickForm_Renderer {
    
    /**
     * This function override finishForm from moodleform
     */
    function finishForm(&$form){
        parent::finishForm($form);
        global $PAGE;
        if (!$form->isFrozen()) {
            $args = $form->getHiddenOptionObject();
            if (count($args[1]) > 0) {
                $jsmodule = array('name'=>'block_usp_cursos', 'fullpath'=>'/blocks/usp_cursos/js/module.js');
                $PAGE->requires->js_init_call('M.block_usp_cursos.initFormHiddens', $args, true, $jsmodule);
            }
        }
    }
 
}

class WizardMoodleQuickForm extends MoodleQuickForm  {

    protected $_hiddens = array();
    
    function hiddenIf($elementName, $hiddenOn, $condition = 'notchecked', $value='1') {
        if (!array_key_exists($hiddenOn, $this->_hiddens)) {
            $this->_hiddens[$hiddenOn] = array();
        }
        if (!array_key_exists($condition, $this->_hiddens[$hiddenOn])) {
            $this->_hiddens[$hiddenOn][$condition] = array();
        }
        if (!array_key_exists($value, $this->_hiddens[$hiddenOn][$condition])) {
            $this->_hiddens[$hiddenOn][$condition][$value] = array();
        }
        $this->_hiddens[$hiddenOn][$condition][$value][] = $elementName;
    }
    
    function getHiddenOptionObject() {
        $result = array();
        foreach ($this->_hiddens as $hiddenOn => $conditions) {
            $result[$hiddenOn] = array();
            foreach ($conditions as $condition=>$values) {
                $result[$hiddenOn][$condition] = array();
                foreach ($values as $value=>$hiddens) {
                    $result[$hiddenOn][$condition][$value] = array();
                    $i = 0;
                    foreach ($hiddens as $hidden) {
                        $elements = $this->_getElNamesRecursive($hidden);
                        if (empty($elements)) {
                            // probably element inside of some group
                            $elements = array($hidden);
                        }
                        foreach($elements as $element) {
                            if ($element == $hiddenOn) {
                                continue;
                            }
                            $result[$hiddenOn][$condition][$value][] = $element;
                        }
                    }
                }
            }
        }
        return array($this->getAttribute('id'), $result);
    }
   
}

abstract class wizard_moodleform extends moodleform {
    
    protected $uistage = null;
    
    function __construct(wizard_ui_stage $uistage, $action=null, $customdata=null,
                         $method='post', $target='', $attributes=null, $editable=true) {
        global $CFG;
        $this->uistage = $uistage;
        if (empty($CFG->xmlstrictheaders)) {
            // no standard mform in moodle should allow autocomplete with the exception of user signup
            // this is valid attribute in html5, sorry, we have to ignore validation errors in legacy xhtml 1.0
            if (empty($attributes)) {
                $attributes = array('autocomplete'=>'off');
            } else if (is_array($attributes)) {
                $attributes['autocomplete'] = 'off';
            } else {
                if (strpos($attributes, 'autocomplete') === false) {
                    $attributes .= ' autocomplete="off" ';
                }
            }
        }

        if (empty($action)){
            $action = strip_querystring(qualified_me());
        }
        // Assign custom data first, so that get_form_identifier can use it.
        $this->_customdata = $customdata;
        $this->_formname = $this->get_form_identifier();
        $this->_form = new WizardMoodleQuickForm($this->_formname, $method, $action, $target, $attributes);
        if (!$editable) { $this->_form->hardFreeze(); }
        $this->definition();
        $this->_form->addElement('hidden', 'sesskey', null); // automatic sesskey protection
        $this->_form->setType('sesskey', PARAM_RAW);
        $this->_form->setDefault('sesskey', sesskey());
        $this->_form->addElement('hidden', '_qf__'.$this->_formname, null);   // form submission marker
        $this->_form->setType('_qf__'.$this->_formname, PARAM_RAW);
        $this->_form->setDefault('_qf__'.$this->_formname, 1);
        $this->_form->_setDefaultRuleMessages();
        // we have to know all input types before processing submission ;-)
        $this->_process_submission($method);
    }
    
    function definition() {
        $ui = $this->uistage->get_ui();
        $mform = $this->_form;
        $stage = $mform->addElement('hidden', 'stage', $this->uistage->get_stage());
        $mform->setType('stage', PARAM_RAW);
        $stage = $mform->addElement('hidden', $ui->get_name(), $ui->get_uniqueid());
        $mform->setType($ui->get_name(), PARAM_RAW);
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
                            $this->uistage->get_stage_value().'action', 'block_usp_cursos'),
                            array('class'=>'proceedbutton'));
        if (!$this->uistage->is_first_stage()) {
            $buttonarray[] = $this->_form->createElement('submit', 'previous',
                                    get_string('previousstage','block_usp_cursos'));
        }
        $buttonarray[] = $this->_form->createElement('cancel', 'cancel', get_string('cancel'), array('class'=>'confirmcancel'));
        $this->_form->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $this->_form->closeHeaderBefore('buttonar');
        
        $config = new stdClass();
    }

    function is_cancelled(){
        $mform =& $this->_form;
        if ($mform->isSubmitted()) {
            if (optional_param('cancel', false, PARAM_RAW)) {
                return true;
            } else {
                foreach ($mform->_cancelButtons as $cancelbutton) {
                    if (optional_param($cancelbutton, 0, PARAM_RAW)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
}

/**
 * Class to show course form
 **/
class wizard_course_form extends wizard_moodleform {
    
    protected $course;
    protected $context;

    function definition() {
        parent::definition();
        global $USER, $CFG, $DB;
        $mform = $this->_form;
        
        $course        = $this->_customdata['course']; // this contains the data
        $category      = $this->_customdata['category'];
        $editoroptions = $this->_customdata['editoroptions'];
        $systemcontext   = context_system::instance();
        $categorycontext = context_coursecat::instance($category->id);
        
        if (!empty($course->id)) {
            $coursecontext = context_course::instance($course->id);
            $context = $coursecontext;
        } else {
            $coursecontext = null;
            $context = $categorycontext;
        }
        
        $courseconfig = get_config('moodlecourse');
        $methodguestdefault = 1;
        
        $blockconfig = get_config('block_usp_cursos');
        $unidadesfechadas = array_map('trim',explode("\n",$blockconfig->unidadesfechadas));
        if($unidade = explode("/",$category->path)[2]) {
            $unidadename = $DB->get_field('course_categories','name', array('id'=>$unidade));
            if(in_array($unidadename,$unidadesfechadas)) {
                $methodguestdefault = 0;
            }
        }


        $this->course  = $course;
        $this->context = $context;

        /// form definition with new course defaults
        //-----------------------------------------------------------------------
        $mform->addElement('header','general', get_string('general', 'form'));
        
        // verify permissions to change course category or keep current
        if (empty($course->id)) {
            if (has_capability('moodle/course:create', $categorycontext)) {
                $displaylist = core_course_category::make_categories_list('moodle/course:create');
                $mform->addElement('select', 'category', get_string('category'), $displaylist);
                $mform->addHelpButton('category', 'coursecategory');
                $mform->setDefault('category', $category->id);
            } else {
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $category->id);
            }
        } else {
            if (has_capability('moodle/course:changecategory', $coursecontext)) {
                $displaylist = core_course_category::make_categories_list('moodle/course:create');
                if (!isset($displaylist[$course->category])) {
                    //always keep current
                    $displaylist[$course->category] = format_string($DB->get_field('course_categories',
                                                                    'name', array('id'=>$course->category)));
                }
                $mform->addElement('select', 'category', get_string('category'), $displaylist);
                $mform->addHelpButton('category', 'coursecategory');
            } else {
                //keep current
                $mform->addElement('hidden', 'category', null);
                $mform->setType('category', PARAM_INT);
                $mform->setConstant('category', $course->category);
            }
        }
        
        $mform->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_MULTILANG);
        
        $mform->addElement('text', 'shortname', get_string('shortnamecourse'), 'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_MULTILANG);

        $mform->addElement('selectyesno', 'methodguest', get_string('methodguest', 'block_usp_cursos'));
        $mform->addHelpButton('methodguest', 'methodguesthelp', 'block_usp_cursos');
        $mform->setDefault('methodguest', $methodguestdefault);

        
        $mform->addElement('text','idnumber', get_string('idnumbercourse'),'maxlength="1000"  size="50"');
        $mform->addHelpButton('idnumber', 'idnumbercourse');
        $mform->setType('idnumber', PARAM_RAW);
        $mform->hardFreeze('idnumber');
        $mform->setConstants('idnumber', $course->idnumber);
        $mform->setAdvanced('idnumber');
        
        $mform->addElement('editor','summary_editor', get_string('coursesummary'), null, $editoroptions);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);

        $courseformats = get_plugin_list('format');
        $formcourseformats = array();
        foreach ($courseformats as $courseformat => $formatdir) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
        $mform->addHelpButton('format', 'format');
        $mform->setDefault('format', $courseconfig->format);
        $mform->setAdvanced('format');

        for ($i = 0; $i <= $courseconfig->maxsections; $i++) {
            $sectionmenu[$i] = "$i";
        }
        $mform->addElement('select', 'numsections', get_string('numberweeks'), $sectionmenu);
        $mform->setDefault('numsections', $courseconfig->numsections);
        $mform->setAdvanced('numsections');

        $mform->addElement('date_selector', 'startdate', get_string('startdate'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', time() + 3600 * 24);
        $mform->setAdvanced('startdate');

        $mform->addElement('date_selector', 'enddate', get_string('enddate'));
        $mform->addHelpButton('enddate', 'enddate');
        $mform->setDefault('endate', time() + 131*(3600*24)); # 130 dias
        $mform->setAdvanced('enddate');

        
        $choices = array();
        $choices['0'] = get_string('hiddensectionscollapsed');
        $choices['1'] = get_string('hiddensectionsinvisible');
        $mform->addElement('select', 'hiddensections', get_string('hiddensections'), $choices);
        $mform->addHelpButton('hiddensections', 'hiddensections');
        $mform->setDefault('hiddensections', $courseconfig->hiddensections);
        $mform->setAdvanced('hiddensections');
        
        $options = range(0, 10);
        $mform->addElement('select', 'newsitems', get_string('newsitemsnumber'), $options);
        $mform->addHelpButton('newsitems', 'newsitemsnumber');
        $mform->setDefault('newsitems', $courseconfig->newsitems);
        $mform->setAdvanced('newsitems');
        
        $mform->addElement('selectyesno', 'showgrades', get_string('showgrades'));
        $mform->addHelpButton('showgrades', 'showgrades');
        $mform->setDefault('showgrades', $courseconfig->showgrades);
        $mform->setAdvanced('showgrades');
        
        $mform->addElement('selectyesno', 'showreports', get_string('showreports'));
        $mform->addHelpButton('showreports', 'showreports');
        $mform->setDefault('showreports', $courseconfig->showreports);
        $mform->setAdvanced('showreports');
        
        $choices = get_max_upload_sizes($CFG->maxbytes);
        $mform->addElement('select', 'maxbytes', get_string('maximumupload'), $choices);
        $mform->addHelpButton('maxbytes', 'maximumupload');
        $mform->setDefault('maxbytes', $courseconfig->maxbytes);
        $mform->setAdvanced('maxbytes');
        
        if (!empty($course->legacyfiles) or !empty($CFG->legacyfilesinnewcourses)) {
            if (empty($course->legacyfiles)) {
                //0 or missing means no legacy files - new course or nobody turned on
                $choices = array('0'=>get_string('no'), '2'=>get_string('yes'));
            } else {
                $choices = array('1'=>get_string('no'), '2'=>get_string('yes'));
            }
            $mform->addElement('select', 'legacyfiles', get_string('courselegacyfiles'), $choices);
            $mform->addHelpButton('legacyfiles', 'courselegacyfiles');
            if (!isset($courseconfig->legacyfiles)) {
                // in case this was not initialised properly due
                // to switching of $CFG->legacyfilesinnewcourses
                $courseconfig->legacyfiles = 0;
            }
            $mform->setDefault('legacyfiles', $courseconfig->legacyfiles);
            $mform->setAdvanced('legacyfiles');
        }
        
        if (!empty($CFG->allowcoursethemes)) {
            $themeobjects = get_list_of_themes();
            $themes=array();
            $themes[''] = get_string('forceno');
            foreach ($themeobjects as $key=>$theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);
            $mform->setAdvanced('theme');
        }

        $choices = array();
        $choices[NOGROUPS] = get_string('groupsnone', 'group');
        $choices[SEPARATEGROUPS] = get_string('groupsseparate', 'group');
        $choices[VISIBLEGROUPS] = get_string('groupsvisible', 'group');
        $mform->addElement('select', 'groupmode', get_string('groupmode', 'group'), $choices);
        $mform->addHelpButton('groupmode', 'groupmode', 'group');
        $mform->setDefault('groupmode', $courseconfig->groupmode);
        $mform->setAdvanced('groupmode');

        $mform->addElement('selectyesno', 'groupmodeforce', get_string('groupmodeforce', 'group'));
        $mform->addHelpButton('groupmodeforce', 'groupmodeforce', 'group');
        $mform->setDefault('groupmodeforce', $courseconfig->groupmodeforce);
        $mform->setAdvanced('groupmodeforce');

        $mform->addElement('selectyesno', 'enablecompletion', get_string('enablecompletion', 'completion'));
        $mform->addHelpButton('enablecompletion', 'enablecompletion', 'completion');
        $mform->setDefault('enablecompletion', $courseconfig->enablecompletion);
        $mform->setAdvanced('enablecompletion');

        
        //------------------------------------------------------------------------
        enrol_course_edit_form($mform, $course, $context);
        //------------------------------------------------------------------------
        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        
        /// finally set the current form data
        //------------------------------------------------------------------------
        $this->set_data($course);
    }
    
    /// perform some extra moodle validation
    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        if ($foundcourses = $DB->get_records('course', array('shortname'=>$data['shortname']))) {
            if (!empty($data['id'])) {
                unset($foundcourses[$data['id']]);
            }
            if (!empty($foundcourses)) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }
                $foundcoursenamestring = implode(',', $foundcoursenames);
                $errors['shortname'] = get_string('shortnametaken', '', $foundcoursenamestring);
            }
        }
        
        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));
        return $errors;
    }
    
}

/**
 * Class to show enrol form
 **/
class wizard_join_form extends wizard_moodleform {

    protected $course;

    function definition() {
        parent::definition();
        global $USER, $CFG, $DB, $OUTPUT, $PAGE; // JOIN COURSE


        $course = $this->_customdata['course'];
        $codmoodle = $this->_customdata['codmoodle'];
        $stage = $this->_customdata['stage'];
        $codmoodle = explode('.', $codmoodle)[0];

        require_once('join_extension.php');
        $renderer = $PAGE->get_renderer('block_usp_cursos', 'wizard');
        $url = new moodle_url('/blocks/usp_cursos/wizard.php', Array('stage'=>$stage,'wizard'=>$course->wizard,'sesskey'=>$USER->sesskey));
        $search = new join_course_search(array('url'=>$url,'codmoodle'=>$codmoodle));
        $courses = $search->get_results();

        $mform = $this->_form;

        $mform->addElement('header', 'joinheader', get_string('join', 'block_usp_cursos').": ".$codmoodle);

        $mform->addElement('html', '<table class="generaltable"><thead><tr><th style="text-align:center;"><input type="checkbox" onchange="checkAll(this)" name="chk[]" style="margin-right: 0;"></th><th>'.get_string('class','block_usp_cursos').'</th><th>'.get_string('teachers','block_usp_cursos').'</th><th>'.get_string('moodlecourse','block_usp_cursos').'</th></tr></thead><tbody>');
        $mform->addElement('html', '
        <script type="text/javascript">
        function checkAll(ele) {
            var checkboxes = document.getElementsByTagName("input");
            if (ele.checked) {
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].type == "checkbox") {
                        checkboxes[i].checked = true;
                    }
                }
             } else {
                for (var i = 0; i < checkboxes.length; i++) {
                     if (checkboxes[i].type == "checkbox" && checkboxes[i].classList.contains("disabled")==false) {
                         checkboxes[i].checked = false;
                     }
                 }
             }
        }
        function checkClass(ele) {
            var eleid = ele.getAttribute("id").split("_");
            if (eleid[1] == "joincourses") {
                var classname = eleid[2].split(".").join(""); //EAC060712017126
            } else {
                var classname = eleid[0].split(".").join("");
            }
            var checkboxes = document.querySelectorAll(\'input[id^="id_joincourses_\'+classname+\'"]\');
            if (ele.checked) {
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].type == "checkbox") {
                        checkboxes[i].checked = true;
                    }
                }
             } else {
                for (var i = 0; i < checkboxes.length; i++) {
                     if (checkboxes[i].type == "checkbox" && checkboxes[i].classList.contains("disabled")==false) {
                         checkboxes[i].checked = false;
                     }
                 }
             }
        }
        </script>');

        foreach($courses as $course) {
            if ( $USER->idnumber == $course->codpes ) { // turmas do criador
                $key = array_search($course,$courses);
                unset($courses[$key]);
                array_unshift($courses,$course);
                
            }
        }

        foreach ($courses as $course) {
            $course->id = str_replace("'","",$course->id); // evitar problemas com professores com ' no nome
            $courselink = "";
            $parts = explode(".",$course->codmoodle);
            $codtur = end($parts);
            $mform->addElement('html', '<tr>');
            $mform->addElement('html', '<td style="text-align:center;">');

            if ( ($course->codmoodle == $this->_customdata['codmoodle']) && ($USER->idnumber == $course->codpes) ) { // turma principal do wizard
                $mform->addElement('checkbox', "joincourses[$course->id]", "","",Array("class" => "disabled", "id" => $course->id, "onchange" => "checkClass(this)"));
                $mform->addElement('html', '<script>window.onload = function(e){document.getElementById("'.$course->id.'").checked = true;checkClass(document.getElementById("'.$course->id.'"));};</script><style>.disabled{cursor: not-allowed;opacity: 0.6;pointer-events: none;-webkit-touch-callout: none;}</style>');// atributo checked não funcionou no MoodleForm
            } elseif ($course->courseid) {
                $courselink = "<a href='{$CFG->wwwroot}/course/view.php?id={$course->courseid}' target='_blank'>{$course->coursename}</a>";
                $mform->addElement('checkbox', "joincourses[$course->id]", "", "", Array("disabled"=>true, "onchange" => "checkClass(this)"));
            } else {
                $mform->addElement('checkbox', "joincourses[$course->id]", "", "", Array("onchange" => "checkClass(this)"));
            }
            $mform->addElement('html', '</td>');

            $mform->addElement('html', '<td>'.$codtur.'</td>');
            $mform->addElement('html', '<td>'.$course->nompes.'</td>');
            $mform->addElement('html', '<td>'.$courselink.'</td>');

            $mform->addElement('html', '</tr>');
        }
        $mform->addElement('html', '</tbody></table>');

        /// finally set the current form data
        //------------------------------------------------------------------------
        $this->set_data($mform);
    }
    
}

/**
 * Class to show enrol form
 **/
class wizard_review_form extends wizard_moodleform {

    protected $joincourses;
    protected $course;

    function definition() {
        parent::definition();
        global $USER, $CFG, $DB, $OUTPUT, $PAGE; // JOIN COURSE

        $mform = $this->_form;

        $course = $this->_customdata['course'];
        $joincourses = $this->_customdata['joincourses'];
        $stage = $this->_customdata['stage'];

        $html = html_writer::start_tag('div', array('class'=>'wizard-course-selector wizard-join'));
        $html .= html_writer::tag('p', '<b>'.get_string("fullnamecourse").':</b> '.$course->fullname);
        $html .= html_writer::tag('p', '<b>'.get_string("shortnamecourse").':</b> '.$course->shortname);
        $category = $DB->get_record('course_categories',array('id'=>$course->category));
        $cats = explode('/',$category->path);end($cats);$pcatid = prev($cats);
        $pcategory = $DB->get_record('course_categories',array('id'=>$pcatid));
        $html .= html_writer::tag('p', '<b>'.get_string("category").':</b> '.$pcategory->name.'/'.$category->name);
        $html .= html_writer::tag('p', '<b>'.get_string("description").':</b> '.$course->summary_editor["text"]);
        $html .= html_writer::tag('p', '<b>'.get_string("format").':</b> '.get_string("pluginname","format_$course->format"));
        $html .= html_writer::tag('p', '<b>'.get_string("numberweeks").':</b> '.$course->numsections);
        $html .= html_writer::tag('p', '<b>'.get_string("startdate").':</b> '.date("d/m/Y",$course->startdate));
        $html .= html_writer::tag('p', '<b>'.get_string("enddate").':</b> '.date("d/m/Y",$course->enddate));
        $methodguest = ($course->methodguest) ? get_string("yes") : get_string("no");
        $html .= html_writer::tag('p', '<b>'.get_string("methodguest","block_usp_cursos").':</b> '.$methodguest);

        if (isset($joincourses->joincourses)) {
            $html .= html_writer::tag('h4', get_string("wizardjoin_heading","block_usp_cursos") );
            $html .= html_writer::start_tag('ul');
            foreach ($joincourses->joincourses AS $key => $value) {
                $res1 = explode("_", $key);
                $res2 = explode(".",$res1[0]);
                $codtur = end($res2);
                $usp = $DB->get_record("user", Array("idnumber" => $res1[1]));
                if ($usp) {
                    $link = '<a href="'.$CFG->wwwroot.'/user/profile.php?id='.$usp->id.'" target="_blank">'.$res1[2].' (número USP: '.$res1[1].')</a>';
                } else {
                    $link = $res1[2].' (número USP: '.$res1[1].') - Não cadastrado no e-Disciplinas';
                }
                $html .= html_writer::tag('li', "<b>Turma: </b> $codtur - <b>Docente: </b> $link");
            }
            $html .= html_writer::end_tag('ul');
        }

        $html .= html_writer::end_tag('div');

        $mform->addElement('html', $html);
    }

}