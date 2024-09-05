<?php
include "../../Config.php";
include "../../sharedFunctions.php";
if(!isset($_POST["u"]) || !isset($_POST["t"]) || !isset($_POST["a"])){ //$_POST["u"] is the username and t is either a authtoken or the password. a is the action
	exit;
}

// Userdata
$getUserData = array();
$token = "";
switch($_POST["a"]){
	case "login":
	$getUserData = doLogin($_POST["u"], $_POST["t"]);
	$token = generateToken();
    updateToken($token, $getUserData["Username"]);
	break;
	case "authenticate":
	$token = $_POST["t"];
	$getUserData = legacyRefresh($_POST["t"], $_POST["u"]);
	break;
	case "register":
	doRegisterPicaboo($_POST["u"], $_POST["t"]);
	$getUserData = doLogin($_POST["u"], $_POST["t"]);
	$token = generateToken();
    updateToken($token, $getUserData["Username"]);
	break;
	default:
	sendErrorJSONToClient("unknown action");
}
$contactsList = getFriendsLegacy($getUserData["Username"]);
// json
die(json_encode(array(
	"logged" => true,
	"name" => $getUserData["Username"],
	"token" => $token,
	"contacts" => $contactsList,
	"picaboos" => array(),
	
	"message" => "",
	)));
	
