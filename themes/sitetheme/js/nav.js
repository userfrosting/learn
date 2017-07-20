$( document ).ready(function() {
    $( ".dropdown-button, .dropdown-content" )
    .mouseenter(function() {
        $(".dropdown-content").css("display", "block");
    });
    
    $( ".dropdown-button, .dropdown-content" )
      .mouseleave(function() {
        $(".dropdown-content").css("display", "none");
    });

    // Hide the navbar when scrolled down
    var isHidden = false;

    $(window).on('scroll', function () {
        if (($(document).scrollTop() <= 200)) {
            if (isHidden) {
                toggleNav($("#nav-main"), true);
                isHidden = false;
            }
        } else {
            if (!isHidden) {
                toggleNav($("#nav-main"), false);
                isHidden = true;
            }
        }
    });
});

function toggleNav(element, on) {
    if (on) {
        element.css('top', '0px');
    } else {
        element.css('top', '-200px');
    }
    console.log("Yup");
}

/*
function toggleNav(element, on) {
    if (on) {
        element.css({
            'display': 'block',
            'opacity': '1'
        });
    } else {
        element.css({
            'display': 'none',
            'opacity': '0'
        });
    }
    console.log("Yup");
};*/

