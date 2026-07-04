<?php

$serverName = "localhost";
$userName = "root";
$password = "";
$databaseName = "care_medical";
$port = 3307;

$connection = mysqli_connect(
    $serverName,
    $userName,
    $password,
    $databaseName,
    $port
);

if (!$connection) {
    die("Connection Failed: " . mysqli_connect_error());
}

// echo "Database Connected Successfully";

?>