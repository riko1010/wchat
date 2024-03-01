
/*  loads, on ready -> */
$(document).ready(function () {

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



