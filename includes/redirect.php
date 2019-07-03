<?php
/**
* @file redirect.php
* Purpose: redirect function 
*
* @author Keith Gudger
* @copyright  (c) 2014, Keith Gudger, all rights reserved
* @license    http://opensource.org/licenses/BSD-2-Clause
* @version    Release: 1.0
* @package    Volunteer Now!
*
* @note CIS-165PH  Final Project
*/

/**
 *	Sets up html header function to redirect form
 *	@param $url, url to redirect to
 */
function redirect($url) {
    $url = trim($url);
    $absURL = "Location: ";
    if (substr($url, 0, 1) == "/") {
        $absURL .= "http://".$_SERVER['HTTP_HOST'];
    } elseif (strtolower(substr($url, 0, 7)) != "http://") {
        $absURL .= "http://".$_SERVER['HTTP_HOST'];
        $absURL .= dirname($_SERVER['PHP_SELF'])."/";
    }
    $absURL .= $url;
    header($absURL);
    // Make sure nothing else happens
    die("Could not redirect");
}
?>
