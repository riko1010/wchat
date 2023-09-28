<?php
/* 
Initializations of classes, functions happen at the end of the functions and classes definitions. composer requires file base use keyword for new instances.

initializing the functions or classes before definitions is not feasible

*/
require_once($baseDir.'/vendor/autoload.php');
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

function addurl($path){
  return (
    $GLOBALS['SiteUrl']
  .'/'
  .Path::makeRelative($path, $GLOBALS['baseDir'])
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

function CFgetfolders($cfFolder) {
  /* can filter dirs with iteration of glob */
  if (!isset($cfFolder)) return 'error: empty path';
  if (!is_dir($cfFolder)) return 'error: folder is not a dir or empty path';
  $cfFolders = glob($cfFolder.'/*', GLOB_ONLYDIR);
  if (!$cfFolders || count($cfFolders) < 1) return 'error: folder not found or no dirs in base dir';
  
  return (object) [
    'chatFolders' => $cfFolders,
    'chatFolder' => $cfFolder
    ];
/* end CFgetfolders */
}

function replaceinFile($fromstring, $tostring, $ChatFile){
try {
file_put_contents($ChatFile, str_replace($fromstring, $tostring, file_get_contents($ChatFile)));
} catch(Exception $e)
{
  /* error - modify chat file exception */
  return 'error: could not fix path to renamed extensionless file';
}
return 'success';
}

class Init {
  
public $InitType;
public $baseDir;
public $queryarg;
public $db;

  public function BootLoader(){
  
  }
  
  public function API() {
  $ChatFilesDataExecute = $this->db->SelectOne(
  'chatfiles',
  ['id' => $this->queryarg]
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
  
  public function Index(){
  $ChatFilesDataExecute = $this->db->Select('chatfiles');
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

  public function AppData(){
  /* load app data */
  $AppDataExecute = $this->db->SelectOne(
                        'AppData',
                        ['id' => 1]);
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
  /* load chat files if not loading through api */
  
  return (object) [
  'Data' => $AppData
  ];   
  }
}

class App{
  
public $ChatFile; 
public $DirPath;
public $GroupChat;
public $ChatFilesData;
public $ChatFilesDataIdAsKeys;
public $Selected;
public $SelectedId;
public $NoSelected = true;
public $CheckLegacy = false;
public $VerifiedRecipient;
public $Name;
public $baseDir;
public object $NPagination;
public bool $eof = false;

public function __construct() {

}

public function CFgetfiles($cfFolder, $cfFolders, $pattern) {

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
if (preg_match($pattern, $f, $matches)) {
/* bfc is for aesthtics,not count or pointing */
$bfc++;

$dirname = basename($dir);
$filename = basename($f);
//$f = '';
$cfl[$gbfc]['bfc'] = $bfc;
$cfl[$gbfc]['vrecipient'] = $this->VerifiedRecipient;
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

public function SetChatFile(
  $queryarg = null
  ) {
/* SetChatFile on index uses $_GET/backupfile , on api uses _request/queryarg - queryarg is RecordId, assets-php/sqlite uses queryarg to selectone/select, latter if $api, if not found, ChatFileDataNotEmpty = false , meaning empty  */

if ($queryarg != null) {
/* recursive array search case , ras for search field */
$ras = ras($queryarg, array_column($this->ChatFilesData, 'search'));
/* ras for id field if not found */
$ras = ($ras == null ? 
ras($queryarg, array_column($this->ChatFilesData, 'id')) : $ras );
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
(!$this->CheckLegacy ?: $this->CheckLegacyChatFileQuery($queryarg)); 
} else {
/* ChatFilesData array index, default to first item in array, 0 */ 
$this->Selected = 0;
}

$SelectedChatFile = $this->ChatFilesData[$this->Selected];
$this->SelectedId = $SelectedChatFile['id'];
$this->ChatFile = Path::join(
  $this->baseDir,
  $SelectedChatFile['filepath']
  );
$this->Name = $SelectedChatFile['name'];
$this->DirPath = $SelectedChatFile['dirpath'];
$this->GroupChat = $SelectedChatFile['groupchat'];
}

public function CheckLegacyChatFileQuery($query){
   $query = str_replace('_', '', $query);
    $cflcsearch = array_map(
    function($x) {
      return strtolower(
        str_replace(' ', '', $x)
        );
    }, array_column($this->ChatFilesData, 'dirname'));
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

public function SetVerifiedRecipient($recipient, $cfFiles = null){
$cfFiles = ($cfFiles !== null ?: $this->ChatFile);
$identities = [];   
$i = 0;
$recipient = trim(strtolower($recipient)); 
$cfselectedfilegenerator = $this->ChatFileGeneratorRecipient('all');
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

$this->VerifiedRecipient = (isset($vrecipient) ? $vrecipient : false);
}

public function ChatFileGenerator(
  $PaginationFrom = null,
  $PaginationTo = null,
  $cfFiles = null
  ){
$cfFiles = ($cfFiles !== null ?: $this->ChatFile);  
$sfd = new SplFileObject($cfFiles);
if (!$sfd) return 'error: could not open chat file';
$Pagination = explode(',', $Paginations);
$filearray = [];
$holdbuffer = null;
$this->eof = false;
/* literal newline, alt is htmlspecialchars_decode of phug render, allowing br, to render as html, $message in pug is currently escaped, alt is rendering as html instead of escape plaintext */
$NewLine = '
'; 
$pattern = '/[0-3]?[0-9]\/[0-3]?[0-9]\/(?:[0-9]{2})?[0-9]{2},/';
$from = (is_numeric(trim($PaginationFrom)) ? trim($PaginationFrom) : 0 );
$to = (is_numeric(trim($PaginationTo)) ? trim($PaginationTo) : $GLOBALS['recordsperpage'] );
$i = $from;
$sfd->seek($i);
if ($sfd->eof()) {
  /* eof on non existent pagination?, termination here or at end */
$this->eof = true;  
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

if (!$this->eof) {
$NextTo = $to + $GLOBALS['recordsperpage'];
$this->NPagination = (object) [
  'From' => $to, 
  'To' => $NextTo
  ];
}

return $this->NPagination;
}

public function ChatFileGeneratorRecipient($Paginations, $cfFiles = null){
$cfFiles = ($cfFiles !== null ?: $this->ChatFile);  
$sfd = new SplFileObject($cfFiles);
if (!$sfd) return 'error: could not open chat file';
$Pagination = explode(',', $Paginations);
$filearray = [];
$holdbuffer = null;
$NewLine = '<br/>';
$pattern = '/[0-3]?[0-9]\/[0-3]?[0-9]\/(?:[0-9]{2})?[0-9]{2},/';
$from = (isset($Pagination[0]) && is_numeric(trim($Pagination[0])) ? trim($Pagination[0]) : 0 );
$to = (isset($Pagination[1]) && is_numeric(trim($Pagination[1])) ? trim($Pagination[1]) : $GLOBALS['recordsperpage'] );
$i = $from;
foreach ($sfd as $line)
{
  /* goto start line in pagination arg */
  $sfd->seek($i);
  $buffer = $sfd->current();
   if (!$sfd->valid()) { continue; /* could not read line */ }
   /* whatsapp export lists lines without date string given newline is the delimiter and becomes difficult to determine if line is chat, notification or ....
set file array default key to null, regex if date string.solves newline, of chat continuation problem by appending unidentified lines to previous line */

  if(preg_match($pattern, $buffer, $matches)) {
   $sfd->seek($i + 1);
   if (preg_match($pattern, $sfd->current(), $matches)) {
   yield $buffer;  
   } else {
   $holdbuffer .= $buffer;
   }
   /* return pointer to current iteration */
   $sfd->seek($i);
   /*
   if match, check next, if match, yield
   if not match, hold, continue?, if not match, bind prev, check next, if match, yield, else , hold, continue
   */
   } else { 
   	/* append assumed chat continuation to previous array, \n or \r\n considered. */
   $sfd->seek($i + 1);
   if (preg_match($pattern, $sfd->current(), $matches)) {
   $holdbuffer .= ($holdbuffer != null ?
   $NewLine.$buffer : $buffer);
   yield $holdbuffer;  
   $holdbuffer = null;
   } else {
   $holdbuffer .= ($holdbuffer != null ?
   $NewLine.$buffer : $buffer);
   }
   
   if (!$sfd->valid()) {
     /* end of file, yield holdbuffer containing all unident