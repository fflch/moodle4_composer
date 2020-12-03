<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');

/**
 * Prepotencial candidates
 * This users aren't register in moodle but exist in stoa
 */
class enrol_stoa_prepotential_participant extends user_selector_base {
    
    protected $course;
    protected $enrol_stoa;
    protected $enrolid;
    protected $selecteduser;
    
    public function __construct($name, $options) {
        $this->enrol_stoa = $options['enrol_stoa'];
        $this->course = $options['course'];
        if (isset($options['selecteduser'])) {
            $this->selecteduser = $options['selecteduser'];
        }
        parent::__construct($name, $options);
    }
    
    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['file']    = 'enrol/stoa/locallib.php';
        return $options;
    }
    
    /**
     * Candidate users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        $availableusers = $this->enrol_stoa->list_prepotentialusers($this->course, $search);
        $ta = array();
        foreach($availableusers as $user) {
            $user->firstnamephonetic = "";
            $user->lastnamephonetic = "";
            $user->middlename = "";
            $user->alternatename = "";
            array_push($ta,$user);
        }
        $availableusers = $ta;
        if (isset($this->selecteduser) && !empty($this->selecteduser)) {
            $tmpavailableusers = array();
            foreach ($availableusers as $user) {
                if (!array_key_exists($user->id, $this->selecteduser)) {
                    array_push($tmpavailableusers, $user);
                }
            }
            //$availableusers = array_diff($availableusers, $this->selecteduser);
            $availableusers = $tmpavailableusers;
        }
        
        if (empty($availableusers)) {
            return array();
        }
        if ($search) {
            $groupname = get_string('enrolcandidatesmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolcandidates', 'enrol');
        }
        
        return array($groupname => $availableusers);
    }
    
}

/**
 * Potencial candidates
 * this users must be register in moodle but not in course
 */
class enrol_stoa_potential_participant extends user_selector_base {

    protected $courseid;
    protected $course;
    protected $enrol_stoa;
    protected $enrolid;

    public function __construct($name, $options) {
        $this->enrolid  = $options['enrolid'];
        $this->enrol_stoa = $options['enrol_stoa'];
        $this->course = $options['course'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        
        //$conditionusers = $this->enrol_stoa->get_condition_for_users($this->course);
        $enrol_stoa = enrol_get_plugin('stoa');
        $conditionusers = $enrol_stoa->get_condition_for_users($this->course);
        
        //by default wherecondition retrieves all users except the deleted, not confirmed and guest
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;
        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                WHERE $wherecondition AND
                u.id NOT IN (SELECT ue.userid FROM {user_enrolments} ue
                             JOIN {enrol} e ON (e.id = ue.enrolid AND e.id = :enrolid))";
        if (isset($conditionusers) && !empty($conditionusers)) {
            $sql .= ' AND u.'.$conditionusers;
        } else {
            $sql .= ' AND 1 <> 1';
        }
        $order = ' ORDER BY u.firstname ASC, u.lastname ASC';
        
        $availableusers = $DB->get_records_sql($fields.$sql.$order, $params);
        if (empty($availableusers)) {
            return array();
        }
        
        if ($search) {
            $groupname = get_string('enrolcandidatesmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolcandidates', 'enrol');
        }
        
        return array($groupname => $availableusers);
    }
    
    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['enrol_stoa'] = $this->enrol_stoa;
        $options['course'] = $this->course;
        $options['file']    = 'enrol/stoa/locallib.php';
        return $options;
    }
    
}

/**
 * Enroled users
 */
class enrol_stoa_current_participant extends user_selector_base {
    
    protected $courseid;
    protected $enrolid;

    public function __construct($name, $options) {
        $this->enrolid  = $options['enrolid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param <type> $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        //by default wherecondition retrieves all users except the deleted, not confirmed and guest
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;
        
        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';
        
        $sql = " FROM {user} u
                 JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                WHERE $wherecondition";
        
        $order = ' ORDER BY u.firstname ASC, u.lastname ASC';
        
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > 100) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
        
        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);
        
        if (empty($availableusers)) {
            return array();
        }
        
        if ($search) {
            $groupname = get_string('enrolledusersmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolledusers', 'enrol');
        }
        return array($groupname => $availableusers);
    }
    
    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['file']    = 'enrol/stoa/locallib.php';
        return $options;
    }
}

/**
 * A bulk operation for the stoa enrolment plugin to edit selected users.
 *
 * @copyright 2011 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_stoa_editselectedusers_operation extends enrol_bulk_enrolment_operation {

    /**
     * Returns the title to display for this bulk operation.
     *
     * @return string
     */
    public function get_title() {
        return get_string('editselectedusers', 'enrol_stoa');
    }

    /**
     * Returns the identifier for this bulk operation. This is the key used when the plugin
     * returns an array containing all of the bulk operations it supports.
     */
    public function get_identifier() {
        return 'editselectedusers';
    }

    /**
     * Processes the bulk operation request for the given userids with the provided properties.
     *
     * @global moodle_database $DB
     * @param course_enrolment_manager $manager
     * @param array $userids
     * @param stdClass $properties The data returned by the form.
     */
    public function process(course_enrolment_manager $manager, array $users, stdClass $properties) {
        global $DB, $USER;
        
        if (!has_capability("enrol/stoa:manage", $manager->get_context())) {
            return false;
        }
        
        // Get all of the user enrolment id's
        $ueids = array();
        $instances = array();
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $ueids[] = $enrolment->id;
                if (!array_key_exists($enrolment->id, $instances)) {
                    $instances[$enrolment->id] = $enrolment;
                }
            }
        }
        
        // Check that each instance is manageable by the current user.
        foreach ($instances as $instance) {
            if (!$this->plugin->allow_manage($instance)) {
                return false;
            }
        }
        
        // Collect the known properties.
        $status = $properties->status;
        $timestart = $properties->timestart;
        $timeend = $properties->timeend;
        
        list($ueidsql, $params) = $DB->get_in_or_equal($ueids, SQL_PARAMS_NAMED);
        
        $updatesql = array();
        if ($status == ENROL_USER_ACTIVE || $status == ENROL_USER_SUSPENDED) {
            $updatesql[] = 'status = :status';
            $params['status'] = (int)$status;
        }
        if (!empty($timestart)) {
            $updatesql[] = 'timestart = :timestart';
            $params['timestart'] = (int)$timestart;
        }
        if (!empty($timeend)) {
            $updatesql[] = 'timeend = :timeend';
            $params['timeend'] = (int)$timeend;
        }
        if (empty($updatesql)) {
            return true;
        }
        
        // Update the modifierid
        $updatesql[] = 'modifierid = :modifierid';
        $params['modifierid'] = (int) $USER->id;
        
        // Update the time modified
        $updatesql[] = 'timemodified = :timemodified';
        $params['timemodified'] = time();
        
        // Build the SQL statement
        $updatesql = join(', ', $updatesql);
        $sql = "UPDATE {user_enrolments}
                   SET $updatesql
                 WHERE id $ueidsql";

        if ($DB->execute($sql, $params)) {
            foreach ($users as $user) {
                foreach ($user->enrolments as $enrolment) {
                    $enrolment->courseid  = $enrolment->enrolmentinstance->courseid;
                    $enrolment->enrol     = 'stoa';
                    events_trigger('user_enrol_modified', $enrolment);
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * Returns a enrol_bulk_enrolment_operation extension form to be used
     * in collecting required information for this operation to be processed.
     *
     * @param string|moodle_url|null $defaultaction
     * @param mixed $defaultcustomdata
     * @return enrol_stoa_editselectedusers_form
     */
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/stoa/bulkchangeforms.php');
        return new enrol_stoa_editselectedusers_form($defaultaction, $defaultcustomdata);
    }
    
}

/**
 * A bulk operation for the stoa enrolment plugin to delete selected users enrolments.
 *
 * @copyright 2011 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_stoa_deleteselectedusers_operation extends enrol_bulk_enrolment_operation {

    /**
     * Returns the title to display for this bulk operation.
     *
     * @return string
     */
    public function get_identifier() {
        return 'deleteselectedusers';
    }

    /**
     * Returns the identifier for this bulk operation. This is the key used when the plugin
     * returns an array containing all of the bulk operations it supports.
     *
     * @return string
     */
    public function get_title() {
        return get_string('deleteselectedusers', 'enrol_stoa');
    }

    /**
     * Returns a enrol_bulk_enrolment_operation extension form to be used
     * in collecting required information for this operation to be processed.
     *
     * @param string|moodle_url|null $defaultaction
     * @param mixed $defaultcustomdata
     * @return enrol_stoa_editselectedusers_form
     */
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/stoa/bulkchangeforms.php');
        if (!array($defaultcustomdata)) {
            $defaultcustomdata = array();
        }
        $defaultcustomdata['title'] = $this->get_title();
        $defaultcustomdata['message'] = get_string('confirmbulkdeleteenrolment', 'enrol_stoa');
        $defaultcustomdata['button'] = get_string('unenrolusers', 'enrol_stoa');
        return new enrol_stoa_deleteselectedusers_form($defaultaction, $defaultcustomdata);
    }

    /**
     * Processes the bulk operation request for the given userids with the provided properties.
     *
     * @global moodle_database $DB
     * @param course_enrolment_manager $manager
     * @param array $userids
     * @param stdClass $properties The data returned by the form.
     */
    public function process(course_enrolment_manager $manager, array $users, stdClass $properties) {
        global $DB;

        if (!has_capability("enrol/stoa:unenrol", $manager->get_context())) {
            return false;
        }
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $plugin = $enrolment->enrolmentplugin;
                $instance = $enrolment->enrolmentinstance;
                if ($plugin->allow_unenrol_user($instance, $enrolment)) {
                    $plugin->unenrol_user($instance, $user->id);
                }
            }
        }
        return true;
    }
}



/**
 * A course enrolment manager for stoa enrolment
 *
 */
class course_enrolment_manager_stoa extends course_enrolment_manager {
    
    protected function get_config($key) {
        $config = get_config('enrol_stoa');
        return $config->{$key};
    }
    
    public function get_potential_users($enrolid, $search='', $searchanywhere=false, $page=0, $perpage=25,$addedenrollment = 0,  $returnexactcount = false) {
        global $DB, $CFG;
        
        if (!$enrol_stoa = enrol_get_plugin('stoa')) {
            throw new coding_exception('Can not instantiate enrol_stoa');
        }
        $usercondition = $enrol_stoa->get_condition_for_users($this->course);
       
        // Add some additional sensible conditions
        $tests = array("u.id <> :guestid", 'u.deleted = 0', 'u.confirmed = 1');
        $params = array('guestid' => $CFG->siteguest);
        if (!empty($search)) {
            $conditions = get_extra_user_fields($this->get_context());
            $conditions[] = $DB->sql_concat('u.firstname', "' '", 'u.lastname');
            if ($searchanywhere) {
                $searchparam = '%' . $search . '%';
            } else {
                $searchparam = $search . '%';
            }
            $i = 0;
            foreach ($conditions as $key=>$condition) {
                $conditions[$key] = $DB->sql_like($condition,":con{$i}00", false);
                $params["con{$i}00"] = $searchparam;
                $i++;
            }
            $tests[] = '(' . implode(' OR ', $conditions) . ')';
        }
        $wherecondition = implode(' AND ', $tests);

        $extrafields = get_extra_user_fields($this->get_context(), array('username', 'lastaccess'));
        $extrafields[] = 'username';
        $extrafields[] = 'lastaccess';
        $ufields = user_picture::fields('u', $extrafields);

        $fields      = 'SELECT '.$ufields;
        $countfields = 'SELECT COUNT(1)';
        $sql = " FROM {user} u
            LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid = :enrolid)
                WHERE $wherecondition";
        if (isset($usercondition) && !empty($usercondition)) {
            $sql .= "  AND u.$usercondition";
        } else {
            $sql .= "  AND 1 <> 1";
        }
        $sql .= "      AND ue.id IS NULL";
        $order = ' ORDER BY u.firstname ASC, u.lastname ASC';
        $params['enrolid'] = $enrolid;
        $totalusers = $DB->count_records_sql($countfields . $sql, $params);
        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params, $page*$perpage, $perpage);
        return array('totalusers'=>$totalusers, 'users'=>$availableusers);
    }
    
}

