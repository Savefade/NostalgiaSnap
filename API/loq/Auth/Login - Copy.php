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
    "tos_version_5_accepted" => true,
	"updates_response" => array(
        "user_id" => 1,
        "laguna_id" => 1,
        "has_used_laguna" => true,
        "current_version_number" => 1,
        "device_token" => "12121-121221-1221212-1212",
        "voip_device_token" => "12121-121221-1221212-1212",
        "blizzard_token" => "12121-121221-1221212-1212",
        "bg_fetch_secret_key" => "12121-121221-1221212-1212",
        "birthdate" => "1990-01-01",
        "country_code" => "US",
        "story_count" => 0,
        "display_name" => $getUserData["Nickname"],
        "accepted_terms_of_use_for_version_five" => true,
        //
        "bests" => array(
            //"someguy"
        ),
        "tos_version_5_accepted" => true,
        "qr_path" => "https://www.snapchat.com/loq/qr/" . $getUserData["Username"],
        "snaptagUrl" => "https://www.snapchat.com/loq/snaptag/" . $getUserData["Username"],
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
    ),
    //"identity_check_response" => array(),
    "friends_response" => array(
        "friends" => $getUsersFriends["friends"],
        "added_friends" => array(),
    ),
    "conversations_response" => array(),
    "mischief_response" => array(),
    //"feed_response_info" => array(),
    //"sec_info" => array(),
    "conversations_response_info" => array(),
)));
?>