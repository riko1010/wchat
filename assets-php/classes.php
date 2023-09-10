<?php
/* 
Initializations of classes, functions happen at the end of the functions and classes definitions. composer requires file base use keyword for new instances.

initializing the functions or classes before definitions is not feasible

*/
require_once('vendor/autoload.php');
use SoftCreatR\MimeDetector\MimeDetector;
use SoftCreatR\MimeDetector\MimeDetectorException;
use samdark\sitemap\Sitemap;
use samdark\sitemap\Index;
use Curl\Curl;
use Wikimedia\RelPath;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use League\Csv\Reader;
use League\Csv\Writer;
use function BenTools\IterableFunctions\iterable_to_array as iter_to_array;

function ntfy($message){
  $curl = new Curl;
  $curl->post('https://ntfy.sh/dhskdb', [
    'message' => $message
    ]);
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

class generateSiteMap {

public $cfFiles;
public $generatesitemapfile;
public $SiteUrl;
public $cfFolder;
public $robotstxt;
public $sitemapxml;
public $sitemapcsv;
public $PyArchiveURI;
public $bdir;

public function get(){

if (file_exists($this->generatesitemapfile)) {
  clearstatcache();
  $sitemap = $this->generate();  
} else { 
  /* sitemap generator file does not exist, assumption is sitemap does for now */
  $sitemap = [ 
    'status' => true,
    'response' => 'Sitemap listed.'
    ];
}
//objects 
$files = $this->filesExists(
  $this->bdir,
  $this->sitemapxml, 
  $this->sitemapcsv,
  $this->robotstxt
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

public function mergedrop($old, $new){

$sitemapcsvList = '"sync","mtime","url","archivedurl","filepath","synctime"'.PHP_EOL;

foreach ($new as $cl){
$mtime = filemtime($cl['filepath']);
$filepath = $cl['filepath'];
clearstatcache();
$url = addurl($cl['search']);
$sync = 1;
$archivedurl = '';
$synctime = date('m.d.y g:i a');

try {
$unmodarray = ras($filepath, $old);
if ($unmodarray){
$unmodarray = $old[$unmodarray];
if ($unmodarray['mtime'] == $mtime && 
$unmodarray['url'] == $url ) {
$archivedurl = $unmodarray['archivedurl']; 
$sync = $unmodarray['sync']; 
}
}
} catch (\Exception|\Throwable $e) {
  
}

$sitemapcsvList .= <<<CSVCONTENT
$sync,"$mtime","$url","$archivedurl","$filepath","$synctime"
CSVCONTENT;
$sitemapcsvList .= PHP_EOL;
}

return (object) [
      'status' => true,
      'response' => $sitemapcsvList
      ]; 

}

public function getcsv($sitemapcsv) {
try {
  $reader = Reader::createFromPath($sitemapcsv, 'r');
  $reader->setHeaderOffset(0);
  $rows = $reader->getRecords();
  $rows = (iter_to_array($rows));
  if (count($rows) <= 0) throw new Exception ('no records in csv file');
  return (object) [
      'status' => true,
      'response' => $rows
      ];
} catch (\Exception|\Throwable|SyntaxError $e) {
  return (object) [
      'status' => false,
      'response' => 'cannot read csv file'.json_encode($e)
      ];
}
}

public function writecsv($records, $sitemapcsv){
  $fs = new Filesystem;
 
try {
  $fs->dumpFile($sitemapcsv, $records);
} catch (IOExceptionInterface $exception) {
    return [
      'status' => false,
      'response' => 'cannot create '. $sitemapcsv.$exception->getPath()
      ];
}

return [
      'status' => true,
      'response' => $sitemapcsv.' file written'
      ];

}
  
public function generate(){

if (!is_dir('autodelete')) {
  if (!mkdir('autodelete')) {
    return (object) [
    'status' => false,
    'response' => 'cannot create auto delete folder'
    ];
  }
}

if (file_exists($this->sitemapxml)) {
clearstatcache();  
  if (!rename($this->sitemapxml, Path::join('autodelete', $this->sitemapxml))) { 
    return (object) [
    'status' => false,
    'response' => 'cannot move '.$this->sitemapxml.' to autodelete'
    ];
  }
}

try {
// create Sitemap
$sitemap = new Sitemap($this->sitemapxml);
} catch(Exception $e) {
  return [
    'status' => false,
    'response' => 'error: samdark/sitemap new Sitemap error'
    ];
}

try {
$subject = 'Archive links from '
.Faker\Factory::create()->unique()->name()
.' on '
.date("r");
} catch (Exception $e) {
/* faker error */
$subject = 'Archive links from '.rand(1000000,9999999).' on '.date("r");
}
$cfl_column_search = array_column($this->cfFiles->cfl, 'search');
foreach ($cfl_column_search as $Links) {
$Link = addurl($Links);
// add some URLs to sitemap
try {
$sitemap->addItem($Link, time()); 
} catch (Exception $e) {
  //archive links error
}
}

try {
$sitemap->setStylesheet(addurl('sitemap.xsl'));
$sitemap->write();
} catch(Exception $e){
  //sitemap.xml write error
  return (object) [
    'status' => false,
    'response' => $this->sitemapxml.' - cant update(3)'
    ];
}

try {
$sitemapcsv = $this->getcsv($this->sitemapcsv);
$csvoldarray = $sitemapcsv->status ? $sitemapcsv->response : [];
$sitemapcsv = $this->mergedrop(
              $csvoldarray, 
              $this->cfFiles->cfl
              );
if(!$sitemapcsv = $this->writecsv($sitemapcsv->response, $this->sitemapcsv) || !$sitemapcsv->status) throw Exception($this->sitemapcsv.' write failed');

} catch(\Exception|\Throwable) {
  return (object) [
    'status' => false,
    'response' => $this->sitemapcsv.' write failed'
    ];
}

if (file_exists($this->sitemapxml) && file_exists($this->generatesitemapfile)) {
  clearstatcache();
  rename($this->generatesitemapfile, Path::join('autodelete', $this->generatesitemapfile));
} else {
  /* failed to generate sitemap, restore old */
  if (file_exists(Path::join('autodelete', $this->sitemapxml))) {
    clearstatcache();
    rename(Path::join('autodelete', $this->sitemapxml), $this->sitemapxml);
  return (object) [
    'status' => false,
    'response' => 'sitemap generator failed - old sitemap restored'
    ];
  }
}

try {
/* call py wayback executable */
$curl = new Curl;
$curl->post($this->PyArchiveURI);
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
function ras($needle,$haystack) {
  /* recursive array search */
  if (!is_array($haystack)) return false;
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if (is_array($value)) {
         $value = array_map('strtolower', $value);
        } elseif(is_string($value)) { strtolower($value); 
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

function CFgetfiles($cfFolder, $cfFolders, $pattern) {
$rbf = ((isset($_GET['backupfile']) && !empty($_GET['backupfile'])) ? $_GET['backupfile'] : false);
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
$cfl[$gbfc]['name']= trim($matches["name"]);
$cfl[$gbfc]['filename'] = $filename;
$cfl[$gbfc]['filepath'] = $f;
$cfl[$gbfc]['dirpath'] = $dir.'/';
$cfl[$gbfc]['dirname'] = $dirname;
$cfl[$gbfc]['search'] = str_replace('+', '', str_replace(' ', '', $dirname.'-'.$cfl[$gbfc]['name'])).(($bfc < 1) ?: $bfc);
$cfl[$gbfc]['groupchat'] = (str_contains($matches['name'], 'group') ? true : false);
/* list select options of folder and chat files - supports multiple chat files in one dir, media files hopefully wont conflict, idk the chances but... ??? */

$cfl[$gbfc]['selected'] = !$rbf ?: ( isset($bf) ? '' :
( (trim($rbf) == $gbfc || strtolower($rbf) == strtolower($cfl[$gbfc]['search']) ) ? ( ($bf = $gbfc) ? 'selected':'') : '' ) );

$gbfc++;
}
/* end whatsapp backup files */
}

}

if (!isset($bf)) {
  $rbf = strtolower(str_replace('_', '', $rbf));
  $cflcsearch = array_map(
  function($x) {
    return strtolower(
      str_replace(' ', '', $x)
      );
  }, array_column($cfl, 'dirname'));
  $ras = ras($rbf, $cflcsearch);
  $bf = !empty($ras) ? ( ($cfl[$ras]['selected'] = 'selected') ? $ras : $ras ) : 0;
}

return (object) [
  'cfl' => $cfl, 
  'bf' => $bf,
  'gbfc' => $gbfc
  ];
/* end CFgetfiles */
}

function replaceinFile($fromstring, $tostring, $file){
try {
file_put_contents($file, str_replace($fromstring, $tostring, file_get_contents($file)));
} catch(Exception $e)
{
  /* error - modify chat file exception */
  return 'error: could not fix path to renamed extensionless file';
}
return 'success';
}

function CFloadselectedfile($cfFiles, $recipient){
   /* $filearray = file($cfFiles); - incase wish to load using file instead of fopen */
$fd = fopen ($cfFiles, "r");
if (!$fd) return 'error: could not open chat file';
/* identities array, unique key of identity */
$filearray = [];
$identities = [];
$recipient = trim(strtolower($recipient));
$i=0;
while (!feof ($fd)) 
{
   $buffer = fgets($fd, 4096);
   if (!$buffer) { continue; /* could not read line */ }
   /* whatsapp export lists lines without date string given newline is the delimiter and becomes difficult to determine if line is chat, notification or ....
set file array default key to null, regex if date string, although theres indication of a different datetime string, so maybe expand natch with wildcard to confirm if a chat but...solves newline, of chat continuation problem by appending unidentified lines to previous line */

   if(preg_match("/(.*?[0-9]+\/[0-9]+\/[0-9]+.*?),/", $buffer)) {
   $filearray[] = $buffer;
   } else { 
   	$i--;
   	/* append assumed chat continuation to previous array, \n or \r\n considered. */
   $filearray[$i] = (!empty($filearray[$i]) ? "$filearray[$i]\n$buffer" : "$buffer");
   }
   
$pattern = '/(?P<time>.*?,+.*?)-(?P<sender>.*?):(?P<message>.*)/is';
if (preg_match($pattern, $filearray[$i], $matches)) {
  $sender = strtolower(trim($matches["sender"]));
  if (!empty($sender)) {
   $identities[$sender] = [
     'id' => $i,
     'sender' => $sender,
     'levenshtein' => ''];
  }
}
   
$i++;  
}
/* set verified recipient */
if (count($identities) > 0 ) {
  if (isset($identities[$recipient]) && $identities[$recipient]) {
    $vrecipient = $identities[$recipient]['sender'];
    $identities[$recipient]['levenshtein'] = 0;
  } else {
    /* levenshtein based */
    foreach($identities as $identity) {
     $pperc = (isset($perc) ? $perc : 'notset');
     $perc = levenshtein($recipient, $identity['sender']);
     $identities[$identity['sender']]['levenshtein'] = $perc;
     if ($pperc == 'notset') {
     $vrecipient = $identity['sender'];
     } else {
     $vrecipient = ($pperc > $perc ? $identity['sender'] : $vrecipient);
     }
    }
    /* end levenshtein guess */
  }

  /* end $identities similarity check */
}

fclose ($fd);

return (object) [
  'filearray' => (isset($filearray) ? $filearray : []),
  'vrecipient' => (isset($vrecipient) ? $vrecipient : false)
  ];
}

function CFgetattachments($message, $attachmentneedle, $file, $dirpath) {
$attachments = (str_contains($message, $attachmentneedle) ? explode($attachmentneedle, $message) : false);
if (!$attachments) return 'error: no files attached';

$attachment = (isset($attachments[0]) ? trim($attachments[0]) : false );
if (!$attachment) return 'error: no files attached';

$ext = (isset($attachments[0]) ? trim(strtolower(pathinfo($attachments[0], PATHINFO_EXTENSION))) : '');
$caption = (isset($attachments[1]) ? $attachments[1] : '' );
$filepath = $dirpath.'/'.$attachment;
$exists = (!empty($attachment) && file_exists($filepath) ? true : false);
clearstatcache();
if (!$exists) return 'error: attached file was not found';

if (empty($ext)) {
$extmime = mimedetector($filepath);
$ext = (isset($extmime["ext"]) ? (trim($extmime["ext"])) : false);
if (!$ext) return 'error: could not guess file extension';
$newfilepath = $filepath.$ext;  
if(!rename($filepath, $newfilepath)) return 'error: could not fix file extension';
/* modify this chat file */
if('success' !== replaceinFile($attachment, $attachment.$ext, $file)) return 'error: could not update chat file with fixed extensionless files'; 
$filepath = $newfilepath;
}
return (object) [
  "name" => $attachment,
  "ext" => $ext,
  "caption" => $caption,
  "exists" => $exists,
  "filepath" => $filepath
  ];
}



/* initializations */

/* instantiate class */
