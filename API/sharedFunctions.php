<?php
function doLogin($username){
	include "Config.php";
	$RetrieveUserData = $RetrieveDBData->prepare("SELECT * FROM Accounts WHERE Username = ? || Email = ? LIMIT 1");
	$RetrieveUserData->bind_param("ss", $username, $username);
	$RetrieveUserData->execute();
	$RetrivalResult = $RetrieveUserData->get_result();
	if($RetrivalResult->num_rows == 0){
		sendErrorJSONToClient($incorrectPasswordMessage);
	}
	$getUserData = $RetrivalResult->fetch_assoc();
	return $getUserData;
}

function doesUserExist($username){
	include "Config.php";
	$RetrieveUserData = $RetrieveDBData->prepare("SELECT * FROM Accounts WHERE Username = ? LIMIT 1");
	$RetrieveUserData->bind_param("s", $username);
	$RetrieveUserData->execute();
	$RetrivalResult = $RetrieveUserData->get_result();
	if($RetrivalResult->num_rows == 0){
		sendFriendDoesnNotExistError("A user with this username does not exist!");
	}
	$getUserData = $RetrivalResult->fetch_assoc();
	return $getUserData;
}

function legacyRefresh($token, $username){
	include "Config.php";
	$RetrieveUserData = $RetrieveDBData->prepare("SELECT * FROM Accounts WHERE Username = ? && AuthToken = ? LIMIT 1");
	$RetrieveUserData->bind_param("ss", $username, $token);
	$RetrieveUserData->execute();
	$RetrivalResult = $RetrieveUserData->get_result();
	if($RetrivalResult->num_rows == 0){
		send401ErrorJSONToClient($incorrectPasswordMessage);
	}
	$getUserData = $RetrivalResult->fetch_assoc();
	return $getUserData;
}

function updateToken($token, $username){
	include "Config.php";
	$RetrieveUserData = $RetrieveDBData->prepare("UPDATE Accounts SET AuthToken = ? WHERE Username = ? LIMIT 1");
	$RetrieveUserData->bind_param("ss", $token, $username);
	$RetrieveUserData->execute();
	return;
}

function updateTokenUsingEmail($token, $email){
	include "Config.php";
	$RetrieveUserData = $RetrieveDBData->prepare("UPDATE Accounts SET AuthToken = ? WHERE Username = ? LIMIT 1");
	$RetrieveUserData->bind_param("ss", $token, $email);
	$RetrieveUserData->execute();
	return;
}

function doRegister($email, $password, $age, ...$moredata){
	include "Config.php";
    if(!checkIfEmailOrUsernameIsInUse($email)){
		$sanitisedUsername = strtolower($email);
		$salt = md5(rand(0, 2147483646));
		$hashedPassword = password_hash($salt . $password . $salt, PASSWORD_BCRYPT);
		$RetrieveUserData = $RetrieveDBData->prepare("INSERT INTO `accounts` (`ID`, `Username`, `Nickname`, `Email`, `PhoneNumber`, `Salt`, `Password`, `AuthToken`) VALUES (NULL, '', '', ?, '', ?, ?, '');");
		$RetrieveUserData->bind_param("sss", $email, $salt, $hashedPassword);
		$RetrieveUserData->execute();
	}else{
		sendErrorJSONToClient($EmailIsInUseErrorMessage);
	}
	return;
}

function doRegisterPicaboo($username, $password){
	include "Config.php";
    if(!checkIfEmailOrUsernameIsInUse($username)){
		$sanitisedUsername = strtolower($username);
		$salt = md5(rand(0, 2147483646));
		$hashedPassword = password_hash($salt . $password . $salt, PASSWORD_BCRYPT);
		$RetrieveUserData = $RetrieveDBData->prepare("INSERT INTO `accounts` (`ID`, `Username`, `Nickname`, `Email`, `Salt`, `Password`, `AuthToken`) VALUES (NULL, ?, ?, '', ?, ?, '');");
		$RetrieveUserData->bind_param("ssss", $sanitisedUsername, $username, $salt, $hashedPassword);
		$RetrieveUserData->execute();
	}else{
		sendErrorJSONToClient($usernameIsInUseErrorMessage);
	}
	return;
}

function checkIfEmailOrUsernameIsInUse($username){
	include "Config.php";
	$RetrieveUserData = $RetrieveDBData->prepare("SELECT * FROM Accounts WHERE Username = ? || Email = ? LIMIT 1");
	$RetrieveUserData->bind_param("ss", $username, $username);
	$RetrieveUserData->execute();
	$RetrivalResult = $RetrieveUserData->get_result();
	if($RetrivalResult->num_rows == 0){
		return false;
	}
	return true;
}

function setUsername($email, $username){
	include "Config.php";
	$RetrieveUserData = $RetrieveDBData->prepare('UPDATE Accounts SET Username = ? WHERE email = ? && Username = "" LIMIT 1');
	$RetrieveUserData->bind_param("ss", $username, $email);
	$RetrieveUserData->execute();
	return;
}

function generateToken(){
	return md5(rand(0, 2147483646) . time());
}

function isTokenValidReqToken($req_token, $timestamp, $dbToken){
	$secret = "iEk21fuwZApXlz93750dmW22pw389dPwOk";
    $hashPattern = "0001110111101110001111010101111011010001001110011000110001000110";
    $firstHash = hash('sha256', $secret . $dbToken);
    $secondHash = hash('sha256', $timestamp . $secret);
	$result = "";
    for ($repeated = 0, $length = strlen($hashPattern); $repeated < $length; $repeated++) {
        $result .= $hashPattern[$repeated] === '0' ? $firstHash[$repeated] : $secondHash[$repeated];
    }
	if($result != $req_token){
		send401ErrorJSONToClient("You have been logged out! res: $result req: $req_token");
	}
    return true; //thank you, gibsec security!
}

function getFriends($username){
	include "Config.php";
    $friendsArray = array("friends" => array(), "added_friends" => array());
	$RetrieveFriends = $RetrieveDBData->prepare("SELECT * FROM Friends WHERE AddedByUsername = ? || AddedUsername = ?");
	$RetrieveFriends->bind_param("ss", $username, $username);
	$RetrieveFriends->execute();
	$dbResult = $RetrieveFriends->get_result();
 	while($friend = $dbResult->fetch_assoc()){
		$friendUsername = $friend["AddedByUsername"];
		$friendPlacement = 1;
		if($friend["AddedByUsername"] == $username){
			$friendUsername = $friend["AddedUsername"];
		    $friendPlacement = 0;
		}
		$type = json_decode($friend["TypeJSON"], true);
		$nicknames = json_decode($friend["NicknamesJSON"], true);
		switch ($type[$friendPlacement]){
			case 2:
			case 0:
				$friendsArray["friends"][] = array(
				"can_see_custom_stories" => true,
				"name" => $friendUsername,
				"display" => $nicknames[$friendPlacement],
				"type" => $type[$friendPlacement]
				);
			break;
			case 1:
			$friendsArray["friends"][] = array(
					"ts" => $friend["Timestamp"],
					"name" => $friendUsername,
					"display" => $nicknames[$friendPlacement],
					"type" => $type[$friendPlacement]
				);
			break;
			case 3:
			$friendsArray["added_friends"][] = array(
					"ts" => $friend["Timestamp"],
					"name" => $friendUsername,
					"display" => $nicknames[$friendPlacement],
					"type" => 0
				);
			break;
		}
	}
	return $friendsArray;
}

function updateMyNickname($username, $newNickname){
	include "Config.php";
	$RetrieveFriends = $RetrieveDBData->prepare("UPDATE Accounts SET Nickname = ? WHERE Username = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ss", $newNickname, $username);
	$RetrieveFriends->execute();
	die(json_encode(array(
	    "logged" => true,
		"message" => "Done!",
	)));
}

function getFriendsLegacy($username){
	include "Config.php";
    $friendsArray = array($username);
	$usernameWithBrackets = '%"' . $username . '"%';
	$RetrieveFriends = $RetrieveDBData->prepare("SELECT * FROM Friends WHERE WHERE AddedByUsername = ? || AddedUsername = ?");
	$RetrieveFriends->bind_param("s", $usernameWithBrackets);
	$RetrieveFriends->execute();
	$dbResult = $RetrieveFriends->get_result();
 	while($friend = $dbResult->fetch_assoc()){
		$friendUsername = $friend["AddedByUsername"];
		$friendPlacement = 0;
		if($friend["AddedByUsername"] == $username){
			$friendUsername = $friend["AddedUsername"];
		    $friendPlacement = 1;
		}
		$type = json_decode($friend["TypeJSON"], true);
		$friendPlacement = ($placement == 1)?  0: 1;
		if($type[$placement] != 2){
			$friendsArray[] = $friendUsername;
		}
	}
	return $friendsArray;
}

function sendFriendRequest($getUserData, $req_UserData){
	include "Config.php";
	$currentTS = time();
	$typeJSON = '[\"1\",\"3\"]';
	$usernamesJSON = '[\"'. $getUserData["Username"] .'\",\"'. $req_UserData["Username"] .'\"]';
	$nicknamesJSON = '[\"'. $req_UserData["Nickname"] .'\",\"'. $getUserData["Nickname"] .'\"]';
	$RetrieveUserData = $RetrieveDBData->prepare("INSERT INTO `friends` (`ID`, `TypeJSON`, `AddedByUsername`, `AddedUsername`, `NicknamesJSON`, `Timestamp`) VALUES (NULL, '$typeJSON', ?, ?, '$nicknamesJSON', ?)");
	$RetrieveUserData->bind_param("sss", $getUserData["Username"], $req_UserData["Username"], $currentTS);
	$RetrieveUserData->execute();
	
	die(json_encode(array(
	    "logged" => true,
		"ts" => time() * 1000,
        "name" => $req_UserData["Username"],
        "display" => $req_UserData["Nickname"],
        "type" => 0
	)));
}

function doesFriendRequestExist($getUserData, $friendUsername){
	include "Config.php";
	$RetrieveFriends = $RetrieveDBData->prepare("SELECT * FROM `friends` WHERE AddedByUsername = ? && AddedUsername = ? ;");
	$RetrieveFriends->bind_param("ss", $friendUsername, $getUserData["Username"]);
	$RetrieveFriends->execute();
	$dbResult = $RetrieveFriends->get_result();
	if($dbResult->num_rows == 0){
		return false;
	}
	return true;
}

function isThereAFriendRequestSendByMe($getUserData, $friendUsername){
	include "Config.php";
	$RetrieveFriends = $RetrieveDBData->prepare("SELECT * FROM `friends` WHERE AddedByUsername = ? && AddedUsername = ?;");
	$RetrieveFriends->bind_param("ss", $getUserData["Username"], $friendUsername);
	$RetrieveFriends->execute();
	$dbResult = $RetrieveFriends->get_result();
	if($dbResult->num_rows == 0){
		return false;
	}
	return $dbResult->fetch_assoc();
}

function acceptFriendRequest($getUserData, $friendUsername){
	include "Config.php";
	$usernamesJSON = '[\"'. htmlspecialchars($friendUsername) .'\",\"'. $getUserData["Username"] .'\"]';
	$RetrieveFriends = $RetrieveDBData->prepare("UPDATE `friends` SET `TypeJSON` = '[\"0\",\"0\"]' WHERE `TypeJSON` = '[\"1\",\"3\"]' && AddedByUsername = ? && AddedUsername = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ss", $friendUsername, $getUserData["Username"]);
	$RetrieveFriends->execute();
		die(json_encode(array(
	    "logged" => true,
		"message" => "Success!",
	)));
}

function blockFriend($getUserData, $friendUsername){
	include "Config.php";
	$usernamesJSON = '[\"'. htmlspecialchars($friendUsername) .'\",\"'. $getUserData["Username"] .'\"]';
	$RetrieveFriends = $RetrieveDBData->prepare("UPDATE `friends` SET `TypeJSON` = '[\"0\",\"0\"]' WHERE `TypeJSON` = '[\"1\",\"3\"]' && AddedByUsername = ? && AddedUsername = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ss", $friendUsername, $getUserData["Username"]);
	$RetrieveFriends->execute();
		die(json_encode(array(
	    "logged" => true,
		"message" => "Blocked!",
	)));
}

function deleteFriend($getUserData, $friendUsername){
	include "Config.php";
}