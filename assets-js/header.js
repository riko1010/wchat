$(document).ready(function(){
/* headroom */
  var options = {
    offset: 80,
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

/* chat-container top margin, header is sticky on load, changes to fixed and add top margin of calculated header height to chat-container */ 
$('.chat-container').css({
'margin-top' : $('#header').outerHeight() + 'px'
});
/* end chat-container header */
});