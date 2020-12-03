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
 * Database enrolment plugin settings and presets.
 *
 * @package    enrol
 * @subpackage stoa
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_stoa_settings', '', get_string('pluginname_desc', 'enrol_stoa')));

    $settings->add(new admin_setting_heading('enrol_stoa_exdbheader', get_string('settingsheaderdb', 'enrol_stoa'), ''));

    $options = array('', "access","ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2", "fbsql", "firebird", "ibase", "informix72", "informix", "mssql", "mssql_n", "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc", "odbc_mssql", "odbc_oracle", "oracle", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp");
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('enrol_stoa/dbtype', get_string('dbtype', 'enrol_stoa'), get_string('dbtype_desc', 'enrol_stoa'), '', $options));

    $settings->add(new admin_setting_configtext('enrol_stoa/dbhost', get_string('dbhost', 'enrol_stoa'), get_string('dbhost_desc', 'enrol_stoa'), 'localhost'));

    $settings->add(new admin_setting_configtext('enrol_stoa/dbuser', get_string('dbuser', 'enrol_stoa'), '', ''));

    $settings->add(new admin_setting_configpasswordunmask('enrol_stoa/dbpass', get_string('dbpass', 'enrol_stoa'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_stoa/dbname', get_string('dbname', 'enrol_stoa'), '', ''));

    $settings->add(new admin_setting_configtext('enrol_stoa/dbencoding', get_string('dbencoding', 'enrol_stoa'), '', 'utf-8'));

    $settings->add(new admin_setting_configtext('enrol_stoa/dbsetupsql', get_string('dbsetupsql', 'enrol_stoa'), get_string('dbsetupsql_desc', 'enrol_stoa'), ''));

    $settings->add(new admin_setting_configcheckbox('enrol_stoa/dbsybasequoting', get_string('dbsybasequoting', 'enrol_stoa'), get_string('dbsybasequoting_desc', 'enrol_stoa'), 0));

    $settings->add(new admin_setting_configcheckbox('enrol_stoa/debugdb', get_string('debugdb', 'enrol_stoa'), get_string('debugdb_desc', 'enrol_stoa'), 0));



    $settings->add(new admin_setting_heading('enrol_stoa_localheader', get_string('settingsheaderlocal', 'enrol_stoa'), ''));

    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'shortname'=>'shortname');
    $settings->add(new admin_setting_configselect('enrol_stoa/localcoursefield', get_string('localcoursefield', 'enrol_stoa'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'email'=>'email', 'username'=>'username'); // only local users if username selected, no mnet users!
    $settings->add(new admin_setting_configselect('enrol_stoa/localuserfield', get_string('localuserfield', 'enrol_stoa'), '', 'idnumber', $options));

    $options = array('id'=>'id', 'shortname'=>'shortname', 'fullname'=>'fullname');
    $settings->add(new admin_setting_configselect('enrol_stoa/localrolefield', get_string('localrolefield', 'enrol_stoa'), '', 'shortname', $options));



    $settings->add(new admin_setting_heading('enrol_stoa_remoteheader', get_string('settingsheaderremote', 'enrol_stoa'), ''));

    $settings->add(new admin_setting_configtext('enrol_stoa/remoteenroltable', get_string('remoteenroltable', 'enrol_stoa'), get_string('remoteenroltable_desc', 'enrol_stoa'), ''));

    $settings->add(new admin_setting_configtext('enrol_stoa/remotecoursefield', get_string('remotecoursefield', 'enrol_stoa'), get_string('remotecoursefield_desc', 'enrol_stoa'), ''));

    $settings->add(new admin_setting_configtext('enrol_stoa/remoteuserfield', get_string('remoteuserfield', 'enrol_stoa'), get_string('remoteuserfield_desc', 'enrol_stoa'), ''));

    $settings->add(new admin_setting_configtext('enrol_stoa/remoterolefield', get_string('remoterolefield', 'enrol_stoa'), get_string('remoterolefield_desc', 'enrol_stoa'), ''));

    $settings->add(new admin_setting_configtext('enrol_stoa/remotestamtrfield', get_string('remotestamtrfield', 'enrol_stoa'), get_string('remotestamtrfield_desc', 'enrol_stoa'), 'stamtr'));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_stoa/defaultrole', get_string('defaultrole', 'enrol_stoa'), get_string('defaultrole_desc', 'enrol_stoa'), $student->id, $options));
    }

    $settings->add(new admin_setting_configcheckbox('enrol_stoa/ignorehiddencourses', get_string('ignorehiddencourses', 'enrol_stoa'), get_string('ignorehiddencourses_desc', 'enrol_stoa'), 0));

    $options = array(ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'enrol'),
                     ENROL_EXT_REMOVED_KEEP           => get_string('extremovedkeep', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'enrol'),
                     ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'enrol'));
    $settings->add(new admin_setting_configselect('enrol_stoa/unenrolaction', get_string('extremovedaction', 'enrol'), get_string('extremovedaction_help', 'enrol'), ENROL_EXT_REMOVED_UNENROL, $options));
    
    //-- user info
    $settings->add(new admin_setting_heading('enrol_stoa_remoteuserheader',
                                             get_string('settingsremoteuserheader', 'enrol_stoa'), ''));
    $settings->add(new admin_setting_configtext('enrol_stoa/remoteusertable',
                                                get_string('remoteusertable', 'enrol_stoa'),
                                                get_string('remoteusertable_desc', 'enrol_stoa'), ''));
    $settings->add(new admin_setting_configtext('enrol_stoa/remoteusercodefield',
                                                get_string('remoteusercodefield', 'enrol_stoa'),
                                                get_string('remoteusercodefield_desc', 'enrol_stoa'), ''));
    $settings->add(new admin_setting_configtext('enrol_stoa/remoteusernamefield',
                                                get_string('remoteusernamefield', 'enrol_stoa'),
                                                get_string('remoteusernamefield_desc', 'enrol_stoa'), ''));
    $settings->add(new admin_setting_configtext('enrol_stoa/remoteuseremailfield',
                                                get_string('remoteuseremailfield', 'enrol_stoa'),
                                                get_string('remoteuseremailfield_desc', 'enrol_stoa'), ''));
    
    //-- course info
    $settings->add(new admin_setting_heading('enrol_stoa_newcoursesheader',
                                             get_string('settingsheadernewcourses', 'enrol_stoa'), ''));
    $settings->add(new admin_setting_configtext('enrol_stoa/newcoursetable',
                                                get_string('newcoursetable', 'enrol_stoa'),
                                                get_string('newcoursetable_desc', 'enrol_stoa'), ''));
    $settings->add(new admin_setting_configtext('enrol_stoa/newcoursefullname',
                                                get_string('newcoursefullname', 'enrol_stoa'), '', 'fullname'));
    $settings->add(new admin_setting_configtext('enrol_stoa/newcourseshortname',
                                                get_string('newcourseshortname', 'enrol_stoa'), '', 'shortname'));
    $settings->add(new admin_setting_configtext('enrol_stoa/newcourseidnumber',
                                                get_string('newcourseidnumber', 'enrol_stoa'), '', 'idnumber'));
    $settings->add(new admin_setting_configtext('enrol_stoa/newcoursecategory',
                                                get_string('newcoursecategory', 'enrol_stoa'), '', ''));

    if (!during_initial_install()) {
        require_once($CFG->dirroot.'/course/lib.php');
        $options = core_course_category::make_categories_list();
        $settings->add(new admin_setting_configselect('enrol_stoa/defaultcategory',
                            get_string('defaultcategory', 'enrol_stoa'), get_string('defaultcategory_desc', 'enrol_stoa'), 1, $options));
    }

    $settings->add(new admin_setting_configtext('enrol_stoa/templatecourse',
                            get_string('templatecourse', 'enrol_stoa'), get_string('templatecourse_desc', 'enrol_stoa'), ''));

    
    $settings->add(new admin_setting_heading('enrol_stoa_turmasheader',
                                             get_string('settingsheaderturmas', 'enrol_stoa'), ''));
    $settings->add(new admin_setting_configtext('enrol_stoa/turmastable', get_string('turmastable', 'enrol_stoa'), '',''));

}


