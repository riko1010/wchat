$(document).ready(function(){
  
/* set body scroll padding top to header height */
$('body').css({
'scroll-padding-top' : $('#header').outerHeight() + 'px'
});
/* end set body scroll padding top to header height */
  
/* headroom */
  var options = {
    offset: $('#header').outerHeight(),
    tolerance : {
        up : 0,
        down : 0
    },
};
var myElement = document.querySelector("header");
// construct an instance of Headroom, passing the element
var headroom  = new Headroom(myElement, options);
// initialise
headroom.init();
/* end headroom */

/* change header position to fixed on load */
$('#header').css({
  'position' : 'fixed'
});  
/* end change header position to fixed on load */

/* chat-container top margin, header is sticky on load, changes to fixed and add top margin of calculated header height to chat-container  */
$('.chat-container').css({
'margin-top' : $('#header').outerHeight() + 'px'
});
/* end chat-container header */
});