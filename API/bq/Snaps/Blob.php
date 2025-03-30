<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["id"]) || !isset($_POST["req_token"]) || !isset($_POST["timestamp"]) || !isset($_POST["username"])){
	exit;
}
if(empty($_POST["id"])){
	exit;
}
$getUserData = doLogin($_POST["username"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], $getUserData["AuthToken"]);

$RetrieveBlob = $RetrieveDBData->prepare("SELECT * FROM `Snaps` WHERE BlobID = ? && Recipient = ?;");
$RetrieveBlob->bind_param("ss", $_POST["id"], $getUserData["Username"]);
$RetrieveBlob->execute();
$dbResult = $RetrieveBlob->get_result();
if($dbResult->num_rows == 0){
	sendError401JSONToClient("No blob found!");
}
$getBlobData = $dbResult->fetch_assoc();

if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/Storage/BlobID_" . $getBlobData["BlobID"])){
	die(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/Storage/BlobID_" . $getBlobData["BlobID"]));
}