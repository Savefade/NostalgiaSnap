<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
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
	case "delete":
		deleteFriend($getUserData, $friendUsername);
		break;
	case "display":
		if(isset($_POST["display"])){
			if($getUserData["Username"] != $_POST["friend"]){
				updateFriendNickname($getUserData, $friendUsername, $_POST["display"]);
			}
			updateMyNickname($getUserData, $_POST["display"]);
		}
		break;
	case "block":
		blockFriend($getUserData, $friendUsername);
		break;
	case "unblock":
		unblockFriend($getUserData, $friendUsername);
		break;
	default:
		sendErrorJSONToClient("Not implemented!");
		break;
}