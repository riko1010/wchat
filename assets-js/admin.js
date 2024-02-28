$(document).ready(function(){

/* logout */
$(".logout").on('click', function (e) {
    e.preventDefault();
    $('#LogoutFormUnknownErrorStatus, #LogoutFormStatus').addClass('hidden');
    devlog('logout request');
    AdminLogoutRequest = $.ajax({
      method: "POST",
      url: ADMINUrl,
      data: {
       logout: 'yes'
        },
      dataType: "html",
    }).always(function (data) {
       try {
        jsonlogoutdata = $.parseJSON(data);
       } catch(e) {
         devlog('invalid response');
         $('#LogoutFormUnknownErrorStatus').removeClass('hidden');
         return false;
        }
      LoggedIn = false;
      $('#LogoutFormStatus').removeClass('hidden');
      $('.form-signin').removeClass('hidden');
      $('.admincontainer').addClass('hidden');  
    });
  });
  
/* login */
  $(".adminlogin").submit(function (e) {
    e.preventDefault();
    /* disable all fields on request */
    $('.loginfields').prop('disabled', true);
    AdminLogin(
      $("#AdminEmail").val(),
      $("#AdminPassword").val(),
    );
    /* enable fields when done */
    $('.loginfields').prop('disabled', false);
  });

/* listen for loggedin status */
window.onmessage = function(e) {
    if (e.data == 'notloggedin') {
    $('.logout').trigger('click');
    }
};
/* end dom ready */
});

/* headjs */
/* headjs loads, on ready -> */
head(function () {

/* copy admin chatfile link */
   var clipboard3 = new ClipboardJS(".AdminChatfileLink", {
      container: document.getElementById('adminModal'),
      text: function (trigger) {
      adminchatfilelink = trigger.getAttribute('value');
      devlog(adminchatfilelink);
      return adminchatfilelink;
    },
  });
  clipboard3.on("success", function (e) {
    //toast copied.
    jSuites.notification({
      title: "Chat File Link Copied",
      message: "Chat File Link Copied to clipboard.",
      timeout: 5000,
    });
    devlog('chat file link copied to clipboard');
  });
  clipboard3.on("error", function (e) {
    //toast or link popup in modal
    devlog('failed: chat file link was not copied to clipboard');
  });
/* end copy admin chatfile link */

/* end headjs */  
});  
  
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
   $('#AnnotationUpdateSuccess').removeClass('hidden');
 } else {
   devlog('update failed');
   $('#AnnotationUpdateFailed').removeClass('hidden');
 }

    });
  });