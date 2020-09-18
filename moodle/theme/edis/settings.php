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
 * @package   theme_edis
 * @copyright 2020 Helbert dos Santos <helbert@codely.com.br>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingedis', 'e-Disciplinas');
    $page = new admin_settingpage('theme_edis_general', 'General Settings');

    $setting = new admin_setting_configtext('theme_edis/token','Token Theme Webservice', '','');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_confightmleditor('theme_edis/feature', get_string('feature','theme_edis'), get_string('featuredesc','theme_edis'), get_string('featuredefault','theme_edis'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_confightmleditor('theme_edis/home', get_string('home','theme_edis'), get_string('homedesc','theme_edis'), get_string('homedefault','theme_edis'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $setting = new admin_setting_confightmleditor('theme_edis/footer', get_string('footer','theme_edis'), get_string('footerdesc','theme_edis'), get_string('footerdefault','theme_edis'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    // HOME NOT LOGGEDIN
    $page2 = new admin_settingpage('theme_edis_unlogged', get_string('homeunloggedtab','theme_edis'));

    $setting = new admin_setting_confightmleditor('theme_edis/unloggedfeature1', get_string('unloggedfeature1','theme_edis'), get_string('unloggedfeature1desc','theme_edis'), get_string('unloggedfeature1default','theme_edis'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page2->add($setting);

    $setting = new admin_setting_confightmleditor('theme_edis/unloggedabout', get_string('unloggedabout','theme_edis'), get_string('unloggedaboutdesc','theme_edis'), get_string('unloggedaboutdefault','theme_edis'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page2->add($setting);

    $setting = new admin_setting_confightmleditor('theme_edis/unloggedfooter', get_string('unloggedfooter','theme_edis'), get_string('unloggedfooterdesc','theme_edis'), get_string('unloggedfooterdefault','theme_edis'));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page2->add($setting);

    $settings->add($page2);
}
