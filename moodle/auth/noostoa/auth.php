<?php

/**
 * @author Renato Coutinho
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package stoa
 *
 * Authentication Plugin: Stoa Authentication
 *
 * Description:
 * Authentication via an external Elgg server
 *
 * Contact: renato.coutinho@gmail.com
 * Upgrade (moodle 2.1): geiser@ime.usp.br
 * Adapatação para Noosfero: ewout@usp.br
 *
 * License:  GPL License v2
 *
 * 2008-10-04 File created.
 * 2011-11-09 File upgrade (moodle 2.1).
 * 2012-03-25 Versão para Novo Stoa (noosfero)
 * 2014-03-31 Evitar duplicação de contas ao autenticar via mobile
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * Stoa authentication plugin.
 */
class auth_plugin_noostoa extends auth_plugin_base {

    /**
     * Store error messages from authentication attempts.
     */
    var $lasterror;

    /**
     * Constructor.
     */
    function __construct() {
        $this->authtype = 'noostoa';
        $this->config = get_config('auth/noostoa');
        $this->errormessage = '';
    }

    function loginpage_hook() {
        global $noostoa_user_info, $frm, $user, $DB, $CFG;

    // Esta função é chamado antes da autenticação de verdade.
    // Tentamos se logar e se conseguir mudamos o string dado no login form para o
    // username cadastrado na tabela mdl_user.
    // A razão é um usuário pode se logar usando seu número USP, mas
    // não queremos criar novos usuários com número USP como username.
    // (no NooStoa via Webservice este hook não é chamada, levando a
    // duplicação de contas. É a razão da configuração $CFG->authloginviaidnumber
    // e as modificações em lib/moodlelib.php. Infelizmente, o problema ainda
    // continua se o login / username é um email e o email no noosfero é
    // diferente que o email no moodle.)

        $frm = data_submitted();

        if(!empty($frm->username) && !empty($frm->password) && empty($user)){
            $frm->username = trim(core_text::strtolower($frm->username));
            $success = $this->user_login($frm->username, $frm->password);
            if($success) {
                if(!empty($noostoa_user_info['nusp'])){
                    if($user = $DB->get_record('user', array('idnumber'=>$noostoa_user_info['nusp']))) {
                        if(!empty($user) && $user->auth == 'shibboleth') {
                            //$message = get_string('auth_noostoa_sucess_but_shib', 'auth_noostoa') ;
                            //\core\notification::add($message);
                            //redirect(new moodle_url($CFG->wwwroot.'/auth/shibboleth') , $message, 20);
                        }
                    }
                }
                $frm->username = $noostoa_user_info['username'];
                $user = $DB->get_record('user', array('username'=>$noostoa_user_info['username']));
            }
        }
        return;
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {

        global $noostoa_user_info;

        // variable to store possible errors during authentication
        $errormessage = str_repeat(' ', 2048);

        // just for testing and debugging
        error_reporting(E_ALL);

        // se auth = noosfero, simplesmente não permitir se logar usando email
        if($email = clean_param($username, PARAM_EMAIL)){
            return false;
        }

        $output = $this->authenticate_with_noosfero ('login',$username,$password);
        //print_r($output);exit();
        if( is_array($result = json_decode(trim($output), true)) ){
            //print_r($result);
            if (!empty($result['error'])) {
                // tentar de novo
                $output = $this->authenticate_with_noosfero ('usp_id',$username,$password);
                if( is_array($result = json_decode(trim($output), true)) ){
                    if (!empty($result['error'])) {
                        return false;
                    }
                } else {
                    $this->lasterror = 'JSON response from Noosfero invalid (at 2nd attempt).';
                    return false;
                }
            }
            $noostoa_user_info = $result;
            return true;
        } else {
            $this->lasterror = 'JSON response from Noosfero invalid.';
            return false;
        }
    }


    function authenticate_with_noosfero ($loginfield,$value,$password) {


        // If the daemon is configured, we use that
        if(!empty($this->config->daemon)) {
            $ch = curl_init($this->config->daemon);
            curl_setopt($ch, CURLOPT_PORT,$this->config->daemonport);
            //print "curling daemon {$this->config->daemon}";
        } else {
            // the server host, with https and without leading slash
            $hosturl = $this->config->host;
            $endpoint = $this->config->endpoint;
            $ch = curl_init("{$hosturl}{$endpoint}");
            // print "curling {$hosturl}{$endpoint}";
            // this is dangerous!
            // set to TRUE / 2 once SSL has a valid CA
            // or use CURLOPT_CAPATH to custom CA
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


        // Tentar esperar mais caso o webservice demorar para responder
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 60); // 60 segundos
        curl_setopt($ch, CURLOPT_TIMEOUT , 60); // 60 segundos

        $data = array($loginfield => $value,'password' => $password,'fields' => 'average');
        $data = http_build_query($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //print_r("<br>data:".$data);
        $output = curl_exec($ch);
        $error = curl_error($ch);
        //print_r("<br>dfsd".$output);
        curl_close($ch);
        if (!empty($error)){
            return false;
        } else {
            return $output;
        }
    }

    /**
     * Reads any other information for a user from external database,
     * then returns it in an array
     *
     * @param string $username (with system magic quotes)
     *
     * @return assoc array
     */
    function get_userinfo ($username) {

        global $noostoa_user_info;

        $user_info['firstname'] = $noostoa_user_info['first_name'];
        $user_info['lastname'] = $noostoa_user_info['surname'];
        $user_info['username'] = $noostoa_user_info['username'];
        $user_info['email'] = $noostoa_user_info['email'];
        $user_info['country'] = "BR";
        $user_info['url'] = $noostoa_user_info['homepage'];
        $user_info['idnumber'] = $noostoa_user_info['nusp'];

    // TODO: não está funfando...
        // $this->usp_update_user($user_info['idnumber']);

        return $user_info;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the users' passwords, or empty if the default
     * URL can be used. This method is used if can_change_password() returns true.
     * This method is called only when user is logged in, it may use global $USER.
     *
     * @return string
     */
    function change_password_url() {
      return "http://social.stoa.usp.br/account/change_password";
    }


    function can_signup() {
        //override if needed
        return false;
    }

    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    function process_config($config) {
        // set to defaults if undefined
        if (!isset($config->host)) {
            $config->host = 'localhost';
        }
        if (!isset($config->endpoint)) {
            $config->endpoint= '/plugin/stoa/authenticate';
        }
        if (!isset($config->daemon)) {
            $config->daemon = '';
        }
        if (!isset($config->daemonport)) {
            $config->daemonport = '4000';
        }

        //$config = stripslashes_recursive($config);
        // save settings
        set_config('host',          $config->host,          'auth/noostoa');
        set_config('endpoint',    $config->endpoint,    'auth/noostoa');
        set_config('daemon',          $config->daemon,          'auth/noostoa');
        set_config('daemonport',    $config->daemonport,    'auth/noostoa');

        return true;
    }

    /**
     * Função que atualiza o bloco usp_cursos adicionando os cursos que ele pode criar
     * TODO valir o que acontece si a gente tem mais de uma instancia do bloco usp_cursos
     */
    function usp_update_user($nusp){
      global $CFG, $USER, $DB, $PAGE;

        require_once($CFG->libdir.'/blocklib.php');

        if($instance = $DB->get_record_sql("SELECT * from {block_instances} where blockname = 'usp_cursos'")){
            block_load_class('usp_cursos');
            $blockobject = block_instance('usp_cursos');
            $blockobject->_load_instance($instance, $PAGE);

            if($blockobject->usp_user_courses($nusp)){
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

}

?>
