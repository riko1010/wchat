
function SearchChats(url, queryarg, paginationfrom, needle, more = ''){
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
$('.searchchatsresults').text('no match found.');  
devlog('no match found');
/* hide showmore */
$('.searchchatsshowmore').slideUp();
} else {
devlog('matches found');  
/* insert search result into div */
$('.searchchatsresults').html(SearchAjaxResponse);  
/* highlight search term */
$('.searchchatsresults .mEl').mark(needle , null);
/* show showmore */
$('.searchchatsshowmore').slideDown();
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

function devlog(str){
  if (dev == true)  console.log(str);
}