<?php
namespace WChat;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;
class Init {
public $UpdateSitemap = false;
public object $Data;

  public function __construct(
    \Psr\Container\ContainerInterface $c,
    ) {
    
    $c->call([$this, 'Loader']);

    }
  
  public function Loader(
    \Psr\Container\ContainerInterface $c,
    ) {
    $Config = $c->get('WChat\Config');
    $Request = $c->get('WChat\Request');

    /* append request to config */
    $Config->PaginationFrom = (isset($Request->paginationfrom) ? $Request->paginationfrom : $Config->PaginationFrom);
    $Config->PaginationTo = ($Config->PaginationFrom + $Config->recordsperpage);
    
    /* set nonset requests to null */
    $Request->queryarg = (isset($Request->queryarg) ? $Request->queryarg : null);
    /* append config prior to CheckFileSystemModification */
    $AppendConfig = fn() => $c->call([$this, 'AppendConfig'], [
      'Table' => 'AppData', 
      'ColumnValuesArray' => ['id' => 1], ]);
   /* append config */ 
    $AppendConfig();
    
    $CheckFileSystemModification = $c->call([$this, 'CheckFileSystemModification']);
    $_SESSION['statusconsole'][] = $CheckFileSystemModification->response;
    
    if ($CheckFileSystemModification->status !== false) {
      $UpdateDBFromFileSystem = $c->call([$this, 'UpdateDBFromFileSystem']);
       if (!$UpdateDBFromFileSystem->status)
       {
        print $UpdateDBFromFileSystem->response;
        exit;
       }
       $this->UpdateSitemap = true;
      /* reappend updated config */
      $AppendConfig();
    }
    
    if ($Config->InitType == 'API') {
    $this->Data = $c->call([$this, 'API']);
    } else {
    $this->Data = $c->call([$this, 'Index']);
    }

  }
  
  public function API(
    \Psr\Container\ContainerInterface $c, 
    ){
      
  $Request = $c->get('WChat\Request');
  $db = $c->get('WChat\Database');
  
  $ChatFilesDataExecute = $db->SelectOne(
  'chatfiles',
  ['id' => $Request->queryarg]
  );
  $ChatFilesDataSelectType = 'one';
  $ChatFilesData = $ChatFilesDataExecute->status ? 
  \BenTools\IterableFunctions\iterable_to_array($ChatFilesDataExecute->response) 
  : 
  [];
  $ChatFilesDataIsEmpty = (count($ChatFilesData) > 0 ? 
  false : true
  );  
  /* isempty true, updatedbfromfilesystem, */
  
  $ChatFilesDataKeys = array_column(
                        $ChatFilesData, 
                        'id'
                        );
  $ChatFilesDataIdAsKeys = array_combine(
                  $ChatFilesDataKeys, 
                  $ChatFilesData
                  );
  
  return (object) [
  'Data' => $ChatFilesData,
  'DataIdAsKeys' => $ChatFilesDataIdAsKeys,
  'Count' => '',
  'IsEmpty' => $ChatFilesDataIsEmpty
  ];    
  }
  
  public function Index(
    \Psr\Container\ContainerInterface $c,
    ){
  
  $db = $c->get('WChat\Database');
  
  $ChatFilesDataExecute = $db->Select('chatfiles', '', ['dirname ASC', 'bfc ASC']);
  $ChatFilesDataSelectType = 'all';
  $ChatFilesData = $ChatFilesDataExecute->status ?
  \BenTools\IterableFunctions\iterable_to_array($ChatFilesDataExecute->response)
  :
  [];
  $ChatFilesDataIsEmpty = (count($ChatFilesData) > 0 ? 
  false : true
  );
  $ChatFilesDataKeys = array_column(
                      $ChatFilesData, 
                      'id'
                      );
  $ChatFilesDataIdAsKeys = array_combine(
            $ChatFilesDataKeys, 
            $ChatFilesData
            );  
  
  return (object) [
  'Data' => $ChatFilesData,
  'DataIdAsKeys' => $ChatFilesDataIdAsKeys,
  'Count' => '',
  'IsEmpty' => $ChatFilesDataIsEmpty
  ];                         
  }

  public function AppendConfig(
    $Table,
    $ColumnValuesArray,
    Database $db,
    Config $Config,
    ){
  /* load app data */
  $AppDataExecute = $db->SelectOne(
                 $Table,
                 $ColumnValuesArray,
                    );
  $AppDatas = ($AppDataExecute->status ?
  \BenTools\IterableFunctions\iterable_to_array($AppDataExecute->response) : []);
  $AppData = (object) (count($AppDatas) > 0 ? $AppDatas[0]  : []);
  /*
  $AppDataNotEmpty = (count($AppData) > 0 ? 
  true 
  : 
  false
  );
  */
  /* add AppData to Config object*/
  $Config->{$Table} = $AppData;
  
  return $Config;   
  }
  
  public function CheckFileSystemModification(
    Config $Config,
    ){
  clearstatcache(true, $Config->cfFolder);
  $CurrentMTime = filemtime($Config->cfFolder);
  
  $PrevMTime = ($Config->AppData->mtimeorhash ?? false ) ? $Config->AppData->mtimeorhash : false;
  
  if ($CurrentMTime == $PrevMTime){
  return (object) [
      'status' => false,
      'response' => 'FileSystem is not modified:'.$PrevMTime.'-'.$CurrentMTime,
      ];   
    
  } else {
  return (object) [
      'status' => true,
      'response' => 'FileSystem is modified:'.$PrevMTime.'-'.$CurrentMTime,
      'PrevMTime' => $PrevMTime,
      'CurrentMTime' => $CurrentMTime,
      ];     
    }   
  }

public function UpdateDBFromFileSystem(
  \Psr\Container\ContainerInterface $c,
  ){
$Config = $c->get('WChat\Config');
$Request = $c->get('WChat\Request'); 
$db = $c->get('WChat\Database');

$cfFolders = $this->CFgetfolders($Config);
if (count($cfFolders->chatFolders) < 1 ) {
return (object) [
      'status' => false,
      'response' => 'No folders in Chat Folder'
      ];   
}

$cfFiles = $this->CFgetfiles(
  $cfFolders->chatFolders, 
  $Config,
  );

  if (count($cfFiles->cfl) < 1) {
  return (object) [
      'status' => false,
      'response' => 'Cannot fetch chat files or no chat files'
      ];   
  }
  
if ($Config->InitType == 'API') {
/* API */
$PrevArray = ($c->call([$this, 'API']))->Data;
} else {
/* Index */ 
$PrevArray = ($c->call([$this, 'Index']))->Data;
}

$NewArray = $cfFiles->cfl;

$MergeDropAndUpdateDb = $c->call([$this, 'MergeDropAndUpdateDb'], [
  $PrevArray, 
  $NewArray,
  ]);

if (!$MergeDropAndUpdateDb->status) {
  return (object) [
      'status' => false,
      'response' => 'Cannot update DB from Filesystem - '.$MergeDropAndUpdateDb->response
      ]; 
} 
/* update Appdata folder with conversation hash */
try {
clearstatcache();
$cfFoldermtimeorhash = filemtime($Config->cfFolder);
$UpdateAppData = $db->InsertOrUpdate(
    'AppData',
    [
    'mtimeorhash' => $cfFoldermtimeorhash,
    'foldername' => $Config->cfFolder
    ],
    [ 'foldername' => $Config->cfFolder ]
  );
  
  if (!$UpdateAppData->status) {
    Throw new \Exception ($UpdateAppData->response);
  }
} catch (\Exception|\Throwable $e) {
  return (object) [
        'status' => false,
        'response' => 'Insert or Update failed:'.$e->getMessage()
        ]; 
  }
/* reinit Init\index */  
return (object) [
      'status' => true,
      'response' => 'DB updated from Filesystem'
      ]; 
  } 

public function MergeDropAndUpdateDb(
  $Prev, 
  $new,
  \Psr\Container\ContainerInterface $c,
  ){
$Config = $c->get('WChat\Config');
$db = $c->get('WChat\Database');
$urlList = null;

try {
$Columnfilepath = array_column($new, 'filepath');

$db->DeleteWhereNot(
  'chatfiles',
  'filepath', 
  ...$Columnfilepath
  );

} catch (\Exception|\Throwable $e) {
return (object) [
      'status' => true,
      'response' => 'Cannot delete redundant Chatfiles from DB'.$e->getMessage()
      ]; 
}

foreach ($new as $cl){
clearstatcache();
$mtimeorhash = filemtime($cl['filepath']);
$bfc = $cl['bfc'];
$filepath = $cl['filepath'];
$name = $cl['name'];
$url = \pj( 
  $Config->SiteUrl,
  $cl['search'], 
  );
$search = $cl['search'];
$filename = $cl['filename'];
$dirpath = $cl['dirpath'];
$dirname = $cl['dirname'];
$groupchat = $cl['groupchat'];
$sync = 1;
$archivedurl = '';
$annotation = '';
//$synctime = time();

try {
$PrevRecordExecute = $db->SelectOne(
    'chatfiles',
    [ 'filepath' => $filepath ]
                        );
$PrevRecords = ($PrevRecordExecute->status ? \BenTools\IterableFunctions\iterable_to_array($PrevRecordExecute->response) : []);
$PrevRecord = count($PrevRecords) > 0 ? $PrevRecords[0] : [];
  
  if ( count($PrevRecords) > 0 ) {
  if ( $PrevRecord['mtimeorhash'] == $mtimeorhash 
  && $PrevRecord['url'] == $url
     ) {
  /* no changes, dont compute archivedurl, sync, linecount , annotation */
  $archivedurl = $PrevRecord['archivedurl']; 
  $sync = $PrevRecord['sync']; 
  $archivedurl = $PrevRecord['archivedurl'];
  $linescount = $PrevRecord['linescount'];
  $annotation = $PrevRecord['annotation'];
  } else {
  /* changes, compute line count 
  compile url list for mailing to archive.org */
  $linescount = $this->LinesCount($cl['filepath']);  
  $urlList .= $url.PHP_EOL;
  $annotation = $PrevRecord['annotation'];
  }
 } else {
  /* if no previous records, compute these , intensive operations */
  $linescount = $this->LinesCount($cl['filepath']);  
  $urlList .= $url.PHP_EOL;
 }
} catch (\Exception|\Throwable $e) {
  
return (object) [
      'status' => false,
      'response' => 'Merge chatfiles records failed:'.$e->getMessage()
      ];   
}
try {
    $RawData = [
      'search' => $search,
      ];
    $data = [];
    $filters = [];
    foreach ($RawData as $RawKey => $RawVal) {
      $data[$RawKey] = $RawVal;
      $filters[$RawKey] = 'trim|empty_string_to_null|strip_tags|escape';
    }
    $SanitizeRawData = new \Elegant\Sanitizer\Sanitizer($data, $filters);
    $SanitizeRawData = $SanitizeRawData->sanitize();
    $RawData = $SanitizeRawData;
/* temporary assignment of chatfiles to users_id (1)*/
$users_id = 1;

  $InsertOrUpdate = $db->InsertOrUpdate(
    'chatfiles',
    [
    'bfc' => $bfc,
    'filename' => $filename, 
    'dirpath' => $dirpath, 
    'dirname' => $dirname, 
    'search' => $RawData['search'],
    'groupchat' => $groupchat,
    'name' => $name,
    'sync' => $sync,
    'filepath' => $filepath,
    'url' => $url,
    'mtimeorhash' => $mtimeorhash,
    'archivedurl' => $archivedurl,
    'linescount' => $linescount,
    'users_id' => $users_id,
    'annotation' => $annotation,
    
    ],
    [ 'filepath' => $filepath ]
  );
  
  if (!$InsertOrUpdate->status) {
    Throw new \Exception ($InsertOrUpdate->response);
  }
} catch (\Exception|\Throwable $e) {
  return (object) [
        'status' => false,
        'response' => 'Insert or Update failed:'.$e->getMessage()
        ]; 
  }
}

/* mail url list */
try {
$MailUrlList = $c->call([$this, 'MailUrlList'], [
      $urlList, ]);
$MailUrlListResponse = $MailUrlList->response;
/* add to statusconsole */
$_SESSION['statusconsole'][] = 'Archive new links status: '.
$MailUrlListResponse;
} catch (\Exception|\Throwable $e) {  
  
}
/* feedback on mail to sitemap status */

return (object) [
      'status' => true,
      'response' => $InsertOrUpdate,
      'MailUrlListResponse' => $MailUrlListResponse,
      ]; 
}

public function MailUrlList(
  $urlList,
  \Psr\Container\ContainerInterface $c,
  ){
$Config = $c->get('WChat\Config');
try {
//Create a new PHPMailer instance
$mail = new PHPMailer();
if ($Config->Mail->Type == 'sendmail') {
  //sendmail,mail
} elseif ($Config->Mail->Type == 'smtp') {
  //smtp
}
//Set who the message is to be sent from
$mail->setFrom(
  $Config->Mail->FromEmail,
  $Config->Mail->FromName,
  );
//add each address in $Config->Mail->ArchiveMailAddresses
foreach ($Config->ArchiveMailAddresses as $Address) {
$mail->addAddress($Address);
}
//subject
$mail->Subject = $Config->SiteUrl.' - Links';

//cc and bcc
if ($Config->Mail->cc != null) $mail->addCC($Config->Mail->cc);
if ($Config->Mail->bcc != null) $mail->addBCC($Config->Mail->bcc);

//Content
$mail->isHTML(true);                  
//Set email format to HTML
$mail->Body    = $urlList;
$mail->AltBody = $urlList;

//send the message, check for errors
/* hopefully mail is sent as a batch, iteration of each address is a reliable approach*/
if (!$mail->send()) {
  $_SESSION['statusconsole'][] = 'Mailer Error: '.$mail->ErrorInfo;
  Throw new \Exception ('Mailer Error: ' . $mail->ErrorInfo);
} else {
  $response = 'New links sent to archive.org'.PHP_EOL.
              $urlList.PHP_EOL;
  $_SESSION['statusconsole'][] = $response;            
}  

} catch (\Exception|\Throwable $e) {  
return (object) [
      'status' => false,
      'response' => $e->getMessage(),
      ];   
}
return (object) [
      'status' => true,
      'response' => $response,
      ];   
}

public function CFgetfiles(
  $cfFolders, 
  Config $Config,
  ) {

$gbfc = 0;
$cfl = [];
if (count($cfFolders) < 1) return 'error: no folders in base dir';
foreach ($cfFolders as $dir) {
$readdir = glob($dir.'/'.'[wW][hatsapp]*[.txt]');
if (!is_array($readdir)) continue;
/* naming conflict resolution is filename.{int}.ext , 
.ext, .1.ext = .1.ext,.ext
natsort(.ext,.1.ext) = .ext, .1.ext
*/
natcasesort($readdir);
$bfc = 0;
foreach ($readdir as $f) {
 /* match file by pattern */
/* list whatsapp chat backup files using regex */
if (preg_match(
  $Config->cfFilespattern, 
  $f, 
  $matches
  )) {
/* bfc is for aesthtics,not count or pointing */
$bfc++;

$dirname = basename($dir);
$filename = basename($f);
$cfl[$gbfc]['bfc'] = $bfc;
$cfl[$gbfc]['name']= trim($matches["name"]);
$cfl[$gbfc]['filename'] = $filename;
$cfl[$gbfc]['filepath'] = $f;
$cfl[$gbfc]['dirpath'] = $dir.'/';
$cfl[$gbfc]['dirname'] = $dirname;
$cfl[$gbfc]['search'] = strtolower(str_replace('+', '', str_replace(' ', '', $dirname.'-'.$cfl[$gbfc]['name'])).($bfc > 1 ? $bfc : ''));
$cfl[$gbfc]['groupchat'] = (str_contains($matches['name'], 'group') ? true : false);
/* list select options of folder and chat files - supports multiple chat files in one dir, media files hopefully wont conflict, idk the chances but... ??? */

$gbfc++;
}
/* end whatsapp backup files */
}

}


return (object) [
  'cfl' => $cfl, 
  'gbfc' => $gbfc
  ];
/* end CFgetfiles */
  }

public function CFgetfolders(Config $Config) {
  /* can filter dirs with iteration of glob */
  if (!isset($Config->cfFolder)) return 'error: empty path';
  if (!is_dir($Config->cfFolder)) return 'error: folder is not a dir or empty path';
  $cfFolders = glob($Config->cfFolder.'/*', GLOB_ONLYDIR);
  if (!$cfFolders || count($cfFolders) < 1) return 'error: folder not found or no dirs in base dir';
  
  return (object) [
    'chatFolders' => $cfFolders,
    'chatFolder' => $Config->cfFolder
    ];
/* end CFgetfolders */
  }
  
public function LinesCount($filepath){
  $file = new \SplFileObject($filepath);
  $file->seek(100000);
  return $file->key();
}

public function addurl(
  $path, 
  \WChat\Config $Config,
  ){
  return \pj(
    $Config->SiteUrl,
    $path,
    );
}

}