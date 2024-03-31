
$(document).ready(function(){
  /* notify parent of loggedin being false */
if (SessionLoggedIn === false) {
  /* notify on sign in click */
  $('.iframeSignin').on('click', function(e){
  e.preventDefault();
  window.top.postMessage('notloggedin', '*');  
  });
}

/* uploadchatfilearchive */
  
/* selected file validation */  
$('body').on('change', '#chatfilearchive', function(e){
  /* allowed extensions */
var fileExtension = ['zip'];
if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
  $('.uploadchatfilearchive').prop('disabled', true);    
  $('.chatfilearchivenotice').html('<span style="color:red;">Only these formats are allowed: '+fileExtension.join(', ')+'</span>');
  $('#FolderName').val('');
        } else {
  try {
  var selectedfilename = e.target.files[0].name;
  selectedfilename = selectedfilename.split('.zip');
  $('.uploadchatfilearchive').prop('disabled', false);       
  $('#FolderName').val(selectedfilename[0]);  
  $('.chatfilearchivenotice').html('');
  } catch(e) {
  }
  
        }
});  

});