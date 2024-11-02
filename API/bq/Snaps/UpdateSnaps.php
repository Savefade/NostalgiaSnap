<?php
include "../../Config.php";
include "../../sharedFunctions.php";

if(!isset($_POST["added_friends_timestamp"]) || !isset($_POST["events"]) || !isset($_POST["req_token"]) || !isset($_POST["json"]) || !isset($_POST["timestamp"]) || !isset($_POST["username"])){
	exit;
}

$getUserData = doLogin($_POST["username"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], $getUserData["AuthToken"]);

$snapStatusJson = json_decode($_POST["json"], true);
$snapIDs = array_keys($snapStatusJson);
$blobIDs = implode(" ,", $snapIDs);
//$blobIDs = implode(" ,", array_map(function($blob){return "'$blob'";}, $snapIDs));

if(count($snapIDs) == 0){
	exit;
}

foreach($snapIDs as $blob){
	$stateJSON = (isset($snapStatusJson[$blob]["c"]))? json_encode(["3", $snapStatusJson[$blob]["c"]]) : "[\"2\"]";
	$updateSnapData = $RetrieveDBData->prepare("UPDATE snaps SET StateJSON = '$stateJSON' WHERE Recipient = ? && StateJSON = '[\"1\"]' && BlobID = ?;");
	$updateSnapData->bind_param("ss", $_POST["username"], $blob);
	$updateSnapData->execute();
}

//echo "UPDATE snaps SET StateJSON = '[\"2\"]' WHERE Recipient = ? && StateJSON = '[\"1\"]' && BlobID IN($blobIDs);";
//$updateSnapData = $RetrieveDBData->prepare("UPDATE snaps SET StateJSON = '[\"2\"]' WHERE Recipient = ? && StateJSON = '[\"1\"]' && BlobID IN($blobIDs);");
//$updateSnapData->bind_param("s", $_POST["username"]);
//$updateSnapData->execute();

die(json_encode(array(
	"logged" => true,
)));