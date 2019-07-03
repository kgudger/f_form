<?php
/**
 * @file formlib.php
 * Purpose: User Preferences Page. 
 * Extends MainPage Class
 *
 * @note CIS-165PH Final project
 *
 * @author Ed Parrish
 * @author Keith Gudger
 * @copyright  (c) 2014, Keith Gudger, all rights reserved
 * @license    http://opensource.org/licenses/BSD-2-Clause
 * @version    Release: 1.0
 * @package    Volunteer Now!
 *
 * @note Has processData and showContent, 
 * main and checkForm in MainPage class not overwritten.
**/

include_once("formverifier.php");

define("VERTICAL", 1); /// orient controls veritcally (up and down)
define("HORIZONTAL", 2); /// orient controls horizontally (sideways)

/**
 * FormLib class for making and verifying forms.
 * Child class derived from FormVerifier.
 */
class FormLib extends FormVerifier {
    /*--- private variables ---*/
    private  $formLayout = VERTICAL; //! is layout vertical?

    /**
     * Constructor
     *
     * @param $errColor The color used to display error messages.
     * @param $layout Orientation for form controls (when it matters).
     */
    function __construct($cssClass = "error", $layout = VERTICAL) {
        parent::__construct($cssClass);
        $this->formLayout = $layout;
    }

    /*--- Functions to make form controls ---*/

    /**
     * Returns the HTML to begin a form.
     *
     * @param $method The method used to submit form data 
	 * ('GET' or 'POST').
     * @param $action Name of the script to receive the form data.
     * @param $other Other attributes of the form.
	 * @return html string for form.
     */
    function start($method = 'POST', $action = '', $other = '') {
        if (!$action) $action = $_SERVER['REQUEST_URI'];
        $html = "<form method=\"$method\" action=\"$action\"";
        if ($other) $html .= " $other";
        $html .= ">\n";
        return $html;
    }

    /**
	 * Simple function to end a form.
	 *
     * @return the HTML to end a form.
     */
    function finish() {
        return "</form>\n";
    }

    /**
     * Returns the HTML to create a form button.
     *
     * @param $value The value to display on the button..
     * @param $name The name of the form control.
	 * @return htmlfor a button.
     */
    function makeButton($value = "Submit", $name= "submitTest") {
        $html = '<input type="submit" name="';
        $html .= $name.'" value="'.$value.'">';
        return $html;
    }

    /**
     * Returns the HTML to create a hidden field.
     *
     * @param $name The name of the form control.
     * @param $value The value for the name.
	 * @return html for a hidden field.
     */
    function makeHidden($name, $value) {
        return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";

    }

    /**
     * Returns the HTML to create one checkbox for each 
	 * item of $listPairs.
     * For example, to make 3 checkboxes with PHP checked:
     * $listPairs = array("ASP"=>"a", "JSP"=>"j", "PHP"=>"p",
        "Other"=>"o");
     * $selectList = array("p");
     * echo $f->makeCheckBoxes('lang', $listPairs, $selectList);
     *
     * @param $name The name of the form control.
     * @param $listPairs An array of $label=>$value pairs.
     * @param $selectList An array of values to preselect (add 'checked').
     * @param $other Other attributes added to every checkbox.
	 * @return html for check boxes.
     */
    function makeCheckBoxes($name, $listPairs, $selectList=null, $other="") {
        if (!is_array($selectList)) $selectList = array($selectList);
        $selectList = $this->getValue($name, $selectList);
        $html = "";
        foreach ($listPairs as $label=>$value) {
            $html .= "<input type=\"checkbox\" name=\"{$name}[]\" value=\"$value\"";
            if ($other) $html .= " $other";
            if ($selectList AND in_array($value, $selectList)) {
                $html .= " checked";
            }
            $html .= "> $label\n";
        }
        return $html;
    }

    /**
     * Returns the HTML to create one radio button for each 
	 * item of the $labelValueList. For example, to make 
	 * a group of 2 radio buttons:
     *    $list = array("Male"=>"m", "Female"=>"f");
     *    echo $f->makeRadioGroup('sex', $list, "f");
     *
     * @param $name The name of the form control.
     * @param $labelValueList The array of $label=>$value pairs.
     * @param $checked The $value of the radio button to mark 'checked'.
     * @param $other Other attributes added to every radio button.
	 * @return html for a radio group.
     */
    function makeRadioGroup($name, $labelValueList, $checked="", $other="") {
        $checked = $this->getValue($name, $checked);
        $html = "";
        foreach ($labelValueList as $label => $value) {
            $item = "<input type=\"radio\" name=\"$name\" value=\"$value\"";
            if ($checked == $value) $item .= " checked";
            if ($other) $html .= " $other";
            $item .= "> $label\n";
            if ($this->formLayout == VERTICAL) {
                $html .= "$item<br>";
            } else {
                $html .= $item;
            }
        }
        return $html;
    }

    /**
     * Returns the HTML to create a selection control with each 
	 * item of the $labelValueList on the list. 
	 * For example, to make a selction control
     * with California initially selected:
     *    $list = array("- Select One -"=>"", "California"=>"CA",
     *                            "Oregon"=>"OR", "Washington"=>"WA");
     *    echo $f->makeSelect('state', $list, "CA");
     *
     * @param $name The name of the form control.
     * @param $labelValueList An array of $label=>$value pairs.
     * @param $selected The value of the item to select on the list.
     * @param $other Other attributes added to the control.
	 * @return html for a selection group.
     */
    function makeSelect($name, $labelValueList, $selected="", $other="") {
        $selected = $this->getValue($name, $selected);
        $html = "<select name=\"{$name}\"";
        if ($other) $html .= " $other";
        $html .= ">\n";
        foreach ($labelValueList as $prompt => $value) {
            $html .= "<option value=\"$value\"";
            if ($selected == $value) {
                $html .= " selected";
            }
            $html .= ">$prompt</option>\n";
        }
        $html .= "</select>\n";
        return $html;
    }

    /**
     * Returns the HTML to create a selection control with each 
	 * item of the $labelValueList on the list and allowing 
	 * multiple items to be selected.
     * For example, to make a multi-selction control 
	 * with both California and Washington initially selected:
     *    $labelValueList = 
	 * 		array("- Select One -"=>"", "California"=>"CA",
     *                       "Oregon"=>"OR", "Washington"=>"WA");
     *    $selectList = array("CA", "WA");
     *    echo $f->makeSelectMulti('state', $list, $selectList);
     *
     * @param $name The name of the form control.
     * @param $labelValueList An array of $label=>$value pairs.
     * @param $selected The value of the item to select on the list.
     * @param $other Other attributes added to the control.
	 * @return html for a multiple item select field.
     */
    function makeSelectMulti($name, $labelValueList, $selectList=0, $other=""){
        $selectList = $this->getValue($name, $selectList);
        $html = "<select name=\"{$name}[]\" multiple";
        if ($other) $html .= " $other";
        $html .= ">\n";
        foreach ($labelValueList as $label => $value) {
            $html .= "<option value=\"$value\"";
            if ($selectList AND in_array($value, $selectList)) {
                $html .= " selected";
            }
            $html .= ">$label</option>\n";
        }
        $html .= "</select>\n";
        return $html;
    }

    /**
     * Returns the HTML to create a text area control.
     *
     * @param $name The name of the form control.
     * @param $rows The number of rows in the textarea.
     * @param $cols The number of columns in the textarea.
     * @param $text The text to initially display.
     * @param $other Other attributes added to the control.
	 * @return html for a text area.
     */
    function makeTextArea($name, $rows=0, $cols=0, $text="", $other="") {
        $text = $this->getValue($name, $text);
        $html = "<textarea name=\"$name\"";
        if ($cols) $html .= " cols=\"$cols\"";
        if ($rows) $html .= " rows=\"$rows\"";
        if ($other) $html .= " $other";
        $html .= ">\n$text</textarea>\n";
        return $html;
    }

    /**
     * Returns the HTML to create a single-line text-input control.
     *
     * @param $name The name of the form control.
     * @param $value The text to initially display.
     * @param $other Other attributes added to the control.
	 * @return html for a text input.
     */
    function makeTextInput($name, $value = "", $other = "") {
        $value = $this->getValue($name, $value);
        $html = "<input type=\"text\" name=\"$name\" value=\"$value\"";
        if ($other) $html .= " $other";
        $html .= ">";
        return $html;
    }

    /**
     * Returns the HTML to create a single-line date-input control.
     *
     * @param $name The name of the form control.
     * @param $value The date to initially display.
     * @param $other Other attributes added to the control.
	 * @return html for a date input field.
     */
    function makeDateInput($name,$value="",$other="") {
        $value = $this->getValue($name, $value);
        $html="<input type=\"date\" name=\"$name\" value=\"$value\"";
        if ($other) $html .= " $other";
        $html .= ">";
        return $html;
    }

    /**
     * Returns the HTML to create a single-line time-input control.
     *
     * @param $name The name of the form control.
     * @param $value The time to initially display.
     * @param $other Other attributes added to the control.
	 * @return html for a time input field.
     */
    function makeTimeInput($name,$value="",$other="") {
        $value = $this->getValue($name, $value);
        $html="<input type=\"time\" name=\"$name\" value=\"$value\"";
        if ($other) $html .= " $other";
        $html .= ">";
        return $html;
    }

    /**
     * Returns the HTML to create a single-line 
	 * text-password control.
     * @note password values are not retained on error. If you want
     * to retain the value on error, then call the function 
	 * like this:
     * echo $f->makePassword($name, $f->getValue($name);
     *
     * @param $name The name of the form control.
     * @param $value The text to initially enter.
     * @param $other Other attributes added to the control.
	 * @return html for a password field.
     */
    function makePassword($name, $value = "", $other = "") {
        $value = $this->getValue($name, $value);
        $html = "<input type=\"password\" name=\"$name\" value=\"$value\"";
        if ($other) $html .= " $other";
        $html .= ">";
        return $html;
    }

    /**
     * Returns the HTML to create a single-line number input.
     *
     * @param $name The name of the form control.
     * @param $min The minimum value to accept.
     * @param $max The maximum value to accept.
     * @param $value The number to initially display.
     * @param $other Other attributes added to the control.
	 * @return html for a number input field.
     */
    function makeNumberInput($name,$min="",$max="",
							$value="",$other="") {
        $value = $this->getValue($name, $value);
        $html="<input type=\"number\" name=\"$name\" ";
		$html .= "value=\"$value\"";
        if ($min) $html .= " min=$min";
        if ($max) $html .= " max=$max";
        if ($other) $html .= " $other";
        $html .= ">";
        return $html;
    }

    /**
     * Returns the HTML to create a single-line email input.
     *
     * @param $name The name of the form control.
     * @param $min The minimum value to accept.
     * @param $max The maximum value to accept.
     * @param $value The text to initially display.
     * @param $other Other attributes added to the control.
     * @return html for an email input field.
     */
    function makeEmailInput($name,$min="",$max="",
				$value="",$other="") {
        $value = $this->getValue($name, $value);
        $html="<input type=\"email\" name=\"$name\" ";
	$html .= "value=\"$value\"";
        if ($min) $html .= " min=$min";
        if ($max) $html .= " max=$max";
        if ($other) $html .= " $other";
        $html .= ">";
        return $html;
    }

    /**
     * Returns the HTML to create a file input box
     *
     * @param $name The name of the form control.
     * @param $value The text to initially display.
     * @param $other Other attributes added to the control.
     * @return html for an email input field.
     */
    function makeFileInput($name,$value="",$other="") {
        $value = $this->getValue($name, $value);
        $html="<input type=\"file\" name=\"$name\" ";
        if ($value) 
		$html .= "value=\"$value\"";
        if ($other) $html .= " $other";
        $html .= ">";
        return $html;
    }
}
?>
