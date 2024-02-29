$(document).ready(function () {
  
  //instantiate tooltips
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => $(tooltipTriggerEl).tooltip()
  );
 
   $(document).on('shown.bs.tooltip', function (e) {
      setTimeout(function () {
        $(e.target).tooltip('hide');
      }, 2000);
   });
   
   /* end dom ready */
});