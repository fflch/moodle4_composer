<?php

require_once('../../config.php');
require_once('wizard.class.php');
require_once('wizard_ui.class.php');
require_once('wizard_controller.class.php');

$type = optional_param('type', wizard::TYPE_CREATE, PARAM_TEXT);
/**
 * Part of the forms in stages after initial, never use GET
 */
$wizardid = optional_param('wizard', false, PARAM_ALPHANUM);
$codmoodle = optional_param('codmoodle', '', PARAM_TEXT);

$PAGE->set_context(context_system::instance());
$url = new moodle_url('/blocks/usp_cursos/wizard.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');

require_login();
if (!confirm_sesskey()) {
    redirect(new moodle_url('/'));
}

switch ($type) {
    case wizard::TYPE_CREATE:
        $heading = get_string('heading_wizard_create', 'block_usp_cursos');
        break;
    case wizard::TYPE_JOIN:
        $heading = get_string('heading_wizard_join', 'block_usp_cursos');
        break;
    default:
        print_error('unknownwizardtype');
}

if (!($wc = wizard_ui::load_controller($wizardid))) {
    $wc = new wizard_controller($type, $codmoodle, $USER->id, required_param('id', PARAM_INT));
}

// create ui interface
$wizard = new wizard_ui($wc);
$wizard->process();
if ($wizard->is_final_stage()) {
    $wizard->execute();
} else {
    $wizard->save_controller();
}

$PAGE->set_title($heading.': '.$wizard->get_stage_name());
$PAGE->set_heading($heading);
$PAGE->navbar->add($wizard->get_stage_name());

// get renderer
$renderer = $PAGE->get_renderer('block_usp_cursos', 'wizard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('wizard'.$type.'_heading', 'block_usp_cursos'));
echo $renderer->progress_bar($wizard->get_progress_bar());
echo '<p></p>';
echo $OUTPUT->box(get_string('wizard'.$wizard->get_value_from_plan($wizard->get_stage()).'_info', 'block_usp_cursos'));
echo $wizard->display();


//// check if we are join
//if (($type == wizard::TYPE_JOIN) && !optional_param('joinid', false, PARAM_INT)) {
//    require_once('join_extension.php');
//    $search = new join_course_search(array('url'=>$url));
////    echo $OUTPUT->header();
//    echo $renderer->join_course_selector(new moodle_url($url, array('type'=>wizard::TYPE_JOIN,
//                                                                    'wizard'=>$wizard->get_uniqueid(),
//                                                                    'sesskey'=>sesskey())), $search);
////    echo $OUTPUT->footer();
////    die();
//}

$wizard->destroy();
unset($wizard);
echo $OUTPUT->footer();

