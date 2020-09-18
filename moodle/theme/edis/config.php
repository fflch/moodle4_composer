<?php

defined('MOODLE_INTERNAL') || die();

$THEME->name = 'edis';
$THEME->sheets = ['edis'];
$THEME->editor_sheets = [];
$THEME->parents = ['classic'];
$THEME->rendererfactory = 'theme_overridden_renderer_factory';

$THEME->layouts = [
    // Standard layout with blocks, this is recommended for most pages with general information.
    'standard' => array(
        'file' => 'columns.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // Main course page.
    'course' => array(
        'file' => 'columns.php',
        'regions' => array('side-pre','side-post'),
        'defaultregion' => 'side-pre',
        'options' => array('langmenu' => true),
    ),
    'coursecategory' => array(
        'file' => 'columns.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // Part of course, typical for modules - default page layout if $cm specified in require_login().
    'incourse' => array(
        'file' => 'columns.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'columns.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('nofullheader' => true),
    ),
    // The pagelayout used for safebrowser and securewindow.
    'secure' => array(
        'file' => 'columns.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre'
    )
];