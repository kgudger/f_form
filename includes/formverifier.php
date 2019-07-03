<?php
/**
 * @file formverifier.php
 * FormVerifier class for verifying form data.
 *
 * @author Ed Parrish
 * @author Keith Gudger
 * @copyright  (c) 2014, Keith Gudger, all rights reserved
 * @license    http://opensource.org/licenses/BSD-2-Clause
 * @version    Release: 1.0
 * @package    Volunteer Now!
 */

/**
 * Parent class for formlib implements error functions.
 *
 */
class FormVerifier {
    /*--- private variables ---*/
    private $errorList = array(); //! array of errors
    private $cssClass;			  //! css class for error display
	protected $textSize = 35;	  //! Input field size maximum
	/* for the future, I would like to add methods to set this
	 * value to different sizes.  It might be good to have a way
	 * to set it for each field, too. */

    /*--- General purpose functions ---*/

    /**
     * Constructor
     *
     * @param $cssClass The CSS class name for errors.
     */
    function __construct($cssClass = "error") {
        $this->cssClass = $cssClass;
    }

    /**
     * Returns the value of a form field (name).
     *
     * @param $field The form field to get the value from.
     * @param $defaultVal The initial value for a form control.
	 * @return value or defaultVal
     */
    function getValue($field, $defaultVal = "") {
        if (isset($_REQUEST[$field])) {
			if (is_array($_REQUEST[$field])) {
            	foreach ($_REQUEST[$field] as $item) 
					$item = trim($item);
				return $_REQUEST[$field];
			} else
				return trim($_REQUEST[$field]);
		}
        return $defaultVal;
    }

    /*--- Error tracking and reporting functions ---*/

    /**
     * Add errors to the error list
     *
     * @param $field The form field where the error occurred.
     * @param $value The value of the form field with the error.
     * @param $msg The error message presented to the user.
     */
    function addError($field, $value, $msg) {
        if (empty($this->errorList[$field])) {
            $this->errorList[$field] = array(
                "field" => $field,
                "value" => $value,
                "msg" => $msg);
        } else { // allow for multiple errors
            $errArray = $this->errorList[$field];
            // Do not add if a duplicate message
            if ($errArray["msg"] != $msg) {
                $this->errorList[] = array(
                    "field" => $field,
                    "value" => $value,
                    "msg" => $msg);
             }
        }
    }

    /**
     * Returns true if there are any error on the list, 
	 * otherwise returns false.
	 *
	 * @return true or false if errorlist exists.
     */
    function isError() {
        if ($this->errorList) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Returns a list of messages for the specified field.
     *
     * @param $field The form field to check for error.
     * @return All the error messages for $field as an array.
     */
    function getErrorMessages($field) {
        $messages = array();
        foreach ($this->errorList as $err) {
            if ($err["field"] == $field) {
                $messages[] = $err["msg"];
            }
        }
        return $messages;
    }

    /**
     * Reset the error list to an empty list.
     */
    function resetErrorList() {
        $this->errorList = array();
    }

    /**
     * Highlight $formElement with a span tag if $field has an error.
     *
     * @param $field The form field to check for error.
     * @param $formElement The HTML code to display.
	 * @return html with correct span tag.
     */
    function formatOnError($field, $formElement) {
        $html = $formElement;
        if (isset($this->errorList[$field])) {
            $html = "<span class=\"$this->cssClass\">";
            $html .= $formElement.'</span>';
        }
        return $html;
    }

    /**
     * Return the first error message in a span tag if there is an
     * error or $formElement if there is no error.
     *
     * @param $field The form field to check for error.
     * @param $formElement The HTML to display when there is no error.
	 * @return html with error message.
     */
    function showMessageOnError($field, $formElement = "") {
        $html = $formElement;
        if (isset($this->errorList[$field])) {
            $errArray = $this->errorList[$field];
            $html = "<span class=\"$this->cssClass\">";
            $html .= $errArray["msg"].'</span>';
        }
        return $html;
    }

    /**
     * Returns a default HTML message listing the errors found.
	 *
	 * @return html error header text
     */
    function reportErrors() {
        $html = "";
        if ($this->isError()) {
            $html = "<strong>We found some error(s).</strong>";
            $html .= "<br>Please try again after making these changes:";
            $html .= "<ul>";
            foreach ($this->errorList as $err) {
                $html .= '<li>'.$err['msg']."</li>\n";
            }
            $html .= "</ul><br>";
        }
        return $html;
    }

    /**
     * Adds a $msg to the list if the form control $field is empty.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
	 * @return true if field is empty,
	 * false if not empty and adds error message to errorlist
	 *
	 * @note: should be isNotEmpty
     */
    function isEmpty($field, $msg) {
        $value = $this->getValue($field);
        if (!is_array($value)) {
			if (trim($value) == "") {
	            $this->addError($field, $value, $msg);
    	        return false;
			}
			elseif ( ($this->textSize > 0) &&
				  ($sz=strlen($value) > $this->textSize) ) {
				$this->addError($field, $value,
				$msg .=" Input data is too long.");
				return false;
			}
        } elseif (is_array($value) and empty($value)) {
            $this->addError($field, $value, $msg);
            return false;
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                if ($item == "") {
                    $this->addError($field, $value, $msg);
                    return false;
                }
            }
        } else {
            return true;
        }
    }

    /**
     * Adds a $msg to the list if the form control $field is not
	 * numeric.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
	 * @return true if field is numeric,
	 * false if not numeric and adds error message to errorlist
	 *
	 * @note: should be isNumeric
     */
    function isNotNumeric($field, $msg) {
        $value = $this->getValue($field);
        if(!is_numeric($value)) {
            $this->addError($field, $value, $msg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Adds a $msg to the list if the form control $field is not
	 * a valid date.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
	 * @return true if field is a date,
	 * false if not date and adds error message to errorlist
	 *
	 * @note: should be isDate
     */
    function isNotDate($field, $msg) {
        $value = $this->getValue($field);
        if (trim($value) == "") {
            $this->addError($field, $value, $msg);
            return false;
        } elseif (!is_array($datev = explode("-",$value))) {
            $this->addError($field, $value, $msg);
            return false;
        } else {
    	    if(!checkdate($datev[1],$datev[2],$datev[0])) {
    	        $this->addError($field, $value, $msg);
    	        return false;
    	    } else {
    	        return true;
    	    }
		}
    }

    /**
     * Adds a $msg to the list if the form control $field is not
	 * a valid time.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
     * @param $is24Hours Is it 24 hr time, not 12 hr time.
     * @param $seconds Are there seconds to test too.
	 * @return true if field is time,
	 * false if not time and adds error message to errorlist
	 *
	 * @note: should be isTime
     */
    function isNotTime($field, $msg,$is24Hours=true,$seconds=false) {
        $value = $this->getValue($field);
        if (trim($value) == "") {
            $this->addError($field, $value, $msg);
            return false;
        } else {
    		$pattern = "/^".($is24Hours ? 
			"(2[0-3]|[01]?[1-9])" : "(1[0-2]|0?[1-9])").
			":([0-5]?[0-9])".
			($seconds ? ":([0-5]?[0-9])" : "")."$/";
		    if (!preg_match($pattern, $value)) {
	            $this->addError($field, $value, $msg);
    	        return false;
    	    } else {
    	        return true;
    	    }
		}
    }

    /**
     * Adds a $msg to the list if the form control $field is not an integer.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
	 * @return true if field is integer,
	 * false if not integer and adds error message to errorlist
	 *
	 * @note: should be isInteger
     */
    function isNotInteger($field, $msg) {
        $value = $this->getValue($field);
        if(!is_numeric($value) || $value != intval($value)) {
            $this->addError($field, $value, $msg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Adds a $msg to the list if the form control $field is not within a
     * numeric range.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
	 * @return true if field is in range,
	 * false if not in range and adds error message to errorlist
	 *
	 * @note: should be isInsideRange
     */
    function isOutsideRange($field, $msg, $min, $max) {
        $value = $this->getValue($field);
        if(!is_numeric($value) OR $value < $min OR $value > $max) {
            $this->addError($field, $value, $msg);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Adds a $msg to the list if the form control $field is not a valid
     * email address.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
	 * @return true if field is valid email,
	 * false if not and adds error message to errorlist
	 *
	 * @note: should be isValidEmail
     */
    function isInvalidEmail($field, $msg) {
        $value = $this->getValue($field);
        $pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*";
        $pattern .= "@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";
        if(preg_match($pattern, $value)) {
            return true;
        } else {
            $this->addError($field, $value, $msg);
            return false;
        }
    }

    /**
     * Adds a $msg to the list if the form control $field is not 
	 * a valid CAPTCHA.
     *
     * @param $field The form field to check.
     * @param $msg The error message presented to the user.
	 * @return true if field is valid captcha,
	 * false if not numeric and adds error message to errorlist
	 *
	 * @note: should be is isValidCaptcha
     */
	function isInvalidCaptcha($field,$msg) {
    	    return false;
	}
}	
?>
