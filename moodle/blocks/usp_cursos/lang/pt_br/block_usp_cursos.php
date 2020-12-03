<?php

$string['pluginname'] = "Cursos na USP";
$string['pluginname_desc'] = "Este bloco permite que cada pessoa veja seus cursos (adicione à página principal).
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

$string['remotetutorenrolheader'] = 'Inscrição de ministrantes remotos';
$string['remotetutortable'] = 'Tabela de inscrição dos ministrantes';
$string['remotetutortable_desc'] = 'O nome da tabela que contem a lista de inscrições dos ministrantes';
$string['remotecoursetutorfield'] = 'Campo curso remoto';
$string['remotecoursetutorfield_desc'] = 'O nome do campo curso na tabela de inscrição de ministrantes';
$string['remoteusertutorfield'] = 'Campo ministrante remoto';
$string['remoteusertutorfield_desc'] = 'O nome do campo ministrante na tabela de inscrição de ministrantes';
$string['remotecoursetypetutorfield'] = 'Campo tipo de curso remoto';
$string['remotecoursetypetutorfield_desc'] = 'O nome do campo tipo de curso na tabela de inscrição de ministrantes';

$string['remoteenrolheader'] = 'Inscrição de usuários remotos';
$string['remoteenroltable'] = 'Tabela de inscrição dos usuários';
$string['remoteenroltable_desc'] = 'O nome da tabela que contem a lista de inscrições dos usuários';
$string['remotecourseenrolfield'] = 'Campo curso remoto';
$string['remotecourseenrolfield_desc'] = 'O nome do campo curso na tabela de inscrições de usuários';
$string['remoteuserenrolfield'] = 'Campo usuário remoto';
$string['remoteuserenrolfield_desc'] = 'O nome do campo usuário na tabela de inscrições de usuários';
$string['remoteroleenrolfield'] = 'Campo papel remoto';
$string['remoteroleenrolfield_desc'] = 'O nome do campo papel na tabela de inscrições de usuários';
$string['defaultroleenrolfield'] = 'Inscrição do papel padrão';
$string['defaultroleenrol_desc'] = 'O papel que será atribuido por padrão, se nenhum outro papel é especificado na tabela de inscrições de usuários';

$string['remoteemailheader'] = 'Email de usuários remotos';
$string['remoteemailtable'] = 'Tabela de usuários remotos';
$string['remoteemailtable_desc'] = 'O nome da tabela que contem os emails dos usuários';
$string['remoteaddressemailfield'] = 'Campo e-mail remoto';
$string['remoteaddressemailfield_desc'] = 'O nome do campo e-mail na tabela de usuários';
$string['remoteuseremailfield'] = 'Campo usuário remoto';
$string['remoteuseremailfield_desc'] = 'O nome do campo usuário na tabela de usuários';

$string['find'] = "Buscar ambiente";
$string['enrol'] = "Inscrições";
$string['message'] = "Mensagem";
$string['end'] = "Fim";

$string['messagetext'] = '<p>Caro aluno,</p><p>{$a->instructor}, docente da disciplina {$a->course}, está usando o ';
$string['messagetext'] .= '<a href="https://edisciplinas.usp.br">Moodle da USP</a> como ambiente de apoio online. Para ter acesso ao conteúdo e atividades, ';
$string['messagetext'] .= 'acesse o sistema usando a <a href="https://id.usp.br/">Senha Única da USP</a>. </p><p>Att - Equipe Moodle da USP</p>';
$string['noreplysubject'] = 'Convite Moodle da USP (não responder)';

$string['heading_wizard_create'] = 'Criar Ambiente de Apoio';
$string['heading_wizard_join'] = 'Inscrições';

$string['currentstageSTAGE_COURSE'] = 'Configurações';
$string['currentstageSTAGE_JOIN'] = 'Inscrições';
$string['currentstageSTAGE_MESSAGE'] = 'Convites';
$string['currentstageSTAGE_FINAL'] = 'Executar';
$string['currentstageSTAGE_COMPLETE'] = 'Fim';

$string['previousstage'] = 'Anterior';
$string['wizardstageSTAGE_COURSEaction'] = 'Próximo';
$string['wizardstageSTAGE_JOINaction'] = 'Próximo';
$string['wizardstageSTAGE_MESSAGEaction'] = 'Executar';

$string['enrol_selected_users'] = 'Selecionados';
$string['enrol_potential_users'] = 'Alunos potenciais';
$string['message_selected_users'] = 'Selecionados';
$string['message_potential_users'] = 'Alunos potenciais';

$string['message'] = 'Mensagem';
$string['emailmessage'] = 'Mensagem de convite.';
$string['emailmessage_help'] = 'A mensagem  é enviado aos estudantes ainda sem conta neste Moodle. ';
$string['emailmessage_help'] .= 'A mensagem deve conter informação que explica como acessar o Moodle da USP.';

$string['selectcourse'] = 'Selecionar um curso';
$string['joincoursewith'] = 'Buscar outro curso em Moodle para efetuar a união com outro curso';
$string['totalcoursesearchresults'] = 'Número de cursos: {$a}';
$string['nomatchingcourses'] = "Não achamos um outro ambiente de apoio apropriado para juntar esta turma. Clique em Continuar para escolher um dos seus outros ambientes ou entre em contato com a Equipe Moodle da USP: suporte@edisciplinas.usp.br.";

$string['wizardjoin_heading'] = 'Inscrições';
$string['wizardcreate_heading'] = 'Novo ambiente de apoio';

$string['wizardSTAGE_COURSE_info'] = '<div class="alert alert-block alert-info"><p>As configurações principais do ambiente (elas podem ser modificadas agora ou após a criação do ambiente).</p> <p>Clique no botão "Próximo" no final do formulário para ir ao passo 2.</p></div>';
$string['wizardSTAGE_JOIN_info'] = '<p>Clique no botão "Próximo" com a opção "Todos" para inscrever todos os alunos que já possuem conta no Moodle da USP.<p>'.
                                    '<p>Use "Nenhum" para pular este passo.</p><p>Use "Selecionar" para inscrever neste momento somente alguns alunos.</p>'.
                                    '<p>Somente serão inscritos alunos que já tem conta no Moodle da USP (mas veja o passo seguinte).</p>';
$string['wizardSTAGE_MESSAGE_info'] = '<p>Neste passo é possível convidar matriculados/inscritos no Júpiter mas ainda sem conta no Moodle da USP (personalize a mensagem abaixo).</p>'.
                                      '<p>Se quiser pular este passo, escolha "Nenhum". Ou se preferir '.
                                      'escolher um por um os convidados, escolha "Selecionar".</p>';
$string['wizardSTAGE_COMPLETE_info'] = 'O seu ambiente foi criado! Clique em "Continuar" para começar colocar conteúdo e atividades ou <a href="https://docs.atp.usp.br/artigos/importar-conteudos-de-anos-passados/" target="_blank">importar conteúdo de anos passados</a>. Veja <a href="https://docs.atp.usp.br/artigos/" target="_blank">aqui (abre em outra janela)</a> para mais informações e dicas';

// @codely.com.br >>> block_usp_cursos WIZARD
$string['methodguest'] = 'Ambiente aberto para visitantes (e Google)';
$string['methodguesthelp'] = 'Ambiente aberto';
$string['methodguesthelp_help'] = 'Um ambiente aberto permite visitantes (mesmo não logados) acessar os materiais didáticos disponibilizados. Visitantes não podem participar e nunca podem acessar contribuições de alunos (Mensagens, Fóruns, Tarefas etc.). Escolhe "Não" aqui se seus arquivos não devem ser visíveis para visitantes ou Google.';


// STAGE JOIN
$string['wizardSTAGE_JOIN_info'] = '<div class="alert alert-block alert-info"><p>Neste passo é possível escolher as turmas (alunos e ministrantes) do Júpiter ou Janus que serão inscritas neste ambiente. Recomendamos <a href="https://docs.atp.usp.br/artigos/juntar-turmas/" target="_blank" title="Abre numa outra janela">juntar as turmas</a> num único ambiente, sempre que possível.</p><p>Caso queiram trabalhar em ambientes separados, é possível criar o ambiente da outra turma após este. Se um ambiente de outra turma desta disciplina já foi criado anteriormente, não poderá inscrever estes alunos. Neste caso, entre em contato com o suporte@edisciplinas.usp.br se precisar juntar as turmas, ou resolver outros casos mais complexos.</p></div>';
$string['currentstageSTAGE_JOIN'] = 'Inscrições';
$string['wizardstageSTAGE_JOINaction'] = 'Próximo';
$string['join'] = 'Inscrever turmas e docentes';
$string['teachers'] = 'Docente(s)';
$string['class'] = 'Turma';
$string['moodlecourse'] = 'Ambiente Existente';

// STAGE REVIEW
$string['currentstageSTAGE_REVIEW'] = 'Revisar';
$string['wizardSTAGE_REVIEW_info'] = '<h3>Revisão</h3><div class="alert alert-block alert-info"><p>Veja algumas características do ambiente prestes a ser criado. Podem ser modificadas após a criação do ambiente.</p></div>';
$string['wizardstageSTAGE_REVIEWaction'] = 'Executar';
