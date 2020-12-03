<?php

defined('MOODLE_INTERNAL') || die();

class block_usp_cursos extends block_list {
    
    function init(){
        $this->title = "Disciplinas da USP";
        $this->config = get_config('block_usp_cursos');
    }

    function get_config($key) {
        return $this->config->{$key};
    }
    
    function db_init() {
        global $CFG;

        require_once($CFG->libdir.'/adodb/adodb.inc.php');

        // Connect to the external stoa (forcing new connection)
        $extdb = ADONewConnection($this->get_config('dbtype'));
        if ($this->get_config('debugdb')) {
            $extdb->debug = true;
            ob_start(); //start output buffer to allow later use of the page headers
        }

        $extdb->Connect($this->get_config('dbhost'),
                        $this->get_config('dbuser'),
                        $this->get_config('dbpass'),
                        $this->get_config('dbname'), true);
        $extdb->SetFetchMode(ADODB_FETCH_ASSOC);
        if ($this->get_config('dbsetupsql')) {
            $extdb->Execute($this->get_config('dbsetupsql'));
        }
        return $extdb;
    }
    
    function db_encode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_encode($value);
            }
            return $text;
        } else {
            return textlib_get_instance()->convert($text, 'utf-8', $dbenc);
        }
    }

    function db_decode($text) {
        $dbenc = $this->get_config('dbencoding');
        if (empty($dbenc) or $dbenc == 'utf-8') {
            return $text;
        }
        if (is_array($text)) {
            foreach($text as $k=>$value) {
                $text[$k] = $this->db_decode($value);
            }
            return $text;
        } else {
            return textlib_get_instance()->convert($text, $dbenc, 'utf-8');
        }
    }
 
    function db_addslashes($text) {
        // using custom made function for now
        if ($this->get_config('dbsybasequoting')) {
            $text = str_replace('\\', '\\\\', $text);
            $text = str_replace(array('\'', '"', "\0"), array('\\\'', '\\"', '\\0'), $text);
        } else {
            $text = str_replace("'", "''", $text);
        }
        return $text;
    }

    function db_get_sql($table, array $conditions, array $fields, $distinct = false, $sort = "") {
        $fields = $fields ? implode(',', $fields) : "*";
        $where = array();
        if ($conditions) {
            foreach ($conditions as $key=>$value) {
                $value = $this->db_encode($this->db_addslashes($value));
                $where[] = "$key = '$value'";
            }
        }
        $where = $where ? "WHERE ".implode(" AND ", $where) : "";
        $sort = $sort ? "ORDER BY $sort" : "";
        $distinct = $distinct ? "DISTINCT" : "";
        $sql = "SELECT $distinct $fields
                  FROM $table
                 $where
                  $sort";

        return $sql;
    }
    
    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;
        if (isset($this->content)) {
            return $this->content;
        }
        $this->content = new stdClass;
        
        // usuario sem idnumber (nusp) nao ve nada...
        if (!isset($USER->{$this->get_config('localuserfield')})) {
            $this->content->items = array('ww');
            $this->content->footer = '';
            return $this->content;
        }
        $this->content->items = array();
        $this->content->icons = array();
        //$this->content->text = 'dd';

        if(has_capability('moodle/category:manage',$this->context)) {
            require_once('codmoodles_form.php');
            $mform = new block_usp_cursos_codmoodles_form($PAGE->url);
            $this->content->items[] = $mform->render();
            if($formdata = $mform->get_data()) {
                //print_r($formdata);exit;
                if($newcourse=$this->get_potential_course($formdata->codmoodles)) {
                    //print_r($newcourse->cat);exit;
                    $catcxt = context_coursecat::instance($newcourse->cat->id);
                    if(has_capability('moodle/category:manage',$catcxt)) {
                        $sesskey = sesskey();
                        $posturl = new moodle_url('/blocks/usp_cursos/wizard.php',
                        array('id'=>$this->instance->id, 'codmoodle'=>$formdata->codmoodles, 'sesskey'=>$sesskey));
                        redirect($posturl);        
                    } else {
                        $this->content->items[] = "sem permissão p/ criar ambientes em {$newcourse->cat->name}";
                    }
                }
            }
        }
        $sesskey = sesskey();
        $created_courses = array();
        $coursefield = $this->get_config('localcoursefield');

        $courses = $this->get_teacher_courses($USER->{$this->get_config('localuserfield')});
        $disciplinas = array();
        foreach ($courses as $course) {
            if ($course->created == 0) {
                $parts = explode('.', $course->codmoodle);
                $coddis = $parts[0];
                $codtur = $parts[2];


                if(array_key_exists($coddis,$disciplinas)) {
                    $disciplinas[$coddis] += 1;
                } else {
                    $disciplinas[$coddis] = 1;
                }
                if ($disciplinas[$coddis] > 1) {
                    continue; # mais que 2 turmas
                }

                $content = $coddis." - ".$course->nomdis.'<br>'.
                    html_writer::link(new moodle_url('/blocks/usp_cursos/wizard.php',
                        array('id'=>$this->instance->id, 'codmoodle'=>$course->codmoodle, 'sesskey'=>$sesskey)), 
                              'criar ambiente', array('class'=>'btn btn-xs btn-success'));
                
		        //$content .= ' | '.html_writer::link(new moodle_url('/blocks/usp_cursos/wizard.php',array('id'=>$this->instance->id, 'codmoodle'=>$coddis.' '.substr($codtur,0,4), 'search'=>$coddis.' '.substr($codtur,0,4),'stage'=>'2', 'sesskey'=>$sesskey)), 'juntar turmas');
                $this->content->items[] = $content;
            } else if (enrol_is_enabled('stoa') && !empty($course->codmoodle)) { //-- external stoa enrolment is installed
                $pcourses = $DB->get_records_select("course", "{$coursefield} LIKE :codmoodle", array('codmoodle'=>'%'.$course->codmoodle.'%'));
                foreach ($pcourses as $pcourse) {
                    $context = context_course::instance($pcourse->id);
                    if (has_capability('enrol/stoa:manage', $context) && !array_key_exists($pcourse->id, $created_courses)) {
                        //-- has permission to manage
                        $created_courses[$pcourse->id] = $pcourse; // echo 'found:: '.$pcourse->id.' <br/><hr/>';
                    }
                }
            }
        }

        // se tem cursos sendo oferecidos mas todos já foram criados
        if(empty($this->content->items) && !empty($courses)) {
            $this->content->items[] = "Todos os seus ambientes já foram criados.";
        }
        // se tem cursos a serem criados, forçar mostrar sidebar para o tema Stoa14
        if(!empty($this->content->items)) {
            set_user_preference('stoa_sidebar',1);
        }
        
        // listar os cursos creados
        /*foreach ($created_courses as $course) { 
            if ($instance = $DB->get_record('enrol', array('courseid'=>$course->id,
                                                           'enrol'=>'stoa',
                                                           'status'=>ENROL_INSTANCE_ENABLED))) {
                $content = $course->fullname.' - '.$course->shortname.' (Opções ';
                $managelink = new moodle_url("/enrol/stoa/manage.php", array('enrolid'=>$instance->id));
                $content .= $OUTPUT->action_icon($managelink, new pix_icon('i/users', get_string('enrolusers', 'enrol_stoa'),
                                                                      'core', array('class'=>'iconsmall'))).' ';
                $messagelink = new moodle_url('/enrol/stoa/message.php', array('enrolid'=>$instance->id));
                $content .= $OUTPUT->action_icon($messagelink, new pix_icon('i/email', get_string('sendmessage', 'enrol_stoa'),
                                                                       'core', array('class'=>'iconsmall')));
                $this->content->items[] = $content.')';
            }
        }*/
        return $this->content;
    }
    
    function has_config() {
        return true;
    }
    
    function applicable_formats() {
        return array(
            'site' => true,
            'my'   => true,
            'course-index-category' => true
        );
    }
    
    function cron() {
        return false; // rodamos o sync_usp_cursos manualmente
    }

    
    // retorna array de objetos
    function get_teacher_courses($nusp) {
        global $CFG, $DB;
        
        $courses = $DB->get_records('block_usp_cursos', array('codpes'=>$nusp),$sort="dataini desc");
        if(!$courses) {
            return array();
        } else {
            return $courses;
        }
    }
    
    function update_all_courses($trace) {
        global $CFG, $DB;
        
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remotetutortable') or
            !$this->get_config('remoteusertutorfield') or
            !$this->get_config('remotecoursetutorfield') or
            !$this->get_config('remotecoursetypetutorfield')) {
            return;
        }

        core_php_time_limit::raise();
        raise_memory_limit(MEMORY_HUGE);

        
        $tutortable = strtolower($this->get_config('remotetutortable'));
        $usertutorfield = strtolower($this->get_config('remoteusertutorfield'));
        $coursetutorfield = strtolower($this->get_config('remotecoursetutorfield'));
        $coursetypetutorfield = strtolower($this->get_config('remotecoursetypetutorfield'));
        
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');
        
        $extdb = $this->db_init();
        $trace->output("Atualização da tabela mdl_block_usp_cursos");
        $sql = "SELECT * FROM ".$tutortable;
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF)  {
                $turmas = array();
                while ($fields = $rs->FetchRow()) {
                    $curso = new stdClass;
                    $curso->codpes = 0;
                    $curso->codmoodle = '';
                    $curso->nomdis = '';
                    $curso->objdis = '';
                    $curso->tipo = '';
                    $curso->created = 0;
                    $curso->checked = 1; // não tem significado mais. Mas é trabalho demais para remover a coluna.
                    $curso->dataini = 0;
                    $curso->datafim = 0;

                    $curso->codpes = $fields[$usertutorfield];                        
                    $curso->codmoodle = $fields[$coursetutorfield];
                    $curso->tipo = $fields[$coursetypetutorfield];
                    $curso->dataini = strtotime($fields['dataini']);
                    $curso->datafim = strtotime($fields['datafim']);

                    $parts = explode('.', $curso->codmoodle);
                    if($curso->tipo == 'GR') {
                        $sql = "SELECT nomdis, objdis FROM disciplinas_gr where coddis = '{$parts[0]}' AND verdis = {$parts[1]}";
                    } else if($curso->tipo == 'POS') {
                        $sql = "SELECT nomdis, objdis FROM disciplinas_pos where sgldis = '{$parts[0]}' AND numseqdis = {$parts[1]}";
                    }
                    if ($rs_info = $extdb->Execute($sql)) {
                        if (!$rs_info->EOF)  {
                            $fields_info = $rs_info->FetchRow();
                            $curso->nomdis = addslashes($fields_info['nomdis']);
                            $curso->objdis = addslashes($fields_info['objdis']);
                        }
                    }

                    if ($DB->count_records_sql("SELECT COUNT(*) FROM {course} WHERE ".$localcoursefield." LIKE '%{$curso->codmoodle}%'")) {
                        $curso->created = 1;
                    } else {
                        $curso->created = 0;
                    }
                    
                    $turmas[] = $curso; 
                }
                if(sizeof($turmas) > 0) {
                    $DB->execute("TRUNCATE {block_usp_cursos}");
                    $DB->insert_records('block_usp_cursos',$turmas);
                } else {
                    $trace->output('Algo deu muito errado: sem resultados após o loop sobre turmas_moodle');
                    return false;
                }
            } else {
                $trace->output('Update all courses: atualização dos cursos USP não teve sucesso. Falha na conexão ao DB.');
                $extdb->Close();
                return false;
            }
        }
        $trace->output("Tudo certo!");
        return true;
    }

    /*
     * sync course prefixes with external USP db
     *
     * returns true / false in case of success/failure
     *
     */
    function usp_update_prefixes($trace) {
        global $CFG, $DB;
        // conectando ao DB
        $extdb = $this->db_init();

        $sql = sprintf("SELECT * FROM prefixodiscip");
        if ($rs = $extdb->Execute($sql)) {
            $DB->delete_records('block_usp_prefixos');
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);

                    $category = new stdClass;
                    $category->pfxdisval = $fields['pfxdisval'];
                    $category->dscpfxdis = $fields['dscpfxdis'];
                    $category->sglfusclgund = $fields['sglfusclgund'];
                    $category->nomclgund = $fields['nomclgund'];
                    
                    $DB->insert_record('block_usp_prefixos', $category);
                }
            }
            $rs->Close();
            $extdb->Close();
            return true; 
        } else {
            $trace->output('Atualização dos prefixos USP não teve sucesso. Falha na conexão ao DB.');
            $extdb->Close();
            return false;
        } 
    }
    

    /**
     * Function to create new category
     */
    function create_category($name, $desc,$parentid) {
        global $DB, $CFG, $OUTPUT;
        
        $newcategory = new stdClass();
        $newcategory->name = $name;
        $newcategory->description = $desc;
        $newcategory->sortorder = 999;
        $newcategory->parent = $parentid;
        if(!$category = core_course_category::create($newcategory)) {
            \core\notification::error("Could not insert the new category '{$newcategory->name}'");
            return null;
        }
        return $category;
    }

    /**
     * Function to get category using prefix
     */
    function usp_get_category($prefix,$anooferecimento = '') {
        // se o ano do oferecimento corresponde a uma categoria existente (ex. 2016)
        // colocaremos a categoria embaixo desta categoria. Senão, coloque a categorias 
        // (e a categoria da unidade) abaixo do raiz do site.
        
        global $CFG, $DB;
        $config = get_config('block_usp_cursos');
        $anocatid = 0; //raiz
        if(!empty($anooferecimento)) {
            if($anocat = $DB->get_record('course_categories', array('name'=>$anooferecimento, 'depth'=>1))) {
                $anocatid = $anocat->id;
            }
        }
        //TODO: make this more robust if the prefix isn't in block_usp_prefixos.
        if ($pfxs = $DB->get_records('block_usp_prefixos', array('pfxdisval'=>$prefix), 'dscpfxdis desc')) {
            $pfx = reset($pfxs);
            if(!$unidadecat = $DB->get_record('course_categories',array('name'=>$pfx->sglfusclgund,'parent'=>$anocatid))) {
                $unidadecat = $this->create_category($pfx->sglfusclgund, $pfx->nomclgund,$anocatid);
                if(!$unidadecat) {
                    // falha criar categoria da unidade ... 
                    return $DB->get_record('course_categories', array('id'=>$config->newcoursecategory), '*', MUST_EXIST);
                }
                
            }
            $unidadecatid = $unidadecat->id;       
            if(!$pfxcat = $DB->get_record('course_categories',array('name'=>$pfx->pfxdisval,'parent'=>$unidadecatid))) {
                $pfxcat = $this->create_category($pfx->pfxdisval, $pfx->dscpfxdis,$unidadecatid);
                if(!$pfxcat) {
                    // falha criar categoria do pfx ... 
                    return $DB->get_record('course_categories', array('id'=>$config->newcoursecategory), '*', MUST_EXIST);
                }      
            }
            
            return $pfxcat;
        }
    }
    
    
    /**
     * Functions that obtain course information using codmoodle
     * @param codmoodle string of code of moodle
     * @return object of course information
     */
    public function get_potential_course($codmoodle) {
        global $DB, $USER;
        
        $parts = explode('.', $codmoodle);
        $coddis = $parts[0];
        $codtur = substr($parts[2],4); //acrescentamos o ano ao shortname embaixo
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');
        
        $course = new stdClass();
        $course->shortname = "{$coddis}-{$codtur}";
        $course->fullname = $course->shortname;
        //$disciplina = $DB->get_record('block_usp_cursos', array('codpes'=>$USER->{$localuserfield}, 'codmoodle'=>$codmoodle));
        
        if($disciplina = $DB->get_records('block_usp_cursos', array('codmoodle'=>$codmoodle))) {
            $disciplina = reset($disciplina);
        }
        if($disciplina->dataini <> 0){
            $ano = date('Y',$disciplina->dataini);
            $course->startdate = $disciplina->dataini;
        } else {
            $ano = date('Y',time());
            $course->startdate = time();
        }
        if($disciplina->datafim <> 0){
            $ano = date('Y',$disciplina->datafim);
            $course->enddate = $disciplina->datafim;
        } else {
            $course->enddate = $course->startdate + 130*3600*24; # 130 dias
        }

        $course->shortname = $course->shortname . "-$ano";
        if (!empty($disciplina->nomdis)) {
            $course->fullname = $coddis." - ".$disciplina->nomdis." (".$ano.")";
        }
        $course->cat = $this->usp_get_category(substr($coddis,0,3),$ano);
        $course->summary = $disciplina->objdis;
        $course->{$localcoursefield} = $codmoodle;
        $course->timemodified = time();
        return $course;
    }
    
    /**
     * Function that list potential users for course
     * @param course record
     * @return string of condition with localuserfield IN (userids)
     */
    public function get_enrol_potential_users($course, $coursecodes=null, &$roles=array()) {
        global $CFG, $DB;
        
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remoteenroltable') or
            !$this->get_config('remotecourseenrolfield') or
            !$this->get_config('remoteuserenrolfield')) {
            return;
        }
        
        $enroltable  = $this->get_config('remoteenroltable');
        $coursefield = strtolower($this->get_config('remotecourseenrolfield'));
        $userfield   = strtolower($this->get_config('remoteuserenrolfield'));
        $rolefield   = strtolower($this->get_config('remoteroleenrolfield'));
        
        $localuserfield   = $this->get_config('localuserfield');
        $localcoursefield = $this->get_config('localcoursefield');
        $defaultroleid    = $this->get_config('defaultroleenrolfield');
        
        if (!isset($coursecodes)) {
            $coursecodes = preg_split("/[\s,]+/", $course->{$localcoursefield});
        }
        $sql = "SELECT DISTINCT {$userfield}";
        if (isset($rolefield) && !empty($rolefield)) {
            $sql .= ", {$rolefield} ";
        }
        $sql .= " FROM {$enroltable} WHERE {$coursefield} IN ('".implode("','",$coursecodes)."')";
        
        $result = array();
        $extdb = $this->db_init();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    //-- if not exits in moodle
                    $users = $DB->get_records('user', array($localuserfield=>$fields[$userfield]));
                    foreach ($users as $userid=>$user) {
                        if (array_key_exists($userid, $result)) {
                            continue;
                        }
                        $result[$userid] = $user;
                        //-- update roles
                        if (isset($rolefield) && !empty($rolefield)) {
                            $roles[$userid] = $fields[$rolefield];
                        } else {
                            $roles[$userid] = $defaultroleid;
                        }
                    }
                }
            }
            $rs->Close();
            $extdb->Close();
        } else { // bad luck, with the db connection
            $extdb->Close();
            return null;
        }
        return $result;
    }
    
    /**
     * Function that list pre-potential users from external database
     * @param course record
     * @return array of (emails=>users)
     */
    public function get_enrol_prepotential_users($course, $coursecodes=null) {
        global $CFG, $DB;
        
        if (!$this->get_config('dbtype') or
            !$this->get_config('dbhost') or
            !$this->get_config('remoteenroltable') or
            !$this->get_config('remotecourseenrolfield') or
            !$this->get_config('remoteuserenrolfield') or
            !$this->get_config('remoteemailtable') or
            !$this->get_config('remoteaddressemailfield') or
            !$this->get_config('remoteuseremailfield')) {
            return;
        }
        
        $enroltable       = $this->get_config('remoteenroltable');
        $courseenrolfield = strtolower($this->get_config('remotecourseenrolfield'));
        $userenrolfield   = strtolower($this->get_config('remoteuserenrolfield'));
        
        $emailtable        = $this->get_config('remoteemailtable');
        $addressemailfield = strtolower($this->get_config('remoteaddressemailfield'));
        $useremailfield    = strtolower($this->get_config('remoteuseremailfield'));
        
        $localuserfield    = $this->get_config('localuserfield');
        $localcoursefield  = $this->get_config('localcoursefield');
        
        //-- get external sql
        if (!isset($coursecodes)) {
            $coursecodes = preg_split("/[\s,]+/", $course->{$localcoursefield});
        }
        $sql  = "SELECT DISTINCT {$addressemailfield} AS email, {$useremailfield} AS {$localuserfield}";
        $sql .= " FROM {$emailtable} WHERE {$useremailfield} IN (";
        $sql .= "   SELECT {$userenrolfield}";
        $sql .= "     FROM {$enroltable} WHERE {$courseenrolfield} IN ('".implode("','",$coursecodes)."')";
        $sql .= ")";
        
        $result = array();
        $extdb = $this->db_init();
        if ($rs = $extdb->Execute($sql)) {
            if (!$rs->EOF) {
                while ($fields = $rs->FetchRow()) {
                    $fields = array_change_key_case($fields, CASE_LOWER);
                    $fields = $this->db_decode($fields);
                    //-- if not exits in moodle
                    if (!$DB->record_exists('user', array($localuserfield=>$fields[$localuserfield]))) {
                        $result[$fields['email']] = $fields['email'];
                    }
                }
            }
            $rs->Close();
            $extdb->Close();
        } else { // bad luck
            $extdb->Close();
            return;
        }
        return $result;
    }
}

