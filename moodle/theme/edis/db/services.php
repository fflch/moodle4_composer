<?php
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
 * Web service Functions Theme
 *
 * @package    theme_edis
 * @copyright  2020 Helbert dos Santos <helbert@codely.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
        // COURSE SEARCH
        'theme_edis_search_courses' => array(
                'classname'     => 'theme_edis_external',
                'methodname'    => 'search_courses',
                'classpath'     => 'theme/edis/externallib.php',
                'description'   => 'Webservice search Courses by filter page',
                'type'          => 'read',
                'capabilities'  => 'webservice/rest:use',
                'ajax'          => true,
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'Theme EDIS Services' => array(
                'functions' => array (
                    'theme_edis_search_courses'
                ),
                'restrictedusers' => 0, // if 1, the administrator must manually select which user can use this service. 
                                                   // (Administration > Plugins > Web services > Manage services > Authorised users)
                'enabled' => 1, // if 0, then token linked to this service won't work
        )
);