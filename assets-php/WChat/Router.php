<?php
namespace WChat;
class Router {
  
public $httpMethod;
public $httpURI;

public function Info ( $Dispatcher,) {
  $Info = $Dispatcher->dispatch($this->httpMethod, $this->httpURI);
  return (object) [
  'Dispatcher' => $Info[0],
  'Handler' => (isset($Info[1]) ? $Info[1] : null),
  'RequestRaw' => $Info[2],
  ];
}

public function RelativeURI(
  $uriRequest, 
  $uriSelf,
  ) {
// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uriRequest, '?')) {
$uriRequest = substr($uriRequest, 0, $pos);
}
$uriRequest = rawurldecode($uriRequest);
$uriSelf = \Symfony\Component\Filesystem\Path::getDirectory($uriSelf);
$RelativeURI = \Symfony\Component\Filesystem\Path::makeRelative($uriRequest, $uriSelf);
$RelativeURI = (substr($RelativeURI, 0) == '/' ? $RelativeURI : '/'.$RelativeURI);
return $RelativeURI; 
}

}
