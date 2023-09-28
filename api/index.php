<?php 
//error_reporting(0);
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
    'queryarg' => $_REQUEST['queryarg'],
    'pagination' => $_REQUEST['pagination']
];

$filters = [
    'queryarg' => 'trim|empty_string_to_null|strip_tags|escape',
    'pagination' => 'trim|empty_string_to_null|strip_tags|escape'
];

$REQUEST = (object) (new Sanitizer($data, $filters))->sanitize();
/* fallback will be termination, notice */

if (
  $REQUEST->pagination != null && 
  $REQUEST->queryarg != null
  ) {

/* further declaration of multiple use vars */
/* _request/queryarg can accept $ChatFilesData index starting from 0, $ChatFilesDataIdAsKeys indexed by id, search */
$queryarg = $REQUEST->queryarg;
$pagination = $REQUEST->pagination;
$ApiResponse = new stdClass;

$db = new sqlitedb(
  pj($baseDir, $sqlitedb)
  );
$Init = new Init;
$Init->baseDir = $baseDir;
$Init->queryarg = $REQUEST->queryarg;
$Init->db = $db;
$InitData = $Init->API();
$AppData = $Init->AppData();

/* app instance */
$app = new App;

if ($InitData->IsEmpty) {
 /* query arg did not find requested file, no file exists all possible reason for isempty, index updates db from fs when this happens using generatesitemap\get , filenotfound may trigger ajax index refresh (prospect of file being intentionally deleted, will notify and return to hash with true page reload, wont update db from fs here */
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
 $ApiResponse->status = 'filenotfound';
 $ApiResponse->response = 'no valid queryarg';
 print json_encode($ApiResponse);
 exit; 
}

$app->SetVerifiedRecipient( $app->Name );

$PaginationViability = $app->PaginationViability(
  $REQUEST->pagination
  );
  
if ($PaginationViability->status === false) {
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
$processlines->spagination = $pagination;
$processlines->iterable = $app->ChatFileGenerator(
  $processlines->spagination 
  );
  
$ApiResponse->response = $processlines->Process();
/* $processlines->iterable->getReturn() reasonably available after processLines/Process* */
$ApiResponse->response = iter_to_array($ApiResponse->response);
$ApiResponse->pagination = $processlines->iterable->getReturn();
$ApiResponse->status = 'success';
print json_encode($ApiResponse);
exit;
}

/* no request */
  $ApiResponse->response = '';
  $ApiResponse->status = 'no request:'.json_encode($_REQUEST);
  print json_encode($ApiResponse);
?>