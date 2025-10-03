<?php

function sendNotification($message, $deviceID){
    include $_SERVER['DOCUMENT_ROOT'] . "/API/Config.php";
    $rInfo = extractRoutingInfo($deviceID);

    if(!$rInfo){
        return;
    }

    $notificationJSON = json_encode(array(    
            'routing_key' => bin2hex($rInfo["rkey"]), 
            'server_address' => $rInfo["address"],
            'data' => array(
                "aps" => array(
                    "alert" => $message,
                ),
            ),
        ),
    );

    $streamContext = stream_context_create(array(
        'http' => [
            'method' => "POST",
            'header' => "Content-Type: application/json",
            "ignore_errors" => true,
            'content' => $notificationJSON,
        ]
    ));

    file_get_contents("https://sgnprod.preloading.dev/send", true, $streamContext);
}

function extractRoutingInfo($deviceID){
    if(strlen($deviceID) != 64){
        return;
    }

    $deviceID = hex2bin($deviceID);

    $serverAddressRaw = substr($deviceID, 0, 16);
    $serverAddress = rtrim($serverAddressRaw, "\x00");
    $secret = substr($deviceID, 16, 16);

    $pattern = '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/i';
    if(!preg_match($pattern, $serverAddress)){
        return;
    }

    $routingKey = hash("sha256", $secret, true);

    return array(
        "rkey" => $routingKey,
        "address" => $serverAddress
    );
}