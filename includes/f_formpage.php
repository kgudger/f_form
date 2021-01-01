<?php
/**
* @file f_formpage.php
* Purpose: File Upload Form 
* Extends MainPage Class
*
* @author Keith Gudger
* @copyright  (c) 2019, Keith Gudger, all rights reserved
* @license    https://www.gnu.org/licenses/gpl-3.0.html
* @version    Release: 1.1
* @package    KSQD
*
* @note Has processData and showContent, 
* main and checkForm in MainPage class not overwritten.
* 
*/
require_once("/var/www/html/kgwpt/wp-content/plugins/f_form/includes/mainpage.php");
include_once "/var/www/html/includes/util.php";
require_once("/var/www/html/kgwpt/wp-content/plugins/f_form/includes/recaptchalib.php");
/**
 * Child class of MainPage used for user preferrences page.
 *
 * Implements processData and showContent
 */

class fFormPage extends MainPage {

/**
 * Process the data and insert / modify database.
 *
 * @param $uid is user id passed by reference.
 */
function processData(&$uid) {
//	$response = NULL;
//	$reCaptcha = new ReCaptcha($this->secret);

    // Process the verified data here.
	$fname   = $this->formL->getValue("fname");
	$dirname = $this->formL->getValue("directory");
	$minutes = $this->formL->getValue("minutes");
	$seconds = $this->formL->getValue("seconds");
	$t_time  = $minutes*60 + $seconds; // total seconds
	// if submitted check response
/*	if ($_POST["g-recaptcha-response"]) {
	    $response = $reCaptcha->verifyResponse(
	        $_SERVER["REMOTE_ADDR"],
	        $_POST["g-recaptcha-response"]
	    );
	    if ($response != null && $response->success) {
//		print_r($_FILES);
*/
	if (1) { //(isset($_POST['recaptcha_response'])) {

    // Build POST request:
		$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
		$recaptcha_secret = $this->secret;
		$recaptcha_response = $_POST['recaptcha_response'];

    // Make and decode POST request:
		$recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
		$recaptcha = json_decode($recaptcha);

    // Take action based on the score returned:
		if (1) /*($recaptcha->score >= 0.5)*/ {
        // Verified - upload files if present
			if (isset($_FILES['fname']) && $_FILES['fname']['error'] === UPLOAD_ERR_OK)
			{
		// get details of the uploaded file
			$fileTmpPath = $_FILES['fname']['tmp_name'];
			$fileName = $_FILES['fname']['name'];
			$fileSize = $_FILES['fname']['size'];
			$fileType = $_FILES['fname']['type'];
			$fileNameCmps = explode(".", $fileName);
			$fileExtension = strtolower(end($fileNameCmps));
			// sanitize file-name
			$fileNameCmps[0] = str_replace('.', "", $fileNameCmps[0]);
			$newFileName = $fileNameCmps[0] . "_" . time() . '.' . $fileExtension;
			$newFileName = str_replace('"', "", $newFileName);
			$newFileName = str_replace("'", "", $newFileName);
			// check if file has one of the following extensions
			$allowedfileExtensions = array('wav', 'aiff', 'ogg', 'mp3', 'aac', 'wma', 'alac');
			if (in_array($fileExtension, $allowedfileExtensions))
			{
			// directory in which the uploaded file will be moved
				$uploadFileDir = "/./archive/$dirname";
				$dest_path = $uploadFileDir . $newFileName;
				try
				{
					$tfile = tempnam("/tmp", "SFT"); // file for sftp command
					$ufile = tempnam("/tmp", "UPL"); // file to move uploaded file to
					$ffile = tempnam("/tmp", "FFM"); // output file from ffmpeg strip
					$ffile .= "." . $fileExtension ;
//					echo "File Name " . $ffile . "<br>";
					if ($t_time <= 0) { // don't trim file, just move it
						move_uploaded_file($fileTmpPath,$ufile); // needed because temp file disappears when this php file stops.
					} else {  // trim file and get ready for upload
						shell_exec("/usr/bin/ffmpeg -i $fileTmpPath -ss $t_time -codec:a copy $ffile 2>&1");
						$ufile = $ffile ; // make upload file name = trimmed file
					}
					file_put_contents($tfile, "put $ufile \"$dest_path\""); // sftp command, quotes needed for files with spaces in name
					chmod($fileTmpPath, 0644);
					chmod($tfile, 0644);
					$f = socket_create(AF_UNIX, SOCK_DGRAM, 0); // create unix socket for IP communication
					$server_side_sock = "/tmp/server.sock";
					$len = strlen($tfile);
					$tmpret = socket_sendto($f, $tfile, $len, 0, $server_side_sock); // send the sftp file name
					socket_close($f);
					if ($tmpret) { // socket message sent
						$message ='File will be uploaded shortly.';
						$message .= "<br>Upload path is <a href='$this->sftpurl/$dirname$newFileName'>$this->sftpurl/$dirname$newFileName</a>";
					} else {
						$message = "Unsuccessful upload, sorry.";
					}
				}
				catch (Exception $e)
				{
					$this->retstring = $e->getMessage() . "\n";
				}
			} else {
				$message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
			}
		} else {
			$message = 'There is some error in the file upload. Please check the following error.<br>';
			$message .= 'Error:' . $_FILES['uploadedFile']['error'];
		}
      } else 
		$message = "reCaptcha Failed, sorry";
	} else 
		$message = "<p><font color='red'>reCaptcha Failed, sorry</font></p>";
	$this->retstring = $message . "<br>";
}

/**
 * Display the content of the page.
 *
 * @param $title is page title.
 * @param $uid is user id passed by reference.
 */
function showContent($title, &$uid) {

$this->retstring.= "<br>";
// Put HTML after the closing PHP tag
$htmlCont = file_get_contents("https://ksqd.info:444");
$DOM = new DOMDocument();
$DOM->loadHTML($htmlCont);
//$tables = $DOM->getElementsbyTagName('table');
$rows = $DOM->getElementsbyTagName('tr');
$tabData = array("" => "");
$i = 0;
foreach ($rows as $row) {
	if ($i > 1) { // throw out first 2 rows
		$cols = $row->getElementsByTagName('td');
		$tdata = trim($cols[1]->textContent);
		$tabData[$tdata] = $tdata;
	}
	$i++ ;
}
//print_r($tabData);
$this->retstring.= <<<EOT
<script src="https://www.google.com/recaptcha/api.js?render=6Lejg8cUAAAAAOa1YnxyH5OlD8ylW5jhD-CfRPaW"></script>
<div class="preamble" id="KSQD-preamble" role="article">
<h3 id="serv_head">Please fill out all the fields</h3>
EOT;
	$this->retstring.= $this->formL->reportErrors();
//	echo $this->formL->start('POST', "", 'enctype="multipart/form-data" name="server_file_upload"');
	$this->retstring.= $this->formL->start('POST', "", 'name="server_file_upload" enctype="multipart/form-data" id="server_form"');
	$this->retstring.= $this->formL->makeFileInput("fname");
	$this->retstring.= $this->formL->formatonError('fname','File Name') . "<br><br>";
	$this->retstring.= $this->formL->makeSelect("directory",$tabData);
	$this->retstring.= $this->formL->formatonError('directory','Folder for upload') ."<br><br>" ;
	$this->retstring.= "<strong>If you would like to trim time from the beginning of this file, enter the minutes and seconds to trim below. Otherwise leave set to zero (0).</strong><br>";
	$this->retstring.= $this->formL->makeNumberInput('minutes',"0","","0");
	$this->retstring.= $this->formL->formatonError('minutes','Minutes') ."<br><br>" ;
	$this->retstring.= $this->formL->makeNumberInput('seconds',"0","","0");
	$this->retstring.= $this->formL->formatonError('seconds','Seconds') ."<br><br>" ;
$this->retstring.= <<<EOT
<br>
<input class="subbutton" type="submit" name="Submit" value="Upload File" id="serv_sub">
<br><br>
<div id="serv_foot">Please be patient - depending on your internet speed and file size it can take a long time to upload.<br>
Please do not close the window.
<input type="hidden" name="recaptcha_response" id="recaptchaResponse">
</fieldset>
</form>
<script>
    grecaptcha.ready(function () {
        grecaptcha.execute('6Lc2x20UAAAAAMdFBs4QS72nnNh1Smn6hTtfU9pl', { action: 'contact' }).then(function (token) {
            var recaptchaResponse = document.getElementById('recaptchaResponse');
            recaptchaResponse.value = token;
        });
    });
    var f_form = document.getElementById('server_form');
    f_form.onsubmit = f_sub;
    function f_sub(event) {
	var sfoot = document.getElementById("serv_foot");
	sfoot.innerHTML = 
			"<strong><font color='red'>Please wait for the file to upload.</font></strong>";
        };
</script>
EOT;
$this->retstring.=$this->formL->finish();
return $this->retstring;
}
}
// <div id = "recap" class="g-recaptcha" data-sitekey="6LcwJagUAAAAANWRDfITT9FdTquL6DVoZRMgO4Ta"></div>

class SFTPConnection
{
    private $connection;
    private $sftp;

    public function __construct($host, $port=22)
    {
        $this->connection = @ssh2_connect($host, $port);
        if (! $this->connection)
            throw new Exception("Could not connect to $host on port $port.");
    }

    public function login($username, $password)
    {
        if (! @ssh2_auth_password($this->connection, $username, $password))
            throw new Exception("Could not authenticate with username $username " . "and password $password.");
        $this->sftp = @ssh2_sftp($this->connection);
        if (! $this->sftp)
            throw new Exception("Could not initialize SFTP subsystem.");
    }

    public function uploadFile($local_file, $remote_file)
    {
        $sftp = $this->sftp;
//		$fp = fopen("ssh2.sftp://" . intval($sftp) . $remoteFile, "r");
//      changed to intval to fix php bug
        $stream = @fopen("ssh2.sftp://" . intval($sftp) . $remote_file, 'w');
        if (! $stream)
            throw new Exception("Could not open file: $remote_file");
        $data_to_send = @file_get_contents($local_file);
        if ($data_to_send === false)
            throw new Exception("Could not open local file: $local_file.");
        if (@fwrite($stream, $data_to_send) === false)
            throw new Exception("Could not send data from file: $local_file.");
        @fclose($stream);
    }
    
        function scanFilesystem($remote_file) {
              $sftp = $this->sftp;
            $dir = "ssh2.sftp://$sftp$remote_file";  
              $tempArray = array();
            $handle = opendir($dir);
          // List all the files
            while (false !== ($file = readdir($handle))) {
            if (substr("$file", 0, 1) != "."){
              if(is_dir($file)){
//                $tempArray[$file] = $this->scanFilesystem("$dir/$file");
               } else {
                 $tempArray[]=$file;
               }
             }
            }
           closedir($handle);
          return $tempArray;
        }    

    public function receiveFile($remote_file, $local_file)
    {
        $sftp = $this->sftp;
        $stream = @fopen("ssh2.sftp://$sftp$remote_file", 'r');
        if (! $stream)
            throw new Exception("Could not open file: $remote_file");
        $contents = fread($stream, filesize("ssh2.sftp://$sftp$remote_file"));            
        file_put_contents ($local_file, $contents);
        @fclose($stream);
    }
        
    public function deleteFile($remote_file){
      $sftp = $this->sftp;
      unlink("ssh2.sftp://$sftp$remote_file");
    }
}
?>
