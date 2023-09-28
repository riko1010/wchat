class infinitescrollrequest {
  url;
  responsecontainer;
  queryarg;
  pagination;
  npagination;
  recordsperpage;

  FetchData() {
    var Ct = this;
    return new Promise(function(resolve, reject) {
      /* post request sent was built and network debugger shows the request body built and concatenated with &, type www/urlencoded** data , api shows $_REQUEST to be empty. using get, requests is deprecated, axios examples for post do not work, superagent, fetch, others considered or xmlhttp* */
      let PromiseResponse = {};
      Ct.StartTime = Date.now();
      $.ajax({
        method: "GET",
        url: Ct.url,
        data: {
          queryarg: Ct.queryarg,
          paginationfrom: Ct.paginationfrom,
          paginationto: Ct.paginationto,
          recordsperpage: Ct.recordsperpage
        },
        dataType: 'json'
}).then(function(AjaxResponse) {
Ct.EndTime = Date.now();
devlog('request done');
if (AjaxResponse.status == 'success') {
$(Ct.responsecontainer).append(AjaxResponse.response);

Ct.TotalDuration = (Ct.EndTime - Ct.StartTime);
PromiseResponse.npaginationfrom = AjaxResponse.paginationfrom;
PromiseResponse.npaginationto = AjaxResponse.paginationto;
PromiseResponse.nrecordsperpage = Ct.recordsperpage;

if (Ct.TotalDuration > Ct.maxFetchDataDuration) {
/* adjust total records by response speed for 100 records default min = 25, max = 100 */
devlog(`${Ct.TotalDuration}  mseconds`);
Ct.DurationForARecord = Ct.TotalDuration / Ct.recordsperpage;
Ct.nrecordsperpage = Ct.maxFetchDataDuration * Ct.DurationForARecord;
devlog(`New records per page: ${Ct.nrecordsperpage}`);
if (Ct.nrecordsperpage < Ct.minrecordsperpage) {
/* set to minimum if connection extremely slow, must overflow vh for trigger reasonably */
Ct.nrecordsperpage = Ct.minrecordsperpage;
Ct.npaginationto = PromiseResponse.npaginationfrom + Ct.nrecordsperpage;
} else {
Ct.npaginationto = PromiseResponse.npaginationfrom + Ct.nrecordsperpage;
}

PromiseResponse.npaginationto = Ct.npaginationto;
PromiseResponse.nrecordsperpage = Ct.nrecordsperpage; 
devlog(`Adjusted Records per page: ${Ct.nrecordsperpage}`);
devlog(`Adjusted Pagination: ${PromiseResponse.npaginationfrom} - ${PromiseResponse.npaginationto}`);
}
       
        } else if (AjaxResponse.status == 'eof') {
          /* response starting with eof is end of pagination, destroy scene notice to promise */
          PromiseResponse.requestresponse = 'destroyscene';
        } else if (AjaxResponse.status == 'filenotfound') {
          /* response starting with filenotfound is end of pagination - page refresh to promise ?, ajax refresh to set session/recordid if magically unavailable possible */
          PromiseResponse.requestresponse = 'ajaxrefresh';
        } else if (AjaxResponse.status == 'no request') {
          /* response starting with no request is no request received - notice */
          PromiseResponse.requestresponse = 'ajaxrefresh';
        } else {
          PromiseResponse.requestresponse = 'unknown';
        }
        devlog('resolving promise');
        resolve(PromiseResponse);
      }, function(){
        PromiseResponse.requestresponse = 'unknown';
        devlog('resolving failed request promise');
        reject(PromiseResponse);
      });
    });
  }

FetchDataSuccessHandler(PromiseResponse) {
switch (PromiseResponse) {

  case 'destroyscene':
    controller.destroy(reset);
		controller = null;
		scene.destroy(reset);
  	scene = null;
    break;
  case 'ajaxrefresh':
    /* ajax put,post, request without response body, request to same url ?? */
    
    break;
  case 'unknown':
    /* unknown, all other responses, response is not returned but PromiseResponse object returns all assigned properties */
    break;
  default:
    scene.enabled(true);
    scene.update();
    break;
  }
}
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

function devlog(str){
  if (dev == true)  console.log(str);
}