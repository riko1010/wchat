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
  { quill: "https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.2/dist/quill.js" },
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
  /* end copy page link at top menu, will switch to data attr*/
  
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

