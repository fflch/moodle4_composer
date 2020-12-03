<?php

defined('MOODLE_INTERNAL') || die();


$functions = array(

    // services for  Júpiter/Janus enrolment related functions.

    'enrol_stoa_get_codmoodles' => array(
        'classname'    => 'enrol_stoa\external',
        'methodname'   => 'get_codmoodles',
        'classpath'    => '',
        'description'  => 'List of courses in Júpiter/Janus.',
        'type'         => 'read',
        'capabilities' => '',
        'ajax'         => true,
    ),
);


