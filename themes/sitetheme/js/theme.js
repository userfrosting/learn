$( document ).ready(function() {
   $('a').smoothScroll({offset: -100});

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