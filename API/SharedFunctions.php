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

function doesUsernameMeetRequirements($username){
	if(!ctype_alnum($username)){
		sendErrorJSONToClient('Usernames can only contain a-z,A-Z and 0-9 characters!');
	}
	if(strlen($username) > 15 || strlen($username) < 3){
		sendErrorJSONToClient('Usernames can only be 3-15 characters!');
	}
	return true;
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
		send401ErrorJSONToClient("auth");
	}
	$getUserData = $RetrivalResult->fetch_assoc();
	return $getUserData;
}

function updateToken($token, $username){
	include "Config.php";
	$UpdateUserData = $RetrieveDBData->prepare("UPDATE Accounts SET AuthToken = ? WHERE Username = ? LIMIT 1");
	$UpdateUserData->bind_param("ss", $token, $username);
	$UpdateUserData->execute();
	return;
}

function updateTokenUsingEmail($token, $email){
	include "Config.php";
	$UpdateUserData = $RetrieveDBData->prepare("UPDATE Accounts SET AuthToken = ? WHERE Username = ? LIMIT 1");
	$UpdateUserData->bind_param("ss", $token, $email);
	$UpdateUserData->execute();
	return;
} //might be removed

function doRegister($email, $password, $age, ...$moredata){
	include "Config.php";
    if(!checkIfEmailOrUsernameIsInUse($email)){
		$sanitisedUsername = strtolower($email);
		$salt = md5(rand(0, 2147483646));
		$hashedPassword = password_hash($salt . $password . $salt, PASSWORD_BCRYPT);
		$UploadUserData = $RetrieveDBData->prepare("INSERT INTO `accounts` (`ID`, `Username`, `Nickname`, `Email`, `PhoneNumber`, `Salt`, `Password`, `AuthToken`) VALUES (NULL, '', '', ?, '', ?, ?, '');");
		$UploadUserData->bind_param("sss", $email, $salt, $hashedPassword);
		$UploadUserData->execute();
	}else{
		sendErrorJSONToClient($emailIsInUseErrorMessage);
	}
	return;
}

function doRegisterPicaboo($username, $password){
	include "Config.php";
    if(!checkIfEmailOrUsernameIsInUse($username)){
		$lowerCaseUsername = strtolower($username);
		$salt = md5(rand(0, 2147483646));
		$hashedPassword = password_hash($salt . $password . $salt, PASSWORD_BCRYPT);
		$UploadUserData = $RetrieveDBData->prepare("INSERT INTO `accounts` (`ID`, `Username`, `Nickname`, `Email`, `Salt`, `Password`, `AuthToken`) VALUES (NULL, ?, ?, '', ?, ?, '');");
		$UploadUserData->bind_param("ssss", $lowerCaseUsername, $username, $salt, $hashedPassword);
		$UploadUserData->execute();
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
	$lowerCaseUsername = strtolower($username);
	$UpdateUserData = $RetrieveDBData->prepare('UPDATE Accounts SET Username = ?, Nickname = ? WHERE email = ? AND Username = "" LIMIT 1');
	$UpdateUserData->bind_param("sss", $lowerCaseUsername, $username, $email);
	$UpdateUserData->execute();
	return;
}

function generateToken(){
	return md5(rand(0, 2147483646) . time());
}

function isTokenValidReqToken($req_token, $timestamp, $dbToken){
	include "Config.php";
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
    $friendsArray = array("friends" => array(), "added_friends" => array(), "blocked_usernames" => array());
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
		    $friendsArray["blocked_usernames"][] = $friendUsername;
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

function getFriend($getUserData, $friendUsername){
	include "Config.php";
	$RetrieveFriend = $RetrieveDBData->prepare("SELECT * FROM `friends` WHERE AddedByUsername = ? && AddedUsername = ? || AddedByUsername = ? && AddedUsername = ? LIMIT 1;");
	$RetrieveFriend->bind_param("ssss", $getUserData["Username"], $friendUsername, $friendUsername, $getUserData["Username"]);
	$RetrieveFriend->execute();
	$dbResult = $RetrieveFriend->get_result();
	if($dbResult->num_rows == 0){
		sendErrorJSONToClient("Friend not found!");
	}
	return $dbResult->fetch_assoc();
}

function getFriendsLegacy($username){ //to be fixed
	include "Config.php";
    $friendsArray = array($username);
	$RetrieveFriends = $RetrieveDBData->prepare("SELECT * FROM Friends WHERE AddedByUsername = ? || AddedUsername = ?");
	$RetrieveFriends->bind_param("ss", $username, $username);
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
		if($type[$friendPlacement] != 2){
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
	$friendshipData = getFriend($getUserData, $friendUsername);
	$typeJSON = ($friendshipData["AddedByUsername"] == $friendUsername)?  '[\"1\",\"2\"]': '[\"2\",\"1\"]';
	$RetrieveFriends = $RetrieveDBData->prepare("UPDATE `friends` SET `TypeJSON` = '$typeJSON' WHERE `TypeJSON` = '[\"0\",\"0\"]' && AddedByUsername = ? && AddedUsername = ? || `TypeJSON` = '[\"0\",\"0\"]' && AddedByUsername = ? && AddedUsername = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ssss", $friendUsername, $getUserData["Username"], $getUserData["Username"], $friendUsername);
	$RetrieveFriends->execute();
		die(json_encode(array(
	    "logged" => true,
		"message" => "Blocked!",
	)));
}

function unblockFriend($getUserData, $friendUsername){
	include "Config.php";
	$friendshipData = getFriend($getUserData, $friendUsername);
	$RetrieveFriends = $RetrieveDBData->prepare("UPDATE `friends` SET `TypeJSON` = '[\"0\",\"0\"]' WHERE `TypeJSON` = '[\"1\",\"2\"]' && AddedByUsername = ? && AddedUsername = ? || `TypeJSON` = '[\"2\",\"1\"]' && AddedByUsername = ? && AddedUsername = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ssss", $friendUsername, $getUserData["Username"], $getUserData["Username"], $friendUsername);
	$RetrieveFriends->execute();
		die(json_encode(array(
	    "logged" => true,
		"message" => "Unblocked!",
	)));
}

function deleteFriend($getUserData, $friendUsername){
	include "Config.php";
	$RetrieveFriends = $RetrieveDBData->prepare("DELETE FROM `friends` WHERE AddedByUsername = ? && AddedUsername = ? || AddedByUsername = ? && AddedUsername = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ssss", $friendUsername, $getUserData["Username"], $getUserData["Username"], $friendUsername);
	$RetrieveFriends->execute();
		die(json_encode(array(
	    "logged" => true,
		"message" => "Removed!",
	)));
}


function updateMyNickname($getUserData, $newNickname){
	include "Config.php";
	$RetrieveFriends = $RetrieveDBData->prepare("UPDATE Accounts SET Nickname = ? WHERE Username = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ss", $newNickname, $username["Username"]);
	$RetrieveFriends->execute();
	die(json_encode(array(
	    "logged" => true,
		"message" => "Successfully updated personal display name!",
	)));
}

function updateFriendNickname($getUserData, $friendUsername, $newNickname){
	include "Config.php";
	$friendshipData = getFriend($getUserData, $friendUsername);
	$nicknames = json_decode($friendshipData["NicknamesJSON"], true);
		$placement = 1;
    if($friendshipData["AddedByUsername"] == $getUserData["Username"]){
	    $placement = 0;
	}
	$nicknames[$placement] = $newNickname;
	$nicknameJSON = json_encode($nicknames);
	$RetrieveFriends = $RetrieveDBData->prepare("UPDATE Friends SET NicknamesJSON = '$nicknameJSON' WHERE AddedByUsername = ? && AddedUsername = ? || AddedByUsername = ? && AddedUsername = ? LIMIT 1;");
	$RetrieveFriends->bind_param("ssss", $friendUsername, $getUserData["Username"], $getUserData["Username"], $friendUsername);
	$RetrieveFriends->execute();
	die(json_encode(array(
	   "logged" => true,
	   "message" => "Success!",
	)));
}

function getSnaps($getUserData, $blockedUsernames){
	include "Config.php";
	$snaps = array();
	$snapArray = array();
	$sanitisedBlockedUsernames = substr(json_encode($blockedUsernames), 1, -1);
	if(!empty($sanitisedBlockedUsernames)){
		$RetrieveSnaps = $RetrieveDBData->prepare("SELECT * FROM `snaps` WHERE ( recipient = ? || sender = ?) && sender NOT IN($sanitisedBlockedUsernames);");
    }else{
		$RetrieveSnaps = $RetrieveDBData->prepare("SELECT * FROM `snaps` WHERE recipient = ? || sender = ?");
	}   
	$RetrieveSnaps->bind_param("ss", $getUserData["Username"], $getUserData["Username"]);
	$RetrieveSnaps->execute();
	$dbResult = $RetrieveSnaps->get_result();
	while($snap = $dbResult->fetch_assoc()){
		$state = json_decode($snap["StateJSON"], true);
		$isSnapMine = ($snap["Sender"] == $getUserData["Username"])? true : false;
		if($isSnapMine){
			$snapArray = array(
             "c_id" => $snap["BlobID"],
             "rp" => $snap["Recipient"],
             "ts" => $snap["Timestamp"] * 1000,
             "sts" => $snap["Timestamp"] * 1000,
             //"m" => $snap["MediaType"],
             "st" => $state[0]   //to be fixed
        );
		}
		if($snap["Recipient"] == $getUserData["Username"]){
			$snapArray = array(
            "id" => $snap["BlobID"],
            "sn" => $snap["Sender"],
            "t" => $snap["ViewingTime"],
            "ts" => $snap["Timestamp"] * 1000,
            "sts" => $snap["Timestamp"] * 1000,
            "m" => $snap["MediaType"],
            "st" => $state[0] 
           );
		}
		if(isset($state[1])){
				$snapArray["c"] = $state[1];
		}
		$snaps[] = $snapArray;
	}
    return $snaps;
}

function getPicaboos($getUserData){
	include "Config.php";
	$snaps = array();
	$snapArray = array();
	$RetrieveSnaps = $RetrieveDBData->prepare("SELECT * FROM `snaps` WHERE (recipient = ? || sender = ?) && MediaType = 0"); 
	$RetrieveSnaps->bind_param("ss", $getUserData["Username"], $getUserData["Username"]);
	$RetrieveSnaps->execute();
	$dbResult = $RetrieveSnaps->get_result();
	while($snap = $dbResult->fetch_assoc()){
		$state = json_decode($snap["StateJSON"], true);
		$isSnapMine = ($snap["Sender"] == $getUserData["Username"])? true : false;
		if($isSnapMine){
			$snapArray = array(
			 "id" => $snap["BlobID"],
			 "recipient" => $snap["Recipient"],
			 "viewed" => $snap["Timestamp"] * 1000 //time when vied
			);
		}
		if($snap["Recipient"] == $getUserData["Username"]){
			$snapArray = array(
            "id" => $snap["BlobID"],
			"sender" => $snap["Sender"],
            "timer" => $snap["ViewingTime"],
            "sent" => $snap["Timestamp"] * 1000,
            "st" => $state[0] 
           );
		}
		if(isset($state[1])){
				$snapArray["c"] = $state[1];
		}
		$snaps[] = $snapArray;
	}
    return $snaps;
}

function getSS2Picaboos($getUserData){
	include "Config.php";
	$snaps = array();
	$snapArray = array();
	$RetrieveSnaps = $RetrieveDBData->prepare("SELECT * FROM `snaps` WHERE (recipient = ? || sender = ?) && MediaType = 0"); 
	$RetrieveSnaps->bind_param("ss", $getUserData["Username"], $getUserData["Username"]);
	$RetrieveSnaps->execute();
	$dbResult = $RetrieveSnaps->get_result();
	while($snap = $dbResult->fetch_assoc()){
		$state = json_decode($snap["StateJSON"], true);
		$isSnapMine = ($snap["Sender"] == $getUserData["Username"])? true : false;
		if($isSnapMine){
			$snapArray = array(
            "id" => $snap["BlobID"],
            "rp" => $snap["Recipient"],
            "t" => $snap["ViewingTime"],
            "v" => $snap["Timestamp"] * 1000,
            "s" => $state[0] 
           );
		}
		if($snap["Recipient"] == $getUserData["Username"]){
			$snapArray = array(
            "id" => $snap["BlobID"],
            "sn" => $snap["Sender"],
            "t" => $snap["ViewingTime"],
            "v" => $snap["Timestamp"] * 1000,
            "s" => $state[0] 
           );
		}
		if(isset($state[1])){
				$snapArray["c"] = $state[1];
		}
		$snaps[] = $snapArray;
	}
    return $snaps;
}

function getSnapData($ID){ //this uses either the blob id or mediaid
	include "Config.php";
	$RetrieveSnaps = $RetrieveDBData->prepare("SELECT * FROM `snaps` WHERE ( BlobID = ? || MediaID = ?) LIMIT 1;");
	$RetrieveSnaps->bind_param("ss", $ID, $ID);
	$RetrieveSnaps->execute();
	$snap = $RetrieveSnaps->get_result()->fetch_assoc();
    return $snap;
}
