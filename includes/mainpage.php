<?php
/**
 * @file mainpage.php
 * MainPage class for handling most page duties.
 *
 * @author Keith Gudger
 * @copyright  (c) 2019, Keith Gudger, all rights reserved
 * @license    http://opensource.org/licenses/BSD-2-Clause
 * @version    Release: 1.0
 * @package    Contact form for hosts
 *
 */

include_once "/var/www/html/wp-content/plugins/f_form/includes/redirect.php";
include_once "/var/www/html/wp-content/plugins/f_form/includes/formlib.php";
include_once "/var/www/html/includes/util.php";

/**
 * Parent class used for all form pages.
 *
 * Implements main and checkForm
 * provides template for processData and showContent
 */
class MainPage {
    /*--- private variables ---*/
    protected $formL;	//!< form from library formlib
	protected $sessnp; 	//!<sets as non-profit or individual?
	protected $db;		//!< PDO data base object
	protected $checkArray; //!<check array for checkForm.
	protected $secret;     //!< secret for recaptcha
	protected $sftpacct;   //!< sftp server account
	protected $sftppwd;    //!< sftp server password
	protected $sftpurl;    //!< sftp read url
	protected $sftpsftp;   //!< sftp sftp url
	protected $sftpport;   //!< sftp sftp port
	protected $retstring;  //!< return string for wordpress

    /**
     * Constructor.
     *
     * @param $db is the PDO database object
	 * @param $seesnp is yes = non-profit, no = individual account
	 * @param @checkArray tells checkForm what and how to check form
     */
    function __construct($db = "", $sessnp="no", $checkArray="", $secret="", $sftpacct="",$sftppwd="", $sftpurl="",$sftpsftp="",$sftpport) {
	$this->db = $db ;
	$this->sessnp = $sessnp;
	$this->secret = $secret;
	$this->sftpacct= $sftpacct;
	$this->sftppwd = $sftppwd ;
	$this->sftpurl = $sftpurl ;
	$this->sftpsftp = $sftpsftp ;
	$this->sftpport = $sftpport ;
   	$this->formL = new FormLib("error") ;
	$this->checkArray = $checkArray;
	$this->retstring = "" ;
	if ( isset( $_COOKIE['host_email']) ) {
		$this->cookie_key = $_COOKIE['host_email'];
	} else {
		$this->cookie_key = 'mark@ksqd.org';
	}
    }

    /**
     * Handles the processing of the whole page.
     *
     * @param $title is the title of the page.
     * @param $normRedirect is the usual redirect.
     * @param $altRedirect is the alternate redirect.
     */
	function main($title = "", &$uid, $normRedirect = "",
					$altRedirect = "") {
	
		if (isset($_POST["Submit"])) {
    	    $this->checkForm();
    	    if (!($this->formL->iserror())) { // data is OK
				$this->processData($uid);
    		    if (!($this->formL->iserror())) { // data is OK
					if ( ($this->sessnp) == "yes" ) { // for non-profit
			            if (!empty($altRedirect)) 
							redirect($altRedirect);
					} else {						// for individual
			            if (!empty($normRedirect)) 
				            redirect($normRedirect);
					}
				}
			}
		}
//    	include "includes/header.php";	// same header for every page
    	return $this->showContent($title, $uid);
//    	include "includes/footer.php";  // same footer for every page
	}

    /**
     * Form Checking using checkArray.
     *
     */
	function checkForm() {
		if (!empty($this->checkArray)) {
			foreach ($this->checkArray as $checkval) {
				$this->formL->{$checkval[0]}($checkval[1],$checkval[2]);
			}
		}
	}

    /**
     * Processes the db requests (if necessary, overwritten).
     *
	 * @param $uid passed by reference for database
     */
	function processData(&$uid) {
	}

    /**
     * displays the actual page (always overwritten).
     *
	 * @param $title is page title.
	 * @param $uid passed by reference for database
     */
	function showContent($title, &$uid) {
	}
}
?>
