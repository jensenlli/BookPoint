<?php
    $hostname     = "localhost:3306"; // Enter Your Host Name
    $username     = "root";      // Enter Your Table username
    $password     = "";          // Enter Your Table Password
    $databasename = "digitalbook"; // Enter Your database Name


    $conn = new mysqli($hostname, $username, $password, $databasename);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>