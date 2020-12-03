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

defined('MOODLE_INTERNAL') || die();

function xmldb_block_usp_cursos_upgrade($oldversion, $block) {
    global $CFG, $DB;
    
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2017071500) {

        // Define field dataini to be added to block_usp_cursos.
        $table = new xmldb_table('block_usp_cursos');
        $field = new xmldb_field('dataini', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'checked');

        // Conditionally launch add field dataini.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Usp_cursos savepoint reached.
        upgrade_block_savepoint(true, 2017071500, 'usp_cursos');
    }

    if ($oldversion < 2018010200) {

        // Define field datafim to be added to block_usp_cursos.
        $table = new xmldb_table('block_usp_cursos');
        $field = new xmldb_field('datafim', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'dataini');

        // Conditionally launch add field datafim.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Usp_cursos savepoint reached.
        upgrade_block_savepoint(true, 2018010200, 'usp_cursos');
    }

    return true;
}