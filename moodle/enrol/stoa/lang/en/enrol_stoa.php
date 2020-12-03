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
 * Strings for component 'enrol_stoa', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   enrol_stoa
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['dbencoding'] = 'Database encoding';
$string['dbhost'] = 'Database host';
$string['dbhost_desc'] = 'Type stoa server IP address or host name';
$string['dbname'] = 'Database name';
$string['dbpass'] = 'Database password';
$string['dbsetupsql'] = 'Database setup command';
$string['dbsetupsql_desc'] = 'SQL command for special stoa setup, often used to setup communication encoding - example for MySQL and PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Use sybase quotes';
$string['dbsybasequoting_desc'] = 'Sybase style single quote escaping - needed for Oracle, MS SQL and some other stoas. Do not use for MySQL!';
$string['dbtype'] = 'Database driver';
$string['dbtype_desc'] = 'ADOdb stoa driver name, type of the external stoa engine.';
$string['dbuser'] = 'Database user';
$string['debugdb'] = 'Debug ADOdb';
$string['debugdb_desc'] = 'Debug ADOdb connection to external stoa - use when getting empty page during login. Not suitable for production sites!';
$string['defaultcategory'] = 'Default new course category';
$string['defaultcategory_desc'] = 'The default category for auto-created courses. Used when no new category id specified or not found.';
$string['defaultrole'] = 'Default role';
$string['defaultrole_desc'] = 'The role that will be assigned by default if no other role is specified in external table.';
$string['ignorehiddencourses'] = 'Ignore hidden courses';
$string['ignorehiddencourses_desc'] = 'If enabled users will not be enrolled on courses that are set to be unavailable to students.';
$string['localcoursefield'] = 'Local course field';
$string['localrolefield'] = 'Local role field';
$string['localuserfield'] = 'Local user field';
$string['newcoursetable'] = 'Remote new courses table';
$string['newcoursetable_desc'] = 'Specify of the name of the table that contains list of courses that should be created automatically. Empty means no courses are created.';
$string['newcoursecategory'] = 'New course category id field';
$string['newcoursefullname'] = 'New course full name field';
$string['newcourseidnumber'] = 'New course ID number field';
$string['newcourseshortname'] = 'New course short name field';

$string['settingsheaderturmas'] = 'Remote turmas table';
$string['turmastable'] = 'Turmas table';

$string['pluginname'] = 'USP Enrolments ';

$string['pluginname_desc'] = 'You can use this plugin to integrate this Moodle with a copy of the Júpiter/Janus enrolment databases';
$string['remotecoursefield'] = 'Remote course field';
$string['remotecoursefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the course table.';
$string['remoteenroltable'] = 'Remote user enrolment table';
$string['remoteenroltable_desc'] = 'Specify the name of the table that contains list of user enrolments. Empty means no user enrolment sync.';
$string['remoterolefield'] = 'Remote role field';
$string['remoterolefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the roles table.';
$string['remotestamtrfield'] = "Remote Matriculation Status";
$string['remotestamtrfield_desc'] = "The name of the field in the remote table that we are using to define the matriculation status";
$string['remoteuserfield'] = 'Remote user field';
$string['settingsheaderdb'] = 'USP enrolments connection';
$string['settingsheaderlocal'] = 'Local field mapping';
$string['settingsheaderremote'] = 'Remote enrolment sync';
$string['settingsheadernewcourses'] = 'Creation of new courses';
$string['remoteuserfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the user table.';
$string['templatecourse'] = 'New course template';
$string['templatecourse_desc'] = 'Optional: auto-created courses can copy their settings from a template course. Type here the shortname of the template course.';

$string['settingsremoteuserheader'] = 'Remote user information';
$string['remoteusertable'] = 'Remote user information table';
$string['remoteusertable_desc'] = 'Specify the name of the table that contains list of user informations.';
$string['remoteusercodefield'] = 'Remote user code field';
$string['remoteusercodefield_desc'] = 'The name of the field in the remote table that we are using how user identifier.';
$string['remoteusernamefield'] = 'Remote user name field';
$string['remoteusernamefield_desc'] = 'The name of the field in the remote table that we are using how user name.';
$string['remoteuseremailfield'] = 'Remote user email field';
$string['remoteuseremailfield_desc'] = 'The name of the field in the remote table that we are using how user email.';

$string['manageheader_desc'] = 'This page allows you to control the enrolments by the USP enrolments plugin.';
$string['enrolusers'] = 'Enrol users with USP integration plugin';
$string['sortenrolusers'] = 'USP enrol';
$string['sendmessage'] = 'Send an invitation by email';
$string['messageheader_desc'] = 'Here you can send an invitation to unregistered users to access this Moodle.';
$string['to'] = 'Recipients';

$string['status'] = 'Enable USP enrolments';
$string['status_desc'] = 'Allow course access of internally enrolled users. This should be kept enabled in most cases.';
$string['status_help'] = 'This setting determines whether users can be enrolled manually, via a link in the course administration settings, by a user with appropriate permissions such as a teacher.';
$string['defaultperiod'] = 'Default enrolment duration';
$string['defaultperiod_desc'] = 'Default length of time that the enrolment is valid (in seconds). If set to zero, the enrolment duration will be unlimited by default.';
$string['defaultperiod_help'] = 'Default length of time that the enrolment is valid, starting with the moment the user is enrolled. If disabled, the enrolment duration will be unlimited by default.';

$string['assignrole'] = 'Assign role';
$string['editenrolment'] = 'Edit USP enrolment';

$string['noreplysubject'] = 'Do not reply to this email';
$string['removeall'] = 'Remove all contacts';
$string['bodyemail'] = 'Write the content of an invitation';

$string['messagetext'] = 'Dear student,'."\n\n". 'Professor {$a->instructor}, responsible for the course {$a->course}, is using ';
$string['messagetext'] .= 'the Moodle of USP as an online space. To get access to the activities and content published there, ';
$string['messagetext'] .= "access the system at https://edisciplinas.usp.br using your USP password (see https://id.usp.br )\n\n";
$string['messagetext'] .= 'In case you need it, your USP student number is {CODPES}';
$string['messagetext'] .= "\n\n".'Regards - Team e-Disciplinas';

$string['unenrolselfconfirm'] = 'Are you sure you want to unenrol yourself com this course? Please note that if you are registered in Júpiter/Janus for this course <em>in the current year</em>, you will be re-enrolled the next time you login. In all other cases, you will loose access to this course. ';

$string['codmoodles'] = "Júpiter/Janus integration codes";
$string['codmoodles_desc'] = "Selection of the course/sections that will be enrolled.";
$string['codmoodles_help'] = "Start typing and select the course/section from Júpiter/Janus that will be enrolled in this course.";
$string['participants'] = "Registered in Júpiter/Janus";
$string['pienrol'] = "Enrol active";
$string['pisuspend'] = "Enrol suspended";
$string['piunenrol'] = "Don't enrol / unenrol (!)";
$string['p_enrolmode'] = "Suspend 'provisional' enrolments (P)";
$string['p_enrolmode_help'] = 'If enabled, enrol but suspend, students in the "P" state in Júpiter/Janus ("pendente")';
$string['i_enrolmode'] = "Suspend 'tentative' enrolments (I)";
$string['i_enrolmode_help'] = 'If enabled, enrol but suspend, students in the "I" state in Júpiter/Janus ("inscrito")';
$string['syncnow'] = "Sync now";
$string['syncnow_help'] = "Synchronize enrolment with Júpiter/Janus now";
$string['suspendothers'] = "Suspend excluded";
$string['suspendothers_help'] = "Suspend the students that were excluded by Júpiter/Janus \n<strong>Note:</strong> afterwards, you can select all suspended student and remove them from this course in the user list.";

// capabilities
$string['stoa:config'] = 'Configure USP enrolments';
$string['stoa:manage'] = 'Manage USP enrolments';
$string['stoa:enrol'] = ' Enrol someone via USP enrolments';
$string['stoa:unenrol'] = 'Unenrol someone via USP enrolments';
$string['stoa:unenrolself'] = 'Unenrol themselves via USP enrolments';

// Bulk operation
$string['editselectedusers'] = 'Edit selected users';
$string['deleteselectedusers'] = 'Delete selected users';
$string['confirmbulkdeleteenrolment'] = 'Confirm unenrol of the users above?';
$string['unenrolusers'] = 'Unenrol';

// Tasks
$string['syncenrolmentstask'] = "Sync for USP Enrolments";
$string['pluginnotenabled'] = "Plugin USP Enrolments is not enabled";
$string['synccohortstask'] = "USP course-cohort sync";