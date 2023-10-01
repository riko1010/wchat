<?php 
//error_reporting(0);
header('Content-Type: text/plain; charset=utf-8');
/*
simple api implementation
pagination
  pagination viability 
*/
require_once '../vendor/autoload.php';

use Symfony\Component\Filesystem\Path;
use function BenTools\IterableFunctions\iterable_to_array as iter_to_array;
use Elegant\Sanitizer\Sanitizer;

use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

/* declaration of vars, some vars will be redeclared in settings, regardless of depth, absolute path to shared assets is desired */
$baseDir = dirname(__DIR__);

require Path::join($baseDir, 'assets-php/settings.php');
require Path::join($baseDir, 'assets-php/classes.php');

/* Request Handler */
$data = [
'queryarg' => (isset($_REQUEST['queryarg']) ? $_REQUEST['queryarg'] : null ),
'paginationfrom' => (isset($_REQUEST['paginationfrom']) ? $_REQUEST['paginationfrom'] : null ),
'paginationto' => (isset($_REQUEST['paginationto']) ? $_REQUEST['paginationto'] : null ),
'recordsperpage' => (isset($_REQUEST['recordsperpage']) ? $_REQUEST['recordsperpage'] : null )
];

$filters = [
    'queryarg' => 'trim|empty_string_to_null|strip_tags|escape',
    'paginationfrom' => 'trim|empty_string_to_null|strip_tags|escape',
    'paginationto' => 'trim|empty_string_to_null|strip_tags|escape',
    'recordsperpage' => 'trim|empty_string_to_null|strip_tags|escape'
];

$REQUEST = (object) (new Sanitizer($data, $filters))->sanitize();
/* fallback will be termination, notice */

if (
  $REQUEST->paginationfrom != null && 
  $REQUEST->paginationto != null && 
  $REQUEST->queryarg != null
  ) {

/* further declaration of multiple use vars */
/* _request/queryarg can accept $ChatFilesData index starting from 0, $ChatFilesDataIdAsKeys indexed by id, search */
$queryarg = $REQUEST->queryarg;
$recordsperpage = ($REQUEST->recordsperpage != null ? $REQUEST->recordsperpage : $recordsperpage);

$db = new sqlitedb(
  pj($baseDir, $sqlitedb)
  );
$Init = new Init;
$Init->baseDir = $baseDir;
$Init->queryarg = $REQUEST->queryarg;
$Init->db = $db;
$InitData = $Init->API($REQUEST);
$AppData = $Init->AppData();

/* app instance */
$app = new App;

if ($InitData->IsEmpty) {
 /* query arg did not find requested file, no file exists all possible reason for isempty, index updates db from fs when this happens using generatesitemap\get , filenotfound may trigger ajax index refresh (prospect of file being intentionally deleted, will notify and return to hash with true page reload, wont update db from fs here */
 $ApiResponse = new stdClass;
 $ApiResponse->status = 'filenotfound';
 $ApiResponse->response = 'file not found';
 print json_encode($ApiResponse);
 exit; 
}

$app->ChatFilesData = $InitData->Data;
$app->ChatFilesDataIdAsKeys = $InitData->DataIdAsKeys;
$app->baseDir = $baseDir;
$app->SetChatFile($REQUEST->queryarg);

/* for api, cant default to first item in array, App\NoSelected does not return true on defaulting to first item in ChatFilesData , ChatFilesDataIdAsKeys */
if ($app->NoSelected === true) {
 $ApiResponse = new stdClass;
 $ApiResponse->status = 'filenotfound';
 $ApiResponse->response = 'no valid queryarg';
 print json_encode($ApiResponse);
 exit; 
}

$app->SetVerifiedRecipient( $app->Name );

$PaginationViability = $app->PaginationViability(
  $REQUEST->paginationfrom,
  $REQUEST->paginationto
  );
  
if ($PaginationViability->status === false) {
  $ApiResponse = new stdClass;
  $ApiResponse->response = $PaginationViability->response;
  $ApiResponse->status = 'eof';
  print json_encode($ApiResponse);
  exit;
}

$processlines = new processLines;
$processlines->vrecipient = $app->VerifiedRecipient;
$processlines->groupchat = $app->GroupChat;
$processlines->ChatFile = $app->ChatFile;
$processlines->dirpath = $app->DirPath;
$processlines->baseDir = $baseDir;
$processlines->PaginationFrom = $REQUEST->paginationfrom;
$processlines->PaginationTo = $REQUEST->paginationto;
$processlines->iterable = $app->ChatFileGenerator(
  $processlines->PaginationFrom,
  $processlines->PaginationTo
  );
$ApiResponse = new stdClass;
$ProcessLines = $processlines->Process();
/* $processlines->iterable->getReturn() reasonably available after processLines/Process* */
$ApiResponse->response = iter_to_array($ProcessLines);
$ApiResponse->paginationfrom = $app->NPaginationFrom;
$ApiResponse->paginationto = $app->NPaginationTo;
$ApiResponse->status = 'success';
print json_encode($ApiResponse);
exit;
}

/* no request */
  $ApiResponse = new stdClass;
  $ApiResponse->response = '';
  $ApiResponse->status = 'no request:'.json_encode($_REQUEST);
  print json_encode($ApiResponse);
?>