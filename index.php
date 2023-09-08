<?php 
//error_reporting(0);
require_once 'vendor/autoload.php';

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$currentURL = '';

require 'assets-php/settings.php';
require 'assets-php/classes.php';

$cfFolders = CFgetfolders($cfFolder);
/* 
returns 
 'chatFolders' - array
  'chatFolder' - chat folder input returned
*/
/*
chatfile.(unique int identifier).txt works */
if (isset($cfFolders->chatFolders) && count($cfFolders->chatFolders) > 0 ) {

$cfFiles = CFgetfiles(
  $cfFolders->chatFolder, 
  $cfFolders->chatFolders, 
  $cfFilespattern
  );
  /* 
  returns array
  'cfl' - array
  'bf' - selected backup file index 
  'gbfc' - global backup file count
  */
  if (isset($cfFiles->cfl) && count($cfFiles->cfl) > 0) {
  
  /* set default to 1 if none selected */
  $bf = (!isset($cfFiles->bf) ? (($nobf = true) ? 1 : 1 ) : $cfFiles->bf );
  
  $cfl = $cfFiles->cfl;
  $cf = (object) $cfFiles->cfl[$bf];
  }
}

if (isset($cfFiles->gbfc) && $cfFiles->gbfc > 0) { 
$chatLinks = array_column($cfl, 'search');
/*  load whatsapp backup file by ?backupfile=1
BASE KEY = 1, NOT 0 */

//try {
 /* load chat file selected */
$fileandrecipient = CFloadselectedfile($cf->filepath, $cf->name);
//} catch(Exception $e) { }

$filearray = isset($fileandrecipient->filearray) ? $fileandrecipient->filearray : (object) [];

/* undecided, intensive process.. cant trust js , either separate process or retained */
if (count($chatLinks) > 0) {
$sitemap = new generateSiteMap;
$sitemap->cfFiles = $cfFiles;
$sitemap->generatesitemapfile = $generatesitemapfile; 
$sitemap->SiteUrl = $SiteUrl; 
$sitemap->cfFolder = $cfFolder;
$sitemap->robotstxt = $robotstxt;
$sitemap->sitemapxml = $sitemapxml;
$sitemap->sitemapcsv = $sitemapcsv;
$sitemap->archivedsitemap = $archivedsitemap;
$sitemap->PyArchiveURI = $PyArchiveURI;
$sitemap->bdir = $baseDir;
$sitemaps = $sitemap->get();
/*
return [
  'sitemap' => [status, response]
  'files' => [filename, file, exists]
  ];
*/

}

}
$pagetitle = 'Whatsapp Chat '.(isset($nobf) ? '' : (isset($cf->name) && !empty($cf->name) ? ('with '.$cf->name) : ''));

?>
<!DOCTYPE html>

<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo $pagetitle;?></title>

<?php
/* opengraph */
print '    
<meta property="og:title" content="'.$pagetitle.'" />
<meta property="og:type" content="'.$ogcontenttype.'" />
<meta property="og:url" content="'.$currentURL.'" />
<meta property="og:image" content="'.$ogImage.'" />
';
?>
    
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

</head>

<body>
  
    <header class="brand">
      <!-- select -->
<select style="" class="form-control searchselect">
  
<?php 

foreach ($cfl as $SelectList){
$nextchatfilesList = (null !== ( $nextchatfilesList = next($cfl))) ? $nextchatfilesList : false;

print ($SelectList['bfc'] == 1 ? ('<optgroup label="Chats with '.$SelectList['dirname'].'">') : '');

print '
    <option '.$SelectList['selected'].' value="'.$SelectList['search'].'">Chats with '.$SelectList['name'].' '.$SelectList['bfc'].'</option>
  ';
  
print ($nextchatfilesList !== false  ? (($SelectList['dirname'] === $nextchatfilesList['dirname']) ? '' : '</optgroup>') : '</optgroup>');

}            
 ?>           
 </select>      
    </header>
    
    <main class='container h-100'>
<!-- no backupfile found -->
<?php 
/* may provide archive.org or other archive */
if ($cfFiles->gbfc < 1) {
?>
<div class="row">
<div class="col card">
cannot display chat files at this time - <a href="?" class="reloadpage">retry</a>
</div>
</div>
<?php
}
?>

<?php
/* start chat list if backupfile present */
if ($cfFiles->gbfc > 0){
?>
<section><h3 class='chat-title'>WhatsApp Chat with <?php print $cf->name; ?></h3>

      <div class='row p-1' id='whatsappimages spotlight-group'>
<?php
$counterfilearray = 0;
foreach ($filearray as $line) { 
print '<div class="ChatID" id="c'.$counterfilearray.'"></div>';              
$string = $line;

$pattern = '/(?P<time>.*?,+.*?)-(?P<sender>.*?):(?P<message>.*)/is';               
if (preg_match($pattern, $string, $matches)) {
$messagelinetype = 'chat';
$time = trim($matches['time']);
$sender = trim($matches['sender']);
$message = trim($matches['message']);
} else {
$pattern = '/(?P<time>.*?,.*?)-(?P<message>.*)/is';
if (preg_match($pattern, $string, $matches)) {
$messagelinetype = 'notification';
$time = trim($matches['time']);
$message = trim($matches['message']);
$sender = ''; /* notification */
}
}

if (!isset($messagelinetype)) {
$messagelinetype = 'unformatted';
$time = ''; /* unformatted */
$message = $string;
$sender = ''; /* unformatted */
}

if(isset($messagelinetype)){

$recipient = ($cf->groupchat ? true : ( (strtolower($sender) == $fileandrecipient->vrecipient) ? true : false));

$phug = new Phug\Renderer([
'globals' => [
'sender' => $sender,
'message' => $message,
'time' => $time,
'recipient' => $recipient,
'vrecipient' => $fileandrecipient->vrecipient,
'attachmentexists' => false,
'type' => $messagelinetype,
'groupchat' => $cf->groupchat,
'sendercolor' => getsendercolor($sender)
    ]
]);
\Phug\Component\ComponentExtension::enable($phug);

$phug->setOption(['php_token_handlers', T_VARIABLE], null);

$template = '';

if ($messagelinetype == 'unformatted'){
$template .= '
component unformattedchatline
div(class="row justify-content-center m-1")
 div(class="col-9 rounded alert justify-content-center")
  .chat #{$message}
';
}

/* if message contains attachment, find type & caption, display */
/* strpos require !== */
$attachmentneedle = '(file attached)';
$attachments = CFgetattachments(
  $message, 
  $attachmentneedle, 
  $cf->filepath, 
  $cf->dirpath
  );

if (isset($attachments->exists) && $attachments->exists == true) {
  /* image, pdf, video, voicenote*/
$fileext = $attachments->ext;

$phug->share([
'filenameandext' => $attachments->name,
'filecaption' => $attachments->caption,
'filepath' => $attachments->filepath,
'filesize' => getFilesize($attachments->filepath),
'fileext' => $attachments->ext,
'attachmentexists' => $attachments->exists,
'counterfilearray' => $counterfilearray
]);

if (in_array($fileext, array('jpg','jpeg','png','gif'))) {
/* photos */

$phug->share([
   'lfilepath' => baseURI($attachments->filepath)
]);

$template .= '
component attachment
 div(class="whatsappimagescontainer") 
  a(class="whatsappmedia glightbox3" href="$lfilepath" data-gallery="gallery1" data-width="100vw" data-height="auto" data-glightbox="title: $filecaption $filenameandext")
    img(class="object-fit-cover border rounded img-responsive" style="width:200px;" src="$filepath")
 ';

          
} elseif (in_array($fileext, array('mp4','avi','flv','3gp','mkv','mov'))) {
/* video */

$phug->share([
'videoURI' => videoPlayer(urlencode(baseURI($attachments->filepath))),
'videoPoster' => videoPlayerPoster($attachments->filepath)
]);

$template .= '
component attachment
 .videos
  .video-wrap
    .videoinlineposter
      a(class="whatsappmedia glightbox3" href="$videoURI" data-glightbox="title:$filecaption $filenameandext" data-preload="true" data-media="video" data-gallery="gallery1")
          .play-btn
          img(class="object-fit-cover placeholder border rounded img-responsive img-fluid" width="250px" height="auto" src="$videoPoster")
 ';

} elseif (in_array($fileext, array("pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "pages"))) {

$phug->share([
   'fileicon' => exttofileicon($attachments->ext),
   'docviewer' => docViewer(baseURI($attachments->filepath))
]);

$template .= '
component attachment
 .spotlight(indexnum="$counterfilearray" data-src="$docviewer")
  div(class="card shadow-none border bg-soft-whatsapp")
   .card-body
     div(class="avatar me-1")
      div(class="avatar-title rounded bg-soft-whatsapp text-primary")
        img.fileiconsvg( src="$fileicon")
     div 
      a.glightbox3(data-gallery="gallery1" data-height="80vh" data-width="90%" href="$docviewer" data-glightbox="title: $filecaption $filenameandext" data-preload="true")
         h5(class="font-size-15 mb-1" style="color: #000;") #{$filenameandext}
      span(class="font-size-13 text-muted") pages • #{$filesize} • #{$fileext} 
';


} elseif (in_array($fileext, array('opus','mp3'))) {

$phug->share([
   'audiotype' => ($attachments->ext == 'opus' ? 'audio/ogg' : ('audio/'.$attachments->ext))
]);

$template .= '
component attachment
 audio.whatsappaudio(id="audio{$counterfilearray}" controls preload="auto" playsinline)
  source(src="$filepath" type="$audiotype")
 ';
    
} else {
	/* offer download except .exe
	*/

$template .= '
component attachment
 div file ext- "#{$filenameandext}"-"#{$fileext}"
';

} 

/* end file attached */               
}

$template .= '
-
if $type == "notification"
 div(class="row justify-content-center m-1")
  div(class="col-9 rounded alert justify-content-center")
    div
     span.time #{$time}
    p.chat #{$message}
elseif $type == "chat"
  div(class=$recipient ? "justify-content-start" : "justify-content-end" class="row m-1")
    if $groupchat == true
     div(class="col bx bx-sm bxs-user-circle groupchat" style="color:$sendercolor;")
  
    .col-9
      div(class=$recipient ? "" : "message-right" class="col-auto message")
       div(class=$recipient ? "message-left chat message-left-contents" : "message-right-contents bg-soft-whatsapp" class="col rounded p-2")
         div(class=$recipient ? "message-left" : "bg-soft-whatsapp" class="col sender") #{$sender}
         div(class=$recipient ? "message-left" : "")
           if $groupchat == true
            div(class="col groupsender message-left" style="color:$sendercolor;")
             strong #{$sender}
            
           if $attachmentexists == true
            +attachment
           else
            div
             span.messageEl #{$message}
         span(class="col time" class=$recipient ? "message-left" : "bg-soft-whatsapp") #{$time}
else
 +unformattedchatline
';

// Facade way (with Phug)
print $phug->render($template);
                
}
                    
                } 
                 /* temporary increment point */

$counterfilearray++;
                /* end regex else */
            
            print '</div>
</section>';

}
/* end chat list */

            ?>
    </main>
    
<?php
print "<nav hidden>";
foreach ($chatLinks as $link) {
print '<a href="'.addurl($link).'">'.addurl($link).'</a> <br />';
}
print "</nav>";
?>

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
  <a href="<?php print $sitemap['rpath']; ?>" class="link-body-emphasis list-group-item list-group-item-action d-flex justify-content-between align-items-start" target="_blank">
    <div class="ms-2 me-auto">
      <div class="fw-bold">
      <?php print $sitemap['filename']; ?>
        </div>
<?php print isset($sitemap['stats']) ? getFilesize($sitemap['stats']['size'], 'human') : ''; ?>
    </div>
    <span class="badge bg-success rounded-pill">
<?php print isset($sitemap['stats']) ? date('M d, Y', $sitemap['stats']['mtime']) : ''; ?>
    </span>
  </a>  
<?php 
}
}
?>
  <span class="link-body-emphasis list-group-item list-group-item-action d-flex justify-content-between align-items-start">

    <div class="ms-2 me-auto">

      <div class="fw-bold">
      <?php print ($sitemaps->sitemap->status ? 'OK' : 'ERROR'); ?>
        </div>
<?php print $sitemaps->sitemap->response; ?>
    </div>
    <span class="badge bg-success rounded-pill">
<?php print date('M d, Y h:i:sa', time()); ?>
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
      <a href="sitemap.xml" data-bs-toggle="modal" data-bs-target="#sitemapModal">sitemap</a> | from <a target="_blank" href="https://github.com/itxshakil/Whatsapp-backup-Viewer">Shakil Alam on Github</a>
    </footer>

<script>

// call a function immediately after jQuery Tools is loaded
head(function() {
 
   // setup Tooltips
   $(".searchselect").select2(); 
   //select chat, redirect to selection
   $('.searchselect').on('select2:select', function (e) {
    window.location.href = "./"+$(this).val();
});
    //didplay chat id 
    $(".ChatID").on('contextmenu', function(){
      // show menu to copy chat or auto copy chat id then notify... 
    });
   
  const lightbox = GLightbox({
    touchNavigation: true,
    loop: true,
    autoplayVideos: true,
    selector: '.glightbox3',
    closeOnOutsideClick: true,
    preload: true
});

$('.reloadpage').on('click', function(){
  window.location.reload;
});

$(".messageEl").each(function(){
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

});


// load scripts by assigning a label for them
head.js(
{jquery: "https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"},
{bootstrapjs: "https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"},
{select2: "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"},
{glightbox: "https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/js/glightbox.min.js"},
{videojs:"https://cdn.jsdelivr.net/npm/video.js@8.3.0/dist/video.min.js"},
{anchorme:"https://cdn.jsdelivr.net/npm/anchorme@3.0.5/dist/browser/anchorme.min.js"}
);

		</script>
</body>
</html>