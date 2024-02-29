/* functions */

/* adminlogin */
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
      LoggedIn = true;
      devlog('success');
      /* reload iframe on successful login */
      var iframe = document.getElementById('uploadiframe');
      iframe.src = IFRAMESUrl+'?uploadform=show';
      /* list json response */
      jQuery.each(jsondata.chatfiles, function(i, item){
      NameColor = '#e6eaed';//colorize(item.name+item.dirname);
      $('.chatfileslist').append('<div class="d-flex list-group-item list-group-item-action "><div class="d-inline"><i class="fa-solid fa-circle-user position-relative top-50 start-50 translate-middle me-3" style="color: '+NameColor+';font-size:4em;"></i></div><div class="d-inline w-100" aria-current="true"> <div class="d-flex w-100 justify-content-between"> <h5 class="mb-1">'+item.name+'</h5> <small>'+item.dateadded+'</small> </div><p class="mb-1">'+item.dirname+'</p><small> <input type="email" class="AdminChatfileLink form-control" value="'+item.url+'" readonly></small></div></div>');
    
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
/* end functions */

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
    
    AdminLogin(
      $("#AdminEmail").val(),
      $("#AdminPassword").val(),
    );
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
  
/* end headjs */  
});    