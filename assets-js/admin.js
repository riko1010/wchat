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
    AdminLogin(
      $("#AdminEmail").val(),
      $("#AdminPassword").val(),
    );
  });
  
 /* uploadchatfilearchive */
  
  
/* selected file validation */  
$('body').on('change', '#chatfilearchive', function(){
var fileExtension = ['zip'];
if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
  $('.uploadchatfilearchive').prop('disabled', true);    
  $('.chatfilearchivenotice').html('<span style="color:red;">Only these formats are allowed: '+fileExtension.join(', ')+'</span>');
        } else {
  $('.uploadchatfilearchive').prop('disabled', false);       
  $('.chatfilearchivenotice').text($(this).val());        
        }
});  
/* listen for loggedin status */
window.onmessage = function(e) {
    if (e.data == 'notloggedin') {
    $('.logout').trigger('click');
    }
};
/* end dom ready */
});

  /* annotations show edit */
  $('.annotation-body').on('click', function(){
    if (!LoggedIn) {
    $('.annotation-body').removeClass('hidden');
    $('.annotation-body-edit').addClass('hidden');  
    devlog('not logged in');
    devlog(LoggedIn);
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
annotationUpdateInput = $('#annotation-update-input').val();
AdminLogoutRequest = $.ajax({
method: "POST",
url: ANNOTATIONUrl,
data: {
 updateannotation: 'yes',
 annotation: annotationUpdateInput,
  },
dataType: "html",
}).always(function (data) {
 try {
  jsondata = $.parseJSON(data);
 } catch(e) {
   devlog(e);
   console.log(data);
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