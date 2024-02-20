<?php
/* functions */
use SoftCreatR\MimeDetector\MimeDetectorException;
use samdark\sitemap\Index;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use function BenTools\IterableFunctions\iterable_to_array as iter_to_array;
use Psr\Container\ContainerInterface;

function ntfy($message){
  $curl = new \Curl\Curl;
  $curl->post('https://ntfy.sh/fuufuf', [
    'message' => $message
    ]);
}

function pj(...$paths){
  return \Symfony\Component\Filesystem\Path::join(...$paths);
}

function nonull($var) {
  return ((isset($var)) ? $var : false );
}

function getFilesize(
  $filesize, 
  $mode = null,
  ) {
  if ($mode == null) {
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

/* initializations */