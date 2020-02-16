<?php
/*
Plugin Name: f_form
Description: Upload file form for program hosts with variable send to email.
Version:     1.0
Author:      Keith Gudger
Author URI:  http://www.github.com/kgudger
License:     GPL3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

defined( 'ABSPATH' ) or die( 'Ah ah ah, you didn\'t say the magic word' );
add_shortcode('f_form', 'f_form_fun');
function f_form_fun() {
/**
 * dbstart.php opens the database and gets the user variables
 */
require_once("/var/www/html/includes/dbstart.php");

include_once("includes/f_formpage.php");

/**
 * The checkArray defines what checkForm does so you don't
 * have to overwrite it in the derived class. */

$checkArray = array(
//	array("isEmpty","fname", "Please select a file."),
	array("isEmpty","directory", "Please select a destination folder.")
);

/// a new instance of the derived class (from MainPage)
$fform = new fFormPage($db,$sessvar,$checkArray,$secret3a,$sftpacct,$sftppwd,$sftpurl,$sftpsftp,$sftpport) ;
/// and ... start it up!  
return $fform->main("File Upload Form", $uid, "", "");
/**
 * There are 2 choices for redirection dependent on the sessvar
 * above which one gets taken.
 * For this page ... */
}
