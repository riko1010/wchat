<?php
error_reporting(0);

require 'vendor/autoload.php';
require 'assets-php/settings.php';

use Curl\Client;
use CurlDownloader\CurlDownloader;
define('LAZER_DATA_PATH', realpath(__DIR__).'/data/');
/* lazer database seems to not create db folder when nonexistent*/
if (!is_dir('data')) mkdir('data');
use Lazer\Classes\Database as Lazer;

$dlfilename = 'new-conversations';
$dlfileext = '.zip';
$dlfile = $dlfilename.$dlfileext;
$download = false;

/*dropbox webhook response*/
echo (isset($_GET['challenge']) ? $_GET['challenge'] : '');
    
$json = file_get_contents('php://input');

// Converts it into a PHP object
$wh = json_decode($json, true);



    if (count($wh["delta"]["users"]) == 1) {


// Use the get_headers() function to retrieve the headers of the file
$headers = get_headers($dropboxfolderuriaszipheader, 1);

// Check if the "Content-Length" header is present in the response
if (isset($headers["Content-Length"])) {
    // Store the value of the "Content-Length" header in the $filesize variable
    $filesize = max($headers["Content-Length"]);
    
    if ($filesize == 0) exit;

//check old filesize with new max file size


try{
    \Lazer\Classes\Helpers\Validate::table('whatsappchats')->exists();
} catch(\Lazer\Classes\LazerException $e){
    //Database doesn't exist
    Lazer::create('whatsappchats', array(
    'id' => 'integer',
    'oldsize' => 'string'
));
}


try {
$whatsappchatsdb = Lazer::table('whatsappchats')->find(1);
} catch (\Lazer\Classes\LazerException $e) {
  
}

echo $filesize.'-'.$whatsappchatsdb->oldsize.'_'.json_encode($wh);
if (!isset($whatsappchatsdb->oldsize) || empty($whatsappchatsdb->oldsize)) {

$row = Lazer::table('whatsappchats');
$row->oldsize = '0'; 
$row->save();
$download = true;
} else {

/* if download is false, size is equal, exit */
if ($download == false && isset($whatsappchatsdb->oldsize) && $whatsappchatsdb->oldsize == $filesize ) exit;

$download = true;

}

}

/* rename old dl file to autodelete folder if exists */
if (!is_dir('autodelete'))  mkdir('autodelete');
if (file_exists($dlfile)) rename($dlfile, 'autodelete/'.$dlfile);

if ($download == false) exit;
$browser = new Client();
$downloader = new CurlDownloader($browser);

/* dropbox folder url, served as zip */
$response = $downloader->download(
$dropboxfolderuriaszip, 
function ($dlfile) {

    return $GLOBALS["dlfile"];
});

if ($response->status == 200) {
	
$row = Lazer::table('whatsappchats')->find(1); //Edit row with ID 1

$row->oldsize = $filesize;
$row->save();

/* delete old dir, old-conversations
rename cur dir if exist to new-conversations
unzip
*/
if (is_dir('old-conversations')) {
    rename('old-conversations','autodelete');
}
if (is_dir('conversations')) {
    rename('conversations', 'old-conversations');
    mkdir('conversations');
} else {
  mkdir('conversations');
}

if (file_exists($dlfile)){
/* unzip using py */
/* extracts using pypython, call using http request, no output expected */
// returns standardized Response object no matter what
$unzip = $browser->get($unzippyURI);


}

    // 28,851,928 bytes downloaded in 20.041231 seconds
    echo number_format($response->info->size_download) . ' bytes downloaded in ' . $response->info->total_time . ' seconds';
}
    
    }
    
    
?>