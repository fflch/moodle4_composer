<?php

defined('MOODLE_INTERNAL') || die;

/**
 * Function to create new category
 */
function create_category($name, $desc,$parentid) {
  global $DB, $CFG;
    
    $newcategory = new stdClass();
    $newcategory->name = $name;
    $newcategory->description = $desc;
    $newcategory->sortorder = 999;
    $newcategory->parent = $parentid;
    if(!$category = core_course_category::create($newcategory)) {
        notify("Could not insert the new category '{$newcategory->name}'");
        return null;
    }
    return $category;
}

/**
 * Function to get category using prefix
 */
function usp_get_category($prefix,$anooferecimento = '') {
    // se o ano do oferecimento corresponde a uma categoria existente (ex. 2016)
    // colocaremos a categoria embaixo desta categoria. Senão, coloque a categorias 
    // (e a categoria da unidade) abaixo do raiz do site.
    
    global $CFG, $DB;
    $config = get_config('block_usp_cursos');
    $anocatid = 0; //raiz
    if(!empty($anooferecimento)) {
        if($anocat = $DB->get_record('course_categories', array('name'=>$anooferecimento, 'depth'=>1))) {
            $anocatid = $anocat->id;
        }
    }
    //TODO: make this more robust if the prefix isn't in block_usp_prefixos.
    if ($pfxs = $DB->get_records('block_usp_prefixos', array('pfxdisval'=>$prefix), 'dscpfxdis desc')) {
        $pfx = reset($pfxs);
        if(!$unidadecat = $DB->get_record('course_categories',array('name'=>$pfx->sglfusclgund,'parent'=>$anocatid))) {
            $unidadecat = create_category($pfx->sglfusclgund, $pfx->nomclgund,$anocatid);
            if(!$unidadecat) {
                // falha criar categoria da unidade ... 
                return $DB->get_record('course_categories', array('id'=>$config->newcoursecategory), '*', MUST_EXIST);
            }
            
        }
        $unidadecatid = $unidadecat->id;       
        if(!$pfxcat = $DB->get_record('course_categories',array('name'=>$pfx->pfxdisval,'parent'=>$unidadecatid))) {
            $pfxcat = create_category($pfx->pfxdisval, $pfx->dscpfxdis,$unidadecatid);
            if(!$pfxcat) {
                // falha criar categoria do pfx ... 
                return $DB->get_record('course_categories', array('id'=>$config->newcoursecategory), '*', MUST_EXIST);
            }      
        }
        
        return $pfxcat;
    }
}

