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
        element.css('display', 'block');
    } else {
        element.css('display', 'none');
    }
    console.log("Yup");
}
