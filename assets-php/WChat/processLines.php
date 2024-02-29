<?php
namespace WChat;
class processLines {
public int $PaginationFrom;
public int $PaginationTo;

public function Process(
  \Psr\Container\ContainerInterface $c, 
  ){
/* yield */
  foreach ($c->call([$this, 'iterate']) as $iterable) {
      yield $iterable;
    }  
}

public function ProcessAndPrint(
  \Psr\Container\ContainerInterface $c, 
  ){
  /* print */
  foreach ($c->call([$this, 'iterate']) as $iterable) {
    print $iterable;
  }  
}

public function ProcessSearchAndPrint(
  \Psr\Container\ContainerInterface $c, 
  ){
  $Request = $c->get('WChat\Request');
  
  if ($Request->needle == null) return 'no search keyword entered.';
  
  /* lineFilterClass = ['classname  'method']
  the method is called outside this function, DI expects the method to accept autowired classes to access any desired method */
  $lineFilterClass = ['WChat\\processLines','lineFilter'];
  /* search and print */
  foreach ($c->call([$this, 'iterate'], ['lineFilterClass' => $lineFilterClass]) as $iterable) {

    print $iterable;
  }  
}

public function lineFilter(
  $line,
  \Psr\Container\ContainerInterface $c, 
    ) {
  $Request = $c->get('WChat\Request');
     /* extract before, including, and after keywords - WhatsApp presents 50chars */
     
      /* find search keywords */ 
  $needlepos = stripos($line, $Request->needle);
  
  if ($needlepos === false) {
    return null;
  } else {
    return $line;
  }
}

public function iterate(
  \Psr\Container\ContainerInterface $c, 
  $lineFilterClass = false,
  ){
$Config = $c->get('WChat\Config');
$from = $Config->PaginationFrom;
$to = $Config->PaginationTo;
foreach ($c->call(['WChat\\App', 'ChatFileGenerator']) as $line) {
  /* filter line through supplied function */
  if ($lineFilterClass) {
    try {
      $line = $c->call($lineFilterClass, ['line' => $line]);
    } catch (\Exception|\Throwable $e) {
      $line = $line;
    }
  }
  /* temporarily non empty lines will assume the number of preceeding empty lines, chatfiles should be fixed to contain no empty lines */
  if ($line == null) {
    /* increment before continue fixes line numberings */
    $from++; 
    continue;
  }
  
  yield from $c->call([$this, 'processline'], [
    $line, 
    $from,
    ]
    );
  $from++;
}

}

public function processline(
  $line, 
  int $counterfilearray,
  \Psr\Container\ContainerInterface $c, 
  ){ 
$Config = $c->get('WChat\Config');
$App = $c->get('WChat\App');
$db = $c->get('WChat\Database');
$string = $line;

$pattern = '/(?P<time>.*?,+.*?)-(?P<sender>.*?):(?P<message>.*)/is';               
if (preg_match($pattern, $string, $matches)) {
$messagelinetype = 'chat';
$DateTimeRaw = trim($matches['time']);
$sender = trim($matches['sender']);
$message = trim($matches['message']);

} else {
$pattern = '/(?P<time>.*?,.*?)-(?P<message>.*)/is';
if (preg_match($pattern, $string, $matches)) {
$messagelinetype = 'notification';
$DateTimeRaw = trim($matches['time']);
$message = trim($matches['message']);
$sender = ''; /* notification */
}
}

if (!isset($messagelinetype)) {
$messagelinetype = 'unformatted';
$DateTimeRaw = ''; /* unformatted */
$message = $string;
$sender = ''; /* unformatted */
}

if(isset($messagelinetype)){

$recipient = ($App->GroupChat ? true : ( (strtolower($sender) == strtolower($App->SelectedChatFile['name'])) ? true : false));
/* update sender in db, alt is manual input by user */
if (empty($App->SelectedChatFile['sender']) && strtolower($sender) !== strtolower($App->SelectedChatFile['name'])) {
$UpdateAppData = $db->InsertOrUpdate(
    'chatfiles',
    [
    'sender' => $sender
    ],
    [ 'id' => $App->SelectedChatFile['id']],
    'update'
  );
}

if ($recipient == true) {
  $Config->SuggestGroupChat = false;
}
try {
$PrevDateTime = $c->get('processLines.datetime');
if (count($PrevDateTime) < 2) Throw new \Exception('processLines.datetime[] must contain >= 2 values');
  $PrevDate = $PrevDateTime['date'];
  $PrevTime = $PrevDateTime['time'];
} catch (\Exception|\Throwable $e) {
[$PrevDate, $PrevTime] = [null, null,];
}
$DateTime = explode(',', $DateTimeRaw);  

try {
$Date = date_format(date_create($DateTime[0]), 'F j, Y');
} catch(\Exception|\Throwable $e) {
$Date = null;
}

$Time = isset($DateTime[1]) ? $DateTime[1] : null;

$c->set('processLines.datetime', [
  'date' => $Date, 
  'time' => $Time,
  ]);
  
$phug = new \Phug\Renderer([
'globals' => [
'sender' => $sender,
'message' => $message,
'datetimeraw' => $DateTimeRaw,
'date' => ($Date == $PrevDate ? null : $Date),
'time' => $Time,
'recipient' => $recipient,
'attachmentexists' => false,
'type' => $messagelinetype,
'groupchat' => $App->GroupChat,
'sendercolor' => $this->getsendercolor($sender),
'counterfilearray' => $counterfilearray
    ]
]);
\Phug\Component\ComponentExtension::enable($phug);

$phug->setOption(['php_token_handlers', T_VARIABLE], null);

$template = '';

if ($messagelinetype == 'unformatted'){
$template .= '
component unformattedchatline
  div(class="row justify-content-center m-1")
   div(class="col-9 rounded walert justify-content-center")
    .chat !{$message}
';
}

/* if message contains attachment, find type & caption, display */
/* strpos require !== */
$attachmentneedle = '(file attached)';
$attachments = $this->AttachmentHandler(
  $message, 
  $attachmentneedle, 
  $Config,
  $App,
  );

if (isset($attachments->exists) && $attachments->exists == true) {
  /* image, pdf, video, voicenote*/
$fileext = $attachments->ext;

$phug->share([
'filenameandext' => $attachments->name,
'filecaption' => $attachments->caption,
'filepath' => $attachments->filepath,
'urifilepath' => $attachments->urifilepath,
'filesize' => getFilesize($attachments->absfilepath),
'fileext' => $attachments->ext,
'attachmentexists' => $attachments->exists
]);

if (in_array($fileext, array('jpg','jpeg','png','gif'))) {
/* photos */

$template .= '
component attachment
 div(class="wa-ic") 
  a(class="wa-m g" href="$urifilepath" data-gallery="g" data-width="auto" data-height="auto" data-title="$filecaption" data-description="$filenameandext")
    img(class="object-fit-cover border rounded zi-r img-fluid" src="$urifilepath")
 ';

          
} elseif (in_array($fileext, array('mp4','avi','flv','3gp','mkv','mov'))) {
/* video */

$phug->share([
'videoURI' => $this->videoPlayer(urlencode($attachments->urifilepath,), $Config,),
'videoPoster' => 
  $this->videoPlayerPoster($attachments, $Config,),
]);

$template .= '
component attachment
 .zv
  .zvw
    .zvip
      a(class="wa-m g" href="$videoURI" data-glightbox="title:$filecaption $filenameandext" data-preload="true" data-media="video" data-gallery="g")
          .play-btn
          img(class="object-fit-cover placeholder border rounded zi-r img-fluid" width="250px" height="auto" src="$videoPoster")
 ';

} elseif (in_array($fileext, array("pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "pages"))) {

$phug->share([
   'fileicon' => $this->exttofileicon(
     $attachments->ext,
     $Config,
     ),
   'docviewer' => $this->docViewer(
     $attachments->urifilepath, 
     $Config,
     ),
]);

$template .= '
component attachment
 .spotlight(indexnum="$counterfilearray" data-src="$docviewer")
  div(class="card shadow-none border bg-wa")
   .card-body
     div(class="avatar me-1")
      div(class="avatar-title rounded bg-wa text-primary")
        img.zfis( src="$fileicon")
     div 
      a.g(data-gallery="g" data-height="80vh" data-width="90%" href="$docviewer" data-glightbox="title: $filecaption $filenameandext" data-preload="true")
         h5(class="font-size-15 mb-1" style="color: #000;") #{$filenameandext}
      span(class="font-size-13 text-muted") pages &bull; #{$filesize} &bull; #{$fileext}
';


} elseif (in_array($fileext, array('opus','mp3'))) {

$phug->share([
   'audiotype' => ($attachments->ext == 'opus' ? 'audio/ogg' : ('audio/'.$attachments->ext))
]);

$template .= '
component attachment
 audio.wa-a(id="audio{$counterfilearray}" controls preload="auto" preload="none" playsinline)
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
//col-auto position-relative start-50 translate-middle
$template .= '
-
div.cID(id="c{$counterfilearray}" cid="{$counterfilearray}")
  if $date != null
   div(class="row justify-content-center"): strong(class="col-auto zml chat rounded text-secondary p-1 dategroups") #{$date}
  if $type == "notification"
   div(class="row justify-content-center m-1")
    div(class="col-10 rounded walert justify-content-center text-center p-1")
      small.chat(class="justify-content-center") !{$message}
  elseif $type == "chat"
    div(class=$recipient ? "justify-content-start" : "justify-content-end" class="row m-1")
      if $groupchat == true
       div(class="col bx bx-sm bxs-user-circle gc" style="color:$sendercolor;")
    
      .col-10
        div(class=$recipient ? "" : "zmr" class="col-auto zm")
         div(class=$recipient ? "zml chat m-l-c" : "m-r-c bg-wa" class="col rounded p-2")
           div(class=$recipient ? "zml" : "bg-wa" class="col sender") #{$sender}
           div(class=$recipient ? "zml" : "")
             if $groupchat == true
              div(class="col gs zml" style="color:$sendercolor;")
               strong #{$sender}
              
             if $attachmentexists == true
              +attachment
             else
              div
               span.mEl !{$message}
           span(class="col time fw-light" class=$recipient ? "zml" : "bg-wa")
            span(class="" data-bs-toggle="tooltip" data-bs-title="Annotation" data-bs-placement="bottom")
             i(class="bx bxs-message-square-dots bx-tada-hover autohideicons annotation" style="color:;" data-bs-toggle="offcanvas" data-bs-target="#AnnotationsRight" aria-controls="AnnotationsRight")
            i(class="bx bx-link bx-tada-hover copycidlink autohideicons" type="copycidlink" cid="c{$counterfilearray}" style="color:;")
            span(class="datetimeraw col hidden") #{$datetimeraw}
            span(class="timeraw") #{$time}
  else
   +unformattedchatline
';

// instance way (with Phug)
yield $phug->render($template);
$phug = null;
$template = null;           
}
                    
}

public function AttachmentHandler(
  $message, 
  $attachmentneedle, 
  Config $Config,
  App $App,
  ) {
$attachments = (str_contains($message, $attachmentneedle) ? explode($attachmentneedle, $message) : false);
if (!$attachments) return 'error: no files attached';

$attachment = (isset($attachments[0]) ? trim($attachments[0]) : false );
if (!$attachment) return 'error: no files attached';

$ext = (isset($attachments[0]) ? trim(strtolower(pathinfo($attachments[0], PATHINFO_EXTENSION))) : '');
$caption = (isset($attachments[1]) ? $attachments[1] : '' );
/* make absolute for exists */
$absfilepath = \Symfony\Component\Filesystem\Path::join(
  $Config->baseDir, 
  $App->DirPath, 
  $attachment
  );
$filepath = \Symfony\Component\Filesystem\Path::join(
  $App->DirPath,
  $attachment
  );  
$urifilepath = \Symfony\Component\Filesystem\Path::join(
  $Config->SiteUrl,
  $App->DirPath,
  $attachment
  );  
$exists = ($attachment != '' && file_exists($absfilepath) ? true : false);
clearstatcache();
if (!$exists) return 'error: attached file was not found';

if ($ext == '') {
$extmime = (new \SoftCreatR\MimeDetector\MimeDetector)->setFile($absfilepath)->getFileType();
$ext = (isset($extmime["ext"]) ? (trim($extmime["ext"])) : false);
if (!$ext) return 'error: could not guess file extension';
$newfilepath = $absfilepath.$ext;  
if(!rename($absfilepath, $newfilepath)) return 'error: could not fix file extension';
/* modify this chat file */
if('success' !== $App->replaceinFile(
  $attachment, 
  $attachment.$ext, 
  $App->ChatFile,
  )) { 
    return 'error: could not update chat file with fixed extensionless files'; 
  }
$absfilepath = $newfilepath;
}

return (object) [
  "name" => $attachment,
  "ext" => $ext,
  "caption" => $caption,
  "exists" => $exists,
  "filepath" => $filepath,
  "urifilepath" => $urifilepath,
  "absfilepath" => $absfilepath
  ];
}

public function videoPlayer(
  $uri, 
  Config $Config,
  ) {
  return (
  ($Config->videoPlayerURL) 
  ? sprintf($Config->videoPlayerURL, trim($uri))
  : trim($uri)
  );
}

public function videoPlayerPoster(
  $attachments,
  Config $Config,
  ){
  /* filename.ext.png in same dir is used as poster or videoposter.png in /images/ is used. */
clearstatcache();  
return (
  file_exists($attachments->filepath.'.png') ? 
  ($attachments->urifilepath.'.png') :
    \pj(
      $Config->SiteUrl, 
      $Config->videoPlayerPoster
      )
  );
}

public function exttofileicon(
  $fileext, 
  \WChat\Config $Config,
  ) {
  return \pj(
    $Config->SiteUrl, 
    match ($fileext) {
    'pdf' => 'icons/pdf.svg',
    'doc', 'docx' => 'icons/microsoft-word.svg',
    'xls', 'xlsx' => 'icons/excel.svg',
    'ppt', 'pptx' => 'icons/powerpoint.svg',
    default => 'icons/file.svg'
  });
}

public function docViewer(
  $uri,
  Config $Config,
  ) {
  return (
  (isset($Config->docViewer))
  ?
  sprintf($Config->docViewer, trim($uri))
  : trim($uri)
  ); 
}

public function getsendercolor($sender){
return  ('#'.substr(md5($sender), 0, 6) );
}

}