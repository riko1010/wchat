
/* headjs */
/* headjs loads, on ready -> */
head(function () {
  
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

});