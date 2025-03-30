<?php
include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
include $_SERVER['DOCUMENT_ROOT'] . "/API/SharedFunctions.php";
if(!isset($_POST["email"]) || !isset($_POST["username"]) || !isset($_POST["req_token"]) || !isset($_POST["timestamp"])){
	exit;
}
$getUserData = doLogin($_POST["email"]);
isTokenValidReqToken($_POST["req_token"], $_POST["timestamp"], "m198sOkJEn37DjqZ32lpRu76xmw288xSQ9");
doesUsernameMeetRequirements($_POST["username"]);
if(checkIfEmailOrUsernameIsInUse($_POST["username"])){
	sendErrorJSONToClient($usernameIsInUseErrorMessage);
}
$token = generateToken();
updateTokenUsingEmail($token, $_POST["email"]);
setUsername($_POST["email"], $_POST["username"]);

die(json_encode(array(
    "bests" => array(
        //"someguy"
    ),
    "score" => 0,
    "number_of_best_friends" => 0, //1
    "received" => 0,
    "logged" => true,
    "added_friends" => array(
      //  array(
      //      "ts" => 1384417608610,
      //      "name" => "somedude",
      //      "display" => "",
      //      "type" => 0
      //  ),
      //  array(
      //      "ts" => 1385130955168,
      //      "name" => "random",
      //      "display" => "",
      //      "type" => 1
      //  )
    ),
    "beta_expiration" => 0,
    "beta_number" => -1,
    "requests" => array(
       // array(
       //     "display" => "",
       //     "type" => 1,
       //     "ts" => 1377613760506,
       //     "name" => "randomstranger"
       // )
    ),
    "sent" => 0,
    "story_privacy" => "FRIENDS",
    "username" => $_POST["username"],
    "snaps" => array(
     //   array(
      //      "id" => "894720385130955367r",
     //      "sn" => "someguy",
     //      "ts" => 1385130955367,
     //       "sts" => 1385130955367,
      //      "m" => 3,
     //       "st" => 1
     //   ),
     //   array(
     //       "id" => "116748384417608719r",
     //       "sn" => "randomdude",
      //      "ts" => 1384417608719,
      //      "sts" => 1384417608719,
       //     "m" => 3,
       //    "st" => 1
       // ),
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
    "friends" => array(
        array(
            "can_see_custom_stories" => true,
            "name" => strtolower($_POST["username"]),
            "display" => $_POST["username"],
            "type" => 0
      //  ),
      //  array(
     //       "can_see_custom_stories" => true,
     //       "name" => "someguy",
     //       "display" => "Some Guy",
     //       "type" => 0
     //   ),
     //   array(
      //      "can_see_custom_stories" => true,
      //      "name" => "youraccount",
      //      "display" => "",
      //      "type" => 1
        )
    ),
    "device_token" => "",
   	//'feature_settings' => array(),
    "snap_p" => 1,
    "mobile_verification_key" => "", //base64 number:username
    "recents" => array(
     //   "teamsnapchat"
    ),
    "added_friends_timestamp" => 0,
    "notification_sound_setting" => "OFF",
    "snapchat_phone_number" => "+15557350485",
    "auth_token" => $token,
    "image_caption" => false,
    "is_beta" => false,
    "current_timestamp" => 0,
    "can_view_mature_content" => false,
    "email" => $getUserData["Email"],
    "should_send_text_to_verify_number" => true,
    "mobile" => ""
)));