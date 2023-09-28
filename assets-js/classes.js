class infinitescrollrequest {
  url;
  responsecontainer;
  queryarg;
  pagination;
  npagination;
  recordsperpage;

  FetchData() {
    var Classthis = this;
    return new Promise(function(resolve, reject) {
      /* post request sent was built and network debugger shows the request body built and concatenated with &, type www/urlencoded** data , api shows $_REQUEST to be empty. using get, requests is deprecated, axios examples for post do not work, superagent, fetch, others considered or xmlhttp* */
      let PromiseResponse = {};
      Classthis.StartTime = Date.now();
      $.ajax({
        method: "GET",
        url: Classthis.url,
        data: {
          queryarg: Classthis.queryarg,
          pagination: Classthis.pagination,
          recordsperpage: Classthis.recordsperpage
        },
        dataType: 'json'
      }).then(function(AjaxResponse) {
        Classthis.EndTime = Date.now();
        devlog('request done');
        if (AjaxResponse.status == 'success') {
          $(Classthis.responsecontainer).append(AjaxResponse.response);
          
          PromiseResponse.npagination = AjaxResponse.pagination;
          /* adjust total records by response speed for 100 records default min = 25, max = 100 */
          Classthis.TotalDuration = (Classthis.EndTime - Classthis.StartTime);
          devlog(`${Classthis.TotalDuration}  mseconds`);
          Classthis.DurationForARecord = Classthis.TotalDuration / Classthis.recordsperpage;
          Classthis.adjustedrecordsperpage = Classthis.maxFetchDataDuration * Classthis.DurationForARecord;
          if (Classthis.adjustedrecordsperpage < Classthis.minrecordsperpage) {
            /* set to minimum if connection extremely slow, must overflow vh for trigger reasonably */
            Classthis.adjustedrecordsperpage = Classthis.minrecordsperpage;
            Classthis.adjustedpaginationTo = PromiseResponse.npagination.From + Classthis.adjustedrecordsperpage;
          } else {
            Classthis.adjustedpaginationTo = PromiseResponse.npagination.From + Classthis.adjustedrecordsperpage;
          }
          PromiseResponse.npagination.To = Classthis.adjustedpaginationTo;
          PromiseResponse.adjustedrecordsperpage = Classthis.adjustedrecordsperpage; 
         
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