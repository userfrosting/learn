$( document ).ready(function() {
    // Dropdown nav menu
    // Show when mouse id over button
    $( ".dropdown-button" ).mouseenter(function(event) {
        var target = $(event.currentTarget).data('activates');
        $("#" + target).css("display", "block");
    });

    // Show when mouse id over dropdown
    $( ".dropdown-content" ).mouseenter(function() {
        $(event.currentTarget).css("display", "block");
    });

    // Hide when mouse id over button
    $( ".dropdown-button" ).mouseleave(function(event) {
        var target = $(event.currentTarget).data('activates');
        $("#" + target).css("display", "none");
    });

    // Hide when mouse id over dropdown
    $( ".dropdown-content" ).mouseleave(function(event) {
        $(event.currentTarget).css("display", "none");
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
        element.css({
            'transform': 'translateY(200px)'
        });
    } else {
        element.css({
            'transform': 'translateY(-200px)'
        });
    }
};
