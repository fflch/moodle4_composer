<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // general settings 
    $settings->add(new admin_setting_heading('block_usp_cursos_settings', '', get_string('pluginname_desc', 'block_usp_cursos')));

    $settings->add(new admin_setting_heading('block_usp_cursos_settingsheader', get_string('settingsheader', 'block_usp_cursos'), ''));
    $options = array('', "access","ado_access", "ado", "ado_mssql", "borland_ibase", "csv", "db2", "fbsql", "firebird", "ibase",
                     "informix72", "informix", "mssql", "mssql_n", "mysql", "mysqli", "mysqlt", "oci805", "oci8", "oci8po", "odbc",
                     "odbc_mssql", "odbc_oracle", "oracle", "postgres64", "postgres7", "postgres", "proxy", "sqlanywhere", "sybase", "vfp");
    $options = array_combine($options, $options);
    $settings->add(new admin_setting_configselect('block_usp_cursos/dbtype', get_string('dbtype', 'block_usp_cursos'),
                                                  get_string('dbtype_desc', 'block_usp_cursos'), '', $options));
    
    $settings->add(new admin_setting_configtext('block_usp_cursos/dbhost', get_string('dbhost', 'block_usp_cursos'),
                                                get_string('dbhost_desc', 'block_usp_cursos'), 'localhost'));
    $settings->add(new admin_setting_configtext('block_usp_cursos/dbuser', get_string('dbuser', 'block_usp_cursos'), '', ''));
    $settings->add(new admin_setting_configpasswordunmask('block_usp_cursos/dbpass', get_string('dbpass', 'block_usp_cursos'), '', ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/dbname', get_string('dbname', 'block_usp_cursos'), '', ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/dbencoding', get_string('dbencoding', 'block_usp_cursos'), '', 'utf-8'));
    $settings->add(new admin_setting_configtext('block_usp_cursos/dbsetupsql', get_string('dbsetupsql', 'block_usp_cursos'),
                                                get_string('dbsetupsql_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configcheckbox('block_usp_cursos/dbsybasequoting', get_string('dbsybasequoting', 'block_usp_cursos'),
                                                    get_string('dbsybasequoting', 'block_usp_cursos'), 0));
    $settings->add(new admin_setting_configcheckbox('block_usp_cursos/debugdb', get_string('debugdb', 'block_usp_cursos'),
                                                    get_string('debugdb_desc', 'block_usp_cursos'), 0));
    
    $settings->add(new admin_setting_heading('block_usp_cursos_localheader', get_string('settingsheaderlocal', 'block_usp_cursos'), ''));
    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'shortname'=>'shortname');
    $settings->add(new admin_setting_configselect('block_usp_cursos/localcoursefield',
                       get_string('localcoursefield', 'block_usp_cursos'), '', 'idnumber', $options));
    $options = array('id'=>'id', 'idnumber'=>'idnumber', 'email'=>'email', 'username'=>'username');
    $settings->add(new admin_setting_configselect('block_usp_cursos/localuserfield',
                       get_string('localuserfield', 'block_usp_cursos'), '', 'idnumber', $options));
     
    // remote tutor enrolment table
    $settings->add(new admin_setting_heading('block_usp_cursos_remotetutorenrolheader',
          get_string('remotetutorenrolheader', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remotetutortable',
          get_string('remotetutortable', 'block_usp_cursos'), get_string('remotetutortable_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remotecoursetutorfield',
          get_string('remotecoursetutorfield', 'block_usp_cursos'), get_string('remotecoursetutorfield_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remoteusertutorfield',
          get_string('remoteusertutorfield', 'block_usp_cursos'), get_string('remoteusertutorfield_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remotecoursetypetutorfield',
          get_string('remotecoursetypetutorfield', 'block_usp_cursos'), get_string('remotecoursetypetutorfield_desc', 'block_usp_cursos'), ''));
       
    // remote enrolment table
    $settings->add(new admin_setting_heading('block_usp_cursos_remoteenrolheader',
               get_string('remoteenrolheader', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remoteenroltable',
               get_string('remoteenroltable', 'block_usp_cursos'), get_string('remoteenroltable_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remotecourseenrolfield',
               get_string('remotecourseenrolfield', 'block_usp_cursos'), get_string('remotecourseenrolfield_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remoteuserenrolfield',
               get_string('remoteuserenrolfield', 'block_usp_cursos'), get_string('remoteuserenrolfield_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remoteroleenrolfield', get_string('remoteroleenrolfield', 'block_usp_cursos'),
               get_string('remoteroleenrolfield_desc', 'block_usp_cursos'), ''));
    if (!during_initial_install()) {
        $options = get_default_enrol_roles(context_system::instance());
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('block_usp_cursos/defaultroleenrolfield', get_string('defaultroleenrolfield', 'block_usp_cursos'),
                get_string('defaultroleenrol_desc', 'block_usp_cursos'), $student->id, $options));
    }
    
    // remote email table
    $settings->add(new admin_setting_heading('block_usp_cursos_remoteemailheader',
               get_string('remoteemailheader', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remoteemailtable',
               get_string('remoteemailtable', 'block_usp_cursos'), get_string('remoteemailtable_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remoteaddressemailfield',
               get_string('remoteaddressemailfield', 'block_usp_cursos'), get_string('remoteaddressemailfield_desc', 'block_usp_cursos'), ''));
    $settings->add(new admin_setting_configtext('block_usp_cursos/remoteuseremailfield',
               get_string('remoteuseremailfield', 'block_usp_cursos'), get_string('remoteuseremailfield_desc', 'block_usp_cursos'), ''));
    
    // new course
    $settings->add(new admin_setting_heading('block_usp_cursos_newcoursesheader',
                       get_string('settingsheadernewcourses', 'block_usp_cursos'), ''));
    $options =core_course_category::make_categories_list();
    $settings->add(new admin_setting_configselect('block_usp_cursos/newcoursecategory',
                       get_string('newcoursecategory', 'block_usp_cursos'), '', '', $options));
    $options = array();
    foreach (get_all_roles() as $roleid=>$role) { $options[$roleid] = $role->name; }
    $settings->add(new admin_setting_configselect('block_usp_cursos/creatornewrole',
                       get_string('creatornewrole', 'block_usp_cursos'), '', 'Teacher', $options));

    // Exceções para padrão = aberto
    $settings->add(new admin_setting_heading('block_usp_cursos_unidadevisitantesheader',
                       get_string('settingsunidadevisitantesheader', 'block_usp_cursos'), ''));
    $name = new lang_string('unidadesfechadas', 'block_usp_cursos');
    $description = new lang_string('unidadesfechadas_help', 'block_usp_cursos');
    $setting = new admin_setting_configtextarea('block_usp_cursos/unidadesfechadas',$name,$description,'');
    $settings->add($setting);


    
}

