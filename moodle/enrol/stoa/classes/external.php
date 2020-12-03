<?php

namespace enrol_stoa;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once(__DIR__ . "/../lib.php");

use external_api;
use external_function_parameters;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;
use enrol_stoa_plugin;


class external extends external_api {

    public static function get_codmoodles_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string',
            VALUE_DEFAULT,
            ''
        );
        $limitnum = new external_value(
            PARAM_RAW,
            'Number of records to fetch',
            VALUE_DEFAULT,
            100
        );
        return new external_function_parameters(array(
            'query' => $query,
            'limitnum' => $limitnum,
        ));
    }

    public static function get_codmoodles($query,$limitnum) {
        self::validate_parameters(self::get_codmoodles_parameters(),array('query'=>$query,'limitnum'=>$limitnum));
        
        //$return = array("4300160.1.10A", "4300160.1.10C");
        $enrol_stoa = new enrol_stoa_plugin();
        $return = $enrol_stoa->get_codmoodles($query,$limitnum);
        return $return;
        
    }

    public static function get_codmoodles_returns() {
        
        return new external_multiple_structure(
            new external_value(PARAM_RAW,'Cod Moodle')
        );
    }
}