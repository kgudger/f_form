<?php
/**
* @file f_formpage.php
* Purpose: File Upload Form 
* Extends MainPage Class
*
* @author Keith Gudger
* @copyright  (c) 2019, Keith Gudger, all rights reserved
* @license    https://www.gnu.org/licenses/gpl-3.0.html
* @version    Release: 1.0
* @package    KSQD
*
* @note Has processData and showContent, 
* main and checkForm in MainPage class not overwritten.
* 
*/
require_once("/var/www/html/wp-content/plugins/f_form/includes/mainpage.php");
include_once "/var/www/html/includes/util.php";
require_once("/var/www/html/wp-content/plugins/f_form/includes/recaptchalib.php");
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
	$response = NULL;
	$reCaptcha = new ReCaptcha($this->secret);

    // Process the verified data here.
	$fname   = $this->formL->getValue("fname");
	$dirname = $this->formL->getValue("directory");
	// if submitted check response
	if ($_POST["g-recaptcha-response"]) {
	    $response = $reCaptcha->verifyResponse(
	        $_SERVER["REMOTE_ADDR"],
	        $_POST["g-recaptcha-response"]
	    );
	    if ($response != null && $response->success) {
//		print_r($_FILES);
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
			$newFileName = $fileNameCmps[0] . "_" . time() . '.' . $fileExtension;
			// check if file has one of the following extensions
			$allowedfileExtensions = array('wav', 'aiff', 'ogg', 'mp3', 'aac', 'wma', 'alac');
			if (in_array($fileExtension, $allowedfileExtensions))
			{
			// directory in which the uploaded file will be moved
				$uploadFileDir = "/./archive/$dirname";
				$dest_path = $uploadFileDir . $newFileName;
				try
				{
					$sftp = new SFTPConnection($this->sftpsftp,$this->sftpport);
					$sftp->login($this->sftpacct, $this->sftppwd);
					$sftp->uploadFile($fileTmpPath, $dest_path);
					$message ='File is successfully uploaded.';
					$message .= "<br>Upload path is <a href=$this->sftpurl/$dirname$newFileName'>$this->sftpurl/$dirname$newFileName'</a>";
				}
				catch (Exception $e)
				{
					$this->retstring = $e->getMessage() . "\n";
				}
/*				if(move_uploaded_file($fileTmpPath, $dest_path)) 
				}
*/
			} else {
				$message = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
			}
		} else {
			$message = 'There is some error in the file upload. Please check the following error.<br>';
			$message .= 'Error:' . $_FILES['uploadedFile']['error'];
		}
	    } else 
		$message = "Please check the reCaptcha box";
	} else 
		$message = "<p><font color='red'>Please check the reCaptcha box.</font></p>";
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
<script src="https://www.google.com/recaptcha/api.js"></script>
<div class="preamble" id="KSQD-preamble" role="article">
<h3>Please fill out all the fields</h3>
EOT;
	$this->retstring.= $this->formL->reportErrors();
//	echo $this->formL->start('POST', "", 'enctype="multipart/form-data" name="server_file_upload"');
	$this->retstring.= $this->formL->start('POST', "", 'name="server_file_upload" enctype="multipart/form-data"');
	$this->retstring.= $this->formL->makeFileInput("fname");
	$this->retstring.= $this->formL->formatonError('fname','File Name') . "<br><br>";
	$this->retstring.= $this->formL->makeSelect("directory",$tabData);
	$this->retstring.= $this->formL->formatonError('directory','Folder for upload') ."<br><br>" ;
$this->retstring.= <<<EOT
<br>
<input class="subbutton" type="submit" name="Submit" value="Upload File">
<br><br>
<div class="g-recaptcha" data-sitekey="6LcwJagUAAAAANWRDfITT9FdTquL6DVoZRMgO4Ta"></div>
</fieldset>
</form>
EOT;
$this->retstring.=$this->formL->finish();
return $this->retstring;
}
}
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
