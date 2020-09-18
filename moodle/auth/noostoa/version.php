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
 * 2015-12-16 This file (version.php)
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015121600;        // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2011010100;        // Requires this Moodle version
$plugin->component = 'auth_noostoa';         // Full name of the plugin (used for diagnostics)
