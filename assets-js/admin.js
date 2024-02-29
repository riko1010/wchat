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
