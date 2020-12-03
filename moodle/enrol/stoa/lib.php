<?php

defined('MOODLE_INTERNAL') || die();

/** There are three reasonable things to do with students in a P (Pendant)
 * or I (Candidate) state in the Júpiter system:
 * 1. Enroll all normally
 * 2. Enroll and suspend (students can't access, won't show up in gradebook)
 * 3. Don't enroll
 * We'll store this decision in customint1 (P) and customint2 (I). 
 **/

define('ENROL_STOA_PI_ACTIVE', 0);
define('ENROL_STOA_PI_SUSPEND', 1);
define('ENROL_STOA_PI_UNENROL', 2);

class enrol_stoa_plugin extends enrol_plugin {

    public function roles_protected() {
        // users may tweak the roles later
        return false;
    }

    public function allow_enrol(stdClass $instance) {
        // users with enrol cap may unenrol other users manually manually
        return true;
    }
    
    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually manually
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status
        return true;
    }
    
    /**
     * Is it possible to delete enrol instance via standard UI?
     *
     * @param object $instance
     * @return bool
     */
    public function can_delete_instance($instance) {
        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/stoa:config', $context)) {
            return true;
        }
        if (!enrol_is_enabled('stoa')) {
            return true;
        }
        if (!$this->get_config('dbtype') or !$this->get_config('remoteenroltable') or !$this->get_config('remotecoursefield') or !$this->get_config('remoteuserfield')) {
            return true;
        }

        //TODO: connect to external system and make sure no users are to be enrolled in this course
        return false;
    }


    /**
     * Return an array of valid options for the status.
     *
     * @return array
     */
    protected function get_status_options() {
        $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                         ENROL_INSTANCE_DISABLED => get_string('no'));
        return $options;
    }


    protected function get_pienrol_options() {
        $options = array(ENROL_STOA_PI_ACTIVE  => get_string('pienrol','enrol_stoa'),
                         ENROL_STOA_PI_SUSPEND => get_string('pisuspend','enrol_stoa')
        );
        return $options;
    }

    
    public function edit_instance_form($instance, MoodleQuickForm $mform, $context) {
        global $CFG, $DB;
/*
        $nameattribs = array('size' => '20', 'maxlength' => '255');
        $mform->addElement('text', 'name', get_string('custominstancename', 'enrol'), $nameattribs);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'server');
*/
        $options = $this->get_status_options();
        $mform->addElement('select', 'status', get_string('status', 'enrol_stoa'), $options);
        $mform->addHelpButton('status', 'status', 'enrol_stoa');
        $mform->setDefault('status', $this->get_config('status'));
        $mform->setAdvanced('status', $this->get_config('status_adv'));

        $options = array( 'multiple'=>true,'placeholder' => 'JJJJJJJ.V.AAAATTT','tags'=>false,'showsuggestion'=>true,'ajax'=>'enrol_stoa/getcodmoodles');

        $mform->addElement('autocomplete', "codmoodles", get_string('codmoodles', 'enrol_stoa'),array(),$options);
        $course = $DB->get_record('course',array('id'=>$instance->courseid));
        $codmoodles = NULL;
        $cansuspendothers = false;
        if(!empty($course->idnumber)) {
            $codmoodles = explode(",",$course->idnumber);
            foreach($codmoodles as $codmoodle) {
                if(!empty($this->get_codmoodles($codmoodle,1))) {
                    $cansuspendothers = true;
                    break;
                }
            }
        }
        $mform->setDefault('codmoodles',$codmoodles);
        $mform->addHelpButton('codmoodles', 'codmoodles', 'enrol_stoa');

        // We'll interpret customint2 of the enrol instance as what to do with I students.
        $mform->addElement('selectyesno','customint2',get_string('i_enrolmode','enrol_stoa'));
        $mform->setDefault('customint2',ENROL_USER_ACTIVE);
        $mform->addHelpButton('customint2','i_enrolmode','enrol_stoa');


        // We'll interpret customint1 of the enrol instance as what to do with P students.
        $mform->addElement('selectyesno','customint1',get_string('p_enrolmode','enrol_stoa'));
        $mform->setDefault('customint1',ENROL_USER_ACTIVE);
        $mform->addHelpButton('customint1','p_enrolmode','enrol_stoa');

        // We only want to suspend 'other' enrols (not in Júpiter/Janus anymore)
        // when the codmoodle refers to the current year, because in, say, 2019
        // we don't have matriculation information about 2018 or earlier anymore.
        if ($cansuspendothers) {
            $mform->addElement('advcheckbox','suspendothers',get_string('suspendothers','enrol_stoa'));
            $mform->setDefault('suspendothers',FALSE);
            $mform->addHelpButton('suspendothers','suspendothers','enrol_stoa');
        } else {
            $mform->addElement('hidden','suspendothers',FALSE);
        }

        $mform->addElement('advcheckbox','syncnow',get_string('syncnow','enrol_stoa'));
        $mform->setDefault('syncnow',TRUE);
        $mform->addHelpButton('syncnow','syncnow','enrol_stoa');

    }

    /**
     * Perform custom validation of the data used to edit the instance.
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param object $instance The instance loaded from the DB
     * @param context $context The context of the instance we are editing
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     * @return void
     */
    public function edit_instance_validation($data, $files, $instance, $context) {
        $errors = array();

        $validstatus = array_keys($this->get_status_options());
        $tovalidate = array(
            'status' => $validstatus
        );
        $typeerrors = $this->validate_param_types($data, $tovalidate);
        $errors = array_merge($errors, $typeerrors);

        return $errors;
    }


    /**
     * Add new instance of enrol plugin.
     * @param object $course
     * @param array instance fields
     * @return int id of new instance, null if can not be created
     */
    public function add_instance($course, array $fields = NULL) {
        global $DB;
        if ($DB->record_exists('enrol', array('courseid'=>$course->id, 'enrol'=>'stoa'))) {
            // only one instance allowed, sorry
            return NULL;
        }
        if (!isset($fields)) {
            $fields = array('customint1'=>ENROL_USER_ACTIVE,'customint2' => ENROL_USER_ACTIVE,'roleid'=>$this->get_config('defaultrole'));
        }
        if(isset($fields['codmoodles']) && !empty($fields['codmoodles'])) {
            $codmoodles = implode(",",$fields['codmoodles']);
            // sync idnumber of course.
            $DB->update_record('course', array('id' => $fields['courseid'],'idnumber' => $codmoodles));
        }

        $instanceid = parent::add_instance($course, $fields);

        if(isset($fields['syncnow']) && $fields['syncnow']) {
            $trace = new null_progress_trace();
            $this->sync_enrolments($trace,$course->id,FALSE);
        }

        return $instanceid;
    }

    /**
     * Add new instance of enrol plugin with default settings.
     * @param object $course
     * @return int id of new instance
     */
    public function add_default_instance($course) {
        $fields = array('customint1'=>ENROL_USER_ACTIVE,'customint2' => ENROL_USER_ACTIVE,'roleid'=>$this->get_config('defaultrole'));
        return $this->add_instance($course,$fields);
    }


    /**
     * Update instance of enrol plugin.
     * @param stdClass $instance
     * @param stdClass $data modified instance fields
     * @return boolean
     */
    public function update_instance($instance, $data) {
        global $DB;
        // Delete all other instances, leaving only one.
        if ($instances = $DB->get_records('enrol', array('courseid' => $instance->courseid, 'enrol' => 'stoa'), 'id ASC')) {
            foreach ($instances as $anotherinstance) {
                if ($anotherinstance->id != $instance->id) {
                    $this->delete_instance($anotherinstance);
                }
            }
        }
        // make the array of codmoodles into a comma separated string
        $codmoodles = "";
        if(!empty($data->codmoodles)) {
            $codmoodles = implode(",",$data->codmoodles);
            
        }
        // update idnumber of course.
        $DB->update_record('course', array('id' => $instance->courseid,'idnumber' => $codmoodles));

        $return = parent::update_instance($instance, $data);
        if($data->syncnow && $return) {
            $trace = new null_progress_trace();
            // manual update
            $return = $this->sync_enrolments($trace,$instance->courseid,$data->suspendothers);
        }

        return $return;
    }

    /**
     * Delete course enrol plugin instance, unenrol all users.
     * @param object $instance
     * @return void
     *
     * Override to add cleaning of idnumber of course.
     */
    public function delete_instance($instance) {
        global $DB;
        // empty idnumber of course 
        $DB->update_record('course', array('id' => $instance->courseid,'idnumber' => ''));
        parent::delete_instance($instance);
    }

    /**
     * Called after updating/inserting course.
     *
     * @param bool $inserted true if course just inserted
     * @param object $course
     * @param object $data form data
     * @return void
     */
    public function course_updated($inserted, $course, $data) {
        global $DB;
            
        if (!empty($course->idnumber)) {
            if(count(explode('.',$course->idnumber))>2) {
                $this->add_default_instance($course);
            }
        }
    }


    public function use_standard_editing_ui() {
        return true;
    }


     /**
     * Returns true if the current user can add a new instance of enrolment plugin in course.
     * @param int $courseid
     * @return boolean
     */
    public function can_add_instance($courseid) {
        global $DB;
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/stoa:config', $context)) {
            return false;
        }

        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'stoa'))) {
            return false;
        }

        return true;
    }


    public function can_hide_show_instance($instance) {
        $context = context_course::instance($instance->courseid);
        return has_capability('enrol/stoa:config', $context);
    }


    /**
     * The plugin has several bulk operations that can be performed.
     * @param course_enrolment_manager $manager
     * @return array
     */
    public function get_bulk_operations(course_enrolment_manager $manager) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/stoa/locallib.php');
        $context = $manager->get_context();
        $bulkoperations = array();
        if (has_capability("enrol/stoa:manage", $context)) {
            $bulkoperations['editselectedusers'] = new enrol_stoa_editselectedusers_operation($manager, $this);
        }
        if (has_capability("enrol/stoa:unenrol", $context)) {
            $bulkoperations['deleteselectedusers'] = new enrol_stoa_deleteselectedusers_operation($manager, $this);
        }
        return $bulkoperations;
    }

    // function to determine if a userid has any roles in some context
    public function has_roles($userid,$contextid) {
        global $DB;
        
        $sql = "SELECT 1
              FROM {role_assignments}
             WHERE userid = :userid AND contextid = :contextid";
        return $DB->record_exists_sql($sql, array('userid'=>$userid,'contextid'=>$contextid));
    }
    
    /**
     * Override Enrol user into course via stoa instance
     */
    public function enrol_user(stdClass $instance, $userid, $roleid = NULL, $timestart = 0, $timeend = 0, $status = NULL, $groupids = NULL,  $recovergrades = null) {
        
        global $DB, $CFG;
        require_once($CFG->dirroot.'/group/lib.php');
            
        $remoteenroltable  = $this->get_config('remoteenroltable');
        $remotecoursefield = $this->get_config('remotecoursefield');
        $localcoursefield  = $this->get_config('localcoursefield'); 
        $localcoursevalue  = $DB->get_field('course', $localcoursefield, array('id'=>$instance->courseid));
        $localuserfield    = $this->get_config('localcoursefield');
        $localuservalue    = $DB->get_field('user', $localuserfield, array('id'=>$userid));
        $remoteuserfield   = $this->get_config('remoteuserfield');
        $remotestamtrfield = $this->get_config('remotestamtrfield');
        $defaultrole      = $this->get_config('defaultrole');

        if (!isset($groupids) || empty($groupids)) {
            $groupids = array();
        }

        $codmoodles = explode(",", $localcoursevalue);
        $stamtrs = array('M' => 0,'I' => 0, 'P' => 0);
        foreach ($codmoodles as $codmoodle) {
            $codmoodle = trim($codmoodle);
            $sql = $this->db_get_sql($remoteenroltable, array($remoteuserfield=>$localuservalue,
            $remotecoursefield=>$codmoodle), array(), true);
            $extdb = $this->db_init();
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    if($stamtr = $rs->fields[$remotestamtrfield]) {
                        $stamtrs[$stamtr] += 1;
                    }
                    // Find a group to insert user in.
                    $codmoodlearray = explode('.', $codmoodle);
                    $groupname = 'T-'.reset($codmoodlearray).'-'.end($codmoodlearray);
                    $groupid = $DB->get_field('groups', 'id', array('courseid'=>$instance->courseid, 'name'=>$groupname));
                    if (!$groupid) { // create group if doesnt exist
                        $group = new stdClass;
                        $group->courseid = $instance->courseid;
                        $group->name = $groupname;
                        $group->description = "{$groupname} - created by enrol/stoa plugin";
                        if (!$groupid = groups_create_group($group)) {
                            throw new moodle_exception('enrol_stoa_cant_create_group');
                        }
                    }
                    array_push($groupids, $groupid);
                }
                $rs->Close();
                $extdb->Close();
            } else {
                $extdb->Close();
                throw new moodle_exception('enrol_stoa_cant_read_remoteenroltable');
            }
        }

        // update enrol status based on instance configuration
        /*
         * If the student has M for one of the codmoodles: enrolment is active
         * else, if one of them is P, use teachers configuration for P
         * else (all are I), use teachers configuration for I
         */
        if($stamtrs['M'] > 0) {
            $status = ENROL_USER_ACTIVE;
        } elseif($stamtrs['P'] > 0) {
            $status = ($instance->customint1 == ENROL_STOA_PI_ACTIVE)?ENROL_USER_ACTIVE : ENROL_USER_SUSPENDED;
        } elseif($stamtrs['I']>0) {
            $status = ($instance->customint2 == ENROL_STOA_PI_ACTIVE)?ENROL_USER_ACTIVE : ENROL_USER_SUSPENDED;
        }
        $context = context_course::instance($instance->courseid);
        if (!isset($roleid)) {
            $roleid = $defaultrole;
        }
        if($status == ENROL_USER_SUSPENDED) {
            // we don't want any roles if this enrolment is suspended.
            // Reason: we have courses with guest access.
            if($this->has_roles($userid,$context->id)) {
                role_unassign_all(array('userid' => $userid, 'contextid' => $context->id),true);
            }
            // To prevent a role assignment. If this is an enrolment (and not an update), no roles are assigned.
            $roleid = null;
        }
        parent::enrol_user($instance, $userid, $roleid, $timestart, $timeend, $status, $recovergrades);

        foreach ($groupids as $groupid) {
            if (!$DB->record_exists('groups_members', array('groupid'=>$groupid, 'userid'=>$userid))) {
                if (groups_add_member($groupid, $userid)) {
                    error_log("[ENROL_DB] O usuario {$userid} foi incluido no grupo id {$groupid}");
                } else {
                    error_log("[ENROL_DB] Nao foi possivel incluir o usuario {$userid} no grupo id {$groupid}");
                }
            }
        }
    }
    
    /**
     * Unenrol user from course,
     * the last unenrolment removes all remaining roles and groups.
     *
     * @param stdClass $instance
     * @param int $userid
     * @return void
     */
    public function unenrol_user(stdClass $instance, $userid) {
        global $CFG, $USER, $DB;
        require_once($CFG->dirroot.'/group/lib.php');
        
        $groupids = array();
        
        $remoteenroltable  = $this->get_config('remoteenroltable');
        $remotecoursefield = $this->get_config('remotecoursefield');
        $localcoursefield  = $this->get_config('localcoursefield'); 
        $localcoursevalue  = $DB->get_field('course', $localcoursefield, array('id'=>$instance->courseid));
        $localuserfield    = $this->get_config('localcoursefield');
        $localuservalue    = $DB->get_field('user', $localuserfield, array('id'=>$userid));
        $remoteuserfield   = $this->get_config('remoteuserfield');
            
        $codmoodles = explode(",", $localcoursevalue);
        foreach ($codmoodles as $tmp_codmoodle) {
            $codmoodle = trim($tmp_codmoodle);
            if (isset($codmoodle) && !empty($codmoodle)) {
                $sql = $this->db_get_sql($remoteenroltable, array($remoteuserfield=>$localuservalue,
                                                                  $remotecoursefield=>$codmoodle), array(), true);
                $extdb = $this->db_init();
                if ($rs = $extdb->Execute($sql)) {
                    if (!$rs->EOF) {
                        $codmoodlearray = explode('.', $codmoodle);
                        $groupname = 'T-'.reset($codmoodlearray).'-'.end($codmoodlearray);
                        $groupid = $DB->get_field('groups', 'id', array('courseid'=>$instance->courseid, 'name'=>$groupname));
                        if ($groupid && $DB->record_exists('groups_members', array('groupid'=>$groupid, 'userid'=>$userid))) {
                            groups_remove_member($groupid, $userid);
                        }
                    }
                    $rs->Close();
                    $extdb->Close();
                } else {
                    $extdb->Close();
                    throw new moodle_exception('enrol_stoa_cant_read_remoteenroltable');
                }
            }
        }
        parent::unenrol_user($instance, $userid);
    }
    
    /**
     * Function that list pre-potential users
     * @param course record
     * @param user_ids to exclude
     */
    public function list_prepotentialusers($course, $search = '') {
        global $CFG, $DB;
        
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remoteenroltable') or
            !$this->get_config('remotecoursefield') or
            !$this->get_config('remoteuserfield')) {
            return;
        }
        
        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));
        
        $usertable        = $this->get_config('remoteusertable');
        $usercodefield    = strtolower($this->get_config('remoteusercodefield'));
        $usernamefield    = strtolower($this->get_config('remoteusernamefield'));
        $useremailfield   = strtolower($this->get_config('remoteuseremailfield'));
        
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');
        
        //-- get current user_ids
        $user_ids = array_keys($DB->get_records_sql("SELECT DISTINCT {$localuserfield} FROM {user}"));
         
        //-- get external sql
        $coursecodes = array();
        foreach (explode(',', $course->{$localcoursefield}) as $coursecode) {
            array_push($coursecodes, "'".trim($coursecode)."'");
        }
               
        $sql  = "SELECT DISTINCT {$usercodefield} AS id,";
        $sql .= " {$usercodefield} AS {$localuserfield},";
        $sql .= " {$usernamefield} AS firstname,";
        $sql .= " '' AS lastname,";
        $sql .= " GROUP_CONCAT({$useremailfield}) AS email";
        $sql .= " FROM {$usertable} WHERE ";
        if (!empty($search)) {
            $sql .= " {$usernamefield} LIKE '%{$search}%' AND ";
        }
        $sql .= "  {$usercodefield} IN";
        $sql .= "  (SELECT {$userfield} FROM {$table} WHERE {$coursefield} IN (".implode(',', $coursecodes)."))";
        $sql .= " GROUP BY {$localuserfield}";
        
        $result = array();
        $extdb = $this->db_init();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    //-- if not exits in moodle
                    if (in_array($fields[$localuserfield], $user_ids)) {
                        continue;
                    }
                    array_push($result, (object) $fields);
                }
            }
            $rs->Close();
            $extdb->Close();
        } else { // bad luck, something is wrong with the db connection
            $extdb->Close();
            return;
        }
        return $result;
    }
    
    /**
     * Function that list user ids by course
     * @param course record
     * @return string of condition with localuserfield IN (userids)
     */
    public function get_condition_for_users($course, $extcourseids=null) {
        global $CFG, $DB;
        
        // we do not create courses here intentionally because it requires full sync and is slow
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remoteenroltable') or
            !$this->get_config('remotecoursefield') or
            !$this->get_config('remoteuserfield')) {
            return;
        }
        
        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));
        $rolefield        = strtolower($this->get_config('remoterolefield'));
        
        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');
         
        $extdb = $this->db_init();
        // read remote enrols and create instances
        $userids = array();
        if (!isset($extcourseids)) {
            $extcourseids = explode(',', $course->{$localcoursefield});
        }
        foreach ($extcourseids as $extcourseid) {
            $sql = $this->db_get_sql($table, array($coursefield=>trim($extcourseid)), array(), false);
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    while ($fields = $rs->FetchRow()) {
                        $fields = array_change_key_case($fields, CASE_LOWER);
                        $fields = $this->db_decode($fields);
                        
                        if (!in_array($fields[$userfield], $userids)) {
                            // add if not exist in users
                            array_push($userids, $fields[$userfield]);
                        }
                    }
                }
            }
        }
        if (empty($userids)) { return NULL; }
        
        return $localuserfield.' IN ('.implode(',', $userids).')';
    }
    
    /**
     * Forces synchronisation of user enrolments with external stoa,
     * does not create new courses. Called after a user logs in.
     *
     * @param object $user user record
     * @return void
     */
    public function sync_user_enrolments($user) {
        global $CFG, $DB;

        // we do not create courses here intentionally because it requires full sync and is slow
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remoteenroltable') or
            !$this->get_config('remotecoursefield') or
            !$this->get_config('remoteuserfield')) {
            return;
        }
        
        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));
        $rolefield        = strtolower($this->get_config('remoterolefield'));

        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');

        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

        $ignorehidden     = $this->get_config('ignorehiddencourses');

        if (!is_object($user) or !property_exists($user, 'id')) {
            throw new coding_exception('Invalid $user parameter in sync_user_enrolments()');
        }

        if (!property_exists($user, $localuserfield)) {
            debugging('Invalid $user parameter in sync_user_enrolments(), missing '.$localuserfield);
            $user = $DB->get_record('user', array('id'=>$user->id));
        }

        // create roles mapping
        $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        $enrols = array();
        $instances = array();
        
        // read remote enrols and create instances
        $extdb = $this->db_init();
        $sql = $this->db_get_sql($table, array($userfield=>$user->$localuserfield), array(), false);
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);

                    if (empty($fields[$coursefield])) {
                        // missing course info
                        continue;
                    }
                    
                    //STOA: find multiple courses
                    $courseid_visible = $DB->get_records_sql_menu("SELECT id, visible FROM {course} WHERE ".
                                                                  $localcoursefield." LIKE '%".$fields[$coursefield]."%'");
                    if (empty($courseid_visible)) { continue; }
                    foreach ($courseid_visible as $courseid=>$visible) {
                        // iterate in courses and enrol
                        if (!$course = $DB->get_record('course', array('id'=>$courseid))) {
                            continue;
                        }
                        if (!$visible and $ignorehidden) {
                            continue;
                        }
                        
                        if (empty($fields[$rolefield]) or !isset($roles[$fields[$rolefield]])) {
                            if (!$defaultrole) {
                                // role is mandatory
                                continue;
                            }
                            $roleid = $defaultrole;
                        } else {
                            $roleid = $roles[$fields[$rolefield]];
                        }
                        
                        // add role to enrols var
                        if (empty($enrols[$course->id])) {
                            $enrols[$course->id] = array();
                        }
                        $enrols[$course->id][] = $roleid;
                        
                        // get instance of role-enrolment
                        if ($instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'stoa'), '*', IGNORE_MULTIPLE)) {
                            // avoid re-enrol user when this is remove by teacher
                            $instances[$course->id] = $instance;
                            continue;
                        }
                        $enrolid = $this->add_instance($course);
                        $instances[$course->id] = $DB->get_record('enrol', array('id'=>$enrolid));
                    }
                }
            }
            $rs->Close();
            $extdb->Close();
        } else {
            // bad luck, something is wrong with the db connection
            $extdb->Close();
            return;
        }
        
        // enrol user into courses and sync roles
        foreach ($enrols as $courseid => $roles) {
            if (!isset($instances[$courseid])) {
                // ignored
                continue;
            }
            $instance = $instances[$courseid];

            // Just enrol the user in every course after every login. But don't recover grades.
            foreach($roles as $roleid){
                $this->enrol_user($instance, $user->id,$roleid,0,0,null,null,false);
            }
        }
    }
    
    /**
     * Forces synchronisation of all enrolments with external stoa.
     * if onecourse (=courseid) not null, do only one course.
     * @return void
     */
    public function sync_enrolments(progress_trace $trace, $onecourse = null, $suspendothers = false) {
        global $CFG, $DB;
        
        // we do not create courses here intentionally because it requires full sync and is slow
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remoteenroltable') or
            !$this->get_config('remotecoursefield') or
            !$this->get_config('remoteuserfield')) {
            return;
        }
        
        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        
        // second step is to sync instances and users
        $table            = $this->get_config('remoteenroltable');
        $coursefield      = strtolower($this->get_config('remotecoursefield'));
        $userfield        = strtolower($this->get_config('remoteuserfield'));
        $rolefield        = strtolower($this->get_config('remoterolefield'));
        
        $localrolefield   = $this->get_config('localrolefield');
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');
        
        $unenrolaction    = $this->get_config('unenrolaction');
        $defaultrole      = $this->get_config('defaultrole');

        // sanity check
        $sql = "select count(*) c from {$table}";
        if ($rs = $extdb->Execute($sql)) {
            $count = 0;
            if (!$rs->EOF) {
                 $count = $rs->FetchRow();
                 $count = reset($count);
            }
            $rs->Close();
            if ($count < 100) {
                $trace->output('Less then 100 records in {$table}: aborting');
                $trace->finished();
                return 1;
            }
        } else {
            $trace->output('Error while communicating with Stoa external enrolment database');
            $extdb->Close();
            return 2;
        }
        
        // create roles mapping
        $allroles = get_all_roles();
        if (!isset($allroles[$defaultrole])) {
            $defaultrole = 0;
        }
        $roles = array();
        foreach ($allroles as $role) {
            $roles[$role->$localrolefield] = $role->id;
        }

        if ($onecourse) {
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, c.shortname, e.id AS enrolid
                      FROM {course} c
                 LEFT JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'stoa')
                     WHERE c.id = :id";
            if (!$course = $DB->get_record_sql($sql, array('id'=>$onecourse))) {
                // Course does not exist, nothing to sync.
                return 0;
            }
            if (empty($course->mapping)) {
                // We can not map to this course, sorry.
                return 0;
            }
            if (empty($course->enrolid)) {
                $course->enrolid = $this->add_default_instance($course);
            }
            if (!$instance = $DB->get_record('enrol', array('id'=>$course->enrolid))) {
                    return 0; //weird
            }

            $codmoodles = explode(',',$course->mapping);
            $existing = array();
            foreach($codmoodles as $codmoodle) {
                $codmoodle = trim($codmoodle);
                $c = clone $course;
                $c->mapping = $codmoodle;
                $existing[$codmoodle] = $c;
            }

            // No código abaixo não são incluídos usários inscritos no passado
            // mas retirados de alunoturma_moodle. Aqui vamos suspender eles.
            if($suspendothers) {
                $externalenrols = array();
                $cmq = array();
                foreach($codmoodles as $codmoodle) {
                    $cmq[] = "'".$codmoodle."'";
                }
                $instr = implode(',',$cmq);
                $sql = "select codpes from {$table} where {$coursefield} in ({$instr})";
                if ($rs = $extdb->Execute($sql)) {
                    if (!$rs->EOF) {
                        while ($codpes = $rs->FetchRow()) {
                            $codpes = reset($codpes);
                            $codpes = $this->db_decode($codpes);
                            if (empty($codpes)) { // invalid mapping
                                continue;
                            }
                            $externalenrols[$codpes] = true;
                        }
                    }
                    $rs->Close();
                    if (empty($externalenrols)) {
                        $trace->output('No enrolments for {codmoodles} in {$table}: aborting');
                        $trace->finished();
                        return 1;
                    }
                } else {
                    $trace->output('Error while communicating with Stoa external enrolment database');
                    $extdb->Close();
                    return 2;
                }
                
                $current_status = array();
                $currentusers  = array();
                $sql = "SELECT u.$localuserfield AS mapping, u.id, ue.status, ue.userid
                        FROM {user} u
                        JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                        WHERE u.deleted = 0";
                $params = array('enrolid'=>$course->enrolid);
                $rs = $DB->get_recordset_sql($sql, $params);
                foreach ($rs as $ue) {
                    $current_status[$ue->userid] = $ue->status;
                    $currentusers[$ue->mapping] = $ue->userid;
                }
                $rs->close();
                foreach($currentusers as $codpes => $userid) {
                    $status = $current_status[$userid];
                    if(!isset($externalenrols[$codpes])) {
                        $status = ENROL_USER_SUSPENDED;
                    }
                    $this->enrol_user($instance, $userid, null,0,0,$status,null,false);
                }
            }


            // Course being restored are always hidden, we have to ignore the setting here.
            $ignorehidden = false;

        } else {
            // sync all courses
            // get a list of courses to be synced that are in external table
            $externalcourses = array();
            $sql = $this->db_get_sql($table, array(), array($coursefield), true);
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    while ($mapping = $rs->FetchRow()) {
                        $mapping = reset($mapping);
                        $mapping = $this->db_decode($mapping);
                        if (empty($mapping)) { // invalid mapping
                            continue;
                        }
                        $externalcourses[$mapping] = true;
                    }
                }
                $rs->Close();
            } else {
                $trace->output('Error while communicating with Stoa external enrolment database');
                $extdb->Close();
                return 2;
            }
            $preventfullunenrol = empty($externalcourses);
            if ($preventfullunenrol and $unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
                debugging('Preventing unenrolment of all current users, because it might result in major data loss, '.
                'there has to be at least one record in external enrol table, sorry.');
            }
            
            // first find all existing courses with stoa enrol instance
            $existing = array();
            $sql = "SELECT c.id, c.visible, c.$localcoursefield AS mapping, e.id AS enrolid
                  FROM {course} c
                  JOIN {enrol} e ON (e.courseid = c.id AND e.enrol = 'stoa')";
            $rs = $DB->get_recordset_sql($sql); // watch out for idnumber duplicates
            foreach ($rs as $course) {
                if (empty($course->mapping)) {
                    continue;
                }
                $codmoodles = explode(',',$course->mapping);
                foreach($codmoodles as $codmoodle) {
                    $parts = explode('.',$codmoodle);
                    if(count($parts) <> 3) {
                        continue;
                    }
                    $tyear = substr($parts[2],0,4);
                    if($tyear <> date('Y')) {
                        // never mind GR sections not of this year
                        continue;
                    }
                    $codmoodle = trim($codmoodle);
                    $c = clone $course;
                    $c->mapping = $codmoodle;
                    $existing[$codmoodle] = $c;
                }
            }
            $rs->close();

            // free memory
            unset($externalcourses);
            
            $ignorehidden = $this->get_config('ignorehiddencourses');
        }

        // sync enrolments
        $sqlfields = array($userfield);
        if ($rolefield) {
            $sqlfields[] = $rolefield;
        }

        foreach ($existing as $course) {
            if ($ignorehidden and !$course->visible) {
                continue;
            }
            if (!$instance = $DB->get_record('enrol', array('id'=>$course->enrolid))) {
                continue; //weird
            }
            $context = context_course::instance($course->id);
            
            // get current list of enrolled users with their roles
            $current_roles  = array();
            $current_status = array();
            $user_mapping   = array();
            $sql = "SELECT u.$localuserfield AS mapping, u.id, ue.status, ue.userid, ra.roleid
                      FROM {user} u
                      JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                      JOIN {role_assignments} ra ON (ra.userid = u.id AND ra.itemid = ue.enrolid AND ra.component = 'enrol_stoa')
                     WHERE u.deleted = 0";
            $params = array('enrolid'=>$instance->id);
            if ($localuserfield === 'username') {
                $sql .= " AND u.mnethostid = :mnethostid";
                $params['mnethostid'] = $CFG->mnet_localhost_id;
            }
            $rs = $DB->get_recordset_sql($sql, $params);
            foreach ($rs as $ue) {
                $current_roles[$ue->userid][$ue->roleid] = $ue->roleid;
                $current_status[$ue->userid] = $ue->status;
                $user_mapping[$ue->mapping] = $ue->userid;
            }
            $rs->close();
            // get list of users that need to be enrolled and their roles
            $requested_roles = array();
            
            $sql = $this->db_get_sql($table, array($coursefield=>$course->mapping), $sqlfields);
            if ($rs = $extdb->Execute($sql)) {
                if (!$rs->EOF) {
                    if ($localuserfield === 'username') {
                        $usersearch = array('mnethostid'=>$CFG->mnet_localhost_id, 'deleted' =>0);
                    }
                    while ($fields = $rs->FetchRow()) {
                        $fields = array_change_key_case($fields, CASE_LOWER);
                        if (empty($fields[$userfield])) {
                            //user identification is mandatory!
                        }
                        $mapping = $fields[$userfield];
                        if (!isset($user_mapping[$mapping])) {
                            $usersearch[$localuserfield] = $mapping;
                            if (!$user = $DB->get_record('user', $usersearch, 'id', IGNORE_MULTIPLE)) {
                                // user does not exist or was deleted
                                continue;
                            }
                            $user_mapping[$mapping] = $user->id;
                            $userid = $user->id;
                        } else {
                            $userid = $user_mapping[$mapping];
                        }
                        if (empty($fields[$rolefield]) or !isset($roles[$fields[$rolefield]])) {
                            if (!$defaultrole) {
                                // role is mandatory
                                continue;
                            }
                            $roleid = $defaultrole;
                        } else {
                            $roleid = $roles[$fields[$rolefield]];
                        }
                        $requested_roles[$userid][$roleid] = $roleid;
                    }
                }
                $rs->Close();
            } else {
                debugging('Error while communicating with external enrolment stoa');
                $extdb->Close();
                return;
            }
            unset($user_mapping);
            // enrol all users and sync roles
            foreach ($requested_roles as $userid=>$userroles) {
                foreach ($userroles as $roleid) {
                    if (empty($current_roles[$userid])) {
                        $this->enrol_user($instance, $userid, $roleid,0,0,null,null,false);
                        $current_roles[$userid][$roleid] = $roleid;
                    }
                }
                
                // assign extra roles
                foreach ($userroles as $roleid) {
                    if (empty($current_roles[$userid][$roleid])) {
                        role_assign($roleid, $userid, $context->id, 'enrol_stoa', $instance->id);
                        $current_roles[$userid][$roleid] = $roleid;
                    }
                }
                
                // unassign removed roles
                foreach($current_roles[$userid] as $cr) {
                    if (empty($userroles[$cr])) {
                        role_unassign($cr, $userid, $context->id, 'enrol_stoa', $instance->id);
                        unset($current_roles[$userid][$cr]);
                    }
                }
                
            }
        }

        //exit;
        // close db connection
        $extdb->Close();
    }

    /**
     * Performs a full sync with external stoa.
     *
     * First it creates new courses if necessary, then
     * enrols and unenrols users.
     * @return void
     */
    public function sync_courses() {
        global $CFG, $DB;
        
        // make sure we sync either enrolments or courses
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('newcoursetable') or
            !$this->get_config('newcoursefullname') or
            !$this->get_config('newcourseshortname')) {
            return;
        }
        
        // we may need a lot of memory here
        @set_time_limit(0);
        raise_memory_limit(MEMORY_HUGE);
        
        $extdb = $this->db_init();
        
        // first create new courses
        $table     = $this->get_config('newcoursetable');
        $fullname  = strtolower($this->get_config('newcoursefullname'));
        $shortname = strtolower($this->get_config('newcourseshortname'));
        $idnumber  = strtolower($this->get_config('newcourseidnumber'));
        $category  = strtolower($this->get_config('newcoursecategory'));
        
        $sqlfields = array($fullname, $shortname);
        if ($category) {
            $sqlfields[] = $category;
        }
        if ($idnumber) {
            $sqlfields[] = $idnumber;
        }
        $sql = $this->db_get_sql($table, array(), $sqlfields);
        $createcourses = array();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                $courselist = array();
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    if (empty($fields[$shortname]) or empty($fields[$fullname])) {
                        //invalid record - these two are mandatory
                        continue;
                    }
                    $fields = $this->db_decode($fields);
                    if ($DB->record_exists('course', array('shortname'=>$fields[$shortname]))) {
                        // already exists
                        continue;
                    }
                    // allow empty idnumber but not duplicates
                    if ($idnumber and
                        $fields[$idnumber] !== '' and
                        $fields[$idnumber] !== null and
                        $DB->record_exists('course', array('idnumber'=>$fields[$idnumber]))) {
                        continue;
                    }
                    if ($category and
                        !$DB->record_exists('course_categories', array('id'=>$fields[$category]))) {
                        // invalid category id, better to skip
                        continue;
                    }
                    $course = new stdClass();
                    $course->fullname  = $fields[$fullname];
                    $course->shortname = $fields[$shortname];
                    $course->idnumber  = $idnumber ? $fields[$idnumber] : NULL;
                    $course->category  = $category ? $fields[$category] : NULL;
                    $createcourses[] = $course;
                }
            }
            $rs->Close();
        } else {
            debugging('Error while communicating with external enrolment stoa');
            $extdb->Close();
            return;
        }
        if ($createcourses) {
            require_once("$CFG->dirroot/course/lib.php");
            
            $template        = $this->get_config('templatecourse');
            $defaultcategory = $this->get_config('defaultcategory');
            
            if ($template) {
                if ($template = $DB->get_record('course', array('shortname'=>$template))) {
                    unset($template->id);
                    unset($template->fullname);
                    unset($template->shortname);
                    unset($template->idnumber);
                } else {
                    $template = new stdClass();
                }
            } else {
                $template = new stdClass();
            }
            if (!$DB->record_exists('course_categories', array('id'=>$defaultcategory))) {
                $categories = $DB->get_records('course_categories', array(), 'sortorder', 'id', 0, 1);
                $first = reset($categories);
                $defaultcategory = $first->id;
            }

            foreach ($createcourses as $fields) {
                $newcourse = clone($template);
                $newcourse->fullname  = $fields->fullname;
                $newcourse->shortname = $fields->shortname;
                $newcourse->idnumber  = $fields->idnumber;
                $newcourse->category  = $fields->category ? $fields->category : $defaultcategory;

                create_course($newcourse);
            }

            unset($createcourses);
            unset($template);
        }

        // close db connection
        $extdb->Close();
    }



     /**
     * Sincronização dos cursos no Júpiter para cohortes no Moodle
     * 
     * @return void
     */
    public function sync_cohorts(progress_trace $trace, $anosem) {
        global $CFG, $DB;
        
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remoteenroltable') or
            !$this->get_config('remotecoursefield') or
            !$this->get_config('remoteuserfield')) {
            return;
        }
        
        if (!$extdb = $this->db_init()) {
            $trace->output('Error while communicating with external enrolment database');
            $trace->finished();
            return 1;
        }

        // We may need a lot of memory here.
        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        
        // second step is to sync cohorts and users
        $table            = $this->get_config('cohorttable');
        $codcur           = $this->get_config('cohortcodcur');
        $codpes           = $this->get_config('cohortcodpes');
        
        if(!isset($anosem)) {
            return 1;
        }

        // sanity check
        $sql = "select count(*) c from {$table}";
        if ($rs = $extdb->Execute($sql)) {
            $count = 0;
            if (!$rs->EOF) {
                 $count = $rs->FetchRow();
                 $count = reset($count);
            }
            $rs->Close();
            if ($count < 100) {
                $trace->output('Less then 100 records in {$table}: aborting');
                $trace->finished();
                return 1;
            }
        } else {
            $trace->output('Error while communicating with Stoa external enrolment database');
            $extdb->Close();
            return 2;
        }

        // close db connection
        $extdb->Close();
        return 0;
    }

    protected function db_get_sql($table, array $conditions, array $fields, $distinct = false, $sort = "") {
        $fields = $fields ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key=>$value) {
                $value = $this->db_encode($this->db_addslashes($value));

                $where[] = "$key = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }

    protected function db_init() {
        global $CFG;

        require_once($CFG->libdir.'/adodb/adodb.inc.php');
        // Connect to the external stoa (forcing new connection)
        $extdb = ADONewConnection($this->get_config('dbtype'));
        if ($this->get_config('debugdb')) {
            $extdb->debug = true;
            ob_start(); //start output buffer to allow later use of the page headers
        }
        
        $extdb->Connect($this->get_config('dbhost'),
                        $this->get_config('dbuser'),
                        $this->get_config('dbpass'),
                        $this->get_config('dbname'), true);
        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($this->get_config('dbsetupsql')) {
            $extdb->Execute($this->get_config('dbsetupsql'));
        }
        return $extdb;
    }

    protected function db_addslashes($text) {
        // using custom made function for now
        if ($this->get_config('dbsybasequoting')) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    protected function db_encode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_encode($value);
            }
            return $text;
        } else {
            return textlib_get_instance()->convert($text, 'utf-8', $dbenc);
        }
    }

    protected function db_decode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_decode($value);
            }
            return $text;
        } else {
            return textlib_get_instance()->convert($text, $dbenc, 'utf-8');
        }
    }
    
    
    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol_user($instance, $ue) && has_capability("enrol/stoa:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''),
                         get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        if ($this->allow_manage($instance) && has_capability("enrol/stoa:manage", $context)) {
            $url = new moodle_url('/enrol/editenrolment.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/edit', ''),
                         get_string('edit'), $url, array('class'=>'editenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }
    
    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;
        
        if ($instance->enrol !== 'stoa') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);
        
        $icons = array();

        if (has_capability('enrol/stoa:manage', $context)) {
            $managelink = new moodle_url("/enrol/stoa/manage.php", array('enrolid'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($managelink, new pix_icon('i/users', get_string('enrolusers', 'enrol_stoa'),
                                                                      'core', array('class'=>'iconsmall')));
            
            $messagelink = new moodle_url('/enrol/stoa/message.php', array('enrolid'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($messagelink, new pix_icon('i/email', get_string('sendmessage', 'enrol_stoa'),
                                                                       'core', array('class'=>'iconsmall')));
        }
        if (has_capability('enrol/stoa:config', $context)) {
            $parenticons = parent::get_action_icons($instance);
            $icons = array_merge($icons, $parenticons);
        }

        return $icons;
    }  
    public function get_codmoodles($search,$limitnum = 100) {
        
        
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('turmastable') or
            !$this->get_config('remotecoursefield')) {
            return;
        }
        $extdb = $this->db_init();
        
        $turmastable = $this->get_config('turmastable');
        $coursefield = $this->get_config('remotecoursefield');
        //$sql = $this->db_get_sql($enroltable,$conditions=array(),$fields=(array)$coursefield,$distinct=true);
        $sql = $extdb->Prepare("select distinct {$coursefield} from {$turmastable} where {$coursefield} like ? limit ?");
        if ($rs = $extdb->Execute($sql,array("%"."{$search}"."%",$limitnum))) {
            $codmoodles = array();
            if (!$rs->EOF) {
                while ($codmoodle = $rs->FetchRow()) {
                    $codmoodle = $codmoodle[$coursefield];
                    $codmoodles[$codmoodle] = $codmoodle;
                }
            }
            $extdb->Close();
            return $codmoodles;
        }
        $extdb->Close();
        return array();
    }

    
    
}

