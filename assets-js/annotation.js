
/*  loads, on ready -> */
$(document).ready(function () {

  /* annotations show edit */
  $('.annotation-edit').on('click', function(){
    if (!LoggedIn) {
    $('.annotation-body').removeClass('hidden');
    $('.annotation-body-edit').addClass('hidden');  
    devlog('not logged in');
    devlog(LoggedIn);
    /* close annotations , open confirm dialog */
    $('.closeAnnotationsRight').trigger('click');
    notie.confirm({
  text: 'Sign in to edit annotations.',
  submitText: 'Login', // optional, default = 'OK'
  cancelText: 'Cancel', // optional, default = 'Cancel'
  position: 'bottom', // optional, default = 'top', enum: ['top', 'bottom']
  cancelCallback: function () {
  },
  submitCallback: function () {
    /* trigger click on admintrigger to show admin */
    $('.admintrigger').trigger('click');
  }
});


    return false;  
    } else {
      devlog('LoggedIn');
      devlog(LoggedIn);
    }
    $('.annotation-body').addClass('hidden');
    $('.annotation-body-edit').removeClass('hidden');
  });
  /* cancel edit */
  $('.annotation-close').on('click', function(){
    $('.AnnotationNotices').addClass('hidden');
    $('.annotation-body').removeClass('hidden');
    $('.annotation-body-edit').addClass('hidden');
  });
  
  /* add or update annotations */
$(".annotation-submit").on('click', function (e) {
e.preventDefault();
devlog('update annotation request');
annotationUpdateInput = $('#annotation-update-input').trumbowyg('html');

annotationUpdateId = $('#annotation-update-id').val(); 
AdminLogoutRequest = $.ajax({
method: "POST",
url: ANNOTATIONUrl,
data: {
 updateannotation: 'yes',
 annotation: annotationUpdateInput,
 annotationid: annotationUpdateId,
  },
dataType: "html",
}).always(function (data) {
 try {
  jsondata = $.parseJSON(data);
 } catch(e) {
   devlog('invalid response');
   return false;
  }
 if (jsondata.status == 'success') {
   devlog('update successful');
   /* close annotation form*/
   $('.annotation-content').html(jsondata.response);
    annotationstatustext = 'Annotation Updated';
 } else {
   devlog('update failed');
   annotationstatustext = 'Annotation update failed';
 }
 /* hide offcanvas to show notification with a lower zindex */
 bootstrap.Offcanvas.getInstance(document.getElementById("AnnotationsRight")).hide();
 notie.confirm({
  text: annotationstatustext,
  submitText: 'Continue editing', // optional, default = 'OK'
  cancelText: 'Done', // optional, default = 'Cancel'
  position: 'bottom', // optional, default = 'top', enum: ['top', 'bottom']
  cancelCallback: function () {
  /* close edit input */
  /* click close form to show display el */
  $('.annotation-close').trigger('click');
  /* show offcanvas */
  bootstrap.Offcanvas.getInstance(document.getElementById("AnnotationsRight")).show();
  },
  submitCallback: function () {
  /* show offcanvas */
  /* toggle edit for view */
  $('.annotation-edit').trigger('click'); 
  /* show offcanvas */
  bootstrap.Offcanvas.getInstance(document.getElementById("AnnotationsRight")).show();
  }
});

    });
  });
  
/* end  */  
});    