<?php
include "../../Config.php";
include "../../sharedFunctions.php";
if(!isset($_GET["u"]) || !isset($_GET["rt"]) || !isset($_GET["i"]) || !isset($_GET["ts"])){
	exit;
}
if(empty($_GET["i"])){
	exit;
}

$getUserData = doLogin($_GET["u"]);
isTokenValidReqToken($_GET["rt"], $_GET["ts"], $getUserData["AuthToken"]);

$RetrieveBlob = $RetrieveDBData->prepare("SELECT * FROM `Snaps` WHERE BlobID = ? && Recipient = ?;");
$RetrieveBlob->bind_param("ss", $_GET["i"], $getUserData["Username"]);
$RetrieveBlob->execute();
$dbResult = $RetrieveBlob->get_result();
if($dbResult->num_rows == 0){
	send401ErrorJSONToClient("No blob found!");
}
$getBlobData = $dbResult->fetch_assoc();

if(file_exists("../../../Storage/BlobID_" . $getBlobData["BlobID"])){
	die(file_get_contents("../../../Storage/BlobID_" . $getBlobData["BlobID"]));
}