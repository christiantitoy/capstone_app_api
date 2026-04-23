<?php
header('Content-Type: text/plain');

$creds = getenv('FIREBASE_CREDENTIALS');

if ($creds === false) {
    echo "❌ FIREBASE_CREDENTIALS not found";
} else {
    echo "✅ FIREBASE_CREDENTIALS loaded\n\n";
    echo $creds;
}
