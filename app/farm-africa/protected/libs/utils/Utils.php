<?php

/**
 * Description of Utils
 * this function contains utility functions for use within the FarmAfrica 
 * application
 * @author muya
 */
class Utils {

    /**
     * Log String to log File in a predetermined format
     * @param int/text $logLevel 0 = 'CRITICAL', 1 = 'FATAL', 2 = 'ERROR', 3 = 'WARNING', 4 = 'INFO', 5 = 'SEQUEL', 6 = 'TRACE', 7 = 'DEBUG', 8 = 'CUSTOM', 9 = 'UNDEFINED';
     * @param string $logString
     * @param string $filename
     * @param string $function
     * @param int $lineNo
     */
    public static function log($logLevel, $logString = NULL, $fileName = NULL, $function = NULL, $lineNo = NULL, $redAlert = false) {
        $SYSTEM_LOG_LEVEL = self::getConfigValue('systemLogLevel', 10);

        if (is_array($logString)) {
            //if logstring is array convert it to a string
            $logString = Utils::printArray($logString);
        }
        $logDirectory = self::getConfigValue('LOG_DIR', "/var/log/applications/FarmAfrica/UI/");
        $file = $logDirectory . "DEBUG.log";
        $date = date("Y-m-d H:i:s");
        $date .= ' | ' . microtime();
        $logType = null;
        $logType[0] = 'CRITICAL';
        $logType[1] = 'FATAL';
        $logType[2] = 'ERROR';
        $logType[3] = 'WARNING';
        $logType[4] = 'INFO';
        $logType[5] = 'SEQUEL';
        $logType[6] = 'TRACE';
        $logType[7] = 'DEBUG';
        $logType[8] = 'CUSTOM';
        $logType[9] = 'UNDEFINED';
        $logTitle = 'UNDEFINED';

        // covert ID to file Name
        if (!is_int($logLevel)) { // level is a string convert back to int and overide the default file
            if (strtolower(substr($logLevel, (strlen($logLevel) - 4), 4)) == '.log' or strtolower(substr($logLevel, (strlen($logLevel) - 4), 4)) == '.txt') { // overide the current paths {{faster than changing all scripts with custom paths}}
                $file = $logDirectory . basename($logLevel);
            } else { // file does not have the correct extension.
                $file = $logDirectory . basename($logLevel) . '.log';
            }

            $logLevel = 8;
        } else {
            if (isset($logType[$logLevel])) {
                // override the current paths {{faster than changing all scripts with custom paths}}
                $file = $logDirectory . basename($logType[$logLevel]) . ".log";
            } else {
                $logLevel = 9;
            }
        }

        $logTitle = $logType[$logLevel];

        if ($fileName == NULL)
            $fileName = $_SERVER['PHP_SELF'];
        // should be <= $DEBUG_LEVEL
        if ($logLevel <= $SYSTEM_LOG_LEVEL) {
            if ($fo = fopen($file, 'ab')) {

                fwrite($fo, "$date -[$logTitle] $fileName:$lineNo $function| $logString\n");


                fclose($fo);
            } else {
                trigger_error("flog Cannot log '$logString' to file '$file' ", E_USER_WARNING);
            }
        }

        //if this is a red alert, send an email to dev
//        if ($redAlert != false) {
//            if ($redAlert === true) {
//                $sendToUserID = 1;
//            } else {
//                $sendToUserID = $redAlert;
//            }
//            EmailHandler::sendRedAlert($sendToUserID, "$date -[$logTitle] $fileName:$lineNo $function| $logString");
//        }
    }

    /**
     * Return an array as a string indicating all keys and values
     * @param Array $theArray Array to be rendered
     * @param Text $seperator (default '\n') character to use in seperating aray entries
     * @param Text $indent (default '\t') character to prepend every seperate entry
     * @param Bool $keys (default 'true') Show or not to show Key values
     * @param Bool $heading (default 'true') Show or not to show "ARRAY(" headings
     * @param Text $equator (default '=') character to seperate Key from value
     * @param Text $open (default '[') character to appear befor Key value
     * @param Text $close (default ']') character to appeart after key value
     * @param Text $doubleindent (default '\t') character to be appended to $indent when in nested array
     * @return Text Text representation of the array
     */
    public static function printArray($theArray, $seperator = "\n", $indent = " \t", $keys = true, $heading = true, $equator = ' => ', $open = '[', $close = ']', $doubleIndent = " \t") {
        $ss = 0;
        $myString = '';
        if (is_array($theArray)) {
            if ($heading)
                $myString = "Array($seperator" . "$indent";

            foreach ($theArray as $key => $value) {
                if ($ss++ != 0)
                    $myString .= $seperator . $indent;
                if (is_array($value)) {
                    if ($keys) {
                        $myString .= $open . $key . $close . $equator;
                    }

                    $myString .= self::printArray($value, $seperator, $indent . $doubleIndent, $keys, $heading, $equator, $open, $close, $doubleIndent);
                } else {
                    if ($keys) {
                        $myString .= $open . $key . $close . $equator;
                    }

                    $myString .= $value;
                }
            }
            if ($heading)
                $myString .= $seperator . $indent . ")";
        }
        else {
            $myString = (string) $theArray;
        }
        return $myString;
    }

    /**
     * get pre-defined variable values from main.php
     * @param string $variableName
     * @param string $defaultValue
     */
    public static function getConfigValue($variableName, $defaultValue = null) {
        if (isset(Yii::app()->params[$variableName])) {
            return Yii::app()->params[$variableName];
        } else {
            return $defaultValue;
        }
    }

    /**
     * function to format arrays to <b>JSON</b> or <b>Array</b>
     * @param type $arrayToFormat
     * @param type $format
     * @return type
     */
    public static function formatArray($arrayToFormat, $format = 'json') {
        $format = strtolower($format);
        if ($format == 'json') {
            return CJSON::encode($arrayToFormat);
        } else if ($format == 'array') {
            return (array) $arrayToFormat;
        } else {
            return CJSON::encode($arrayToFormat);
        }
    }

    /**
     * Return the current Date and time in the standard format
     * @param string $format the format in which to return the date
     * @return string
     */
    public static function now($format = 'Y-m-d H:i:s') {
        //new CDbExpression('now()');
        return date($format);
    }
    
    /**
     * function to format a response within the System
     * @param mixed $data  any data required
     * @param int $statCode status code for the response, maps to a status code 
     * in statusCodes table
     * @param int $statType stat type for the $statCode
     * @param mixed $statDesc description for the status code
     * @return array formatted response
     */
    public static function formatResponse($data = null, $statCode = null, 
            $statType = null, $statDesc = null){
        return array(
            'DATA' => $data,
            'STATUS_CODE' => $statCode,
            'STATUS_TYPE' => $statType,
            'STATUS_DESCRIPTION' => $statDesc,
        );
    }
    
    /**
     * this method parses a string to how we name classes in our app
     * @param String $className
     * @return String $className the correct class name
     */
    public static function parseClassName($className){
        return ucfirst($className);
    }

//     <div class="alert alert-success">
//   <button type="button" class="close" data-dismiss="alert">×</button>
//   <strong>Well done!</strong> You successfully read this important alert message.
// </div>

    /**
    * function to assemble the flash message to be displayed
    * adds the required css classes & other HTML functionality required
    * @param string $displayMsg message to be displayed
    * @param string $errorType the type of message, defaults to null, meaning just an alert
    * message
    */
    public static function createFlashMessage($displayMsg, $errorType = null){
        if($errorType == null || trim($errorType) == ''){
            $class = 'alert';
        }
        else{
            $class = 'alert alert-'.trim($errorType);
        }
        $flashMsg = '<div class="'.$class.'">';
        $flashMsg .= '<button type="button" class="close" data-dismiss="alert">x</button>';
        $flashMsg .= $displayMsg;
        $flashMsg .= '</div>';
        return $flashMsg;
    }

    /**
     * function to display error/success/info messages in the view, which have
     * been set using the Yii::app()->user->setFlash() method
     * @param string $msg
     * @param $string $errorType
     */
    public static function displayFlashMessage($msg = NULL, $errorType = NULL) {
        // <button type="button" class="close" data-dismiss="alert">×</button>
        //first fetch the messages
        $displayMsg = $error = '';
        if($msg !== null && $errorType !== null){
            $displayMsg = $msg;
            $errorType = $errorType;
            echo Utils::createFlashMessage($displayMsg, $errorType);
        }
        if(Yii::app()->user->hasFlash('success')){
            $displayMsg = Yii::app()->user->getFlash('success');
            $errorType = 'success';
            echo Utils::createFlashMessage($displayMsg, $errorType);
        }

        if(Yii::app()->user->hasFlash('info')){
            $displayMsg = Yii::app()->user->getFlash('info');
            $errorType = 'info';
            echo Utils::createFlashMessage($displayMsg, $errorType);
        }

        if(Yii::app()->user->hasFlash('error')){
            $displayMsg = Yii::app()->user->getFlash('error');
            $errorType = 'error';
            echo Utils::createFlashMessage($displayMsg, $errorType);
        }


        if ($msg !== NULL && $errorType !== NULL) {
            echo '<div class="alert fade in alert-' . $errorType . '">
                  <span><p>' . $msg . '</p></span>
                  </div>';
        }
    }

}

?>
