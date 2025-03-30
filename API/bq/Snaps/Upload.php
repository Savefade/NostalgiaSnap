<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["media_id"]) || !isset($_POST["req_token"]) ||
 !isset($_POST["timestamp"]) || !isset($_POST["type"]) || !isset($_POST["username"]) ||
  !isset($_FILES["data"]["tmp_name"])){
	exit;
}

$getUserData = doLogin($_POST["username"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], $getUserData["AuthToken"]);
$time = time();
$blobID = generateToken();

file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/Storage/BlobID_$blobID", file_get_contents($_FILES["data"]["tmp_name"]));

$UploadUserData = $RetrieveDBData->prepare("INSERT INTO `snaps` 
(`ID`, `BlobID`, `MediaID`, `Timestamp`, `MediaType`, `ViewingTime`, `Recipient`, `Sender`, `StateJSON`) 
VALUES (NULL, '$blobID', ?, $time, ?, '-1', '', ?, '[\"1\"]');");
$UploadUserData->bind_param("sss", $_POST["media_id"], $_POST["type"], $_POST["username"]);
$UploadUserData->execute();

die(json_encode(array(
"logged" => true,
)));