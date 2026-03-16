<?php
$host = "aws-1-ap-southeast-1.pooler.supabase.com";
$port = 6543;

$fp = fsockopen($host, $port, $errno, $errstr, 10);

if (!$fp) {
    echo "Connection failed: $errstr ($errno)";
} else {
    echo "Connection successful";
    fclose($fp);
}
?>