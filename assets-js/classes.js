
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

function getBackgroundColor(stringInput) {
    const h = [...stringInput].reduce((acc, char) => {
        return char.charCodeAt(0) + ((acc << 5) - acc);
    }, 0);
    const s = 95, l = 35 / 100;
    const a = s * Math.min(l, 1 - l) / 100;
    const f = n => {
        const k = (n + h / 30) % 12;
        const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
        return Math.round(255 * color).toString(16).padStart(2, '0');   // convert to Hex and prefix "0" if needed
    };
    return `#${f(0)}${f(8)}${f(4)}`;
}

function getBackgroundColorworks(stringInput) {
    let stringUniqueHash = [...stringInput].reduce((acc, char) => {
        return char.charCodeAt(0) + ((acc << 5) - acc);
    }, 0);
    return `hsl(${stringUniqueHash % 360}, 95%, 35%)`;
}

function devlog(str){
  if (dev == true)  console.log(str);
}