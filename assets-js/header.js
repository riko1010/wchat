$(document).ready(function(){
  var options = {
    offset: 80,
};
var myElement = document.querySelector("header");
// construct an instance of Headroom, passing the element
var headroom  = new Headroom(myElement, options);
// initialise
headroom.init();
});