<?php
namespace WChat;
class App {
  
public $ChatFile; 
public $DirPath;
public $GroupChat;
public $Selected;
public $SelectedId;
public $SelectedChatFile;
public bool $NoSelected = true;
public bool $IndexSelected = false;
public bool $CheckLegacy = false;
public $Name;
public $baseDir;
public bool $Paginateable = false;
public object $PaginationNav;
public $RealPageURI = null;
public $BasePageURI = null;

public function __construct(
  \Psr\Container\ContainerInterface $c,
  ) {
$Config = $c->get("WChat\Config");
/* set chat file */
$c->call([$this, 'SetChatFile']);

if ($Config->InitType != 'API') {
/* set page uri */
$this->RealPageURI = \pj(
  $Config->SiteUrl,
  $this->SelectedChatFile['search'],
  $Config->PaginationFrom,
  );
  
$this->BasePageURI = \pj(
  $Config->SiteUrl,
  $this->SelectedChatFile['search'],
  );  
}
  
}

public function SetChatFile(
  \Psr\Container\ContainerInterface $c,
  ) {
/* SetChatFile on index uses $Request/queryarg , on api uses $Request/queryarg - queryarg is Id, search (may implemeny fuzzy) */
$Config = $c->get('WChat\Config');
$Request = $c->get('WChat\Request');
$Init = $c->get('WChat\Init');

if ($Request->queryarg != null) {
/* recursive array search case , ras for search field */
$ras = \ras($Request->queryarg, array_column($Init->Data->Data, 'search'));
/* ras for id field if not found */
$ras = ($ras === false ) ? 
\ras($Request->queryarg, array_column($Init->Data->Data, 'id')) : $ras ;

  (($ras === false) ? 
  ([ $this->CheckLegacy, $this->NoSelected, $this->Selected ] = [ true, true, 0 ]) : 
      ([ $this->NoSelected, $this->Selected ] = [ false, $ras ]) );

/* legacy url */
(!$this->CheckLegacy ?: $this->CheckLegacyChatFileQuery($Init, $Request)); 
} else {
/* ChatFilesData array index, default to first item in array, 0 
$this->NoSelected should be true but index is a selection of the index chat, unless index offers something else which is reasonable for a social platform. */ 
$this->NoSelected = false;
$this->IndexSelected = true;
$this->Selected = 0;

}

if ($this->NoSelected && $Config->InitType == 'API') return 'Chat ID not found.. ';

$this->SelectedChatFile = $Init->Data->Data[$this->Selected];
$this->SelectedId = $this->SelectedChatFile['id'];

try{
$this->ChatFile = \Symfony\Component\Filesystem\Path::join(
  $Config->baseDir,
  $this->SelectedChatFile['filepath']
  );
} catch(\Exception|\Throwable $e) {
$this->ChatFile = null;  
}

$this->Name = $this->SelectedChatFile['name'];
$this->DirPath = $this->SelectedChatFile['dirpath'];
$this->GroupChat = $this->SelectedChatFile['groupchat'];

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
    $ras = \ras($query, $cflcsearch);
    
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

public function ChatFileGenerator(
 Config $Config,
 \Psr\Container\ContainerInterface $c,
  ){
$Request = $c->get('WChat\Request');

$sfd = new \SplFileObject($this->ChatFile);
if (!$sfd) return 'error: could not open chat file';

$filearray = [];
$B = null;
$holdB = null;
$TerminationType = 'done';

$P = '/[0-3]?[0-9]\/[0-3]?[0-9]\/(?:[0-9]{2})?[0-9]{2},/';
$from = $ofrom = $Config->PaginationFrom;
/* fixed from */
define('FixedFrom', $from);
$to = $oto = ($Config->PaginationTo == 0 ? ($from + $Config->recordsperpage) : $Config->PaginationTo );
$sfd->seek($from);
if (!$sfd->valid()) {
$this->Paginateable = false;  
$TerminationType = 'notPaginateable';
} else {
$this->Paginateable = true;
}

/* check next pagination viability */
/* point to $to+1 */
$sfd->seek($to + 1);
/* check if valid */
if (!$sfd->valid()) {
$this->Paginateable = false;  
} else {
$this->Paginateable = true;
}
/* return pointer to $from */
$sfd->seek($from);

do {
  $B = $sfd->current();
  yield $B;
  $sfd->next();
   
  if ($sfd->valid() === false) {
     /* next line not valid, yield holdbuffer containing all unidentified buffer  */
     $this->Paginateable = false;
     $TerminationType = 'notPaginateable';
     $from++;
     break;
   }

$from++;
} while ($from < $to && $sfd->valid());

/* next Pagination */
if (!$this->Paginateable) {
$Config->NPaginationFrom = 0;
$Config->NPaginationTo = 0;
} else {
$Config->NPaginationFrom = $to;
$Config->NPaginationTo = ($to + $Config->recordsperpage);
}
/* prev Pagination round down to nearest Config/recordsperpage */
$PPaginationFromCalc = $ofrom - $Config->recordsperpage;
$PPaginationFrom = ($PPaginationFromCalc < ($Config->recordsperpage / 2) ? (floor(
  ($PPaginationFromCalc) / $Config->recordsperpage) * $Config->recordsperpage) : $PPaginationFromCalc );
$Config->PPaginationFrom = (($PPaginationFrom >= 0) ? $PPaginationFrom : 0 );
$Config->PPaginationTo = $ofrom;

/* update paginations */
$Config->PaginationFrom = $ofrom;
$Config->PaginationTo = $to;

return (object) [
  'PaginationFrom' => $from,
  'PaginationTo' => $oto,
  'TolerantPaginationTo' => $to,
  'TerminationType' => $TerminationType
  ];
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
$to = $Config->PPaginationTo;
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

$sfd = new \SplFileObject($this->ChatFile);
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
  \Psr\Container\ContainerInterface $c,
  ){
$Config = $c->get("WChat\Config");

if ($Config->recordsperpage >= $this->SelectedChatFile['linescount'] ) {
  $this->Paginateable = false;
}

$CurrentPagination = floor(
  ($Config->PaginationFrom / $Config->recordsperpage + 1));
  
$PrevPaginationStatus = (
  $Config->PaginationFrom == 0 ? 
  'disabled' : ''
  );
$NextPaginationStatus = (
  $this->Paginateable === false ?
    'disabled' : ''
    );

$PrevPaginationHref = \pj(
  $Config->SiteUrl,
  $this->SelectedChatFile['search'],
  $Config->PPaginationFrom,
  );
$FirstPaginationHref = \pj(
  $Config->SiteUrl,
  $this->SelectedChatFile['search'],
  );
$NextPaginationHref = \pj(
  $Config->SiteUrl,
  $this->SelectedChatFile['search'],
  ($Config->NPaginationFrom ?? ''),
  );
$LastPaginationHref = \pj(
  $Config->SiteUrl,
  $this->SelectedChatFile['search'],
  ($this->SelectedChatFile['linescount'] - $Config->recordsperpage));

$PaginationStep = ($Config->recordsperpage < $this->SelectedChatFile['linescount'] ? 
  $Config->recordsperpage : $this->SelectedChatFile['linescount']);
//lines count
//records per page
//249/100 rounded down = 3.
$PaginationListR = range(0, $this->SelectedChatFile['linescount'], $PaginationStep
  );

$PaginationList = (
  $Config->recordsperpage >= $this->SelectedChatFile['linescount'] ?
    [] 
    : 
    array_map(
    fn($CPaginationList) =>
     ['label' => floor(($CPaginationList / $Config->recordsperpage) + 1), 'href'=> \pj(
  $Config->SiteUrl,
  $this->SelectedChatFile['search'],
  $CPaginationList)]
    , $PaginationListR)
    );

return (object) [
  'current' => $CurrentPagination,
  'prev' => [
    'status' => $PrevPaginationStatus,
    'href' => $PrevPaginationHref,
    'first' => $FirstPaginationHref,
    ],
  'next' => [
    'status' => $NextPaginationStatus,
    'href' => $NextPaginationHref,
    'last'=> $LastPaginationHref,
    ],
  'paginationlist' => $PaginationList,
  ];
}

public function PageTitle(){
return 'Whatsapp Chat '.($this->NoSelected ? '' : (isset($this->Name) && $this->Name != '' ? ('with '.$this->Name) : ''));  
 }

public function Menu(
  Init $Init,
  \Psr\Container\ContainerInterface $c,
  ){
  /* build menu */
$Config = $c->get("WChat\Config");
$menu = '';
$ChatFilesData = $Init->Data->Data;

foreach ($ChatFilesData as $SelectList){
$selected = (isset($selected) && $selected == 'selected' ? '' : ( $SelectList['id'] == $this->SelectedId ? 'selected' : ''));
$nextchatfilesList = (null !== ( $nextchatfilesList = next($ChatFilesData))) ? $nextchatfilesList : false;
$menu .= ($SelectList['bfc'] == 1 ? ('<optgroup label="'.$SelectList['dirname'].'">') : '');
$menu .= '
    <option '.$selected.' value="'.\pj($Config->SiteUrl, $SelectList['search'],).'">'.$SelectList['name'].' '.$SelectList['bfc'].'</option>
  ';
$menu .= ($nextchatfilesList !== false  ? (($SelectList['dirname'] === $nextchatfilesList['dirname']) ? '' : '</optgroup>') : '</optgroup>');

}
return $menu;

}

}