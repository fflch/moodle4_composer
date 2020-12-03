<?php

/**
 * Sync enrolments task for USP enrolments
 * @package enrol_stoa
 * based on enrol_ldap code:
 * @author    Guy Thomas <gthomas@moodlerooms.com>
 * @copyright Copyright (c) 2017 Blackboard Inc.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Adapted by:
 * @author Ewout ter Haar <ewout@usp.br>
 */

namespace enrol_stoa\task;

defined('MOODLE_INTERNAL') || die();

class sync_enrolments extends \core\task\scheduled_task {

    /**
     * Name for this task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('syncenrolmentstask', 'enrol_stoa');
    }

    /**
     * Run task for synchronising users.
     */
    public function execute() {

        if (!enrol_is_enabled('stoa')) {
            mtrace(get_string('pluginnotenabled', 'enrol_stoa'));
            exit(0); // Note, exit with success code, this is not an error - it's just disabled.
        }

        $enrol = enrol_get_plugin('stoa');

        $trace = new \text_progress_trace();

        // Update enrolments -- these handlers should autocreate courses if required.
        $enrol->sync_enrolments($trace);
    }

}
