/* Template Name: PRECEU - Moodle
   Author: Marisa Endruveit
   Version: 1.0.0
   File Description: JS para a home
*/

(function ($) {

    'use strict';

    // Selectize
    $('#select-category, #select-lang,#select-country').selectize({
        create: true,
        sortField: {
            field: 'text',
            direction: 'asc'
        },
        dropdownParent: 'body'
    });

    // Checkbox all select
    $("#customCheckAll").click(function() {
        $(".all-select").prop('checked', $(this).prop('checked'));
    });

    // Nice Select
    $('.nice-select').niceSelect();

})(jQuery)