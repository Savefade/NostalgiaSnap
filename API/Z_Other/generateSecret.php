<?php
$secretLength = 34;
$genPattern = sha1(random_bytes($secretLength/ 2), true);

echo "DEBUG: Generated pattern length:" . strlen($genPattern) . "\n";
echo "Secret: " . $genPattern . "\n";

