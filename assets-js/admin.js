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
  $('.chatfilearchivenotice').text("Only these formats are allowed : "+fileExtension.join(', '));
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

function AdminLogin(email,password) {
    devlog('submitted');
    /* clear password */
    $('#AdminPassword').val('');
    /* hide redundant notices */
    $('.LoginNotices').addClass('hidden');
    /* login request */
    AdminLoginRequest = $.ajax({
      method: "POST",
      url: ADMINUrl,
      data: {
       email: email,
       password: password
        },
      dataType: "html",
    }).always(function (data) {
      devlog('request completed');
      try {
        jsondata = $.parseJSON(data);
       } catch(e) {
         devlog('invalid response');
         devlog(e);
         $('#LoginFormUnknownErrorStatus').removeClass('hidden');
         return false;
        }
      
      if (jsondata.status == 'success') {
      devlog('success');
      /* reload iframe on successful login */
      var iframe = document.getElementById('uploadiframe');
      iframe.src = IFRAMESUrl+'?uploadform=show';
      /* list json response */
      jQuery.each(jsondata.chatfiles, function(i, item){
      devlog(item.name);  
      $('.chatfileslist').append('<span class="list-group-item list-group-item-action " aria-current="true"> <div class="d-flex w-100 justify-content-between"> <h5 class="mb-1">'+item.name+'</h5> <small>'+item.dateadded+'</small> </div><p class="mb-1">'+item.dirname+'</p><small> <input type="email" class="AdminChatfileLink form-control" value="'+item.url+'" readonly></small></span>');
    
      });
      /* hide signin form, show admin container */
      $('.form-signin').addClass('hidden');
      $('.admincontainer').removeClass('hidden');
      
      } else {
      /* login failed */  
      $('.form-signin').removeClass('hidden');
      $('.admincontainer').addClass('hidden');  
      $('#LoginFormStatus').removeClass('hidden');
      devlog('error');
      }
    });
  }