<?php
include "../../Config.php";
include "../../sharedFunctions.php";
if(!isset($_POST["email"]) || !isset($_POST["password"]) || !isset($_POST["birthday"]) || !isset($_POST["req_token"])){
	exit;
}

doRegister($_POST["email"], $_POST["password"], $_POST["birthday"]);
$token = generateToken();
updateTokenUsingEmail($token, $_POST["email"]);

die(json_encode(array(
	"logged" => true,
	"snapchat_phone_number" => $snapchatPhoneNumber,
	"token" => $token,
	"email" => $_POST["email"]
)));