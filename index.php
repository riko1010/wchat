<?php 
//error_reporting(0);
require_once 'vendor/autoload.php';
use Elegant\Sanitizer\Sanitizer;
use Laminas\Config\Config as Config;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();


/* Request Handler */
$data = [
    'queryarg' => (isset($_REQUEST['queryarg']) ? $_REQUEST['queryarg'] : null )
];
$filters = [
    'queryarg' => 'trim|empty_string_to_null|strip_tags|escape'
];
$REQUEST = (object) (new Sanitizer($data, $filters))->sanitize();

$currentURL = null;
$totalrecords = null;
$ConfigFile = 'assets-php/settings.php';
require 'assets-php/classes.php';

/*  load whatsapp backup file by ?backupfile=1
BASE KEY = 1, NOT 0 */
$Config = new Config(include $ConfigFile, true);
$db = new Database(
  pj(
    $Config->baseDir, 
    $Config->sqlitedb,
    )
  );
$Init = new Init;
$Init->baseDir = $Config->baseDir;
$Init->queryarg = $REQUEST->queryarg;
$InitData = $Init->Index($db);
$Init->AppendConfig(
  $db,
  'AppData',
  ['id' => 1],
  $Config,
  );
var_dump($Config);
exit;

/* app instance */
$App = new App;

$sitemap = new generateSiteMap;
$sitemap->AppData = &$Config;
$sitemap->cfFilespattern = $cfFilespattern;
$sitemap->SiteUrl = $SiteUrl; 
$sitemap->cfFolder = $cfFolder;
$sitemap->ChatFilesData = &$InitData->Data;
$sitemap->ChatFilesDataIdAsKeys = &$InitData->DataIdAsKeys;
$sitemap->robotstxt = $robotstxt;
$sitemap->sitemapxml = $sitemapxml;
$sitemap->PyArchiveURI = $PyArchiveURI;
$sitemap->bdir = $baseDir;
$sitemap->db = $db;
$sitemap->CallFunc->{'$App\CFgetfiles'} = $App->CFgetfiles(...);
$sitemaps = $sitemap->get();
/*
return [
  'sitemap' => [status, response]
  'files' => [filename, file, exists]
  ];
*/

/* reinit */
$InitData = $Init->Index($db);
$Config = $Init->Config($db);

if (!$InitData->IsEmpty) {
$App->ChatFilesData = $InitData->Data;
$App->ChatFilesDataIdAsKeys = $InitData->DataIdAsKeys;
$App->baseDir = $baseDir;
$App->SetChatFile($REQUEST->queryarg);
/* $App->SelectedId now set  */
$App->SetVerifiedRecipient( $App->Name );

$processLines = new processLines;
$processLines->baseDir = $baseDir;
$processLines->PaginationFrom = 0;
$processLines->PaginationTo = $recordsperpage;
}
/* 
$App\NPaginationFrom
$App\NPaginationTo
now available 
*/
?>
<!DOCTYPE html>

<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?=$App->PageTitle();?></title>

<!-- opengraph -->
<meta property="og:title" content="<?=$App->PageTitle();?>" />
<meta property="og:type" content="<?=$ogcontenttype;?>" />
<meta property="og:url" content="<?=$currentURL;?>" />
<meta property="og:image" content="<?=$ogImage;?>" />
    
<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">

<link href="
https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css
" rel="stylesheet">

<link rel="stylesheet" href="css/main.css">
  
  <link href="
https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/css/glightbox.min.css
" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/video.js@8.3.0/dist/video-js.min.css
" rel="stylesheet">

<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<script src="https://cdnjs.cloudflare.com/ajax/libs/headjs/1.0.3/head.min.js" integrity="sha512-8Nk/zoTKjNixnM15wXjpF26KR4Ln87cc5Yllc5xP54wwbcKnljAAn2JP+tYAS8+4e7s/XK8XTiDH0Ltw2fmoBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js" referrerpolicy="no-referrer"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdn.jsdelivr.net/npm/scrollmagic@2.0.8/scrollmagic/minified/ScrollMagic.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="assets-js/classes.js" referrerpolicy="no-referrer"></script>  
<script>
 $(document).ready(function(){
     // setup Tooltips
   $(".searchselect").select2(); 
   //select chat, redirect to selection
   $('.searchselect').on('select2:select', function (e) {
    window.location.href = "./"+$(this).val();
  });
});
</script>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-MH4G76CM');</script>
<!-- End Google Tag Manager -->
</script>

</head>

<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MH4G76CM"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->  
    <header class="brand">
      <!-- select -->
<select style="" class="form-select form-control searchselect">
<?=$App->Menu();?>           
 </select>      
    </header>
    
    <main class='container h-100'>
<!-- no backupfile found -->
<?php 
/* may provide archive.org or other archive */
if ($InitData->IsEmpty) {
?>
<div class="row">
<div class="col card">
cannot display chat files at this time - <a href="" class="reloadpage">retry</a>
</div>
</div>
<?php
}
?>

<?php
/* start chat list if backupfile present */
if (!$InitData->IsEmpty){
?>
<section><h3 class='chat-title'>WhatsApp Chat with <?=$App->Name;?></h3>
<div class='row p-1 spotlight-group' id='whatsappimages'>
<?=$processLines->ProcessAndPrint($App);?>
</div>

</section>

<section id="ArchivedNav">
<?=$App->PaginationNav();?>
</section>

<div id="loader"> </div>
<div class="loader hidden d-flex justify-content-center">
<img src="icons/loader.gif" alt="Loading">
</div>
<?php
}
/* end chat list */

            ?>
    </main>
<!-- hidden nav, may show on noscript -->    
<?php
print "<nav hidden>";
foreach ($InitData->Data as $link) {
print '<a href="'.$link['url'].'">'.$link['url'].'</a> <br />';
}
print "</nav>";
?>
<!-- end hidden nav -->

<!-- Modal -->
<div class="modal fade" id="sitemapModal" tabindex="-1" aria-labelledby="sitemapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="sitemapModalLabel">Sitemap</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

<ul class="list-group list-group-flush">
<?php        
if (isset($sitemaps->files)) {

foreach($sitemaps->files as $sitemap) {
if ($sitemap['exists'] == false) continue;
?>   
  <a href="<?=$sitemap['rpath']; ?>" class="link-body-emphasis list-group-item list-group-item-action d-flex justify-content-between align-items-start" target="_blank">
    <div class="ms-2 me-auto">
      <div class="fw-bold">
      <?=$sitemap['filename']; ?>
        </div>
<?=isset($sitemap['stats']) ? getFilesize($sitemap['stats']['size'], 'human') : ''; ?>
    </div>
    <span class="badge bg-success rounded-pill">
<?=isset($sitemap['stats']) ? date('M d, Y', $sitemap['stats']['mtime']) : ''; ?>
    </span>
  </a>  
<?php 
}
}
?>
  <span class="link-body-emphasis list-group-item list-group-item-action d-flex justify-content-between align-items-start">

    <div class="ms-2 me-auto">

      <div class="fw-bold">
      <?=($sitemaps->sitemap->status ? 'OK' : 'ERROR'); ?>
        </div>
<?=$sitemaps->sitemap->response; ?>
    </div>
    <span class="badge bg-success rounded-pill">
<?=date('M d, Y h:i:sa', time()); ?>
    </span>
  </span>  

</ul>  
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

    <footer>
      <?=($App->eof ?: $App->NPaginationFrom.','.$App->NPaginationTo);?> <a href="sitemap.xml" data-bs-toggle="modal" data-bs-target="#sitemapModal">sitemap</a> | from <a target="_blank" href="https://github.com/itxshakil/Whatsapp-backup-Viewer">Shakil Alam on Github</a>
    </footer>
  
<script>
dev = true;
<?php
/* try, catch exception to precent break on exceptions */
/* no scrollmagic needed if no id or pagination */
if ($App->eof == false && $App->SelectedId != null) {
?>
$(document).ready(function(){
isr = new infinitescrollrequest();
isr.url = 'api';
isr.queryarg = '<?=$App->SelectedId;?>';
isr.paginationfrom = '<?=$App->NPaginationFrom;?>';
isr.paginationto = '<?=$App->NPaginationTo;?>';
isr.recordsperpage = '<?=$recordsperpage;?>';
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
<?php
}
?>
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

		</script>
</body>
</html>