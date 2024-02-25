
function SearchChats(
  url, 
  queryarg, 
  paginationfrom, 
  needle, 
  more = false
  ){
devlog('SearchChats()');  
/* abort previous request */
try {
        searchchats.abort();
        devlog('previous request aborted');
      } catch(e) {
        devlog('previous request was not aborted or not running');
      }
$('.searchchatsbox-loadingicon').removeClass('hidden');  
$('.searchchatsbox-searchicon').addClass('hidden');   

 /* request */    
 searchchats = $.ajax({
        method: "GET",
        url: url,
        data: {
          queryarg: queryarg,
          paginationfrom: paginationfrom,
          needle: needle,
        },
        dataType: 'html'
});

searchchats.always(function(SearchAjaxResponse) {
devlog('request finished');  
if (SearchAjaxResponse == '') {
  if (!more)  {
  $('.searchchatsresults').text('no match found.');  
  } else {
  /* hide showmore */
  $('.searchchatsshowmore').addClass('hidden');
  /* append no more results */
  $('.searchchatsresults').append('<span class="d-flex justify-content-center">no more results</span>');  
  }
devlog('no match found');
/* hide showmore */
$('.searchchatsshowmore').addClass('hidden');
} else {
devlog('matches found');  
  if (!more) {
  /* insert search result into div */
  $('.searchchatsresults').html(SearchAjaxResponse);  
  } else {
  /* append search result to div */
  $('.searchchatsresults').append(SearchAjaxResponse);    
  }
/* highlight search term */
$('.searchchatsresults .mEl').mark(needle , null);
/* show showmore */
$('.searchchatsshowmore').removeClass('hidden');
}
$('.searchchatsbox-loadingicon').addClass('hidden');  
$('.searchchatsbox-searchicon').removeClass('hidden'); 
});

}

function autolinks(el) {
  /* iterate through els containing links,  make clickable */
  $(el).each(function() {
    var messageEl = $(this).html();
    var autolinks = anchorme({
      input: messageEl,
      options: {
        attributes: {
          target: "_blank",
          class: "autolinked"
        }
      }
    });

    $(this).html(autolinks);

  });
}

  function hideLinesCommentIcons(el){
  $(el).css('visibility', 'visible');
  LinesCommentIcons = 'inprogress';
  hideLinesCommentIcon = setTimeout(function(){
  $(el).css('visibility', 'hidden');
  LinesCommentIcons = 'hide';
  }, LinesTimeout);
  }
  
  function LinesClickAndScrollHandler(e){
    if (LinesCommentIcons == 'inprogress')
    {
    window.clearTimeout(hideLinesCommentIcon);
    hideLinesCommentIcons(e.data.el);
    } else {
    hideLinesCommentIcons(e.data.el);
      }
    /* handler */
    if ($(e.data.el).attr('type') == 'copycidlink') {
        //copy page link
 
    }
  }
  
  if (!String.prototype.format) {
String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}

$.ajaxQ = (function(){
  var id = 0, Q = {};

  $(document).ajaxSend(function(e, jqx){
    jqx._id = ++id;
    Q[jqx._id] = jqx;
  });
  $(document).ajaxComplete(function(e, jqx){
    delete Q[jqx._id];
  });

  return {
    abortAll: function(){
      var r = [];
      $.each(Q, function(i, jqx){
        r.push(jqx._id);
        jqx.abort();
      });
      return r;
    }
  };

})();

function colorize(str) {
    for (var i = 0, hash = 0; i < str.length; hash = str.charCodeAt(i++) + ((hash << 5) - hash));
    color = Math.floor(Math.abs((Math.sin(hash) * 10000) % 1 * 16777216)).toString(16);
    return '#' + Array(6 - color.length + 1).join('0') + color;
}

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
  
function devlog(str){
  if (dev == true)  console.log(str);
}