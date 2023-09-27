<?php

require_once $baseDir.'/vendor/autoload.php';
use function BenTools\IterableFunctions\iterable_to_array as iter_to_array;

$db = new sqlitedb(
  pj($baseDir, $sqlitedb)
  );
/* load app data */
$AppDataExecute = SelectOneConfig([
  'id' => 1
  ]);
$AppData = $AppDataExecute->status ?
iter_to_array($AppDataExecute->response)
:
[];
$AppDataNotEmpty = (count($AppData) > 0 ? 
                    true 
                    : 
                    false
                    );
/* load chat files if not loading through api */
if (!isset($api)) {
$ChatFilesDataExecute = $db->Select();
$ChatFilesDataSelectType = 'all';
$ChatFilesData = $ChatFilesDataExecute->status ?
iter_to_array($ChatFilesDataExecute->response)
:
[];
$ChatFilesDataNotEmpty = (count($ChatFilesData) > 0 ? 
                    true 
                    : 
                    false
                    );
$ChatFilesDataKeys = array_column(
  $ChatFilesData, 
  'id'
  );
$ChatFilesDataIdAsKeys = array_combine(
                        $ChatFilesDataKeys, 
                        $ChatFilesData
                        );
  
} else {
  
$ChatFilesDataExecute = $db->SelectOne([
  'id' => $queryarg
  ]);
$ChatFilesDataSelectType = 'one';
$ChatFilesData = $ChatFilesDataExecute->status ? 
iter_to_array($ChatFilesDataExecute->response) 
: 
[];
$ChatFilesDataNotEmpty = (count($ChatFilesData) > 0 ? 
  true 
  : 
  false
  );  

$ChatFilesDataKeys = array_column(
  $ChatFilesData, 
  'id'
  );
$ChatFilesDataIdAsKeys = array_combine(
                        $ChatFilesDataKeys, 
                        $ChatFilesData
                        );
    
}
