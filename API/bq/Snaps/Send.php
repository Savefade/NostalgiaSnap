<?php
include "../../Config.php";
include "../../sharedFunctions.php";
if(!isset($_POST["media_id"]) || !isset($_POST["recipient"]) || !isset($_POST["req_token"]) || !isset($_POST["time"]) || !isset($_POST["timestamp"]) || !isset($_POST["username"])){
	exit;
}
if(empty($_POST["media_id"])){
	exit;
}
$getUserData = doLogin($_POST["username"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], $getUserData["AuthToken"]);
$recipients = explode(", ", $_POST["recipient"]);

if(count($recipients) > 1){
	$getSnapData = getSnapData($_POST["media_id"]);
	$snapName = "../../../Storage/BlobID_" . $getSnapData["BlobID"];
	$snapContents = (file_exists($snapName))? file_get_contents($snapName) : exit;
	for($recipientPlacement = 1; isset($recipients[$recipientPlacement]); $recipientPlacement++){
		$snapID = generateToken();
		file_put_contents("../../../Storage/BlobID_" . $snapID, $snapContents);
		$UploadUserData = $RetrieveDBData->prepare("INSERT INTO `snaps` (`ID`, `BlobID`, `MediaID`, `Timestamp`, `MediaType`, `ViewingTime`, `Recipient`, `Sender`, `StateJSON`) VALUES (NULL, ?, '', ?, ?, ?, ?, ?, '[\"1\"]');");
		$UploadUserData->bind_param("ssssss", $snapID, $getSnapData["Timestamp"], $getSnapData["MediaType"], $_POST["time"], $recipients[$recipientPlacement], $_POST["username"]);
		$UploadUserData->execute();
	} // replace time with snap's time and type
}

$UploadUserData = $RetrieveDBData->prepare("UPDATE Snaps SET ViewingTime = ?, Recipient = ?, MediaID = '' WHERE Sender = ? && MediaID = ? &&  Recipient = '' LIMIT 1;");
$UploadUserData->bind_param("ssss", $_POST["time"], $recipients[0], $_POST["username"], $_POST["media_id"] );
$UploadUserData->execute();

die(json_encode(array(
	"logged" => true,
)));