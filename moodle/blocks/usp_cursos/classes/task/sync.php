<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * USP Cursos block task. Sincronizes with the external USP class and teacher systems  
 *
 * @package    block_usp_cursos
 * @copyright  20202 Ewout ter Haar <ewout@usp.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_usp_cursos\task;
defined('MOODLE_INTERNAL') || die();

class sync extends \core\task\scheduled_task {
    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('sync', 'block_usp_cursos');
    }
    /**
     * Execute the task.
     */
    public function execute() {
        global $CFG, $DB;
        /*if (empty($CFG->showcrondebugging)) {
            $trace = new \null_progress_trace();
        } else {
            $trace = new \text_progress_trace();
            }*/
        $trace = new \text_progress_trace();
        $trace->output("Iniciando task");

        if ($instances = $DB->get_records_sql("SELECT * FROM {block_instances} where blockname = 'usp_cursos'")) {
            $instance = reset($instances);
            block_load_class('usp_cursos');
            $blockobject = block_instance('usp_cursos');
            $blockobject->_load_instance($instance,NULL);
            $blockobject->init();
            $trace->output("Iniciando atualização da tabela");
            if($blockobject->update_all_courses($trace) && $blockobject->usp_update_prefixes($trace)) {
                $trace->output("iha!");
                return true;
            } else {
                echo "uops\n";
                return false;
            }
        }
    }
}
