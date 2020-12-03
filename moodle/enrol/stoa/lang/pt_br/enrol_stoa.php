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
$string['dbhost_desc'] = 'Type server IP address or host name';
$string['dbname'] = 'Database name';
$string['dbpass'] = 'Database password';
$string['dbsetupsql'] = 'Database setup command';
$string['dbsetupsql_desc'] = 'SQL command for special setup, often used to setup communication encoding - example for MySQL and PostgreSQL: <em>SET NAMES \'utf8\'</em>';
$string['dbsybasequoting'] = 'Use sybase quotes';
$string['dbsybasequoting_desc'] = 'Sybase style single quote escaping - needed for Oracle, MS SQL and some other stoas. Do not use for MySQL!';
$string['dbtype'] = 'Database driver';
$string['dbtype_desc'] = 'ADOdb stoa driver name, type of the external engine.';
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
$string['pluginname'] = 'Inscrições da USP';

$string['pluginname_desc'] = 'You can use an external database (of nearly any kind) to control your enrolments. It is assumed your external database contains at least a field containing a course ID, and a field containing a user ID. These are compared against fields that you choose in the local course and user tables.';
$string['remotecoursefield'] = 'Remote course field';
$string['remotecoursefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the course table.';
$string['remoteenroltable'] = 'Remote user enrolment table';
$string['remoteenroltable_desc'] = 'Specify the name of the table that contains list of user enrolments. Empty means no user enrolment sync.';
$string['remoterolefield'] = 'Remote role field';
$string['remoterolefield_desc'] = 'The name of the field in the remote table that we are using to match entries in the roles table.';
$string['remoteuserfield'] = 'Remote user field';
$string['settingsheaderdb'] = 'External connection';
$string['settingsheaderlocal'] = 'Local field mapping';
$string['settingsheaderremote'] = 'Remote enrolment sync';
$string['settingsheadernewcourses'] = 'Creation of new courses';
$string['remoteuserfield_desc'] = 'The name of the field in the remote table that we are using to match entries in the user table.';
$string['templatecourse'] = 'New course template';
$string['templatecourse_desc'] = 'Optional: auto-created courses can copy their settings from a template course. Type here the shortname of the template course.';

$string['settingsremoteuserheader'] = 'Informação dos usuários remota';
$string['remoteusertable'] = 'Tabela com informações dos usuários';
$string['remoteusertable_desc'] = 'Definir o nome da tabela que contém informações dos usuários.';
$string['remoteusercodefield'] = 'Campo do código de usuário';
$string['remoteusercodefield_desc'] = 'O nome do campo na tabela que é usada como identificador do usuário.';
$string['remoteusernamefield'] = 'Campo do nome de usuário';
$string['remoteusernamefield_desc'] = 'O nome do campo na tabela que é usada como nome do usuário.';
$string['remoteuseremailfield'] = 'Campo do email de usuário';
$string['remoteuseremailfield_desc'] = 'O nome do campo na tabela que é usada como email do usuário.';

$string['manageheader_desc'] = 'Aqui pode editar manualmente as inscrições da integração com os sistemas da USP.';
$string['enrolusers'] = 'Inscrições da USP';
$string['sortenrolusers'] = 'Inscrições da USP';
$string['sendmessage'] = 'Envio de convite por email';
$string['messageheader_desc'] = 'Aqui pode convidar estudantes para entrar no Moodle da USP. Selecione estudantes da lista (Ctrl-A para selecionar todos) e escreve o seu convite que será enviado por email.';
$string['to'] = 'Destinatários';

$string['status'] = 'Habilitar inscrições da USP';
$string['status_desc'] = 'Permitir o acesso de usuários inscritos. Isto deve ser mantido activado, na maioria dos casos.';
$string['status_help'] = 'Essa opção determina se os usuários podem ser incritos mediante um link na configuração do curso, por um usuário com permissões adequadas, por exemplo um professor.';

$string['defaultperiod'] = 'Duração padrão de inscrição';
$string['defaultperiod_desc'] = 'Duração de tempo no que a inscrição é válida (em segundos). Se o valor é definido como zero, a duração da inscrição será ilimitado por padrão.';
$string['defaultperiod_help'] = 'Duração de tempo no que a inscrição é valida, inicia no momento no que o usuário é inscrito. Se é deshabilitado, a duração da inscrição é ilimitada por padrão.';

$string['assignrole'] = 'Atribuir papel';
$string['editenrolment'] = 'Editar inscrições da USP';

$string['noreplysubject'] = 'Não responda ao email';
$string['removeall'] = 'Remover todos';
$string['bodyemail'] = 'Se quiser, modifique o conteúdo do convite:';

$string['messagetext'] = 'Caro aluno,'."\n\n".'{$a->instructor}, docente da disciplina {$a->course}, está usando o ';
$string['messagetext'] .= 'Moodle da USP como ambiente de apoio online. Para ter acesso ao conteúdo e atividades, ';
$string['messagetext'] .= "acesse https://edisciplinas.usp.br (veja https://id.usp.br para criar ou recuperar sua senha da USP).\n\n";
$string['messagetext'] .= 'In case you need it, your USP student number is {CODPES}';
$string['messagetext'] .= "\n\n".'Att - Equipe e-Disciplinas';

$string['unenrolselfconfirm'] = 'Tem certeza que quer sair deste curso? Note que se ainda estiver matriculado nesta disciplina (no Júpiter ou Janus) <em>no ano corrente</em>, será inscrito aqui novamente ao entrar no Moodle da USP a próxima vez. Senão, será desinscrito e perderá accesso a este ambiente de apoio online.';

$string['codmoodles'] = "Códigos da integração com Júpiter/Janus";
$string['codmoodles_help'] = "Começa digitar e selecione o código da disciplina/turma do Júpiter/Janus. Esta turma será inscrito neste ambiente.";
$string['pienrol'] = "Inscrever normalmente";
$string['pisuspend'] = "Inscrever e 'suspender'";
$string['p_enrolmode'] = "Suspender pendentes";
$string['p_enrolmode_help'] =  "Suspender os estudantes no estado 'P' (pendentes) no Júpiter/Janus. <br />'Suspender' significa que podem acessar o ambiente somente como visitante (se o ambiente permite este acesso) e não podem fazer atividades e nem aparecem nas listas de participantes ou no quadro de notas.";
$string['i_enrolmode'] = "Suspender inscritos";
$string['i_enrolmode_help'] = "Suspender  os estudantes no estado 'I' (inscritos) no Júpiter. <br />'Suspender' significa que podem acessar o ambiente somente como visitante (se o ambiente permite este acesso) e não podem fazer atividades e nem aparecem nas listas de participantes ou no quadro de notas.";
$string['suspendothers'] = "Suspender excluídos";
$string['suspendothers_help'] = "Suspender estudantes que foram inscritos no passado por este método, mas que não estão mais matriculados ou inscritos no Júpiter ou Janus.<br />'Suspender' significa que podem acessar o ambiente somente como visitante (se o ambiente permite este acesso) e não podem fazer atividades e nem aparecem nas listas de participantes ou no quadro de notas.<br /><strong>Note:</strong> Se quiser <i>remover</i> estas contas, é possível selecionar todos as contas suspensas na lista de usuários e remover todos de uma vez.";

$string['syncnow'] = "Sincronizar agora";
$string['syncnow_help'] = "Selecionar esta opção significa que inscrevemos os estudantes agora mesmo. Caso contrário, serão inscritos na medida que acessem o ambiente.";
