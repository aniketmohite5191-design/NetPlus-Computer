<?php
// Generate correct password hash for admin123
$hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "Password hash for 'admin123':\n";
echo $hash . "\n";
echo "\nVerify: " . (password_verify('admin123', $hash) ? 'VALID' : 'INVALID') . "\n";
?>
