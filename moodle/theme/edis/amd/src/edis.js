// define(['jquery'], function($) {
//     return {
//         init: function ($params) {
//             $(document).ready(function(){
//                 $("#page-site-index #unidades .resp-tab-item>a").on("click", function() {  $("#page-site-index #unidades .resp-tab-item.active").removeClass("active"); $(this).parent().addClass("active"); });
//
//                 // BLOCKS
//                 $(".block .collapse").on("click", function() {
//                     $(this).toggle();
//                 });
//
//                 // DEFAULT BLOCKS
//                 $("#page-my-index .block .collapse").show();
//
//                 // OPEN BLOCKS
//                 $("'.$openblocks.'").show();
//
//                 // SAVE BLOCKS
//                 $(".block .collapse").on("shown.bs.collapse", function () {
//                     save_openblocks();
//                 });
//
//                 $(".block .collapse").on("hidden.bs.collapse", function () {
//                     save_openblocks();
//                 });
//             });
//             function save_openblocks() {
//                 var values = [];
//                 $.each($(".block .collapse.show").parent().parent().parent().parent(), function() {
//                     values.push(\'.\'+$(this).attr("class").split(" ")[1]+\' .collapse\');
//                 });
//                 // SAVE ALL OPEN BLOCK CLASSES in User Preferences
//                 M.util.set_user_preference("openblocks",values);
//             }
//
//         }
//     };
// });