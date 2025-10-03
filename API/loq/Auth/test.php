<?php

function request_token($auth_token, $timestamp) {
    $secret = "iEk21fuwZApXlz93750dmW22pw389dPwOk";
    $pattern = "0001110111101110001111010101111011010001001110011000110001000110";

    // Generate the first hash
    $first = hash('sha256', $secret . $auth_token);

    // Generate the second hash
    $second = hash('sha256', $timestamp . $secret);
	
	$result = "";

    // Combine the hashes based on the pattern
    for ($i = 0, $len = strlen($pattern); $i < $len; $i++) {
        $result .= $pattern[$i] === '0' ? $first[$i] : $second[$i];
    }

    // Return the final token
    return $result;
}

// Example usage
$auth_token = "m198sOkJEn37DjqZ32lpRu76xmw288xSQ9";
$timestamp = "1725031783056";
echo request_token($auth_token, $timestamp);

?>
