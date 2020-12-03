<?php

defined ('MOODLE_INTERNAL') || die();

class wizard_controller {
    
    protected $id;
    
    protected $type;
    protected $codmoodle;
    protected $userid;
    
    protected $data;
    protected $block;
    protected $instanceid;
    
    protected $checksum;
    
    public function load_block() {
        global $DB;
        $instance = $DB->get_record("block_instances", array('id'=>$this->instanceid));
        block_load_class('usp_cursos');
        $this->block = block_instance('usp_cursos');
        $this->block->_load_instance($instance, NULL);
    }
    
    public function __construct($type, $codmoodle, $userid, $instanceid) {
        $this->type = $type;
        $this->codmoodle = $codmoodle;
        $this->userid = $userid;
        $this->instanceid = $instanceid;
        // calcule unique $id : current time + type + codmoodle + userid
        // should be unique enough. Add one random part at the end
        $this->id = md5(time().'-'.$this->type.'-'.$this->codmoodle.'-'.
                        $this->userid.'-'.$this->instanceid.'-'.random_string(10));
        $this->load_block();
    }
    
    public static function load_controller($id) {
        if (isset($_SESSION["block_usp/wizard/{$id}"])) {
            $controller = unserialize(base64_decode($_SESSION["block_usp/wizard/{$id}"]));
            if (!$controller->is_checksum_correct($_SESSION["block_usp/wizard/checksum/{$id}"])) {
                return false;
            }
            $controller->load_block();
            return $controller;
        }
        return false;
    }
    
    public function save_controller() {
        // Calculate checksum
        $this->checksum = md5('id-'.$this->id.'-type-'.$this->type.'-codmoodle-'.
                              $this->codmoodle.'-userid-'.$this->userid.'-instanceid-'.$this->instanceid);
        $_SESSION["block_usp/wizard/{$this->id}"] = base64_encode(serialize($this));
        $_SESSION["block_usp/wizard/checksum/{$this->id}"] = $this->checksum;
    }
    
    public function is_checksum_correct($checksum) {
        return $this->checksum == $checksum;
    }
    
    public function get_codmoodle() {
        return $this->codmoodle;
    }
    
    public function get_id() {
        return $this->id;
    }
    
    public function get_type() {
        return $this->type;
    }
    
    public function get_data($field=null) {
        if (isset($field) && !empty($field)) {
            if (!property_exists($this->data, $field)) {
                return null;
            }
            return $this->data->{$field};
        }
        return $this->data;
    }
    
    public function set_data($data, $field=null) {
        if (!isset($this->data)) {
            $this->data = new stdClass();
        }
        if (isset($field) && !empty($field)) {
            $this->data->{$field} = $data;
        } else {
            $this->data = $data;
        }
    }
     
    public function get_block() {
        return $this->block;
    }
    
    public function get_courseinfo() {
        global $CFG;
        
        $parts = explode('.', $this->codmoodle);
        $coddis = $parts[0];
        $codtur = $parts[2];

        if (!isset($this->data) || !isset($this->data->course)) {
            $course = $this->block->get_potential_course($this->codmoodle);
            $course->category = $course->cat->id;
            $editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true);
            $course = file_prepare_standard_editor($course, 'summary', $editoroptions, null, 'course', 'summary', null);
            $this->set_data($course, 'course');
            $this->set_data($course->cat,'category');
        }

        if (!isset($this->data) || !isset($this->data->editoroptions)) {
            $editoroptions = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'maxbytes'=>$CFG->maxbytes, 'trusttext'=>false, 'noclean'=>true);
            $editoroptions['context'] = context_coursecat::instance($this->data->category->id);
            $this->set_data($editoroptions, 'editoroptions');
        }
        
        return array('course'=>$this->data->course,
                     'category'=>$this->data->category,
                     'editoroptions'=>$this->data->editoroptions);
    }
    
    public function get_enrolinfo($course) {
        if (!isset($this->data) || !isset($this->data->enrol_type)) {
            $this->set_data('all', 'enrol_type');
        }
        
        $enrol_users = array();
        $enrol_potential_users = $this->get_enrol_potential_users();
        if (isset($this->data->enrol_users) && !empty($this->data->enrol_users)) {
            foreach ($this->data->enrol_users as $pid) {
                if (isset($enrol_potential_users[$pid])) {
                    $enrol_users[$pid] = $enrol_potential_users[$pid];
                }
                unset($enrol_potential_users[$pid]);
            }
        }
        return array('enrol_type'=>$this->data->enrol_type,
                     'enrol_users'=>$enrol_users,
                     'potential_users'=>$enrol_potential_users);
    }
     
    public function get_enrol_potential_users(&$roles=array()) {
        return $this->block->get_enrol_potential_users(null, array($this->codmoodle), $roles);
    }
    
    public function get_messageinfo($course) {
        global $USER;
        if (!isset($this->data) || !isset($this->data->message_type)) {
            $this->set_data('all', 'message_type');
        }
        if (!isset($this->data) || !isset($this->data->messagetext)) {
            $username = $USER->firstname.' '.$USER->lastname;
            $messagetext = get_string('messagetext', 'block_usp_cursos',
                  (object) array('instructor'=>$username, 'course'=>$course->fullname));
            $this->set_data($messagetext, 'messagetext');
        }
        
        $message_users = array();
        $enrol_prepotential_users = $this->get_enrol_prepotential_users();
        if (isset($this->data->message_users) && !empty($this->data->message_users)) {
            foreach ($this->data->message_users as $email) {
                if (isset($enrol_prepotential_users[$email])) {
                    $message_users[$email] = $enrol_prepotential_users[$email];
                }
                unset($enrol_prepotential_users[$email]);
            }
        }
        return array('message_type'=>$this->data->message_type,
                     'messagetext'=>$this->data->messagetext,
                     'message_users'=>$message_users,
                     'potential_users'=>$enrol_prepotential_users);
    }
    
    public function get_enrol_prepotential_users() {
        return $this->block->get_enrol_prepotential_users(null, array($this->codmoodle));
    }
    
    //TODO clean various structures, use $this->plan->destroy
    public function destroy() {        
    }

    public function get_results() {
        return null;
    }
    
    public function execute_plan() {
        global $CFG, $DB, $USER;
        require_once($CFG->libdir.'/enrollib.php');
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->libdir.'/../group/lib.php');
        //-- create or update course information
        $course_obj = $this->get_data('course');
        $joincourses = $this->get_data('joincourses');

        $teachers_mdl = Array();
        $teachers_codpes = Array();
        $codmoodle_teachers = Array();
        $turmas = Array();
        foreach ($joincourses->joincourses AS $key => $value) {
            $res = explode("_", $key); //EAC0507.3.2017101_49241_Gilberto de Andrade Martins
            $codmoodle = $res[0];
            $codpes = $res[1];
            $turmas[$codmoodle] = true;
            if ($user_mdl = $DB->get_record("user", Array("idnumber" => $codpes), "id,idnumber")) {
                $teachers_mdl[$user_mdl->id] = $codmoodle;
                $codmoodle_teachers[$codmoodle][] = $user_mdl->id;
            }
            $teachers_codpes[$codpes][] = $codmoodle;
        }
        $courseidnumber = implode(",", array_keys($turmas));

        $teacher_roleid = $DB->get_record("role", Array("shortname" => "editingteacher"));
        $teacherroleid = $teacher_roleid->id;

        if ($this->type == wizard::TYPE_CREATE) {
            $course_obj->idnumber = $courseidnumber;
            $course = create_course($course_obj, $this->get_data('editoroptions'));
        } else {
            throw new moodle_exception('wizard_ui_type_doesnt_exist');
        }
        $this->set_data($course, 'course');


        // Guest method
        $enrol_guests = $DB->get_records('enrol', array('courseid'=>$course->id,'enrol'=>'guest'));
        if ($course_obj->methodguest) {
            if($enrol_guest = reset($enrol_guests)){
                $DB->set_field('enrol','status',ENROL_INSTANCE_ENABLED,array('courseid'=>$course->id,'enrol'=>'guest'));
            } else {
                $enrol_guest = enrol_get_plugin('guest');
                $instance_guest_id = $enrol_guest->add_instance($course);
            }
        } else {
            if($enrol_guest = reset($enrol_guests)){
                $DB->set_field('enrol','status',ENROL_INSTANCE_DISABLED,array('courseid'=>$course->id,'enrol'=>'guest'));
            }
        }
        //-- enrol users
        if ($this->get_data('enrol_type') != 'none') {
            $enrol_stoa = enrol_get_plugin('stoa');
            $instanceid = $enrol_stoa->add_default_instance($course);
            if ($instanceid == null) {
                $instanceid = $DB->get_field('enrol', 'id', array('courseid'=>$course->id, 'enrol'=>'stoa'));
            }
            $instance = $DB->get_record('enrol', array('id'=>$instanceid), '*', MUST_EXIST);


            $enrol_manual = enrol_get_plugin('manual');
            $manualinstanceid = $DB->get_field('enrol', 'id', array('courseid'=>$course->id, 'enrol'=>'manual'));
            $manualinstance = $DB->get_record('enrol', array('id'=>$manualinstanceid), '*', MUST_EXIST);


            $roles = array();
            $userids = array_keys($this->block->get_enrol_potential_users($course,null,$roles));
            foreach ($userids as $userid) {
                $enrol_stoa->enrol_user($instance, $userid, $roles[$userid]);
            }

            foreach ($teachers_mdl as $teacherid=>$codmoodle) {
                $enrol_manual->enrol_user($manualinstance, $teacherid, $teacherroleid);
            }
            foreach($turmas as $codmoodle=>$exists) {
                //-- create and add user in groups
                $turma = explode(".", $codmoodle);
                $groupname = 'T-'.reset($turma).'-'.end($turma);
                if (!$group = $DB->get_record('groups', array('courseid'=>$course->id, 'name'=>$groupname))) {
                    // o grupo nao existe criar grupo
                    $group = new stdClass;
                    $group->courseid = $course->id;
                    $group->name = $groupname;
                    $group->description = "{$groupname} - Sincronizado do Júpiter/Janus";
                    if (!$groupid = groups_create_group($group)) {
                        throw new moodle_exception('wizard_controller_cant_create_group'); // nao foi possivel cria o grupo
                    }
                    $group->id = $groupid;
                }
                $teachers = $codmoodle_teachers[$codmoodle];
                foreach($teachers as $teacherid) {
                    if (!$DB->record_exists('groups_members', array('groupid'=>$group->id, 'userid'=>$teacherid))) {
                        if (groups_add_member($group->id, $teacherid)) {
                            error_log("[ENROL_DB] O usuário {$teacherid} foi incluido no grupo id $group->id");
                        } else {
                            error_log("[ENROL_DB] Não foi possível incluir o usuário {$teacherid} no grupo id $group->id");
                        }
                    }
                }
            }
            // JOIN TEACHERS, set created in table BLOCK_USP_CURSOS and dont display link to create course in home
            foreach ($teachers_codpes AS $codpes=>$codmoodle_array) {
                foreach ($codmoodle_array as $codmoodle) {
                    $disciplina = $DB->get_record('block_usp_cursos', array('codpes'=>$codpes, 'codmoodle'=>$codmoodle));
                    if ($disciplina) {
                        $disciplina->created = 1;
                        $DB->update_record('block_usp_cursos', $disciplina);
                    }
                }
            }
        }
    }
}