$( document ).ready(function() {
    $( ".dropdown-button, .dropdown-content" )
    .mouseenter(function() {
        $(".dropdown-content").css("display", "block");
    });
    
    $( ".dropdown-button, .dropdown-content" )
      .mouseleave(function() {
        $(".dropdown-content").css("display", "none");
    });
});

function hideynav() {
    var $document = $(document),
        $element = $('#nav-main');
    if ($document.scrollTop() <= 200) {
        $element.stop().css({
            top: '0px'
        });
    } else {
        $element.stop().css({
            top: '-200px'
        });
    }
    console.log("Yup");
}

var scrollTimeout;
var throttle = 2000;

$( document ).ready(function() {
    $(window).on('scroll', function () {
        scrollTimeout = setTimeout(function () {
            hideynav();
            scrollTimeout = null;
        }, throttle);

    });
});

