<?php
/* 
Initializations of classes, functions happen at the end of the functions and classes definitions. composer requires file base use keyword for new instances.

initializing the functions or classes before definitions is not feasible

*/

use SoftCreatR\MimeDetector\MimeDetector;
use SoftCreatR\MimeDetector\MimeDetectorException;
use samdark\sitemap\Sitemap;
use samdark\sitemap\Index;
use Curl\Curl;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use function BenTools\IterableFunctions\iterable_to_array as iter_to_array;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Insert;
use Laminas\Config\Config as Config;
use Elegant\Sanitizer\Sanitizer;

function ntfy($message){
  $curl = new Curl;
  $curl->post('https://ntfy.sh/hsdicbr', [
    'message' => $message
    ]);
}

function pj($p1, $p2){
  return Path::join($p1, $p2);
}

function RequestIssetNotNull(...$request) {
  foreach ($request as $r) {
    settype($r, 'string');
   if (!isset($_REQUEST[$r])) return false;
   if ($_REQUEST[$r] == '') return false; 
  }
  return true;
}

function SessionIssetNotNull(...$session) {
  foreach ($session as $s) {
    settype($s, 'string');
   if (!isset($_SESSION[$s])) return false;
   if ($_SESSION[$s] == '') return false; 
  }
  return true;
}

function mimedetector($filepath){
  $mimedetector = new MimeDetector;
  return $mimedetector->setFile($filepath)->getFileType();
}

function videoPlayer($uri) {
  return (
  ($GLOBALS["videoPlayerURL"]) 
  ? sprintf($GLOBALS["videoPlayerURL"], trim($uri))
  : trim($uri)
  );
}

function videoPlayerPoster($uri){
  /* filename.ext.png in same dir is used as poster or videoposter.png in /images/ is used. */
clearstatcache();  
return (
  file_exists($uri.'.png') ? ($uri.'.png') : $GLOBALS["videoPlayerPoster"]
  );
}

function exttofileicon($fileext) {
  return match ($fileext) {

    'pdf' => 'icons/pdf.svg',
    'doc', 'docx' => 'icons/microsoft-word.svg',
    'xls', 'xlsx' => 'icons/excel.svg',
    'ppt', 'pptx' => 'icons/powerpoint.svg',
    default => 'icons/file.svg'
  };
}

function docViewer($uri) {
  return (
  (isset($GLOBALS["docViewer"]))
  ?
  sprintf($GLOBALS["docViewer"], trim($uri))
  : trim($uri)
  ); 
}

function addurl(
  $path, 
  Config $Config,
  ){
  return (
    $Config->SiteUrl
  .'/'
  .Path::makeRelative($path, $Config->baseDir)
  );
}


function nonull($var) {
  return ((isset($var)) ? $var : false );
}

function baseURI($path){
  /* returns same url if no whatsappchat URI set */
  return (isset($GLOBALS["whatsappchatsURI"]) ? ($GLOBALS["whatsappchatsURI"].'/'.$path) : $path );
}

function setrandrecipient($array) {
  
}

function getsendercolor($sender){
return  ('#'.substr(md5($sender), 0, 6) );
}

function getFilesize($filesize, $mode = '')
{
  if ($mode == '') {
    $filesize = filesize ($filesize);
  }
    if ($filesize > 1024) {
        $filesize = ($filesize / 1024);
        if ($filesize > 1024) {
            $filesize = ($filesize / 1024);
            if ($filesize > 1024) {
                $filesize = ($filesize / 1024);
                $filesize = round($filesize, 1);
                return $filesize . " gb";
            } else {
                $filesize = round($filesize, 1);
                return $filesize . " mb";
            }
        } else {
            $filesize = round($filesize, 1);
            return $filesize . " kb";
        }
    } else {
        $filesize = round($filesize, 1);
        return $filesize . " bytes";
    }
}
function ras($needle, $haystack) {
  /* recursive array search */
  if (!is_array($haystack)) return false;
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if (is_array($value)) {
         $value = array_map('strtolower', $value);
        } elseif(is_string($value)) { $value = strtolower($value); 
        }
        if(strtolower($needle)==$value || (is_array($value) && ras($needle,$value) !== false)) {
            return $current_key;
        }
    }
    return false;
}

Class Request {

private $Data;
private $Writeable;
  
  public function __construct(
    $Requests,
    $Writeable = false,
    ){
  $data = [];
  $filters = [];
  $this->Writeable = $Writeable;
  foreach ($Requests as $Request => $Val) {
  $data[$Request] = $Val;
  $filters[$Request] = 'trim|empty_string_to_null|strip_tags|escape';
  }

$RequestData = new Sanitizer($data, $filters);
$RequestData = $RequestData->sanitize();

$this->Data = $RequestData;

  }
  
    public function __get($PropertyName){
    try {
    return $this->Data[$PropertyName];  
    } catch (\Exception|\Throwable $e) {
    Throw new Exception($PropertyName.' is not in $_REQUEST'); 
    }
  }
  
  public function __set($PropertyName, $Val) {
  if (!$this->Writeable) {
    Throw new Exception($PropertyName.' is not writeable. Pass true as the second parameter in '.get_class($this).' to write temporarily.'); 
    return false;
  }
  
  $this->Data[$PropertyName] = $Val;  
  }
  
  public function __unset($PropertyName){
    try {
    unset($this->Data[$PropertyName]);
    } catch(\Exception|\Throwable $e) {
    
    }
  }
  
  public function __debugInfo(){
    return $this->Data;
  }
  
  public function get(
    $PropertyName, 
    $DefaultValue = false,
    ) {
  try {
    return $this->Data[$PropertyName];  
    } catch (\Exception|\Throwable $e) {
    if (func_num_args() == 2){
    return $DefaultValue;
    } else {
    Throw new Exception($PropertyName.' is not in $_REQUEST'); 
    }
    }    
  }
  
}

class Init {
  
public $Data;
  
  public function Loader(
    Config $Config,
    Database $db,
    Request $Request,
    ) {
    $CheckFileSystemModification = $this->CheckFileSystemModification($Config);
    if ($CheckFileSystemModification->status == true) {
   $UpdateDBFromFileSystem = $this->UpdateDBFromFileSystem(
    $Config,
    $db,
    $Request,
    );  
    }
    
    $this->AppendConfig(
    'AppData',
    ['id' => 1],
    $db,
    $Config,
    );
    
    if ($Config->InitType == 'API') {
      $this->Data = $this->API(
        $Request, 
        $db,
        );
    } else {
    $this->Data = $this->Index($db);
    }

  }
  
  public function API(
    Request $Request,
    Database $db,
    ){
  $ChatFilesDataExecute = $db->SelectOne(
  'chatfiles',
  ['id' => $Request->queryarg]
  );
  $ChatFilesDataSelectType = 'one';
  $ChatFilesData = $ChatFilesDataExecute->status ? 
  iter_to_array($ChatFilesDataExecute->response) 
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
  
  public function Index(Database $db){
  $ChatFilesDataExecute = $db->Select('chatfiles');
  $ChatFilesDataSelectType = 'all';
  $ChatFilesData = $ChatFilesDataExecute->status ?
  iter_to_array($ChatFilesDataExecute->response)
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
  iter_to_array($AppDataExecute->response) : []);
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
    Config $Config
    ){
  clearstatcache();
  $CurrentMTime = filemtime($Config->cfFolder);
  $PrevMTime = ($Config->AppData->mtimeorhash ?? false ) ? $Config->AppData->mtimeorhash : false;
  
  if ($CurrentMTime == $PrevMTime){
  return (object) [
      'status' => false,
      'response' => 'FileSystem is not modified'
      ];   
  } else {
  return (object) [
      'status' => true,
      'response' => 'FileSystem is modified'
      ];     
    }   
  }

  public function UpdateDBFromFileSystem(
  Config $Config,
  Database $db,
  Request $Request,
  ){
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
$PrevArray = $this->API(
  $Request, 
  $db,
  )->Data;
} else {
/* Index */ 
$PrevArray = $this->Index($db)->Data;
}

$NewArray = $cfFiles->cfl;
$MergeDropAndUpdateDb = $this->MergeDropAndUpdateDb(
              $PrevArray, 
              $NewArray,
              $Config,
              $db,
              );

if (!$MergeDropAndUpdateDb->status) {
  return (object) [
      'status' => false,
      'response' => 'Cannot update DB from Filesystem'.$MergeDropAndUpdateDb->response
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
    Throw new Exception ($UpdateAppData->response);
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
  Config $Config,
  Database $db,
  ){

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
$vrecipient = $cl['vrecipient'];
$name = $cl['name'];
$url = addurl( 
  $cl['search'], 
  $Config,
  );
$search = $cl['search'];
$filename = $cl['filename'];
$dirpath = $cl['dirpath'];
$dirname = $cl['dirname'];
$groupchat = $cl['groupchat'];
$sync = 1;
$archivedurl = '';
//$synctime = time();

try {
$PrevRecordExecute = $db->SelectOne(
    'chatfiles',
    [ 'filepath' => $filepath ]
                        );
$PrevRecords = ($PrevRecordExecute->status ? iter_to_array($PrevRecordExecute->response) : []);
$PrevRecord = count($PrevRecords) > 0 ? $PrevRecords[0] : [];
  if ( count($PrevRecords) > 0 ) {
  if ( $PrevRecord['mtimeorhash'] == $mtimeorhash 
  && $PrevRecord['url'] == $url
     ) {
  $archivedurl = $PrevRecord['archivedurl']; 
  $sync = $PrevRecord['sync']; 
  $archivedurl = $PrevRecord['archivedurl'];
  $vrecipient = $PrevRecord['vrecipient'];
  }
 }
} catch (\Exception|\Throwable $e) {
  
return (object) [
      'status' => false,
      'response' => 'Merge chatfiles records failed:'.$e->getMessage()
      ];   
}
try {
$InsertOrUpdate = $db->InsertOrUpdate(
    'chatfiles',
    [
    'bfc' => $bfc,
    'filename' => $filename, 
    'dirpath' => $dirpath, 
    'dirname' => $dirname, 
    'search' => $search,
    'groupchat' => $groupchat,
    'vrecipient' => $vrecipient,
    'name' => $name,
    'sync' => $sync,
    'filepath' => $filepath,
    'url' => $url,
    'mtimeorhash' => $mtimeorhash,
    'archivedurl' => $archivedurl
    ],
    [ 'filepath' => $filepath ]
  );
  
  if (!$InsertOrUpdate->status) {
    Throw new Exception ($InsertOrUpdate->response);
  }
} catch (\Exception|\Throwable $e) {
  return (object) [
        'status' => false,
        'response' => 'Insert or Update failed:'.$e->getMessage()
        ]; 
  }
}

return (object) [
      'status' => true,
      'response' => $InsertOrUpdate
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
$cfl[$gbfc]['vrecipient'] = null;
$cfl[$gbfc]['name']= trim($matches["name"]);
$cfl[$gbfc]['filename'] = $filename;
$cfl[$gbfc]['filepath'] = $f;
$cfl[$gbfc]['dirpath'] = $dir.'/';
$cfl[$gbfc]['dirname'] = $dirname;
$cfl[$gbfc]['search'] = str_replace('+', '', str_replace(' ', '', $dirname.'-'.$cfl[$gbfc]['name'])).(($bfc < 1) ?: $bfc);
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

}

class App {
  
public $ChatFile; 
public $DirPath;
public $GroupChat;
public $Selected;
public $SelectedId;
public $NoSelected = true;
public $CheckLegacy = false;
public $VerifiedRecipient;
public $Name;
public $baseDir;
public bool $eof = false;

public function __construct() {

}

public function SetChatFile(
  Config $Config,
  Request $Request,
  Init $Init,
  App $App,
  Database $db,
  ) {
/* SetChatFile on index uses $_GET/backupfile , on api uses _request/queryarg - queryarg is RecordId, assets-php/sqlite uses queryarg to selectone/select, latter if $api, if not found, ChatFileDataNotEmpty = false , meaning empty  */

if ($Request->queryarg != null) {
/* recursive array search case , ras for search field */
$ras = ras($Request->queryarg, array_column($Init->Data->Data, 'search'));
/* ras for id field if not found */
$ras = ($ras == null ? 
ras($Request->queryarg, array_column($Init->Data->Data, 'id')) : $ras );
  (($ras === false) ? ([
    $this->CheckLegacy, 
    $this->NoSelected, 
    $this->Selected
    ] = [
    true, 
    true, 
    0
    ]) : ([
    $this->NoSelected, 
    $this->Selected
    ] = [
    false, 
    $ras
    ]) );

/* legacy url */
(!$this->CheckLegacy ?: $this->CheckLegacyChatFileQuery($Init, $Request)); 
} else {
/* ChatFilesData array index, default to first item in array, 0 */ 
$this->Selected = 0;
}

$SelectedChatFile = $Init->Data->Data[$this->Selected];
$this->SelectedId = $SelectedChatFile['id'];
$this->ChatFile = Path::join(
  $Config->baseDir,
  $SelectedChatFile['filepath']
  );
$this->Name = $SelectedChatFile['name'];
$this->DirPath = $SelectedChatFile['dirpath'];
$this->GroupChat = $SelectedChatFile['groupchat'];

if ($SelectedChatFile['vrecipient'] != null) {
  $this->VerifiedRecipient = $SelectedChatFile['vrecipient'];
} else {
 var_dump( $this->SetVerifiedRecipient(
  $Config,
  $Init,
  $db,
  )
  );
}

}

public function CheckLegacyChatFileQuery(
  Init $Init,
  Request $Request,
  ){
   $query = str_replace('_', '', $Request->queryarg);
    $cflcsearch = array_map(
    function($x) {
      return strtolower(
        str_replace(' ', '', $x)
        );
    }, array_column($Init->Data->Data, 'dirname'));
    $ras = ras($query, $cflcsearch);
    
    (($ras === false) ? ([
    $this->NoSelected, 
    $this->Selected
    ] = [
    true, 
    0
    ]) : ([
    $this->NoSelected, 
    $this->Selected
    ] = [
    false, 
    $ras
    ]) );

}

public function SetVerifiedRecipient(
  Config $Config,
  Init $Init,
  Database $db,
  ){

$recipient = trim(strtolower($this->Name)); 
$identities = [];   
$i = 0;
$cfselectedfilegenerator = $this->ChatFileGeneratorRecipient($Config);
foreach ($cfselectedfilegenerator as $filearray) {
$pattern = '/(?P<time>.*?,+.*?)-(?P<sender>.*?):(?P<message>.*)/is';
if (preg_match($pattern, $filearray, $matches)) {
  $sender = strtolower(trim($matches["sender"]));
  if ($sender != '') {
   if ($sender == $recipient) {
    $vrecipient = $sender;
    break;
  } else {
    /* levenshtein based */
     $plev = (isset($lev) ? $lev : 'notset');
     $lev = levenshtein($recipient, $sender);

     if ($plev == 'notset') {
     $vrecipient = $sender;
     } else {
     $vrecipient = ($plev > $lev ? $sender : $vrecipient);
     }
    /* end levenshtein guess */
  }
  /* end $identities similarity check */
}
}
$i++;
}

try {
$InsertOrUpdate = $db->InsertOrUpdate(
    'chatfiles',
    [
    'vrecipient' => $vrecipient,
    ],
    [ 'filepath' => $this->ChatFile ]
  );
  
  if (!$InsertOrUpdate->status) {
    Throw new Exception ($InsertOrUpdate->response);
  }
} catch (\Exception|\Throwable $e) {
  return (object) [
        'status' => false,
        'response' => 'Insert or Update failed:'.$e->getMessage()
        ]; 
  }

$this->VerifiedRecipient = (isset($vrecipient) ? $vrecipient : false);

  }

public function ChatFileGenerator(
 Config $Config,
  ){
$sfd = new SplFileObject($this->ChatFile);
if (!$sfd) return 'error: could not open chat file';

$filearray = [];
$holdbuffer = null;
$this->eof = false;
$TerminationType = 'done';
/* literal newline, alt is htmlspecialchars_decode of phug render, allowing br, to render as html, $message in pug is currently escaped, alt is rendering as html instead of escape plaintext */
$NewLine = '
'; 
$pattern = '/[0-3]?[0-9]\/[0-3]?[0-9]\/(?:[0-9]{2})?[0-9]{2},/';
$from = $Config->PaginationFrom;
$to = $oto = ($Config->PaginationTo == 0 ? ($from + $Config->recordsperpage) : $Config->PaginationTo );
$i = $from;
$sfd->seek($i);
if ($sfd->eof()) {
  /* eof on non existent pagination?, termination here or at end */
$this->eof = true;  
$TerminationType = 'eof';
}
do {
  $buffer = $sfd->current();
   /* whatsapp export lists lines without date string given newline is the delimiter and becomes difficult to determine if line is chat, notification or ....
set file array default key to null, regex if date string.solves newline, of chat continuation problem by appending unidentified lines to previous line */
  $sfd->next();
   if(preg_match($pattern, $buffer, $matches)) {
   if (preg_match($pattern, $sfd->current(), $matches)) {
   yield $buffer;  
   $holdbuffer = null;
   } else {
   $holdbuffer .= ($holdbuffer != null ?
   $NewLine.$buffer : $buffer);
   }
   /*
   if match, check next, if match, yield
   if not match, hold, continue?, if not match, bind prev, check next, if match, yield, else , hold, continue
   */
   } else {
   	/* append assumed chat continuation to previous array, \n or \r\n considered. */
   if (preg_match($pattern, $sfd->current(), $matches)) {
   $holdbuffer .= ($holdbuffer != null ?
   $NewLine.$buffer : $buffer);
   yield $holdbuffer;  
   $holdbuffer = null;
   } else {
   $holdbuffer .= ($holdbuffer != null ?
   $NewLine.$buffer : $buffer);
     }
   }
  if ($sfd->eof()) {
     /* end of file, yield holdbuffer containing all unidentified buffer  */
     $this->eof = true;
     $TerminationType = 'eof';
     if ($holdbuffer != null) {
       yield $holdbuffer;
     $holdbuffer = null;
     }
     break;
   }
if ($holdbuffer != null) {
/* tolerant termination to next match */
$to++;
}
$i++;
} while ($i < $to && !$this->eof);

if ($this->eof) {
$Config->NPaginationFrom = 0;
$Config->NPaginationTo = 0;
} else {
$Config->NPaginationFrom = $to;
$Config->NPaginationTo = ($to + $Config->recordsperpage);
}

return (object) [
  'PaginationFrom' => $from,
  'PaginationTo' => $oto,
  'TolerantPaginationTo' => $to,
  'TerminationType' => $TerminationType
  ];
}

/* dedicated recipient identifier */
public function ChatFileGeneratorRecipient(
  Config $Config,
  ){
$sfd = new SplFileObject($this->ChatFile);
if (!$sfd) return 'error: could not open chat file';
$pattern = '/[0-3]?[0-9]\/[0-3]?[0-9]\/(?:[0-9]{2})?[0-9]{2},/';
$from = $Config->PaginationFrom;
$to = ($Config->PaginationTo == 0 ? ($from + $Config->recordsperpage) : $Config->PaginationTo );
$i = $from;
foreach ($sfd as $line)
{
  /* goto start line in pagination arg */
  $buffer = $sfd->current();
   if (!$sfd->valid()) { continue; /* could not read line */ }

  if(preg_match($pattern, $buffer, $matches)) {
   yield $buffer;
   } else {
   
   if (!$sfd->valid()) {
     /* end of file, yield holdbuffer containing all unidentified buffer  */
     if ($holdbuffer != null) {
       yield $holdbuffer;
     $holdbuffer = null;
     }
   }
   }
$i++;  
  }
}

public function replaceinFile(
  $fromstring, 
  $tostring, 
  $File,
  ){
try {
file_put_contents(
  $File, 
  str_replace(
    $fromstring, 
    $tostring, 
    file_get_contents($File)
    )
  );
} catch(Exception $e)
{
  /* error - modify chat file exception */
  return 'error: could not fix path to renamed extensionless file:'.$e->getMessage();
}
return 'success';
}

public function PaginationViability(
 Config $Config,
 $Target = 'next',
  ) {
if ($Target == 'next') {
$from = $Config->NPaginationFrom;
$to = ($Config->NPaginationTo == 0 ? $Config->recordsperpage : $Config->PaginationTo );
} elseif ($Target == 'current') {
$from = $Config->PaginationFrom;
$to = ($Config->PaginationTo == 0 ? $Config->recordsperpage : $Config->PaginationTo );  
} elseif ($Target == 'prev') {
  /* prevent lower to, or 0 by adding recordsperpage */
$from = $Config->PPaginationFrom;
$to = ($Config->PPaginationTo == 0 ? ($from + $Config->recordsperpage) : $Config->PaginationTo );  
} else {
  return (object) [ 
  'status' => false,
  'response' => 'Supported Targets are prev|current|next' 
  ];
}

if ($from === null) {
  return (object) [ 
  'status' => false,
  'response' => 'pagination string invalid' 
  ];
}

$sfd = new SplFileObject($this->ChatFile);
if (!$sfd) {
  return (object) [ 
  'status' => false,
  'response' => 'error: could not open chat file' 
  ];
}
$sfd->seek($from);
if ( !$sfd->valid() ) {
return (object) [ 
  'status' => false,
  'response' => 'line not found' 
  ];
} else {
return (object) [ 
  'status' => true,
  'response' => 'viable' 
  ];
  }
}

public function PaginationNav(
  Config $Config,
  ){
$PrevPaginationStatus = (
  $Config->NPaginationFrom == 0 ? 
  'disabled' : ''
  );
$NextPaginationStatus = (
  $this->eof == true ?
    'disabled' : ''
    );

$PrevPaginationHref = '?'.$Config->PaginationFrom.'-'.$Config->PaginationTo;
$NextPaginationHref = '?'.$Config->NPaginationFrom.'-'.$Config->NPaginationTo;

$PaginationNav = '  
  <nav aria-label="...">
  <ul class="pagination">
    <li class="page-item '.$PrevPaginationStatus.'">
      <a href='.$PrevPaginationHref.' class="page-link">Previous</a>
    </li>
    <li class="page-item '.$NextPaginationStatus.'">
      <a href='.$NextPaginationHref.' class="page-link">Next</a>
    </li>
  </ul>
</nav>';

return $PaginationNav;
}

public function PageTitle(){
return 'Whatsapp Chat '
.(isset($this->NoSelected) ? '' : (isset($this->Name) && $this->Name != '' ? ('with '.$this->Name) : ''));  
}

public function Menu(
  Init $Init,
  ){
  /* build menu */
$menu = '';
$ChatFilesData = $Init->Data->Data;
foreach ($ChatFilesData as $SelectList){
$selected = (isset($selected) && $selected == 'selected' ? '' : ( $SelectList['id'] == $this->SelectedId ? 'selected' : ''));
$nextchatfilesList = (null !== ( $nextchatfilesList = next($ChatFilesData))) ? $nextchatfilesList : false;
$menu .= ($SelectList['bfc'] == 1 ? ('<optgroup label="Chats with '.$SelectList['dirname'].'">') : '');
$menu .= '
    <option '.$selected.' value="'.$SelectList['search'].'">Chats with '.$SelectList['name'].' '.$SelectList['bfc'].'</option>
  ';
$menu .= ($nextchatfilesList !== false  ? (($SelectList['dirname'] === $nextchatfilesList['dirname']) ? '' : '</optgroup>') : '</optgroup>');

}
return $menu;

}

}

class Database {
  
private $adapter;
private $sql;

function __construct(Config $Config){
  
$this->adapter = new Laminas\Db\Adapter\Adapter([
    'driver'   => 'Pdo_Sqlite',
    'database' => $Config->sqlitedb,
]);
$this->sql = new Sql($this->adapter);

$this->Create();
}

/* criminal entities, Michael Morka, Happy Uboh and other persons trying to supress evidence deleted from this section down, current exposure of how Michael Morka prevented evidence frlm being brought to Asaba to protect the assasins who once again attacked my property while in police custody is being threatened by these criminal gans including my supposed father Michael Morka. the current exposure effort is the conversation between Ruth Mouka, my sister while i was in police custody, asaba.
*/

public function Create(){
$sql = "CREATE TABLE IF NOT EXISTS 'chatfiles' (
  'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
  'bfc' INTEGER NOT NULL,
  'filename' TEXT NOT NULL, 
  'dirpath' TEXT NOT NULL, 
  'dirname' TEXT NOT NULL, 
  'search' TEXT NOT NULL, 
  'groupchat' BOOLEAN NOT NULL,
  'vrecipient' TEXT NULL,
  'name' TEXT NOT NULL, 
  'sync' INTEGER DEFAULT 1 , 
  'synctime' INTEGER NULL default (strftime('%s','now')), 
  'filepath' TEXT NOT NULL, 
  'url' TEXT NOT NULL, 
  'archivedurl' TEXT NULL, 
  'mtimeorhash' TEXT NOT NULL
  )";

$statement = $this->adapter->query($sql);
$statement->execute();

$sql = "CREATE TABLE IF NOT EXISTS 'AppData' (
  'id' INTEGER PRIMARY KEY AUTOINCREMENT NULL, 
  'mtimeorhash' TEXT NOT NULL,
  'zipsize' TEXT NULL,
  'foldername' TEXT NOT NULL,
  'synctime' INT NULL default (strftime('%s','now'))
  )";

$statement = $this->adapter->query($sql);
$statement->execute();

$sql = "CREATE TABLE IF NOT EXISTS 'AppLog' (
  'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
  'datetime' INT NULL default (strftime('%s','now')),
  'action' TEXT NOT NULL
  )";

$statement = $this->adapter->query($sql);
$statement->execute();

} 

public function InsertOrUpdate(
  $Table,
  $ColumnValuesArray, 
  $UpdateWhereArray,
  $Target = 'update',
  ){
    
if ($Target != 'update') {
$SelectExecute = $this->SelectOne(
  $Table,
  $UpdateWhereArray
  );
$Select = ($SelectExecute->status ? iter_to_array($SelectExecute->response) : []);
}

if (count($Select) == 1 || strtolower($Target) == 'update') {
$InsertOrUpdate = $this->sql->update($Table);
$InsertOrUpdate->where($UpdateWhereArray);
$InsertOrUpdate->set($ColumnValuesArray);  
} else {
$InsertOrUpdate = $this->sql->insert($Table);  
$InsertOrUpdate->values($ColumnValuesArray);  
}
try {
$statement = $this->sql->prepareStatementForSqlObject($InsertOrUpdate);
$results = $statement->execute();  
} catch (\Exception|\Throwable $e) {
  return (object) [
    'status' => false,
    'response' => 'Insert or Update records failed:'.$e->getMessage()
    ];
}

return (object) [
    'status' => true,
    'response' => $results
    ];
}

public function Select($Table){
$selectone = $this->sql->select();
$selectone->from($Table);
try {
$statement = $this->sql->prepareStatementForSqlObject($selectone);
$results = $statement->execute();
} catch (\Exception|\Throwable $e) {
  return (object) [
    'status' => false,
    'response' => 'Select chat files records failed'
    ];
}

return (object) [
    'status' => true,
    'response' => $results
    ];
}

public function SelectOne(
  $Table,
  $SelectOneWhereArray
  ){
$selectone = $this->sql->select();
$selectone->from($Table);
$selectone->where($SelectOneWhereArray);
$selectone->limit(1);
try {
$statement = $this->sql->prepareStatementForSqlObject($selectone);
$result = $statement->execute();  
} catch (\Exception|\Throwable $e) {
  return (object) [
    'status' => false,
    'response' => 'Select One chat file record failed'
    ];
}

return (object) [
    'status' => true,
    'response' => $result
    ];
}

public function DeleteWhereNot(
  $Table,
  $column, 
  ...$args
  ) {
$delete = $this->sql->delete();
$delete->from($Table);
$delete->where->notIn($column, $args);
try {
$statement = $this->sql->prepareStatementForSqlObject($delete);
$results = $statement->execute();  
} catch (\Exception|\Throwable $e) {
  return (object) [
    'status' => false,
    'response' => 'Delete redundant chat files records failed'
    ];
}

return (object) [
    'status' => true,
    'response' => $results
    ];
}

}

class processLines {
public int $PaginationFrom;
public int $PaginationTo;

public function Process(
  Config $Config,
  App $App,
  ){
/* yield */
  foreach ($this->iterate(
    $Config,
    $App,
    ) as $iterable) {
      yield $iterable;
    }  
}

public function ProcessAndPrint(
  Config $Config,
  App $App,
  ){
  /* print */
  foreach ($this->iterate(
    $Config,
    $App,
    ) as $iterable) {
    print $iterable;
  }  
}

public function iterate(
  Config $Config, 
  App $App,
  ){
$from = $Config->PaginationFrom;
$to = $Config->PaginationTo;

foreach ($App->ChatFileGenerator(
  $Config
  ) as $line) {
  yield from $this->processline(
    $line, 
    $from,
    $Config,
    $App,
    );
  $from++;
}   

}

public function processline(
  $line, 
  int $counterfilearray,
  Config $Config,
  App $App,
  ){ 
         
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

$recipient = ($App->GroupChat ? true : ( (strtolower($sender) == $App->VerifiedRecipient) ? true : false));

$phug = new Phug\Renderer([
'globals' => [
'sender' => $sender,
'message' => $message,
'time' => $time,
'recipient' => $recipient,
'vrecipient' => $App->VerifiedRecipient,
'attachmentexists' => false,
'type' => $messagelinetype,
'groupchat' => $App->GroupChat,
'sendercolor' => getsendercolor($sender),
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
   div(class="col-9 rounded alert justify-content-center")
    .chat #{$message}
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
'filesize' => getFilesize($attachments->absfilepath),
'fileext' => $attachments->ext,
'attachmentexists' => $attachments->exists
]);

if (in_array($fileext, array('jpg','jpeg','png','gif'))) {
/* photos */

$phug->share([
   'lfilepath' => baseURI($attachments->filepath)
]);

$template .= '
component attachment
 div(class="wa-ic") 
  a(class="wa-m g" href="$lfilepath" data-gallery="g" data-width="100vw" data-height="auto" data-glightbox="title: $filecaption $filenameandext")
    img(class="object-fit-cover border rounded zi-r" width="200px" src="$filepath")
 ';

          
} elseif (in_array($fileext, array('mp4','avi','flv','3gp','mkv','mov'))) {
/* video */

$phug->share([
'videoURI' => videoPlayer(urlencode(baseURI($attachments->filepath))),
'videoPoster' => videoPlayerPoster($attachments->filepath)
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
   'fileicon' => exttofileicon($attachments->ext),
   'docviewer' => docViewer(baseURI($attachments->filepath))
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
      span(class="font-size-13 text-muted") pages ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ #{$filesize} ÃƒÂ¢Ã¢â€šÂ¬Ã‚Â¢ #{$fileext}
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

$template .= '
-
div.cID(id="c{$counterfilearray}")
if $type == "notification"
 div(class="row justify-content-center m-1")
  div(class="col-9 rounded alert justify-content-center")
    div
     span.time #{$time}
    p.chat #{$message}
elseif $type == "chat"
  div(class=$recipient ? "justify-content-start" : "justify-content-end" class="row m-1")
    if $groupchat == true
     div(class="col bx bx-sm bxs-user-circle gc" style="color:$sendercolor;")
  
    .col-9
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
             span.mEl #{$message}
         span(class="col time" class=$recipient ? "zml" : "bg-wa") #{$time}
else
 +unformattedchatline
';

// instance way (with Phug)
yield $phug->render($template);
                
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
$absfilepath = Path::join(
  $Config->baseDir, 
  $App->DirPath, 
  $attachment
  );
$filepath = Path::join(
  $App->DirPath,
  $attachment
  );  
$exists = ($attachment != '' && file_exists($absfilepath) ? true : false);
clearstatcache();
if (!$exists) return 'error: attached file was not found';

if ($ext == '') {
$extmime = mimedetector($absfilepath);
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
  "absfilepath" => $absfilepath
  ];
}

}

class generateSiteMap {

public function __construct(){
 
}

public function get(
  Config $Config,
  Init $Init,
  ){
$GenerateSitemap = false;
if ($GenerateSitemap) {
  $Generate = $this->Generate(
    $Config, 
    $Init,
    ); 
  $Responses = 'Generate:'.$Generate->response;
  $sitemap = [ 
    'status' => true,
    'response' => 'Sitemap listed.'
    ];
} else {
  /* check for ??? trigger for sitemap or refresh recommendation for sitemaps */
 // $sitemap = $this->Generate();  
  /* sitemap generator file does not exist, assumption is sitemap does for now */
  $sitemap = [ 
    'status' => true,
    'response' => 'Sitemap listed.'
    ];
}
//objects 
$files = $this->filesExists(
  $Config->baseDir,
  $Config->sitemapxml, 
  $Config->robotstxt,
);

return (object) [
  'sitemap' => (object) $sitemap,
  'files' => $files
  ];
}

public function filesExists($sdir, ...$sfiles){
$data = [];
foreach ($sfiles as $sfile) {
settype($sfile, 'string');
clearstatcache();
$abspath = $sfile;
$data[] = [
  'filename' => $sfile,
  'rpath' => Path::makeRelative($abspath, $sdir),
  'exists' => (($fe = file_exists($abspath)) ? true : false),
  'stats' => ($fe ? stat($abspath) : false)
  ];
}
return $data;
}

public function Generate(
  Config $Config,
  Init $Init,
  ){

if (!is_dir('autodelete')) {
  if (!mkdir('autodelete')) {
    return (object) [
    'status' => false,
    'response' => 'cannot create auto delete folder'
    ];
  }
}

if (file_exists($Config->sitemapxml)) {
clearstatcache();  
  if (!rename($Config->sitemapxml, Path::join('autodelete', $Config->sitemapxml))) { 
    return (object) [
    'status' => false,
    'response' => 'cannot move '.$Config->sitemapxml.' to autodelete'
    ];
  }
}

try {
// create Sitemap
$sitemap = new Sitemap($Config->sitemapxml);
} catch(Exception $e) {
  return [
    'status' => false,
    'response' => 'error: samdark/sitemap new Sitemap error'
    ];
}

$ChatFilesData_column_search = array_column(
  $Init->Data->Data, 
  'url'
  );
foreach ($ChatFilesData_column_search as $Links) {
$Link = $Links;
// add some URLs to sitemap
try {
$sitemap->addItem($Link, time()); 
} catch (Exception $e) {
  //archive links error
}
}

try {
$sitemap->setStylesheet(
  addurl(
    'sitemap.xsl', 
    $Config, 
    ));
$sitemap->write();
} catch(Exception $e){
  //sitemap.xml write error
  return (object) [
    'status' => false,
    'response' => $Config->sitemapxml.' - cant update(3)'
    ];
}

if (file_exists($Config->sitemapxml)) {
  clearstatcache();

} else {
  /* failed to generate sitemap, restore old */
  if (fie_exists(Path::join('autodelete', $Config->sitemapxml))) {
    clearstatcache();
    rename(Path::join('autodelete', $Config->sitemapxml), $Config->sitemapxml);
  return (object) [
    'status' => false,
    'response' => 'sitemap generator failed - old sitemap restored'
    ];
  }
}

try {
/* call py wayback executable */
$curl = new Curl;
$curl->post($Config->PyArchiveURI);
$curl->close();
} catch(Exception $e) {
//post to py, execution does not return output
}

return (object) [
    'status' => true,
    'response' => 'Sitemap generated.'
    ];
/* end sitemap generator function */
}

}

/* initializations */

/* instantiate class */