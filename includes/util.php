<?php
/**
* @file util.php
* utility functions
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
 *	Formats and prints table data for row
 *	@param row is db row data
 *	@param instring is name of column in db row data
 *  @param $format is 0=normal, 1=date, 2=time.
 */
function tdw($row, $instring, $format=0) {
	switch ($format) {
		case 0:
			echo "<td class=cent>" . $row[$instring] . "</td>";
			break;
		case 1:
			echo "<td class=cent>" . change_date($row[$instring]) .
				 "</td>";
			break;
		case 2:
			echo "<td class=cent>" . change_time($row[$instring]) .
				 "</td>";
			break;
	}
}

/**
 *	Formats and returns date as Mon, 00, Year
 *	@param $date is input date
 *	@return string in correct format
 */
function change_date($date) {
	return (date("l F jS, Y",strtotime($date)));
}

/**
 *	Formats and returns time as 01:01 pm
 *	@param $time is input time
 *	@return string in correct format
 */
function change_time($time) {
	return (date("g:i a",strtotime($time)));
}
/**
 *	Validates field data from form, leaving current data
 *	in form value field if it's OK.
 *	@param $field, passed by reference is checked for data
 *	@param $fname is field name in $_POST
 *	@return either the name of the failing field or nothing.
 */
function validate(&$field,$fname) {
    $field = trim($_POST["$fname"]);
    if ( ($field == "") || ($field[0] == "*" ) ) {
        $field = "*Please do not leave this field blank.";
        return $fname;
    }
    else
        return "";
}

/**
 * Gets real user id from session variable.
 *
 * @param $suid is session uid from SESSION variable
 * @param $rid is random number from SESSION variable
 * @return the real user id.
 */
function getReadID($uid, $rid) {
	$rid = $rid >> 1 ; // divide by 2
	for ($j = 0; $j < 4; $j++ ) {
		(($j % 2) == 0) ? $uid+= $rid : $uid-= $rid ;
		$uid = $uid >> 2 ; // divide by 2
	}
	return $uid ;
}

/**
 * Gets real user id from session variable.
 *
 * @param $suid is session uid from SESSION variable
 * (passed by reference)
 * @return the real random number used
 */
function setReadID(&$uid) {
	$rnum = rand(20, 200); // random number 
	$rnum2 = $rnum >> 1 ; // divide by 2
	for ($j = 0; $j < 4; $j++ ) {
		$uid = $uid << 2 ; // multiply by 2
		(($j % 2) == 0) ? $uid+= $rnum2 : $uid-= $rnum2 ;
	}
	return $rnum ;
}

