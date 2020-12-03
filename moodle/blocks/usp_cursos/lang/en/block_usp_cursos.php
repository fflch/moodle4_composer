<?php

$string['pluginname'] = "Cursos na USP";
$string['pluginname_desc'] = "Este bloco permite que cada pessoa veja seus cursos (adicione à página principal).
                              No caso de professores, permite que estes criem seus cursos automaticamente ao clicar
                              no link, importando os alunos do curso e inserindo-o na categoria (departamento/unidade da USP)
                              correta - inclusive criando esta caso ainda não exista.";

$string['settingsheader'] = "Conexão com o banco de dados externo";
$string['dbtype'] = "Driver do banco de dados";
$string['dbtype_desc'] = "Nome do driver de base de dados ADOdb, tipo da base de dados externa.";
$string['dbhost'] = "Nome ou número IP do servidor";
$string['dbhost_desc'] = "Tipo do servidor do Banco de Dados ou hostname";
$string['dbuser'] = "Usuário do banco de dados";
$string['dbuser_desc'] = "Nome de usuário com permissão de leitura no bando de dados";
$string['dbpass'] = "Senha do banco de dados";
$string['dbname'] = "Nome do banco de dados";
$string['dbencoding'] = "Codificação do banco de dados";
$string['dbsetupsql'] = "Database setup command";
$string['dbsetupsql_desc'] = "Comando SQL para configuração especial de banco de dados, geralmente usado para configurar
                              a codificação da comunicação - por exemplo para o MySQL e PostgreSQL: SET NAMES 'utf8' </ em>";
$string['dbsybasequoting'] = "Usar notação Sybase";
$string['debugdb'] = "Debug ADOdb";
$string['debugdb_desc'] = "Depurar a conexão do ADOdb à base de dados externa - usar quando retornada uma página em
                           branco durante o login. Não é adequado para ambientes de produção!";

$string['settingsheaderlocal'] = "Mapeamento de campos local";
$string['localcoursefield'] = "Campo curso local";
$string['localuserfield'] = "Campo usuario local";

$string['remotetutorenrolheader'] = 'Remote tutor enrolment';
$string['remotetutortable'] = 'Remote tutor enrolment table';
$string['remotetutortable_desc'] = 'Specify the name of the table that contains list of tutor enrolments';
$string['remotecoursetutorfield'] = 'Remote course field in tutor enrolment';
$string['remotecoursetutorfield_desc'] = 'The name of the course field in the remote tutor enrolment table';
$string['remoteusertutorfield'] = 'Remote user field in tutor enrolment';
$string['remoteusertutorfield_desc'] = 'The name of the user field in the remote tutor enrolment table';
$string['remotecoursetypetutorfield'] = 'Remote course type field in tutor enrolment';
$string['remotecoursetypetutorfield_desc'] = 'The name of the course type field in the remote tutor enrolment table';

$string['remoteenrolheader'] = 'Remote user enrolment';
$string['remoteenroltable'] = 'Remote user enrolment table';
$string['remoteenroltable_desc'] = 'Specify the name of the table that contains list of user enrolments';
$string['remotecourseenrolfield'] = 'Remote course enrolment field';
$string['remotecourseenrolfield_desc'] = 'The name of the course field in the remote enrolment table';
$string['remoteuserenrolfield'] = 'Remote user enrolment field';
$string['remoteuserenrolfield_desc'] = 'The name of the user field in the remote enrolment table';
$string['remoteroleenrolfield'] = 'Remote role enrolment field';
$string['remoteroleenrolfield_desc'] = 'The name of the role field in the remote enrolment table that we are using to match entries.';
$string['defaultroleenrolfield'] = 'Default role enrolment';
$string['defaultroleenrol_desc'] = 'The role that will be assigned by default if no other role is specified in external enrolment table.';

$string['remoteemailheader'] = 'Remote user email';
$string['remoteemailtable'] = 'Remote user email table';
$string['remoteemailtable_desc'] = 'Specify the name of the table that contains list of user emails';
$string['remoteaddressemailfield'] = 'Remote address email field';
$string['remoteaddressemailfield_desc'] = 'The name of the address in the remote email table';
$string['remoteuseremailfield'] = 'Remote user email field';
$string['remoteuseremailfield_desc'] = 'The name of the user field in the remote email table';

$string['settingsheadernewcourses'] = "Criação de novos cursos";
$string['newcoursecategory'] = "Categoria do novo curso";
$string['creatornewrole'] = "Função do autor no novo curso";

$string['settingsunidadevisitantesheader'] = "Unidades com padrão = fechada";
$string['unidadesfechadas'] = "Unidades fechadas por padrão";
$string['unidadesfechadas_help'] = "Uma lista, um por linha, de unidades com ambientes fechados por padrão na hora de criar o ambiente.";

$string['find'] = "Find courses";
$string['enrol'] = "Enrolments";
$string['end'] = "End";

$string['messagetext'] = 'Dear student, professor {$a->instructor}, responsible for the course {$a->course} is using ';
$string['messagetext'] .= 'the <a href="https://edisciplinas.usp.br">Moodle of USP</a> as an online space. To get access to the activities and content published there, ';
$string['messagetext'] .= 'access the system using your <a href="https://id.usp.br">USP password</a>.';
$string['noreplysubject'] = 'Invitation to access the Moodle of USP (do not respond)';

$string['heading_wizard_create'] = 'USP courses: Create';
$string['heading_wizard_join'] = 'USP courses: Join another';

$string['currentstageSTAGE_COURSE'] = 'Course settings';
$string['currentstageSTAGE_JOIN'] = 'Join settings';
$string['currentstageSTAGE_MESSAGE'] = 'Invite messages';
$string['currentstageSTAGE_FINAL'] = 'Execute';
$string['currentstageSTAGE_COMPLETE'] = 'End';

$string['previousstage'] = 'Previous';
$string['wizardstageSTAGE_COURSEaction'] = 'Next';
$string['wizardstageSTAGE_JOINaction'] = 'Next';
$string['wizardstageSTAGE_MESSAGEaction'] = 'Execute';

$string['enrol_selected_users'] = 'Selected users';
$string['enrol_potential_users'] = 'Potential users';
$string['message_selected_users'] = 'Selected users';
$string['message_potential_users'] = 'Potential users';

$string['message'] = 'Message';
$string['emailmessage'] = 'Invite students to register';
$string['emailmessage_help'] = 'This message is send to students without acccounts in this Moodle. ';
$string['emailmessage_help'] .= 'The message must contain information that explains how to access the Moodle of USP.';

$string['selectcourse'] = 'Select a course';
$string['joincoursewith'] = 'Find another course in moodle to join with another course';
$string['totalcoursesearchresults'] = 'Number of courses: {$a}';
$string['nomatchingcourses'] = "We couldn't find an existing course with this name. Click Continue to choose another or contact us: suporte@edisciplinas.usp.br.";

$string['wizardjoin_heading'] = 'Enroll course sections';
$string['wizardcreate_heading'] = 'Create a new course';

$string['wizardSTAGE_COURSE_info'] = '<div class="alert alert-block alert-info"><p>The main settings of the new course (they can be changed, now or after the course creation).</p> <p>Click on the "Next" button at the end of the form to advance.</p></div>';
$string['wizardSTAGE_JOIN_info'] = 'Use this form to define the JOIN settings';
$string['wizardSTAGE_MESSAGE_info'] = 'Use this form to define the invite message that is going to send users';
$string['wizardSTAGE_COMPLETE_info'] = 'Your course was created! Click "Continue" and start configuring your course or <a href="https://docs.atp.usp.br/artigos/importar-conteudos-de-anos-passados/" target="_blank">use "Import" to use import your archived courses</a>.';

// @codely.com.br >>> block_usp_cursos WIZARD
$string['methodguest'] = 'Guest access';
$string['methodguesthelp'] = 'Guest access';
$string['methodguesthelp_help'] = 'A course with guest acces allows anyone (even non-authenticated visitors) to access the educational resources. Visitors cannot participate and never have access to student contributions (Messages, Forums, Assignments, etc.). Choose "No" here if your files should\'t be visible for visitors or Google.';

// STAGE JOIN
$string['wizardSTAGE_JOIN_info'] = '<div class="alert alert-block alert-info"><p>Choose the sections (students and teachers) from Júpiter/Janus which will be enrolled into this course. We recommend joining sections into one course, if possible.</p><p>[Sections associated with existing courses are shown, but can\'t be joined to this courses. Contact suporte@edisciplinas.usp.br if you need to join these sections or resolve more complex cases.]</p></div>';
$string['currentstageSTAGE_JOIN'] = 'Enrollments';
$string['wizardstageSTAGE_JOINaction'] = 'Next';
$string['join'] = 'Enrol students and teachers';
$string['teachers'] = 'Teacher(s)';
$string['class'] = 'Section';
$string['moodlecourse'] = 'Existing Course';

// STAGE REVIEW
$string['currentstageSTAGE_REVIEW'] = 'Review';
$string['wizardSTAGE_REVIEW_info'] = '<h3>Review</h3><div class="alert alert-block alert-info"><p>Some configurations of the course about to be created. They can be updated after the creation of the course.</p></div>';
$string['wizardstageSTAGE_REVIEWaction'] = 'Execute';

// capabilities
$string['usp_cursos:addinstance'] = 'Add instance';

// admin create
$string['createcourse'] = 'Create';
$string['sync'] = "Sincronize class and teachers with Júpiter/Janus";