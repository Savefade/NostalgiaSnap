<?php
include "../../Config.php";
include "../../sharedFunctions.php";
if(!isset($_POST["action"]) || !isset($_POST["req_token"]) || !isset($_POST["timestamp"]) || !isset($_POST["friend"]) || !isset($_POST["username"])){
	exit;
}
$getUserData = doLogin($_POST["username"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], $getUserData["AuthToken"]);
$friendUsername = strtolower($_POST["friend"]);

switch ($_POST["action"]){
	case "add":
		if(!doesFriendRequestExist($getUserData, $friendUsername)){
			if(!isThereAFriendRequestSendByMe($getUserData, $friendUsername))
			sendFriendRequest($getUserData, doesUserExist($friendUsername));
		}
		else{
			acceptFriendRequest($getUserData, $friendUsername);
		}
	break;
	case "display":
		if(isset($_POST["display"])){
			if($getUserData["Username"] != $_POST["friend"]){
				sendErrorJSONToClient("Not implemented!");
			}
			updateMyNickname($getUserData["Username"], $_POST["display"]);
		}
	break;
	default:
		sendErrorJSONToClient("Not implemented!");
	break;
}