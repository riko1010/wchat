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
  $('.searchchatstrigger').text('Search..');  
  $('.searchchatsresults').text('Enter keywords to search.');
  /* set loading, search icons to default*/
  $('.searchchatsbox-loadingicon').addClass('hidden');  
  $('.searchchatsbox-searchicon').removeClass('hidden');  
  devlog('no search keyword entered');
} else {
searchchatstriggernewval = searchchatstriggernewval.slice(0, 5);
$('.searchchatstrigger').html('<u>'+searchchatstriggernewval+'..</u>');
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
/* add ConfigRecordsPerPage to SPaginationFrom */
SPaginationFrom = SPaginationFrom + ConfigRecordsPerPage;
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
});
/* end search .showmore onclick */

  
});