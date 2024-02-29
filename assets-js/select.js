$(document).ready(function () {
  
  /* setup select2 chafiles search */
  $(".searchselect").select2( {
    theme: 'bootstrap-5'
  } );
  /* select chatfile, redirect to selection */
  $(".searchselect").on("select2:select", function (e) {
    window.location.href = $(this).val();
  });
 /* end dom ready */ 
});