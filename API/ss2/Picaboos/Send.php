<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["r"]) || !isset($_POST["rt"]) || !isset($_POST["time"]) || !isset($_POST["u"]) || !isset($_POST["ts"]) || !isset($_FILES["image"]["tmp_name"])){
	print_r($_FILES["image"]["tmp_name"]);
	exit;
}

if(strpos(",", $_POST["r"])){
	exit;
}

$snapContents = file_get_contents($_FILES["image"]["tmp_name"]);
$getUserData = doLogin($_POST["u"]);
isTokenValidReqToken($_POST["rt"], $_POST["ts"], $getUserData["AuthToken"]);
$timestamp = time();

$snapID = generateToken();
file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/Storage/BlobID_" . $snapID, $snapContents);
$UploadUserData = $RetrieveDBData->prepare("INSERT INTO `snaps` (`ID`, `BlobID`, `MediaID`, `Timestamp`, `MediaType`, `ViewingTime`, `Recipient`, `Sender`, `StateJSON`) VALUES (NULL, ?, '', ?, 0, ?, ?, ?, '[\"1\"]');");
$UploadUserData->bind_param("sssss", $snapID, $timestamp, $_POST["time"], $_POST["r"], $_POST["u"]);
$UploadUserData->execute();

die(json_encode(array(
	"logged" => true,
)));