<?php

function sendErrorJSONToClient($content){
	die(json_encode(["logged" => false, "message" => $content, "status" => -1]));
}

function send401ErrorJSONToClient($content){
	http_response_code(401);
	die(json_encode(["logged" => false, "message" => $content, "status" => -1]));
}

function sendFriendDoesnNotExistError($content){
	die(json_encode(["logged" => true, "exists" => false, "message" => $content]));
}