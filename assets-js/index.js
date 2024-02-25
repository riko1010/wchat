// load scripts with headjs
head.js(
  {
    clipboardjs:
      "https://cdn.jsdelivr.net/npm/clipboard@2.0.11/dist/clipboard.min.js",
  },
  {
    glightbox:
      "https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/js/glightbox.min.js",
  },
  { videojs: "https://cdn.jsdelivr.net/npm/video.js@8.3.0/dist/video.min.js" },
  {
    anchorme:
      "https://cdn.jsdelivr.net/npm/anchorme@3.0.5/dist/browser/anchorme.min.js",
  },
  { jsuites: "https://cdn.jsdelivr.net/npm/jsuites@5.0.27/dist/jsuites.min.js" },
  { notie: "https://cdn.jsdelivr.net/npm/notie@4.3.1/dist/notie.min.js" },
  { markjs: "https://cdn.jsdelivr.net/npm/mark.js@8.11.1/dist/jquery.mark.min.js" },
  { tinymce: "https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" }
);

/* headjs loads, on ready -> */
head(function () {

  /* make links clickable, overflow does nkt wrap - fix asap */
  autolinks(".mEl");

  /* lightbox for images, docs, iframes, current lightbox does not close when clicking overlay */
  const lightbox = GLightbox({
    touchNavigation: true,
    loop: true,
    autoplayVideos: true,
    selector: ".g",
    closeOnOutsideClick: true,
    preload: true,
  });

  /* copy page link at top menu, will switch to data attr*/
  var clipboard = new ClipboardJS(".copypagelink", {
    text: function (trigger) {
      return window.location.href;
    },
  });
  clipboard.on("success", function (e) {
    //toast copied.
    jSuites.notification({
      title: "Page Link Copied",
      message: "Page Link Copied to clipboard.",
      timeout: 5000,
    });
    devlog('page link copied to clipboard');
  });
  clipboard.on("error", function (e) {
    //toast or link popup in modal
    jSuites.notification({
      title: "Conversation Link was not copied",
      message: "Conversation Link was not copied to clipboard.",
      timeout: 5000,
    });
    devlog('failed: page link copy to clipboard');
  });
  
  /* copy individual conversation link - will switch to data attr asap */
   var clipboard2 = new ClipboardJS(".copycidlink", {
    text: function (trigger) {
      conversationurl= CurrentUrl +'#'+ trigger.getAttribute('cid');
      return conversationurl;
    },
  });
  clipboard2.on("success", function (e) {
    //toast copied.
    jSuites.notification({
      title: "Conversation Link Copied",
      message: "Conversation Link Copied to clipboard.",
      timeout: 5000,
    });
    devlog('conversation link copied to clipboard');
  });
  clipboard2.on("error", function (e) {
    //toast or link popup in modal
    devlog('failed: conversation link copy to clipboard');
  });
  /* end copy conversation link */
  
  
  
   /* chat not found notice. default loaded, default gives not found notice, will fix asap*/
  if (AppNoSelected && IndexSelected == false) {
    jSuites.notification({
      title: "Chat not found",
      message: "Requested chat was not found. Loaded default.",
      timeout: 5000,
    });
  }
});

$(document).ready(function () {

  /* change header position to fixed on load */
  $('#header').css({
      'position' : 'fixed'
    });  
  /* chat-container top margin, header is sticky on load, changes to fixed and add top margin of calculated header height to chat-container */ 
    $('.chat-container').css({
            'margin-top' : $('#header').outerHeight() + 'px'
        });
  /* set body scroll padding top to header height */
  $('body').css({
            'scroll-padding-top' : $('#header').outerHeight() + 'px'
        });
  
    /* .cID click handler, 
  siteurl/roundtolowest{ConfigRecordsPerPage}(cID)/#cID
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
  
  /* .searchchats on click, hide navicons, show searchbox and searchresults */
  $('.searchchats').on('click', function(){
  $('.navicons').slideUp();
  $('.searchchats-container, header-search').slideDown();
  $('.searchchatsbox').focus();
  });
  
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
  
  /* .showmore onclick */
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
  /* show navicons, hide searchbox and searchresults */
    $('.chat-container, .wchatlogo, select .searchselect').on('click', function(){
    $('.searchchats-container, header-search').slideUp(); 
    $('.navicons').slideDown();   
    });
  
  // setup select2
  $(".searchselect").select2( {
    theme: 'bootstrap-5'
  } );
  //select chat, redirect to selection
  $(".searchselect").on("select2:select", function (e) {
    window.location.href = $(this).val();
  });
  
  //show comment icons on scroll,click
  if (CommentIcons) {
  LinesCommentIcons = "hide";
  LinesTimeout = 2000;
  $(".Lines").on(
    "click",
    {
      el: ".autohideicons",
    },
    LinesClickAndScrollHandler
  );
  $(window).on(
    "scroll",
    {
      el: ".autohideicons",
    },
    LinesClickAndScrollHandler
  );
  }

  //instantiate tooltips
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => $(tooltipTriggerEl).tooltip()
  );
 
   $(document).on('shown.bs.tooltip', function (e) {
      setTimeout(function () {
        $(e.target).tooltip('hide');
      }, 2000);
   });

  // show menu to copy chat or auto copy chat id then notify...
  /* $(".cID").on("contextmenu", function () {
    return false;
    //context menu
  });
  */

  //suggest group chat
  if (ConfigSuggestGroupChat == true) {
  //suggest group chat
  }

var options = {
    offset: 80,
};
var myElement = document.querySelector("header");
// construct an instance of Headroom, passing the element
var headroom  = new Headroom(myElement, options);
// initialise
headroom.init();
});

//scroll to hash - natural scroll to fails
window.addEventListener('load', function () {
  devlog('scrolltohash');
	if (window.location.hash == '') {
	  devlog('no hash');
		return false;
	}
	var el = document.querySelector(window.location.hash);
	if (el !== null) {
	  devlog('hash'+window.location.hash);
	  devlog('scrolling to '+el);
		el.scrollIntoView({ block: "center", inline: "nearest", behavior: 'auto' });
	}
}, false);

// Google Tag Manager
(function (w, d, s, l, i) {
  w[l] = w[l] || [];
  w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
  var f = d.getElementsByTagName(s)[0],
    j = d.createElement(s),
    dl = l != "dataLayer" ? "&l=" + l : "";
  j.async = true;
  j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
  f.parentNode.insertBefore(j, f);
})(window, document, "script", "dataLayer", "GTM-MH4G76CM");
// End Google Tag Manager