/* Template Name: PRECEU - Moodle
   Author: Marisa Endruveit
   Version: 1.0.0
*/


(function ($) {

    'use strict';

    // STICKY
    $(window).scroll(function() {
        var scroll = $(window).scrollTop();

        if (scroll >= 80) {
            $(".defaultscroll").addClass("scroll");
        } else {
            $(".defaultscroll").removeClass("scroll");
        }
    });

    // Menu 
    $('.navbar-toggle').on('click', function(event) {
        $(this).toggleClass('open');
        $('#navigation').slideToggle(400);
    });
    $('.navigation-menu>li').slice(-1).addClass('last-elements');

    $('.menu-arrow,.submenu-arrow').on('click', function(e) {
        if ($(window).width() < 992) {
            e.preventDefault();
            $(this).parent('li').toggleClass('open').find('.submenu:first').toggleClass('open');
        }
    });

    // Active Menu
    $(".navigation-menu a").each(function() {
        if (this.href == window.location.href) {
            $(this).parent().addClass("active"); // add active to li of the current link
            $(this).parent().parent().parent().addClass("active"); // add active class to an anchor
            $(this).parent().parent().parent().parent().parent().addClass("active"); // add active class to an anchor
        }
    });
	
	 // Lock
	(function () {
        $('.mdi-lock').on('click', function() {
            $('.lock').fadeIn(500, function() {
              $(this).toggleClass('search-toggle');
            });     
        });

        $('.lock-close').on('click', function() {
            $('.lock').fadeOut(500, function() {
                $(this).removeClass('search-toggle');
            }); 
        });

    }());
	
    // projects Filter
	(function () {
		$(window).load(function(){
		  var $portfolio_selectors = $('.project-filter >ul>li>a');
			var $portfolio = $('#projects');
			$portfolio.isotope({
				itemSelector : '.project-content',
				layoutMode : 'fitRows'
			});
			
			$portfolio_selectors.on('click', function(){
				$portfolio_selectors.removeClass('active');
				$(this).addClass('active');
				var selector = $(this).attr('data-filter');
				$portfolio.isotope({ filter: selector });
				return false;
			});
			
		});

    }());
	
	
    
})(jQuery)