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
// json
die(json_encode(array(
	"logged" => true,
	"name" => $getUserData["Username"],
	"token" => $token,
	"contacts" => $contactsList,
	"blocked" => array("test"),
	"picaboos" => array(array( //view snap
            "id" => "325924384416555221r",
			"sender" => "viewSnap",
            "timer" => 10,
            "sent" => 1384416555224,
        ),
		array(
            "id" => "325924384416555222r",
			"sender" => "viewed",
			"viewed" => 1384416555224 //time when vied
        ),
		array(
            "id" => "325924384416555223r",
			"sender" => "screenshotRecieved",
			"recieved" => 1384416555225,
			"count" => 1,
			"screenshotted" => 1384416555224 // recieved screenshotted picaboo
        ),
		array(
            "id" => "325924384416555224r",
			"recipient" => "screenshotSent",
			"screenshotted" => 1384416555224 // sent screenshotted picaboo
        ),
		array(
            "id" => "325924384416555225r",
			"recipient" => "screenshotSent",
			"viewed" => 1384416555224,
			"screenshotted" => 1384416555224 // opened screenshotted picaboo
        ),
		array(
            "id" => "325924384416555225r",
			"recipient" => "screenshotSent",
			"received" => 1384416555224,
			"screenshotted" => 1384416555224 // opened screenshotted picaboo
        ),
		array(
            "id" => "325924384416555226r",
			"pending" => 1384416555224,
			"wasPending" => 1384416555224,
			"recipient" => "sendingPicaboo",  //sending picaboo
        ),
		array(
            "id" => "325924384416555227r",
			"received" => 1384416555224,
			"recipient" => "deliveredPicaboo", //delivered picaboo
        )),
	
	"message" => "",
	)));
	
