<?php

$database_host = 'cs.neiu.edu';
$database_user = 'SP24CS4121_haynazarov2';
$database_password = 'haynazarov2618548';
$database_name = 'SP24CS4121_haynazarov2';

$db = new mysqli($database_host, $database_user, $database_password, $database_name);

if ($db->connect_error) {
    die("Database connection failed: " . $db->connect_error);
} else {
    // echo "Database connected successfully";
}