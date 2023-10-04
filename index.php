<?php
//error_reporting(0);
require_once 'vendor/autoload.php';
/*
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();
*/

$currentURL = null;
$totalrecords = null;

/*  load whatsapp backup file by ?backupfile=1
BASE KEY = 1, NOT 0 */
?>
html(lang="en")
head
  meta(charset="UTF-8")
  meta(name="viewport" content="width=device-width, initial-scale=1.0")
  meta(http-equiv="X-UA-Compatible" content="ie=edge")
  meta(property="og:title" content="<?= $App->PageTitle() ?>")
  meta(property="og:type" content="<?= $Config->og->contenttype ?>")
  meta(property="og:url" content="<?= $currentURL ?>")
  meta(property="og:image" content="<?= $Config->og->image ?>")
  title <?= $App->PageTitle() ?>
style    
  include https://fonts.googleapis.com/icon?family=Material+Icons
  include https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css
  include css/main.css
  include https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/css/glightbox.min.css
  include https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css
  include https://cdn.jsdelivr.net/npm/video.js@8.3.0/dist/video-js.min.css
  include https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css

Script
  include https://cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.min.js" integrity="sha512-8Nk/zoTKjNixnM15wXjpF26KR4Ln87cc5Yllc5xP54wwbcKnljAAn2JP+tYAS8+4e7s/XK8XTiDH0Ltw2fmoBQ==
  include https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js
  include https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js
  include https://cdn.jsdelivr.net/npm/scrollmagic@2.0.8/scrollmagic/minified/ScrollMagic.min.js
  include assets-js/classes.js
  
:javascript
  $(document).ready(function(){
  // setup Tooltips
  $(".searchselect").select2(); 
  //select chat, redirect to selection
  $('.searchselect').on('select2:select', function (e) {
  window.location.href = "./"+$(this).val();
  });
  });
  
  //<!-- Google Tag Manager -->
  (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
  new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
  j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
  'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
  })(window,document,'script','dataLayer','GTM-MH4G76CM');</script>
  //<!-- End Google Tag Manager -->

body
  <!-- Google Tag Manager (noscript) -->
    noscript
      iframe(src="https://www.googletagmanager.com/ns.html?id=GTM-MH4G76CM" height="0" width="0" style="display:none;visibility:hidden")
  header(class="brand")
    select(style="" class="form-select form-control searchselect")
      <?= $App->Menu($Init) ?>           
  main(class='container h-100')
    <?php if ($Init->Data->IsEmpty) { ?>
    div(class="row")
    div(class="col card")
    cannot display chat files at this time - 
    <a href="" class="reloadpage">retry</a>
    <?php } ?>
  
  <?php if (!$Init->Data->IsEmpty) { ?>
  section
    h3(class='chat-title'>WhatsApp Chat with <?= $App->Name ?>')
    div class='row p-1 spotlight-group' id='whatsappimages'>
  <?= $processLines->ProcessAndPrint($Config, $App) ?>
  paginationnav(id="ArchivedNav")
    <?= $App->PaginationNav($Config, $App) ?>
    div(id="loader")
    div(class="loader hidden d-flex justify-content-center")
    <img src="icons/loader.gif" alt="Loading">
  
  nav(hidden)
  foreach ($Init->Data->IsEmpty as $link) {
    print '<a href="' . $link['url'] . '">' . $link['url'] . '</a> <br />';
  }
  
  div(class="modal fade" id="sitemapModal" tabindex="-1" aria-labelledby="sitemapModalLabel" aria-hidden="true")
    div(class="modal-dialog modal-dialog-centered modal-dialog-scrollable")
      div(class="modal-content")
        div(class="modal-header")
          h1(class="modal-title fs-5" id="sitemapModalLabel")Sitemap
          button(type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close")
        div(class="modal-body")
  
  ul(class="list-group list-group-flush")
  <?php if (isset($sitemaps->files)) {
    foreach ($sitemaps->files as $sitemap) {
      if ($sitemap['exists'] == false) {
        continue;
      } ?>   
    <a href="<?= $sitemap[
      'rpath'
    ] ?>" class="link-body-emphasis list-group-item list-group-item-action d-flex justify-content-between align-items-start" target="_blank">
      <div class="ms-2 me-auto">
        <div class="fw-bold">
        <?= $sitemap['filename'] ?>
          </div>
  <?= isset($sitemap['stats'])
    ? getFilesize($sitemap['stats']['size'], 'human')
    : '' ?>
      </div>
      span(class="badge bg-success rounded-pill")
      <?= isset($sitemap['stats'])
        ? date('M d, Y', $sitemap['stats']['mtime'])
        : '' ?>
  <?php
    }
  } ?>
      span(class="link-body-emphasis list-group-item list-group-item-action d-flex justify-content-between align-items-start")
  
      div(class="ms-2 me-auto")
  
        div(class="fw-bold")
        <?= $sitemaps->sitemap->status ? 'OK' : 'ERROR' ?>
          
  <?= $sitemaps->sitemap->response ?>
      
        span(class="badge bg-success rounded-pill")
        <?= date('M d, Y h:i:sa', time()) ?>
  
        div(class="modal-footer")
          button(type="button" class="btn btn-secondary" data-bs-dismiss="modal")Close
  
      footer
        <?= $App->eof ?:
          $Config->NPaginationFrom .
            ',' .
            $Config->NPaginationTo ?> <a href="sitemap.xml" data-bs-toggle="modal" data-bs-target="#sitemapModal">sitemap</a> | from <a target="_blank" href="https://github.com/itxshakil/Whatsapp-backup-Viewer">Shakil Alam on Github</a>
    
  script
  dev = true;
   /* archive.org modifies js, jwuery, functions are available, classes do not seem available, suggestgroupchat will ot utlize classes until solution found */<?php if (
     $Config->SuggestGroupChat == true
   ) { ?>
  
  <?php } ?>
   /* no scrollmagic needed if no id or pagination */<?php if (
     $App->eof == false &&
     $App->SelectedId != null
   ) { ?>
  $(document).ready(function(){
  isr = new infinitescrollrequest();
  isr.url = 'api';
  isr.queryarg = '<?= $App->SelectedId ?>';
  isr.paginationfrom = '<?= $Config->NPaginationFrom ?>';
  isr.paginationto = '<?= $Config->NPaginationTo ?>';
  isr.recordsperpage = '<?= $Config->recordsperpage ?>';
  /* must overflow vh for trigger event 'onenter' reasonably*/
  isr.minrecordsperpage = 50; 
  isr.maxFetchDataDuration = 1000; //ms
  isr.responsecontainer = '#whatsappimages';
  isr.loadercontainer = '.loader';
  // init controller
  controller = new ScrollMagic.Controller();
  	// build scene
  scene = new ScrollMagic.Scene({triggerElement: "#loader", triggerHook: "onEnter"})
  .addTo(controller)
  .on("enter", function (e) 
  {
  scene.enabled(false);
  $(isr.loadercontainer).removeClass("hidden");
  try {
    isr.FetchData().then(function(PromiseResponse) {
      /* success handler */
    isr.paginationfrom = PromiseResponse.npaginationfrom;
    isr.paginationto = PromiseResponse.npaginationto;
    isr.recordsperpage = PromiseResponse.nrecordsperpage;
    
    isr.FetchDataSuccessHandler(PromiseResponse);
    $(isr.loadercontainer).addClass("hidden");
    }, function(PromiseResponse){
    /* failed request, enable scene event listener */
    $(isr.loadercontainer).addClass("hidden");
    scene.enabled(true);
    scene.update();  
    });
  } catch(e) {
    $(isr.loadercontainer).addClass("hidden");
    scene.enabled(true);
    scene.update();  
  }
  
  });
  
  });
  <?php } ?>
  /* headjs loads, on ready -> */
  head(function() {
    /* make links clickable */
    autolinks('.mEl');
    /* lightbox for images, docs, iframes */ 
    const lightbox = GLightbox({
      touchNavigation: true,
      loop: true,
      autoplayVideos: true,
      selector: '.g',
      closeOnOutsideClick: true,
      preload: true
  });
  
    $(".cID").on('contextmenu', function(){
        // show menu to copy chat or auto copy chat id then notify... 
      });
  });
  
  
  // load scripts by assigning a label for them
  head.js(
  {bootstrapjs: "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"},
  {glightbox: "https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/js/glightbox.min.js"},
  {videojs:"https://cdn.jsdelivr.net/npm/video.js@8.3.0/dist/video.min.js"},
  {anchorme:"https://cdn.jsdelivr.net/npm/anchorme@3.0.5/dist/browser/anchorme.min.js"}
  );
