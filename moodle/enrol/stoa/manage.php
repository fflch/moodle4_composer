<?php

/**
 * @package    enrol
 * @subpackage stoa2
 */

require('../../config.php');
require_once($CFG->dirroot.'/enrol/stoa/locallib.php');

$enrolid      = required_param('enrolid', PARAM_INT);
$roleid       = optional_param('roleid', 0, PARAM_INT);
$extendperiod = optional_param('extendperiod', 0, PARAM_INT);
$extendbase   = optional_param('extendbase', 3, PARAM_INT);

$instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'stoa'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
$viewfullnames = has_capability('moodle/site:viewfullnames', $context);
require_capability('enrol/stoa:enrol', $context);
require_capability('enrol/stoa:manage', $context);
require_capability('enrol/stoa:unenrol', $context);

//-- get roles info
if (!$roleid = $instance->roleid) {
    $roleid = get_config("enrol_stoa")->defaultrole;
}
$roles = get_assignable_roles($context);
$roles = array('0'=>get_string('none')) + $roles;

if (!isset($roles[$roleid])) { // weird - security always first!
    $roleid = 0;
}

if (!$enrol_stoa = enrol_get_plugin('stoa')) {
    throw new coding_exception('Can not instantiate enrol_stoa');
}

$instancename = $enrol_stoa->get_instance_name($instance);

$PAGE->set_url('/enrol/stoa/manage.php', array('enrolid'=>$instance->id));
$PAGE->set_pagelayout('admin');
$PAGE->set_title($enrol_stoa->get_instance_name($instance));
$PAGE->set_heading($course->fullname);
navigation_node::override_active_url(new moodle_url('/enrol/users.php', array('id'=>$course->id)));

// Create the user selector objects.
$options = array('enrolid' => $enrolid, 'enrol_stoa'=>$enrol_stoa, 'course'=>$course, 'accesscontext' => $context);

$currentuserselector = new enrol_stoa_current_participant('removeselect', $options);
$currentuserselector->viewfullnames = $viewfullnames;
$potentialuserselector = new enrol_stoa_potential_participant('addselect', $options);
$potentialuserselector->viewfullnames = $viewfullnames;


// Build the list of options for the enrolment period dropdown.
$unlimitedperiod = get_string('unlimited');
$periodmenu = array();
for ($i=1; $i<=365; $i++) {
    $seconds = $i * 86400;
    $periodmenu[$seconds] = get_string('numdays', '', $i);
}
// Work out the apropriate default setting.
if ($extendperiod) {
    $defaultperiod = $extendperiod;
} else {
    $defaultperiod = $instance->enrolperiod;
}

// Build the list of options for the starting from dropdown.
$timeformat = get_string('strftimedatefullshort');
$today = time();
$today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

// enrolment start
$basemenu = array();
if ($course->startdate > 0) {
    $basemenu[2] = get_string('coursestart') . ' (' . userdate($course->startdate, $timeformat) . ')';
}
$basemenu[3] = get_string('today') . ' (' . userdate($today, $timeformat) . ')' ;

// process add and removes
if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {
        foreach($userstoassign as $adduser) {
            switch($extendbase) {
                case 2:
                    $timestart = $course->startdate;
                    break;
                case 3:
                default:
                    $timestart = $today;
                    break;
            }

            if ($extendperiod <= 0) {
                $timeend = 0;
            } else {
                $timeend = $timestart + $extendperiod;
            }
            $enrol_stoa->enrol_user($instance, $adduser->id, $roleid, $timestart, $timeend);
        }
        $potentialuserselector->invalidate_selected_users();
        $currentuserselector->invalidate_selected_users();
    }
}

// Process incoming role unassignments
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstounassign = $currentuserselector->get_selected_users();
    if (!empty($userstounassign)) {
        foreach($userstounassign as $removeuser) {
            $enrol_stoa->unenrol_user($instance, $removeuser->id);
        }
        $potentialuserselector->invalidate_selected_users();
        $currentuserselector->invalidate_selected_users();
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($instancename);

echo '<div>'.get_string('manageheader_desc','enrol_stoa').'</div>';
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url; ?>">
<div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
  <table summary="" class="roleassigntable generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="removeselect"><?php print_string('enrolledusers', 'enrol'); ?></label></p>
          <?php $currentuserselector->display(); ?>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" id="add" type="submit"
                     value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>"
                     title="<?php print_string('add'); ?>" /><br />

              <div class="enroloptions">
              <p><label for="roleid"><?php print_string('assignrole', 'enrol_stoa') ?></label><br />
              <?php echo html_writer::select($roles, 'roleid', $roleid, false); ?></p>

              <p><label for="extendperiod"><?php print_string('enrolperiod', 'enrol') ?></label><br />
              <?php echo html_writer::select($periodmenu, 'extendperiod', $defaultperiod, $unlimitedperiod); ?></p>
              
              <p><label for="extendbase"><?php print_string('startingfrom') ?></label><br />
              <?php echo html_writer::select($basemenu, 'extendbase', $extendbase, false); ?></p>
              </div>
          </div>
          <div id="removecontrols">
              <input name="remove" id="remove" type="submit"
                     value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>"
                     title="<?php print_string('remove'); ?>" />
          </div>
      </td>
      <td id="potentialcell">
          <p><label for="addselect"><?php print_string('enrolcandidates', 'enrol'); ?></label>
          <?php $potentialuserselector->display(); ?></p>
      </td>
    </tr>
  </table>
</div>
</form>
<?php
echo $OUTPUT->footer();

