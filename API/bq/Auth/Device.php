<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
include $_SERVER['DOCUMENT_ROOT'] . "/Handlers/PushHandler.php";

if(!isset($_POST["device_token"]) || !isset($_POST["username"]) || !isset($_POST["type"])
	 || !isset($_POST["req_token"]) || !isset($_POST["timestamp"])){
	exit;
}
if(strlen($_POST["device_token"]) != 64){
	exit;
}
$getUserData = doLogin($_POST["username"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], $getUserData["AuthToken"]);

$updateToken = $RetrieveDBData->prepare("UPDATE accounts SET DeviceID = ? WHERE ID = ?"); //optimise: bin2hex and then update
$updateToken->bind_param("si", $_POST["device_token"], $getUserData["ID"]);
$updateToken->execute();

die(json_encode(array(
	"logged" => true,
)));