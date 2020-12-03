<?php

require('../../config.php');
require_once('edit_form.php');

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
require_capability('enrol/stoa:config', $context);

$PAGE->set_url('/enrol/stoa/edit.php', array('courseid'=>$course->id));
$PAGE->set_pagelayout('admin');

$return = new moodle_url('/enrol/instances.php', array('id'=>$course->id));
if (!enrol_is_enabled('stoa')) {
    redirect($return);
}

$plugin = enrol_get_plugin('stoa');

if ($instances = $DB->get_records('enrol', array('courseid'=>$course->id, 'enrol'=>'stoa'), 'id ASC')) {
    $instance = array_shift($instances);
    if ($instances) {
        // oh - we allow only one instance per course!!
        foreach ($instances as $del) {
            $plugin->delete_instance($del);
        }
    }
} else {
    require_capability('moodle/course:enrolconfig', $context);
    // no instance yet, we have to add new instance
    navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    $instance = new stdClass();
    $instance->id       = null;
    $instance->courseid = $course->id;
}

$mform = new enrol_stoa_edit_form(NULL, array($instance, $plugin, $context));

if ($mform->is_cancelled()) {
    redirect($return);

} else if ($data = $mform->get_data()) { 
    if ($instance->id) {
        $reset = ($instance->status != $data->status);
        
        $instance->status       = $data->status;
        $instance->enrolperiod  = $data->enrolperiod;
        $instance->roleid       = $data->roleid;
        $instance->timemodified = time();
        $DB->update_record('enrol', $instance);
        
        if ($reset) {
            $context->mark_dirty();
        }
    
    } else {
        $fields = array('status'=>$data->status, 'enrolperiod'=>$data->enrolperiod, 'roleid'=>$data->roleid);
        $plugin->add_instance($course, $fields);
    }
    
    redirect($return);
}

$PAGE->set_title(get_string('pluginname', 'enrol_stoa'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'enrol_stoa'));
$mform->display();
echo $OUTPUT->footer();

