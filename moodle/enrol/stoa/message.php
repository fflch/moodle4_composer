<?php

/**
 *
 * @package    enrol
 * @subpackage stoa2
 */

require('../../config.php');
require_once($CFG->dirroot.'/enrol/stoa/locallib.php');

$issession    = optional_param('session', false, PARAM_BOOL);
$enrolid      = required_param('enrolid', PARAM_INT);
$roleid       = optional_param('roleid', -1, PARAM_INT);
$extendperiod = optional_param('extendperiod', 0, PARAM_INT);
$extendbase   = optional_param('extendbase', 3, PARAM_INT);

$instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'stoa'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/stoa:enrol', $context);
require_capability('enrol/stoa:manage', $context);
require_capability('enrol/stoa:unenrol', $context);

if ($roleid < 0) {
    $roleid = $instance->roleid;
}
$roles = get_assignable_roles($context);
$roles = array('0'=>get_string('none')) + $roles;

if (!isset($roles[$roleid])) {
    // weird - security always first!
    $roleid = 0;
}

if (!$enrol_stoa = enrol_get_plugin('stoa')) {
    throw new coding_exception('Can not instantiate enrol_stoa');
}

$instancename = $enrol_stoa->get_instance_name($instance);

$PAGE->set_url('/enrol/stoa/message.php', array('enrolid'=>$instance->id, 'session'=>true));
$PAGE->set_pagelayout('admin');
$PAGE->set_title($enrol_stoa->get_instance_name($instance));
$PAGE->set_heading($course->fullname);
navigation_node::override_active_url(new moodle_url('/enrol/users.php', array('id'=>$course->id)));

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

// session
if (!$issession ) {
   unset($_SESSION['selecteduser']);
}

// process send mail
if ($issession && optional_param('send', false, PARAM_BOOL) && confirm_sesskey()) {
    $site = get_site();
    $subject = get_string('noreplysubject', 'enrol_stoa', format_string($site->fullname));
    $body = required_param('bodyemail', PARAM_TEXT);

    foreach ($_SESSION['selecteduser'] as $user) {
        $emails = explode(',', $user->email);
        foreach ($emails as $email) {
            $user->email = $email;
            $codpes = $user->idnumber;
            $emailbody = str_replace("{CODPES}",$codpes,$body);
            email_to_user($user, core_user::get_support_user(), $subject, $emailbody);
        }
    }
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
}

// process remove users
if ($issession && optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $usercode = required_param('code', PARAM_TEXT);
    unset($_SESSION['selecteduser'][$usercode]);
}

// Create the user selector objects.
$options = array('enrolid' => $enrolid, 'enrol_stoa'=>$enrol_stoa, 'course'=>$course);
if ($issession && isset($_SESSION['selecteduser'])) {
    $options['selecteduser'] = $_SESSION['selecteduser'];
}
$userselector = new enrol_stoa_prepotential_participant('addselect', $options);

// process add users
if ($issession && optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $userselector->get_selected_users();
    if (!empty($userstoassign)) {
        foreach($userstoassign as $adduser) {
            if (!isset($_SESSION['selecteduser'])) {
                $_SESSION['selecteduser'] = array();
            }
            $_SESSION['selecteduser'][$adduser->id] = $adduser;
        }
        
        //$userselector->invalidate_selected_users();
        //$currentuserselector->invalidate_selected_users();
        
        //TODO: log
    }
    $options['selecteduser'] = $_SESSION['selecteduser'];
    $userselector = new enrol_stoa_prepotential_participant('addselect', $options);
}

$username = $USER->firstname.' '.$USER->lastname;
$messagetext = get_string('messagetext', 'enrol_stoa',
                (object) array('instructor'=>$username, 'course'=>$course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading($instancename);

echo '<div>'.get_string('messageheader_desc','enrol_stoa').'</div>';
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url; ?>">
<div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>" />
  <table summary="" class="roleassigntable generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td id="existingcell">
          <p><label for="addselect"><?php print_string('enrolcandidates', 'enrol'); ?></label></p>
          <?php $userselector->display(); ?>
      </td>
      <td id="messagecell">
        <div id="controls">
          <input name="add" id="add" type="submit"
                 value="<?php echo get_string('add').'&nbsp;'.$OUTPUT->rarrow(); ?>"
                 title="<?php print_string('add'); ?>" /> (<?php
            $removelink = new moodle_url('/enrol/stoa/message.php', array('enrolid'=>$instance->id,
                                                                          'sesskey'=>sesskey()));
            echo get_string('removeall', 'enrol_stoa').' <span class="removecontact">'.
                            $OUTPUT->action_icon($removelink, new pix_icon('t/delete', get_string('remove'),
                                                          'core', array('class'=>'iconsmall'))).'</span>
                  '; ?>)
        </div>
        <div><?php print_string('to', 'enrol_stoa'); ?>:</div>
        <div id="selectedusers"><?php if ($issession && isset($_SESSION['selecteduser'])) {
        foreach ($_SESSION['selecteduser'] as $suser) {
            $removelink = new moodle_url('/enrol/stoa/message.php', array('enrolid'=>$instance->id,
                                                                          'remove'=>'true', 'code'=>$suser->id,
                                                                          'sesskey'=>sesskey(), 'session'=>true));
            echo '<div class="contact">'.$suser->firstname.' ('.$suser->idnumber.') <span class="removecontact">'.
                    $OUTPUT->action_icon($removelink, new pix_icon('t/delete', get_string('remove'),
                                                          'core', array('class'=>'iconsmall'))).'</span>
                  </div>';
        }} ?></div>
        <p></p>
        <p><label for="bodyemail"><?php print_string('bodyemail', 'enrol_stoa'); ?></label></p>
        <?php
            $attributes['name'] = 'bodyemail';
            $attributes['rows'] = 10;
            $attributes['cols'] = 40;
            $attributes['style'] = "width: 100%";
            echo html_writer::tag('textarea', $messagetext, $attributes);
        ?>
        <p><?php echo html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'send', 'value'=>get_string('sendmessage', 'message'))); ?></p> 
      </td>
    </tr>
  </table>
</div>
</form>
<?php
echo $OUTPUT->footer();

