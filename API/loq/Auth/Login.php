<?php
include "../Config.php";
$username = (isset($_POST["username"]))? $_POST["username"] : ((isset($_POST["u"]))? $_POST["u"] : exit());
$password = (isset($_POST["password"]))? $_POST["password"] : ((isset($_POST["t"]))? $_POST["t"] : exit());
$apiEndpoint = explode("/", $_SERVER["REQUEST_URI"])[1];
if(in_array($apiEndpoint, ["ss2","bq","loq"])){
	include_once "../../Responses/$apiEndpoint/Auth/Login.php";
}else{
	include_once "../../Responses/picaboo/Auth/Login.php";
}

// Userdata
$RetrieveUserData = $RetrieveDBData->prepare("SELECT * FROM Accounts WHERE Username = ? || Email = ? LIMIT 1");
$RetrieveUserData->bind_param("ss", $username, $username);
$RetrieveUserData->execute();
$RetrivalResult = $RetrieveUserData->get_result();
if($RetrivalResult->num_rows == 0){
	sendErrorJSONToClient($incorrectPasswordMessage);
}
$getUserData = $RetrivalResult->fetch_assoc();
if(!password_verify($getUserData["Salt"] . $password . $getUserData["Salt"], $getUserData["Password"])){
	sendErrorJSONToClient($incorrectPasswordMessage);
}
// snaps

// chat if loq

sendLoginResponse($getUserData, array(), array());
?>