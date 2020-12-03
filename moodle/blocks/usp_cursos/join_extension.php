<?php

defined ('MOODLE_INTERNAL') || die();

class join_course_search implements renderable {
    
    const DEFAULT_SEARCH = '';
    
    protected $url = null;
    protected $search = null;
    protected $results = null;
    
    protected $totalcount = null;
    
    static $VAR_SEARCH = 'search';
    static $MAXRESULTS = 50;
    
    public function __construct(array $config=array()) {
        $this->url = $config['url'];
        $codmoodle = optional_param('codmoodle', '', PARAM_TEXT);
        $this->search = ($codmoodle != '') ? $codmoodle : $config['codmoodle'];
    }
    
    final public function get_url() {
        return $this->url;
    }
    
    final public function get_count() {
        if ($this->totalcount === null) {
            $this->search();
        }
        return $this->totalcount;
    }
    
    final public function get_search() {
        return ($this->search !== null)?$this->search:self::DEFAULT_SEARCH;
    }
    
    final public function search() {
        global $DB;
        if (!is_null($this->results)) {
            return $this->results;
        }
        $page = 0;
        $this->results = array();
        $this->totalcount = 0;
        //list($sql, $params) = $this->get_searchsql();
        $search = $this->search;
        $searchterms = array('');
        if (!empty($search)) {
            $searchterms = explode(" ", $search);
            // Search for words independently
            foreach ($searchterms as $key => $searchterm) {
                if (strlen($searchterm) < 2) { unset($searchterms[$key]); }
            }
            $search = trim(implode(" ", $searchterms));
        }
        //$resultset = $DB->get_recordset_sql($sql, $params, 0, 250);
        $this->results = $this->get_courses_search($searchterms, "codmoodle ASC", $page, self::$MAXRESULTS, $totalcount);
        $this->totalcount = $totalcount;
        return $this->totalcount;
    }

    protected function get_courses_search($searchterms, $sort='codmoodle DESC', $page=0, $recordsperpage=50, &$totalcount) {
        global $CFG, $DB, $USER;

        // $searchterms = Array ( [0] => QFL0238 )

        $config = get_config('block_usp_cursos');
        $uspdbname = $config->dbname;
        
        $sql = "SELECT
                CONCAT(turma_mdl.codmoodle,'_',pessoa.codpes,'_',pessoa.nompes) AS id,
                turma_mdl.codmoodle AS codmoodle,
                pessoa.codpes AS codpes,
                pessoa.nompes AS nompes,
                turma_mdl.tipo AS tipo,
                course_mdl.id AS courseid,
                course_mdl.fullname AS coursename
                FROM {$uspdbname}.turmas_moodle AS turma_mdl
                JOIN {$uspdbname}.pessoa AS pessoa ON turma_mdl.codpes=pessoa.codpes
                LEFT JOIN {course} AS course_mdl ON course_mdl.idnumber like CONCAT('%',turma_mdl.codmoodle,'%')
                WHERE turma_mdl.codmoodle like '$searchterms[0]%' order by $sort";

        $usp_turmas = $DB->get_records_sql($sql);
        $totalcount = count($usp_turmas);

        return $usp_turmas;
    }

    final public function get_results() {
        if ($this->results === null) {
            $this->search();
        }
        return $this->results;
    }

}

