<?php

$database_host = 'huhuh.bcom';
$database_user = 'okuihuh';
$database_password = 'pasws';
$database_name = 'usbame';

$db = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
} else {
    // echo "Database connected successfully";
}
