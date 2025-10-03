<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["username"]) || !isset($_POST["password"]) || !isset($_POST["birthday"]) || !isset($_POST["req_token"])){
	exit;
}

doRegisterPicaboo($_POST["username"], $_POST["password"]);
$token = generateToken();
updateToken($token, $_POST["username"]);

die(json_encode(array(
	"logged" => true,
	"snapchat_phone_number" => $snapchatPhoneNumber,
	"token" => $token,
	"email" => $_POST["email"]
)));