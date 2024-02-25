<?php
namespace WChat;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Insert;
class Database {
  
private $adapter;
private $sql;

function __construct(Config $Config){
  
$this->adapter = new \Laminas\Db\Adapter\Adapter([
    'driver'   => 'Pdo_Sqlite',
    'database' => $Config->sqlitedb,
]);
$this->sql = new \Laminas\Db\Sql\Sql($this->adapter);

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
  'name' TEXT NOT NULL, 
  'sync' INTEGER DEFAULT 1 , 
  'synctime' INTEGER NULL default (strftime('%s','now')), 
  'filepath' TEXT NOT NULL, 
  'url' TEXT NOT NULL, 
  'archivedurl' TEXT NULL, 
  'linescount' INTEGER NULL,
  'mtimeorhash' TEXT NOT NULL,
  'users_id' INTEGER NULL,
  'dateadded' INT NOT NULL default current_timestamp,
  'annotation' INTEGER NULL
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

$sql = "CREATE TABLE IF NOT EXISTS 'users' (
  'id' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
  'email' TEXT NOT NULL,
  'password' TEXT NOT NULL
  )";

$statement = $this->adapter->query($sql);
$statement->execute();
} 

public function InsertOrUpdate(
  $Table,
  $ColumnValuesArray, 
  $UpdateWhereArray,
  $Target = 'insertorupdate',
  ){
/* Targets = insertorupdate|insert|update
simplify logic asap
*/  
$Target = strtolower($Target);
$TType = null;
if ($Target == 'insertorupdate' || $Target == 'update') {
$SelectExecute = $this->SelectOne(
  $Table,
  $UpdateWhereArray
  );
$Select = ($SelectExecute->status ? \BenTools\IterableFunctions\iterable_to_array($SelectExecute->response) : []);
$TType = (count($Select) == 1 ? 'update' : null);
if ($TType == 'update') {
$InsertOrUpdate = $this->sql->update($Table);
$InsertOrUpdate->where($UpdateWhereArray);
$InsertOrUpdate->set($ColumnValuesArray);  
}
}

if (($Target == 'insertorupdate' && $TType !== 'update') || $Target == 'insert'){
$InsertOrUpdate = $this->sql->insert($Table);  
$InsertOrUpdate->values($ColumnValuesArray);  
}
try {
$statement = $this->sql->prepareStatementForSqlObject($InsertOrUpdate);
$results = $statement->execute();  
} catch (\Exception|\Throwable $e) {
  return (object) [
    'status' => false,
    'type' => $TType,
    'response' => 'Insert or Update records failed:'.$e->getMessage()
    ];
}
/* idk if necessary, may verify, transactions, rollbacks, reasonably implement operation integrity, laminas-db doesnt expose this at its docs surface but a qestions shows possible */
return (object) [
    'status' => true,
    'type' => $TType,
    'response' => $results
    ];
}

public function Select(
  $Table, 
  $SelectWhereArray, 
  array|string $Order = null
  ){
$selectall = $this->sql->select();
$selectall->from($Table);
if ($SelectWhereArray != null) {
$selectall->where($SelectWhereArray);
}
if (null != $Order) {
  $selectall->order($Order);
}
try {
$statement = $this->sql->prepareStatementForSqlObject($selectall);
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