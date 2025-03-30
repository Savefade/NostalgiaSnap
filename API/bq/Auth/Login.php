<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["username"]) || !isset($_POST["password"])){
	exit;
}

// Userdata
$getUserData = doLogin($_POST["username"]);
$getUsersFriends = getFriends($_POST["username"]);
$token = md5(rand(0, 2147483646) . time());
updateToken($token, $getUserData["Username"]);
// json
$getUsersFriends["friends"][] = array(
	"can_see_custom_stories" => true,
    "name" => $getUserData["Username"],
    "display" => $getUserData["Nickname"],
    "type" => 0
);
die(json_encode(array(
    "bests" => array(
        //"someguy"
    ),
    "score" => 0,
    "number_of_best_friends" => 0, //1
    "received" => 0,
    "logged" => true,
    "added_friends" => $getUsersFriends["added_friends"],
    "beta_expiration" => 0,
    "beta_number" => -1,
    "requests" => array(
    ),
    "sent" => 0,
    "story_privacy" => "FRIENDS",
    "username" => $_POST["username"],
    "snaps" => array(
       // array(
       //     "id" => "325924384416555224r",
       //     "sn" => "teamsnapchat",
       //     "t" => 10,
       //     "ts" => 1384416555224,
       //     "sts" => 1384416555224,
       //     "m" => 0,
       //     "st" => 1
       // )
    ),
    "friends" => $getUsersFriends["friends"],
     //2 blocked // 1 pending // 0 friend
    "device_token" => "",
    "snap_p" => 1,
    "mobile_verification_key" => base64_encode(time() * 1000 . ":" . $getUserData["Username"]) . "DONOTSEND!", //base64 number:username
    "recents" => array(//   "teamsnapchat"
    ),
    "added_friends_timestamp" => 0,
    "notification_sound_setting" => "OFF",
    "snapchat_phone_number" => "+15557350485",
    "auth_token" => $token,
    "image_caption" => false,
    "is_beta" => false,
    "current_timestamp" => time() * 1000,
    "can_view_mature_content" => false,
    "email" => $getUserData["Email"],
    "should_send_text_to_verify_number" => $sendSMSToVerifyNumber,
    "mobile" => $getUserData["PhoneNumber"]
)));
?>