<?php
$patternlength = 19; // It has to match the length of the original pattern
$pattern = "";

for($i = 0; $i < $patternlength; $i++){
    $pattern .= rand(0, 1);
}

echo "DEBUG: Generated pattern length:" . strlen($pattern) . "\n";
echo "Pattern: " . $pattern . "\n";