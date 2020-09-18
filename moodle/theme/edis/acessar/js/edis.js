// CUSTOM CODELY
$body = $("body");

$(document).on({
    ajaxStart: function() { $body.addClass("loading");    },
    ajaxStop: function() { $body.removeClass("loading"); }
});

$(document).ready(function() {

    // Global Variables
    var domainname = $("#wwwroot").attr("value");
    var serverurl = domainname + "/webservice/rest/server.php";
    var token = $("#token_ws").attr("value");

    // FIRE Search BTN: disparar busca

    $('#search').keyup(function(e){
        $("#search_btn").click();
        // if(e.keyCode == 13)
        // {
        //     $("#search_btn").click();
        // }
    });

    $("#search_btn, input[type=radio]").on("click", function() {
        var data = {
            wstoken: token,
            moodlewsrestformat: "json",
            wsfunction: "theme_edis_search_courses",
            search: $("#search").val(),
            units: $('input[name=units]:checked').val(),
            page: 0
        };

        $.ajax({
            url: serverurl,
            method: "POST",
            data: data,
            cache: true,
            success: function (data) {
                count = data.length;
                if (count == 1) {
                    $("#countresults").html('um resultado:');
                } else if (count > 99) {
                    $("#countresults").html('Mais de 100 resultados');
                } else if (count > 1) {
                    $("#countresults").html(count + ' resultados');
                } else {
                    $("#countresults").html('Nenhum resultado');
                }

                $("#results").html("");
                $.each(data, function(index,item) {
		    if(item.summary){
			summary = item.summary.replace(/<\/?[^>]+(>|$)/g, "");
		    } else {
			summary = '';
		    }

                    $("#results").append('<div class="row">' +
                       '                        <div class="col-lg-12">' +
                       '                            <div class="job-list-box mt-4">' +
                       '                                <div class="p-3">' +
                       '                                    <div class="row align-items-center">' +
                       '                                        <div class="col-lg-2">' +
                       '                                            <div class="company-logo-img">' +
                       '                                                <img src="'+item.image+'" alt="" class="img-fluid mx-auto d-block">' +
                       '                                            </div>' +
                       '                                        </div>' +
                       '                                        <div class="col-lg-7 col-md-9">' +
                       '                                            <div class="job-list-desc">' +
                       '                                                <h6 class="mb-2 item-cut"><a href="'+domainname+'/course/view.php?id='+item.id+'" class="text-dark" title="'+item.fullname+'">'+item.fullname+'</a></h6>' +
                       '                                                <p class="text-muted mb-0 item-cut" title="'+summary+'"><i class="mdi mdi-bank mr-2"></i>'+summary+'</p>' +
                       '                                                <ul class="list-inline mb-0">' +
                       '                                                    <li class="list-inline-item">' +
                       '                                                        <p class="text-muted mb-0"><i class="mdi mdi-clock mr-2"></i>'+item.modified+'</p>' +
                       '                                                    </li>' +
                       '' +

                       '                                                </ul>' +
                       '                                            </div>' +
                       '                                        </div>' +
                       '                                        <div class="col-lg-3 col-md-3">' +
                       '                                            <div class="job-list-button-sm text-right">' +
                       '                                                <div class="mt-3">' +
                       '                                                    <a href="'+domainname+'/course/view.php?id='+item.id+'" class="apply-btn-sm btn-custom">Visitar</a>' +
                       '                                                </div>' +
                       '                                            </div>' +
                       '                                        </div>' +
                       '                                    </div>' +
                       '                                </div>' +
                       '                            </div>' +
                       '                        </div>' +
                       '                    </div>');
                });
            }
        });
    });

    $("#search_btn").click();
});
