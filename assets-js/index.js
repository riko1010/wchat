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
  { trumbowyg: "https://cdn.jsdelivr.net/npm/trumbowyg@2.27.3/dist/trumbowyg.min.js" },
);

/* headjs loads, on ready -> */
head(function () {

  /* make links clickable, overflow does nkt wrap - fix asap */
  autolinks(".mEl");

   /* chat not found notice. default loaded, default gives not found notice, will fix asap*/
   devlog('appnoselected'+AppNoSelected);
   devlog('indexselected'+IndexSelected);
  if (!IndexSelected) {
  if (AppNoSelected) {
    jSuites.notification({
      title: "Chat not found",
      message: "Requested chat was not found. Loaded default.",
      timeout: 5000,
    });
   }
  }
});

$(document).ready(function () {

/* set body scroll padding top to header height */
$('body').css({
'scroll-padding-top' : $('#header').outerHeight() + 'px'
});
/* end set body scroll padding top to header height */

  /* setup select2 chafiles search */
  $(".searchselect").select2( {
    theme: 'bootstrap-5'
  } );
  /* select chatfile, redirect to selection */
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

