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



$( document ).ready(function() {
var $document = $(document),
       $element = $('#nav-main');
       
   $document.scroll(function() {
       if ($document.scrollTop() <= 200) {
           $element.stop().css({
               top: '0px'
           });
       } else {
           $element.stop().css({
               top: '-200px'
           });
       }
   });
});