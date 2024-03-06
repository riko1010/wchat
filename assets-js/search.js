/* functions */
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
$('.showmore-loadingicon').removeClass('hidden');       
$('.searchchatsbox-loadingicon').removeClass('hidden');  

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
  nomore = true;
  if (!more)  {
  $('.searchchatsresults').text('no match found.');  
  /* return pointer to previous value for re-query */
  SPaginationFrom = Number(SPaginationFrom) - Number(ConfigRecordsPerPage);
  } else {
  /* hide showmore */
  $('.searchchatsshowmore').addClass('hidden');
  /* append no more results */
  $('.searchchatsresults').append('<span class="d-flex justify-content-center">no more results</span>');  
  }
devlog('no match found');
/* hide showmore and container */
$('.searchchatsshowmore').addClass('hidden');
} else {
nomore = false;  
devlog('matches found');  
  if (!more) {
  /* insert search result into div */
  $('.searchchatsresults').html(SearchAjaxResponse);  
  $('.showmore').removeClass('hidden');
 $('.showmore-loadingicon').addClass('hidden');
  } else {
  /* append search result to div */
  $('.searchchatsresults').append(SearchAjaxResponse);    
  $('.showmore').removeClass('hidden');
   $('.showmore-loadingicon').addClass('hidden');
  }
/* highlight search term */
$('.searchchatsresults .mEl').mark(needle , null);
/* show showmore */
$('.searchchatsshowmore').removeClass('hidden');
}
$('.searchchatsbox-loadingicon').addClass('hidden');  
});

}

/* end functions */

$(document).ready(function(){
/* conversation .cID click handler, siteurl/roundtolowest{ConfigRecordsPerPage}(cID)/#cID
*/
$('.searchchatsresults').on('click', '.cID', function(){
var searchchatsresultsFrom = Math.floor($(this).attr('cid') / ConfigRecordsPerPage) * ConfigRecordsPerPage;
var searchchatsresultsHash = '#' + $(this).attr('id');
searchchatsresultsURI = BasePageURI 
+ '/' 
+ searchchatsresultsFrom 
+ searchchatsresultsHash;
window.location.href = searchchatsresultsURI;
});
/* end conversation .cID click handler */  

/* searchchatsbox on search (keyup) */
$('.searchchatsbox').on('keyup', function(){
searchchatstriggernewval = $('.searchchatsbox').val();
/* hide showmore */
$('.searchchatsshowmore').slideUp();

if (searchchatstriggernewval == ''){
  devlog('search input empty');
  /* abort previous request */
  try {
    searchchats.abort();
    devlog('searchchats aborted');
  } catch(e) {
    devlog('searchchats was not aborted');
  }
  /* set trigger to default */
  $('.searchchatstrigger').text('');  
  $('.searchchatsresults').text('Enter keywords to search.');
  /* set loading, search icons to default*/
  $('.searchchatsbox-loadingicon').addClass('hidden');  
  devlog('no search keyword entered');
} else {
searchchatstriggernewval = searchchatstriggernewval.slice(0, 5);
/* managing real estate
$('.searchchatstrigger').html('<u>'+searchchatstriggernewval+'..</u>');
*/
$('.searchchatsresults').text('Searching..');
/* default is searching entire file from 0 with pagination of 100 lines */
if (SelectedPaginationFrom = 'entirechatfile') {
 SPaginationFrom = 0;
} else {
 SPaginationFrom = AppPaginationFrom;
}
// search
SearchChats(
  APIUrl, 
  AppSelectedId, 
  SPaginationFrom, 
  searchchatstriggernewval,
  );
  
}
});
/* end searchchatsbox on search (keyup) */

/* search .showmore onclick */
$('.showmore').on('click', function(e){
devlog('showmore');
e.preventDefault();
 $('.showmore').addClass('hidden');
 $('.showmore-loadingicon').removeClass('hidden');
/* add ConfigRecordsPerPage to SPaginationFrom */
SPaginationFrom = Number(SPaginationFrom) + Number(ConfigRecordsPerPage);
// search - showmore
/* searchchats input value */
searchchatstriggernewval = $('.searchchatsbox').val();
SearchChats(
  APIUrl, 
  AppSelectedId, 
  SPaginationFrom, 
  searchchatstriggernewval,
  true,
  );
  
  if (nomore) {
 $('.showmore').addClass('hidden');
 $('.showmore-loadingicon').addClass('hidden'); 
  } else {
 $('.showmore').removeClass('hidden');
 $('.showmore-loadingicon').addClass('hidden');
  }
});
/* end search .showmore onclick */

/* .searchchats on click, hide navicons, show searchbox and searchresults */
$('.searchchats').on('click', function(){
$('.navicons').slideUp();
$('.searchchats-container').slideDown();
$('header-search').slideDown();
$('.searchchatsbox').focus();
$('.searchchatsresults').removeClass('hidden');
});
/* end .searchchats on click, */
 
  /* show navicons, hide searchbox and searchresults */
    $('.chat-container, .wchatlogo, select .searchselect').on('click', function(){
      $('.searchchatsresults').addClass('hidden');
      $('.searchchatsshowmore').addClass('hidden');
      $('.searchchats-container').slideUp();
      $('header-search').slideUp(); 
    $('.navicons').slideDown();  
    
    });


/* end dom ready */    
});