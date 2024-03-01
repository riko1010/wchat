
/*  */
/*  loads, on ready -> */
$(document).ready(function () {
  
  /* copy admin chatfile link */
   var clipboard3 = new ClipboardJS(".AdminChatfileLink", {
      container: document.getElementById('adminModal'),
      text: function (trigger) {
      adminchatfilelink = trigger.getAttribute('value');
      devlog(adminchatfilelink);
      return adminchatfilelink;
    },
  });
  clipboard3.on("success", function (e) {
    //toast copied.
    jSuites.notification({
      title: "Chat File Link Copied",
      message: "Chat File Link Copied to clipboard.",
      timeout: 5000,
    });
    devlog('chat file link copied to clipboard');
  });
  clipboard3.on("error", function (e) {
    //toast or link popup in modal
    devlog('failed: chat file link was not copied to clipboard');
  });
/* end copy admin chatfile link */

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
  
});