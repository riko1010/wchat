
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

  function hideLinesCommentIcons(el){
  $(el).css('visibility', 'visible');
  LinesCommentIcons = 'inprogress';
  hideLinesCommentIcon = setTimeout(function(){
  $(el).css('visibility', 'hidden');
  LinesCommentIcons = 'hide';
  }, LinesTimeout);
  }
  
  function LinesClickAndScrollHandler(e){
    if (LinesCommentIcons == 'inprogress')
    {
    window.clearTimeout(hideLinesCommentIcon);
    hideLinesCommentIcons(e.data.el);
    } else {
    hideLinesCommentIcons(e.data.el);
      }
    /* handler */
    if ($(e.data.el).attr('type') == 'copycidlink') {
        //copy page link
 
    }
  }
  
  if (!String.prototype.format) {
String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) { 
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}

$.ajaxQ = (function(){
  var id = 0, Q = {};

  $(document).ajaxSend(function(e, jqx){
    jqx._id = ++id;
    Q[jqx._id] = jqx;
  });
  $(document).ajaxComplete(function(e, jqx){
    delete Q[jqx._id];
  });

  return {
    abortAll: function(){
      var r = [];
      $.each(Q, function(i, jqx){
        r.push(jqx._id);
        jqx.abort();
      });
      return r;
    }
  };

})();

function colorize(str) {
    for (var i = 0, hash = 0; i < str.length; hash = str.charCodeAt(i++) + ((hash << 5) - hash));
    color = Math.floor(Math.abs((Math.sin(hash) * 10000) % 1 * 16777216)).toString(16);
    return '#' + Array(6 - color.length + 1).join('0') + color;
}

  
function devlog(str){
  if (dev == true)  console.log(str);
}