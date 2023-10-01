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

Class Request {
  
  public function _construct($Requests){
  $data = [];
  $filters = [];
  foreach ($Requests as $Request => $Val) {
  $data[] = [
      $Request => $Val,
  ];
  $filters[] = [
      $Request => 'trim|empty_string_to_null|strip_tags|escape',
  ];
  }

$RequestData = new Sanitizer($data, $filters);
$RequestData = $Request->sanitize();

return (object) $RequestData;

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
    );  
    }
    
    $Init->AppendConfig(
    'AppData',
    ['id' => 1],
    $db,
    $Config,
    );
    
    if ($Config->InitType == 'API') {
      $this->Data = $Init->API(
        $Request, 
        $db,
        );
    } else {
    $this->Data = $Init->Index($db);
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
$url = addurl($cl['search']);
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
public int $NPaginationFrom;
public int $NPaginationTo;
public bool $eof = false;

public function __construct() {

}

public function SetChatFile(
  Config $Config,
  Request $Request,
  Init $Init,
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

public function SetVerifiedRecipient($recipient, $cfFiles = null){
$cfFiles = ($cfFiles !== null ?: $this->ChatF