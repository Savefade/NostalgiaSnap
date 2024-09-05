<?php
include_once "../../displayErrorMessage.php";
// Server Config
$underMaintenance = false;
$maintenanceMessage = "We're making things more awesome! Be back soon.";
$maintenanceBypassUserAgents = [];
//error_reporting(1);
// DB Config
$DBURL = "127.0.0.1";
$DBusername = "root";
$DBpassword = "";
$DBname = "LegacySnapDB";
$RetrieveDBData = new mysqli($DBURL, $DBusername, $DBpassword, $DBname);
// API Manager
$loqEnabled = true;
$bqEnabled = true;
$ss2Enabled = true;
$picabooEnabled = true;
$snapchatPhoneNumber = "+15557350485"; 
$sendSMSToVerifyNumber = false;
$versionIncompatibleMessage = "Sorry, but this app version is incompatible!";
$usernameIsInUseErrorMessage = "Sorry, but this username is already in use!";
$emailIsInUseErrorMessage = "Sorry, but this email is already in use!";
$apiNotImplementedErrorMessage = "Sorry, but this API is not currently implemented please try again later!";
$incorrectPasswordMessage = "Incorrect username or password!";
// Maintenance code
if($underMaintenance){
	if(!in_array($_SERVER["HTTP_USER_AGENT"], $maintenanceBypassUserAgents)){
		sendErrorJSONToClient($maintenanceMessage);
	}
}