<?php

defined ('MOODLE_INTERNAL') || die();

class block_usp_cursos_wizard_renderer extends plugin_renderer_base {

    public function progress_bar(array $items) {
        foreach ($items as &$item) {
            $text = $item['text'];
            unset($item['text']);
            if (array_key_exists('link', $item)) {
                $link = $item['link'];
                unset($item['link']);
                $item = html_writer::link($link, $text, $item);
            } else {
                $item = html_writer::tag('span', $text, $item);
            }
        }
        return html_writer::tag('div', implode(get_separator(), $items), array('class'=>'usp_cursos_progress clearfix'));
    }
}

