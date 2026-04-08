<?php
echo "OK - Apache is running!<br>";
echo "Server Port: " . ($_SERVER['SERVER_PORT'] ?? 'unknown') . "<br>";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'unknown') . "<br>";
phpinfo();
?>