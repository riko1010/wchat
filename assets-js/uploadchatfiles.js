
$(document).ready(function(){
  /* notify parent of loggedin being false */
{% if session.loggedin == false %}
  /* notify on sign in click */
  $('.iframeSignin').on('click', function(e){
  e.preventDefault();
  window.top.postMessage('notloggedin', '*');  
  });
  {% endif %}

/* uploadchatfilearchive */
  
/* selected file validation */  
$('body').on('change', '#chatfilearchive', function(){
  /* allowed extensions */
var fileExtension = ['zip'];
if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
  $('.uploadchatfilearchive').prop('disabled', true);    
  $('.chatfilearchivenotice').html('<span style="color:red;">Only these formats are allowed: '+fileExtension.join(', ')+'</span>');
        } else {
  $('.uploadchatfilearchive').prop('disabled', false);       
  $('.chatfilearchivenotice').text($(this).val());        
        }
});  

});