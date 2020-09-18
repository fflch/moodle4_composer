<?php 
// This file implements a health-check of one webserver node behind a 
// a loadbalancer.

/*
 * @copyright  2016 Universidade de São Paulo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//A linha abaixo deve ser descomentada para executar o script de teste localmente junto com o require_once('config.php'); do moodle. Manter comentada para funcionar o HealthCheck por http
//define('CLI_SCRIPT', true);

require ('./monitor-http-ecv-conf.php');
//echo gethostname();
$connUSP = new mysqli($DBServerUSP[gethostname()], $DBUserUSP, $DBPassUSP, $DBNameUSP);

// trigger_error() does not stop execution unless you pass the second argument as E_USER_ERROR
// nao precisamos dos if else aninhados pois usamos o E_USER_ERROR
// check connection
if ($connUSP->connect_error) {
	echo "ERRO";
	trigger_error('Database connection failed: '  . $connUSP->connect_error, E_USER_ERROR);
}
$sql="select * from $table limit $limit;";
$rs=$connUSP->query($sql);
if($rs === false) {
	echo "ERRO2";
	trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $connUSP->error, E_USER_ERROR);
}

$rows_returned = $rs->num_rows;
//if ($rows_returned <= 0){
if ($rows_returned != $limit){
	echo "ERRO3";
	trigger_error('Wrong number of lines',E_USER_ERROR);
}

$connMoodle = new mysqli($DBServerMoodle[gethostname()], $DBUserMoodle, $DBPassMoodle, $DBNameMoodle);

// check connection
if ($connMoodle->connect_error) {
	echo "ERRO4";
	trigger_error('Moodle Database connection failed: '  . $connMoodle->connect_error, E_USER_ERROR);
}

// Comentei pois retornava dava o erro abaixo
// Foi detectado acesso Incorreto. Este servidor pode ser acessado apenas através do endereço "http://NOME.stoa.usp.br".
//require_once('config.php');

echo "OK";

?>

