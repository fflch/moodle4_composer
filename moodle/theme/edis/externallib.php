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
 * External Web Service Template
 *
 * @package    theme_edis
 * @copyright  2020 Helbert dos Santos <helbert@codely.com.br>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class theme_edis_external extends external_api {
    
    // SEARCH COURSES
    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
    public static function search_courses_parameters(){
        return new external_function_parameters(
            array(
                'search' => new external_value(PARAM_TEXT, 0),
                'units' => new external_value(PARAM_TEXT, 0),
                'page' => new external_value(PARAM_INT, 0)
            )
        );
    }

    public static function search_courses($search, $units, $page){
        global $CFG, $DB;

        $where = '';

        if ($search) {
            $where .= ' AND (course.fullname LIKE "%'.$search.'%" OR course.shortname LIKE "%'.$search.'%" OR course.summary LIKE "%'.$search.'%")';
        }

        if ($units) {
            $where .= ' AND cat.path LIKE "'.$units.'%"';
        }

        $where .= " AND course.visible = 1";

        $sql = '
        SELECT
            course.id,
            course.fullname,
            course.summary,
            from_unixtime(course.timemodified,"%Y/%m/%d") modified
        FROM {course} AS course
        JOIN {course_categories} AS cat ON cat.id=course.category
        JOIN {enrol} AS enrol ON (enrol.courseid=course.id AND enrol.enrol="guest" AND enrol.status=0)
        WHERE 1=1 '.$where.'
        ORDER BY course.timecreated desc';

        $res = $DB->get_records_sql($sql, Array(), 0, 100);

        // COURSEIMAGE
        $results = Array();
        require_once($CFG->libdir . '/coursecatlib.php');

        foreach ($res AS $course) {
            $courseobj = $DB->get_record("course", Array("id" => $course->id));
            $courseimage = new course_in_list($courseobj);
            $imageurl = '';
            foreach ($courseimage->get_course_overviewfiles() as $file) {
                if ($file->is_valid_image()) {
                    $imagepath = '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename();
                    $imageurl = file_encode_url($CFG->wwwroot . '/pluginfile.php', $imagepath, false);
                }
            }
            if (!$imageurl) {
                $imageurl = 'images/courses/img-1.png';
            }
            $course->image = $imageurl;
            //$course->modified = $course->modified;
        }

        return $res;
    }

    public static function search_courses_returns(){
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, ''),
                    'fullname' => new external_value(PARAM_TEXT, ''),
                    'summary' => new external_value(PARAM_RAW, ''),
                    'image' => new external_value(PARAM_TEXT, ''),
                    'modified' => new external_value(PARAM_TEXT, '')
                )
            )
        );
    }
}