<?php
/** sync_usp_cursos.php
 *
 *
 */
define('CLI_SCRIPT', true);
require_once(dirname(dirname(dirname(__FILE__))).'/config.php'); // global moodle config file.
require_once("$CFG->libdir/blocklib.php");
require_once("$CFG->libdir/clilib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('verbose' => false, 'help' => false),
                                               array('v' => 'verbose', 'h' => 'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}


if ($options['help'] ) {
    $help = "Execute cohort sync with external database.

Options:
-v, --verbose         Print verbose progress information
";

    echo $help;
    die;
}

if (empty($options['verbose'])) {
    $trace = new null_progress_trace();
} else {
    $trace = new text_progress_trace();
}

$nomoodlecookie = true; // cookie not needed

if ($instances = $DB->get_records_sql("SELECT * FROM {block_instances} where blockname = 'usp_cursos'")) {
    $instance = reset($instances);
    block_load_class('usp_cursos');
    $blockobject = block_instance('usp_cursos');
    $blockobject->_load_instance($instance,NULL);
    $blockobject->init();
    if($blockobject->update_all_courses($trace) && $blockobject->usp_update_prefixes($trace)) {
        $trace->output("iha!");
        return true;
    } else {
        echo "uops\n";
        return false;
    }
}