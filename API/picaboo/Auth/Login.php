<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["u"]) || !isset($_POST["t"]) || !isset($_POST["a"])){ //$_POST["u"] is the username and t is either a authtoken or the password. a is the action
	exit;
}

//easter
if($_POST["u"] == "picaboo" && $_POST["t"] == "showMeAnEasterEggPlease"){
	sendErrorJSONToClient("boo!");
}
if($_POST["u"] == "pikachu" && $_POST["t"] == "showMeAnEasterEggPlease"){
	sendErrorJSONToClient("Pikachuuuuuuuuuuu!");
}
if($_POST["u"] == "Linus Torvalds" && $_POST["t"] == "showMeAnEasterEggPlease"){
	sendErrorJSONToClient("Fuck you Nvidia!");
}
//

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
$picaboos = getPicaboos($getUserData);
// json
die(json_encode(array(
	"logged" => true,
	"name" => $getUserData["Username"],
	"token" => $token,
	"contacts" => $contactsList,
	"bests" => $contactsList,
	"picaboos" => $picaboos,
	
	"message" => "",
	)));
	
