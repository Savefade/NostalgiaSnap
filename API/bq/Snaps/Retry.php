<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["media_id"]) || !isset($_POST["recipient"]) || !isset($_POST["req_token"]) || !isset($_POST["time"]) || !isset($_POST["timestamp"]) || !isset($_POST["username"]) || !isset($_FILES["data"]["tmp_name"]) || !isset($_POST["type"])){
	exit;
}
if(empty($_POST["media_id"])){
	exit;
}

$snapContents = file_get_contents($_FILES["data"]["tmp_name"]);
$getUserData = doLogin($_POST["username"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], $getUserData["AuthToken"]);
$recipients = explode(", ", $_POST["recipient"]);
$timestamp = time();

for($recipientPlacement = 1; isset($recipients[$recipientPlacement]); $recipientPlacement++){
	$snapID = generateToken();
	file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/Storage/BlobID_" . $snapID, $snapContents);
	$UploadUserData = $RetrieveDBData->prepare("INSERT INTO `snaps` (`ID`, `BlobID`, `MediaID`, `Timestamp`, `MediaType`, `ViewingTime`, `Recipient`, `Sender`, `StateJSON`) VALUES (NULL, ?, '', ?, ?, ?, ?, ?, '[\"1\"]');");
	$UploadUserData->bind_param("ssssss", $snapID, $timestamp, $_POST["type"], $_POST["time"], $recipients[$recipientPlacement], $_POST["username"]);
	$UploadUserData->execute();
}

die(json_encode(array(
	"logged" => true,
)));