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
 * The columns layout for the classic theme.
 *
 * @package   theme_classic
 * @copyright 2018 Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


//@codely.com.br: Because AutoLoginGuest, force redirect login page >>>>>
if (is_guest(context_course::instance(SITEID)) && $OUTPUT->body_id() == "page-site-index") {
    redirect($CFG->wwwroot.'/login');
}

// NAVDRAWER
user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}

if ($COURSE->id > 1) {
    global $DB;
    require_once($CFG->dirroot .'/mod/forum/lib.php');
    $forum = forum_get_course_forum($COURSE->id, 'news');
    if($guestenrol = $DB->get_record('enrol', array('courseid'=>$COURSE->id, 'enrol'=> 'guest','password'=>'','status'=>ENROL_INSTANCE_ENABLED))){
        $enrollink = '<a class="btn btn-outline-success" href="'.$CFG->wwwroot.'/enrol/instances.php?id='.$COURSE->id.'" title="Ambiente aberto para visitantes"><i class="fa fa-unlock-alt"></i></a>';
    } else {
        $enrollink = '<a class="btn btn-outline-warning" href="'.$CFG->wwwroot.'/enrol/instances.php?id='.$COURSE->id.'" title="Ambiente fechado para visitantes"><i class="fa fa-lock"></i></a>';
    }
    

    $sidebarshortcuts = '
    <div class="sidebar-shortcuts" id="sidebar-shortcuts">
        <div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
            <a class="btn btn-purple" href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'" title="Voltar para a página da disciplina">
                <i class="fa fa-home"></i>
            </a>
            <a class="btn btn-info" href="'.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id.'" title="Avisos Gerais da Disciplina">
                <i class="fa fa-envelope"></i>
            </a>
            <a class="btn btn-warning" href="'.$CFG->wwwroot.'/user/index.php?id='.$COURSE->id.'" title="Participantes">
                <i class="fa fa-group"></i>
            </a>
            <a class="btn btn-danger" href="'.$CFG->wwwroot.'/course/recent.php?id='.$COURSE->id.'" title="Atividades recentes">
                <i class="fa fa-folder"></i>
            </a>
            <a class="btn btn-success" href="'.$CFG->wwwroot.'/grade/report/index.php?id='.$COURSE->id.'" title="Quadro de Notas">
                <i class="fa fa-table"></i>
            </a>
            '.$enrollink.'

        </div>
    </div>';
} else {
    if(get_user_preferences('user_home_page_preference') == HOMEPAGE_MY) {
        $home = $CFG->wwwroot."/my/";
    } else {
        $home = $CFG->wwwroot;
    }
    $sidebarshortcuts = '
    <div class="sidebar-shortcuts" id="sidebar-shortcuts">
        <div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
            <a class="btn btn-purple" href="'.$home.'" title="Página Inicial da Plataforma">
                <i class="fa fa-home"></i>
            </a>
            <a class="btn btn-info" href="'.$CFG->wwwroot.'/message/index.php" title="Centro de Mensagens">
                <i class="fa fa-envelope"></i>
            </a>
            <a class="btn btn-warning" href="'.$CFG->wwwroot.'/calendar/view.php?view=month" title="Calendário">
                <i class="fa fa-calendar"></i>
            </a>
            <a class="btn btn-danger" href="'.$CFG->wwwroot.'/user/files.php" title="Arquivos Privados">
                <i class="fa fa-folder"></i>
            </a>
            <a class="btn btn-success" href="'.$CFG->wwwroot.'/mod/page/view.php?id=12" title="Ajuda e Fale Conosco">
                <i class="fa fa-question-circle"></i>
            </a>
        </div>
    </div>';
}
//@codely.com.br <<<<<

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockspre = $OUTPUT->blocks('side-pre');
$blockspost = $OUTPUT->blocks('side-post');

$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$templatecontext = [
    //@codely.com.br >>>>>
    'wwwroot' => $CFG->wwwroot,
    'navdraweropen' => $navdraweropen,
    'sidebarshortcuts' => $sidebarshortcuts,
    //@codely.com.br <<<<<

    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'sidepreblocks' => $blockspre,
    'sidepostblocks' => $blockspost,
    'haspreblocks' => $hassidepre,
    'haspostblocks' => $hassidepost,
    'bodyattributes' => $bodyattributes
];

echo $OUTPUT->render_from_template('theme_edis/columns', $templatecontext);

// @codely: OPEN BLOCKS >>>>>
user_preference_allow_ajax_update('openblocks', PARAM_TEXT);
$openblocks = get_user_preferences('openblocks', null, $USER->id);
if (!$openblocks) {
    $openblocks = "[]"; //initialize js array
}


echo $PAGE->requires->js_amd_inline('
require(["jquery"], function($) {
   $(document).ready(function(){
        $("#page-site-index #unidades .resp-tab-item>a").on("click", function() {  $("#page-site-index #unidades .resp-tab-item.active").removeClass("active"); $(this).parent().addClass("active"); });
        
        // OFF - DEFAULT CENTER MOODLEMY BLOCKS
        //$("#page-my-index #block-region-content .block .collapse").addClass("show");
        $("#page-my-index #block-region-content .block .collapse").show();
        
        // SHOW ADD BLOCK DEFAULT
        $(".block_adminblock .collapse").show();
        $(".block_adminblock .collapse").on("click", function() {
            $(this).show();
        });
        
        // SHOW QUIZ-NAVIGATION DEFAULT
        $("#mod_quiz_navblock .collapse").show();
        $(".block_book_toc .collapse").show();
        
        //MOVE BLOCK_ADVANCED_NOTIFICATION TO PAGEHEADER
        $(".block_advnotifications").appendTo("#page-header");
        $(".block_advnotifications .collapse").show();
        
        //HIDDEN NAVDRAWER MOBILE WHEN OPEN
        //bsContainerWidth = $("html").width();
        //if (bsContainerWidth <= 768 && $("body.drawer-open-left").length == 1) {
        //    //$("#nav-drawer").toggle();
        //}
        
        // OPEN BLOCKS
        var blocks = '.$openblocks.';
        $.each(blocks, function(index, obj) {
           $(".block_"+obj.name+" .collapse").addClass("show"); 
        });
        
        // SAVE BLOCKS
        $(".block:has(.collapse)").on("shown.bs.collapse", function () {
            var block = $(this).attr("data-block");
            element = {name: block};
            save_openblocks("add", element);            
        });
        $(".block:has(.collapse)").on("hidden.bs.collapse", function () {
            var block = $(this).attr("data-block");
            element = {name: block};
            save_openblocks("remove", element);   
        });
        function save_openblocks(action, element) {
            //Search in Array by BlockName
            var index = blocks.findIndex(x => x.name === element.name);
            if (index === -1) {
                blocks.push(element);
            } else if (action == "remove") { 
                blocks.splice(index, 1);
            } else {
                blocks[index] = element;
            }
            M.util.set_user_preference("openblocks",JSON.stringify(blocks));
        }
    });
});');

// OFF AMD
//echo $this->page->requires->js_call_amd('theme_edis/edis', 'init', Array("openblocks" => get_user_preferences('openblocks', null, $USER->id)));
