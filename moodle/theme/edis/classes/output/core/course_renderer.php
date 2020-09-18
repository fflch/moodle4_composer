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
 * Course renderer.
 *
 * @package    theme_edis
 * @copyright  2020 Helbert dos Santos - Codely Tecnologia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_edis\output\core;
defined('MOODLE_INTERNAL') || die();

use moodle_url;

require_once($CFG->dirroot . '/course/renderer.php');

class course_renderer extends \core_course_renderer {

    public function categoria_arquivada($objcat) {
        $res = Array();
        $year = date("Y");

        $catname = $objcat->name;
        //$res[0] = $catname; //Guardar Ano, para exibir posteriormente ao lado da categoria arquivada

        if (strpos($catname,'20') !== false) { //Verifica se tem 20 no nome da categoria, pois as categorias arquivadas tem como pai o ano que passou
            $cat20 = true;
        } else {
            $cat20 = false;
        }
        $catcount = strlen($catname); //Apenas categorias com nomes de 4 digitos

        if ($cat20 && $catcount == 4 && intval($catname) < $year ) { //condicoes para categoria arquivada (2013,2012)
            $res[0] = $catname;
            $res[2] = $objcat->id;
            $res[1] = true;
        } else {
            $res[1] = false;
        }

        if ($objcat->depth > 1) { //se existe categoria pai, continue procurando categoria arquivada
            $coursecat = \core_course_category::get($objcat->parent);
            $res = $this->categoria_arquivada($coursecat);
        }

        return $res;
    }

    public function contar_disciplinas($objcat) {
        global $DB;

        $coursecount = 0;
        $path = $objcat->path;
        $rs = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE visible=1 AND path LIKE '{$path}%' ORDER BY path");
        foreach ($rs as $cat) {
            $coursecount = $coursecount + $cat->coursecount;
        }

        return $coursecount;
    }

    public function frontpage_categories_list() {
        global $CFG, $DB, $USER;
        $output = '<div class="cleaner_h20"></div>';

        $isadmin = is_siteadmin($USER);

        if (isloggedin() && !$isadmin && !isguestuser()) {
            $showmycourses = true;
        } else {
            $showmycourses = false;
        }

        if ($showmycourses) {
            $mycourses = enrol_get_my_courses($fields = NULL, $sort = 'category ASC', $limit = 0); //ordenar conforme visualizado no MDL
            //print_r($mycourses);
            if(count($mycourses) == 0) {
                $showmycourses = false;
            }
            $allcatarquiv = Array();
            $allcatemand = Array();

            foreach ($mycourses as $course) {
                $coursecat = \core_course_category::get($course->category);
                $catarquiv = $this->categoria_arquivada($coursecat); //Verifica se a disciplina esta em alguma categoria arquivada
                if ($catarquiv[1]) {
                    $catyear = '<span class="catyear">'.$catarquiv[0].'</span>';
                    $allcatarquiv[] = Array('catid'=>$catarquiv[2], 'catname'=>$catyear, 'course'=>$course->id, 'coursename'=>$course->fullname, 'objcourse'=>$course);
                } else {
                    $allcatemand[] = Array('catid'=>$coursecat->id, 'catname'=>$coursecat->name, 'course'=>$course->id, 'coursename'=>$course->fullname, 'objcourse'=>$course);
                }

            }
        }

        $output .= '
	<div class="body">
		<div class="row-fluid">
			<div class="col-md-12" id="box-toggle">
			    
				<ul id="myTab" class="nav nav-tabs" role="tablist">';
        if ($showmycourses) {
            $output .= '<li class="active nav-item"><a href="#unidades" data-toggle="tab" class="nav-link active">Minhas Disciplinas</a></li>';
            $output .= '<li class="nav-item"><a href="#anos" data-toggle="tab" class="nav-link">Anos anteriores</a></li>';
        } else {
            $ano = date('Y');
            $output .= "<li class=\"active nav-item\"><a href=\"#unidades\" data-toggle=\"tab\" class=\"nav-link active\">{$ano}</a></li>";

        }
        $output .= '<li class="nav-item"><a href="#navegar" data-toggle="tab" class="nav-link">Navegar</a></li>';
        $output .= '<li class="nav-item"><a href="#buscar" data-toggle="tab" class="nav-link">Buscar</a></li>
				</ul>
				<div id="myTabContent" class="tab-content">
					<div class="tab-pane active in fade show mt-3" id="unidades">';
        if ($showmycourses) {

            $active = 'active';
            $lastcatid = -1;
            $tabcat = '';
            $contentcat = '';
            $i = 0;

            if ($allcatemand) {
                foreach ($allcatemand as $course) {
                    if ($lastcatid != $course['catid']) {
                        if ($i != 0) {
                            $contentcat .='</div>'; //finaliza tab-pane apos cada categoria
                        }
                        $tabcat .=' <li class="resp-tab-item '.$active.'">
										<a class="pull-right col-md-1" href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$course['catid'].'" target="_blank" title="Acessar a categoria"><i class="fa fa-external-link"></i></a>
										<a class="categorylink pl-0 pull-left col-md-11" href="#disciplinas'.$course['catid'].'" data-toggle="tab">'.$course['catname'].'</a> 
                                    </li>';
                        $contentcat .='<div class="tab-pane '.$active.'" id="disciplinas'.$course['catid'].'">';
                        $i=0;
                    }

                    // display course contacts. See course_in_list::get_course_contacts()
                    $course2 = new \core_course_list_element($course['objcourse']);
                    $chelper = new \coursecat_helper();
                    $coursename = $chelper->get_course_formatted_name($course2);
                    $courselink= \html_writer::link(new moodle_url('/course/view.php', array('id' => $course2->id)),
                            $coursename, array('title' => $coursename, 'class' => $course2->visible ? '' : 'dimmed'));

                    $contentcat .= '<div class="col-sm-12">
										<div class="panel panel-tile br-a br-light mt-4">
											<div class="panel-body  p-2u">
												<h5 class="item-cut">'.$courselink.'</h5>';

                    if ($course2->has_course_contacts()) {
                        $contentcat .= \html_writer::start_tag('ul', array('class' => 'teachers points mb-2'));
                        foreach ($course2->get_course_contacts() as $userid => $coursecontact) {
                            $name = \html_writer::link(new moodle_url('/user/view.php',
                                    array('id' => $userid, 'course' => SITEID)),
                                    '<span class="coursecontact">'.$coursecontact['rolename'].': '.'</span>'.$coursecontact['username']);
                            $contentcat .= \html_writer::tag('li', $name);
                        }
                        $contentcat .= \html_writer::end_tag('ul'); // .teachers
                    }

                    // Display course summary
                    $summary = $chelper->get_course_formatted_summary($course2, array('overflowdiv' => false, 'noclean' => false, 'para' => false));
                    $summary = strip_tags($summary);

                    if ($summary) {
                        $contentcat .= \html_writer::start_tag('p', array('title' => $coursename, 'class' => 'summary details item-cut'));
                        $contentcat .= '<span class="fancy">';
                        if (strlen($summary) < 100) {
                            $contentcat .= $summary;
                            $contentcat .= '</span>';
                            $contentcat .= \html_writer::end_tag('p'); // end.summary
                        } else {
                            $summary1 = mb_substr($summary,0,90,"utf-8");
                            $contentcat .= $summary1.' ...';
                            $contentcat .= '</span>';
                            $contentcat .= \html_writer::end_tag('p'); // end.summary
                            //Read more
                            //$contentcat .= '<span class="btn-group"><a class="btn btn-xs btn-primary mb-2">Ver mais</a></span>';
                        }

                    }
                    
                    $contentcat .= \html_writer::link(new moodle_url('/course/view.php', array('id' => $course2->id)),
                            'Acessar', array('class' => $course2->visible ? 'btn btn-xs btn-warning mb-2 w-100' : 'dimmed btn btn-xs btn-warning'));
                    $contentcat .= '</div></div></div>'; //<./col-md-12 e ./well>
                    $lastcatid = $course['catid'];
                    $active = '';
                    $i++;
                }
                $contentcat .= '</div>'; //</ .tab-pane>
            }

            $output .= '<div class="tabbable">';
			$output .= '<div class="verticaltab">';
            $output .= '<ul class="nav resp-tabs-list">'.$tabcat.'</ul>';
            $output .= '<div class="tab-content resp-tabs-container">'.$contentcat.'</div>';
            $output .= '</div></div>'; //</ .verticaltab> </ .tabbable>

        } else { //Admin and Guests
            $output .= '<div class="row">';

            //Obtem lista de categorias do nivel inicial , parent=0
            $ano = date('Y');
            $anocat = $DB->get_record_sql("SELECT * FROM {course_categories} WHERE name = {$ano} AND parent=0");
            $rs = array();
            if(!empty($anocat)) {
                $rs = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE visible=1 AND name NOT LIKE '20%' AND parent={$anocat->id} ORDER BY sortorder");
            }

            $i = 0;

            foreach ($rs as $cat) {
                $coursecount = $this->contar_disciplinas($cat); //obtem quantidade de disciplinas nas sub-categorias

                if ($i==5) { $output .= '</ul></div><div class="row">'; $i=0; }

                $output .=  '<div class="col-sm-3 col-md-2">
								<a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$cat->id.'">
								  <div class="panel panel-tile text-center br-a br-light mt-4">
									<div class="panel-heading">
										<div class="clearfix">
										   <div class="pull-left">
											  <i class="fa fa-folder"></i>
										   </div>
										</div>
									</div>
									<div class="panel-body  p-2u">
									  <h5 class="item-cut"> '.$cat->name.'</h5>
									</div>
									<div class="panel-footer br-light p-2">
									 <div class="label label-warning">'.$coursecount.'</div>
									</div>
								  </div>
								</a>
							</div>';
                $i++;
            }
            $output .= '</div>';
        } // FIM showmycourses
        $output .= '</div>'; // </ #unidades>
        // ==== FIM aba UNIDADES =======
        // ==== INICIO aba ANOS ANTERIORES =======
        if ($showmycourses) {
            $output .= '<div class="tab-pane fade show mt-3" id="anos">';

            $active = 'active';
            $lastcatid = -1;
            $tabcat = '';
            $contentcat = '';
            $i = 0;

            if ($allcatarquiv) {
                rsort($allcatarquiv);
                foreach ($allcatarquiv as $course) {
                    if ($lastcatid != $course['catid']) {
                        if ($i != 0) {
                            $contentcat .='</div></div>'; //finaliza row e tab-pane apos cada categoria
                        }

                        $tabcat .=' <li class="'.$active.'">
										<div class="cursePointer">
										   <a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$course['catid'].'" target="_blank" title="Acessar a categoria"><i class="icon15 icon-circle-arrow-right"></i></a>
										</div>
										<a href="#anos'.$course['catid'].'" data-toggle="tab">'.$course['catname'].'</a>
									</li>';
                        $contentcat .='<div class="tab-pane '.$active.'" id="anos'.$course['catid'].'"><div class="row">';

                        $i=0;
                    }

                    // display course contacts. See course_in_list::get_course_contacts()
                    $course2 = new \core_course_list_element($course['objcourse']);
                    $chelper = new \coursecat_helper();
                    $coursename = $chelper->get_course_formatted_name($course2);
                    $courseshortname = format_string($course2->shortname);
                    $courselink= \html_writer::link(new moodle_url('/course/view.php', array('id' => $course2->id)),
                            $coursename, array('class' => $course2->visible ? '' : 'dimmed'));

                    $contentcat .= '<div class="col-sm-3 col-md-2">
                                        <div class="panel panel-tile text-center br-a br-light mt-4">
                                            <div class="panel-heading">
                                                <div class="clearfix">
                                                   <div class="pull-left">
                                                      <i class="fa fa-folder"></i>
                                                   </div>
                                                </div>
                                            </div>
                                            <div class="panel-body  p-2u">
                                                <h5 class="item-cut"> '.$courselink.'</h5>';
                    $contentcat .= '<p><span class="item-cut">'.$courseshortname.'</span></p>';

                    
                    /*
                    if ($course2->has_course_contacts()) {
                        $contentcat .= \html_writer::start_tag('ul', array('class' => 'teachers points'));
                        foreach ($course2->get_course_contacts() as $userid => $coursecontact) {
                            $name = \html_writer::link(new moodle_url('/user/view.php',
                                    array('id' => $userid, 'course' => SITEID)),
                                    '<span class="coursecontact">'.$coursecontact['rolename'].': '.'</span>'.$coursecontact['username']);
                            $contentcat .= \html_writer::tag('li', $name);
                        }
                        $contentcat .= \html_writer::end_tag('ul'); // .teachers
                    }

                    // Display course summary
                    $summary = $chelper->get_course_formatted_summary($course2, array('overflowdiv' => false, 'noclean' => false, 'para' => false));
                    $summary = strip_tags($summary);

                    if ($summary) {
                        $contentcat .= \html_writer::start_tag('p', array('class' => 'summary details item-cut'));
                        $contentcat .= '<span class="fancy">';
                        if (strlen($summary) < 100) {
                            $contentcat .= $summary;
                            $contentcat .= '</span>';
                            $contentcat .= \html_writer::end_tag('p'); // end.summary
                        } else {
                            $summary1 = substr($summary,0,100);
                            $contentcat .= $summary1.' ...';
                            $contentcat .= '</span>';
                            $contentcat .= \html_writer::end_tag('p'); // end.summary
                            //Read more
                            //$contentcat .= '<span class="btn-group"><a class="btn btn-xs btn-primary mb-2">Ver mais</a></span>';
                        }

                    }
                    */ 
                    $contentcat .= \html_writer::link(new moodle_url('/course/view.php', array('id' => $course2->id)),
                            'Acessar', array('class' => $course2->visible ? 'btn btn-xs btn-warning mb-2' : 'dimmed btn btn-xs btn-warning'));

                    $contentcat .= '</div>'; //end .panel-body
                    $contentcat .= '</div>'; //end .panel
                    $contentcat .= '</div>'; //end .col-sm-3
                    $lastcatid = $course['catid'];
                    $active = '';
                    $i++;
                }
                $contentcat .= '</div></div>'; //finaliza row e tab-pane apos ultima categoria
            }

            $output .= '<div class="tabbable">';
            $output .= '<ul class="nav">'.$tabcat.'</ul>';
            $output .= '<div class="tab-content">'.$contentcat.'</div>';
            $output .= '</div>'; //</ .tabbable #anos>
            $output .= '</div>'; //</ .tab-pane #anos>

        }

        // ==== FIM aba ANOS ANTERIORES =======

        // ===


        $output .= '<div class="tab-pane fade show mt-3" id="navegar">';

        $year = date("Y");
        $yearscat = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE name LIKE '20%' AND name <= {$year} AND parent=0 ORDER BY name DESC");

        $active = 'active';
        $tabcat = '';
        $contentcat = '';

        foreach ($yearscat as $year) {
            $rs = $DB->get_records('course_categories', array('parent'=>$year->id, 'visible'=>'1'), 'sortorder');
            if ($rs) {
                $tabcat .=' <li class="'.$active.'">
								<div class="cursePointer">
								   <a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$year->id.'" target="_blank" title="Acessar a categoria"><i class="icon15 icon-circle-arrow-right"></i></a>
								</div>
								<a href="#nav'.$year->id.'" data-toggle="tab">'.$year->name.'</a>
							</li>';
                $contentcat .='<div class="tab-pane '.$active.'" id="nav'.$year->id.'"> <div class="row">';

                $i=0;

                foreach ($rs as $cat) {
                    $coursecount = $this->contar_disciplinas($cat); //obtem quantidade de disciplinas nas sub-categorias

                    $contentcat .=  '<div class="col-sm-3 col-md-2">
										<a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$cat->id.'">
										  <div class="panel panel-tile text-center br-a br-light mt-4">
											<div class="panel-heading">
												<div class="clearfix">
												   <div class="pull-left">
													  <i class="fa fa-folder"></i>
												   </div>
												</div>
											</div>
											<div class="panel-body  p-2u">
											  <h5 class="item-cut"> '.$cat->name.'</h5>
											</div>
											<div class="panel-footer br-light p-2">
											 <div class="label label-warning">'.$coursecount.'</div>
											</div>
										  </div>
										</a>
									</div>';
                    $i++;
                }
                $contentcat .= '</div></div>';
                $active = '';
            }
        }

        //Obtem lista de categorias do nivel inicial , parent=0
        $rs = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE visible=1 AND name NOT LIKE '20%' AND parent=0 ORDER BY idnumber");
        if ($rs) {
            $tabcat .=' <li class="'.$active.'">
                            <div class="cursePointer">
                               <a href="'.$CFG->wwwroot.'/course/" target="_blank" title="Acessar a categoria"><i class="icon15 icon-circle-arrow-right"></i></a>
                            </div>
                            <a href="#outros" data-toggle="tab">'."Outros".'</a>
                        </li>';
            $contentcat .='<div class="tab-pane '.$active.'" id="outros"><div class="row">';

            $i = 0;
            foreach ($rs as $cat) {
                $coursecount = $this->contar_disciplinas($cat); //obtem quantidade de disciplinas nas sub-categorias

                $contentcat .=  '<div class="col-sm-3 col-md-2">
									<a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$cat->id.'">
									  <div class="panel panel-tile text-center br-a br-light mt-4">
										<div class="panel-heading">
											<div class="clearfix">
											   <div class="pull-left">
												  <i class="fa fa-folder"></i>
											   </div>
											</div>
										</div>
										<div class="panel-body  p-2u">
										  <h5 class="item-cut"> '.$cat->name.'</h5>
										</div>
										<div class="panel-footer br-light p-2">
										 <div class="label label-warning">'.$coursecount.'</div>
										</div>
									  </div>
									</a>
								</div>';
                $i++;
            }
            $contentcat .= '</div></div>';
        }


        $output .= '<div class="tabbable">';
        $output .= '<ul class="nav">'.$tabcat.'</ul>';
        $output .= '<div class="tab-content">'.$contentcat.'</div>';
        $output .= '</div></div>'; //</ .tabbable #navegar>


        // ==== INICIO aba OUTROS =======
        if(!$showmycourses){
            $output .='<div class="tab-pane" id="outros">';
            $output .= '<div class="row">';

            //Obtem lista de categorias do nivel inicial , parent=0
            $rs = $DB->get_records_sql("SELECT * FROM {course_categories} WHERE visible=1 AND name NOT LIKE '20%' AND parent=0 ORDER BY idnumber");
            $i = 0;

            foreach ($rs as $cat) {
                $coursecount = $this->contar_disciplinas($cat); //obtem quantidade de disciplinas nas sub-categorias

                if ($i==5) { $output .= '</div><div class="row">'; $i=0; }

                $output .= '<div class="col-sm-3 col-md-2">
								<a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$cat->id.'">
								  <div class="panel panel-tile text-center br-a br-light mt-4">
									<div class="panel-heading">
										<div class="clearfix">
										   <div class="pull-left">
											  <i class="fa fa-folder"></i>
										   </div>
										</div>
									</div>
									<div class="panel-body  p-2u">
									  <h5 class="item-cut"> '.$cat->name.'</h5>
									</div>
									<div class="panel-footer br-light p-2">
									 <div class="label label-warning">'.$coursecount.'</div>
									</div>
								  </div>
								</a>
							</div>';
                $i++;
            }
            $output .= '</div>';
            $output .= '</div>'; //</ #outros>
        }

        // ==== INICIO aba BUSCAR =======
        $output .= '
        <div class="tab-pane" id="buscar">
            <div class="box py-3 mdl-align">
                <form action="'.$CFG->wwwroot.'/course/search.php" id="coursesearch" method="get" class="form-inline">
                <fieldset class="coursesearchbox invisiblefieldset">
                    <label for="shortsearchbox">'.get_string("searchcourses").'</label>
                    <input name="areaids" type="hidden" value="core_course-course">
                    <input id="shortsearchbox" name="q" type="text" size="12" value="" class="form-control mb-1 mb-sm-0">
                    <button class="btn btn-secondary" type="submit">'.get_string("go").'</button>
                </fieldset>
                </form>
           </div>
        </div>
        ';

        // ==== FIM ABAS =======

        $output .= '</div></div></div>'; //</ .row-fluid .col-md-12 .tab-content>
        return $output;
    }
}
