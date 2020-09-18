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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_edis
 * @copyright  2020 Ewout ter Haar <ewout@usp.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_edis\output;
defined('MOODLE_INTERNAL') || die;

/**
 * Renderers for theme edis
 *
 */
class core_renderer extends \theme_classic\output\core_renderer {

    public function user_menu($user = null, $withlinks = null) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/outputrenderers.php');
        if(!isloggedin() || isguestuser()) {
            $accesshtml = "<a class=\"loggedoutacessar\" href=\"{$CFG->wwwroot}/auth/shibboleth\" class=\"btn btn-custom btn-sm\"><i class=\"fa fa-sign-in\"></i> Acessar</a>";
            return $accesshtml;
        }
        $um = parent::user_menu($user, $withlinks);
        
        return $um;
    }

}
