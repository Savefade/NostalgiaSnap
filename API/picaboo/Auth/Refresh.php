<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["u"]) || !isset($_POST["t"]) || !isset($_POST["s"])){ //$_POST["u"] is the username and t is either a authtoken or the password. a is the action
	exit;
}

// Userdata
$getUserData = array();
$token = "";
$token = $_POST["t"];
$getUserData = legacyRefresh($_POST["t"], $_POST["u"]);
$contactsList = getFriendsLegacy($getUserData["Username"]);
$picaboos = getPicaboos($getUserData);
// json
die(json_encode(array(
	"logged" => true,
	"name" => $getUserData["Username"],
	"token" => $token,
	"contacts" => $contactsList,
	"picaboos" => $picaboos,
	"message" => "",
	)));
	
