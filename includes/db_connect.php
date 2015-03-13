<?php 
//To be included on any page that connects to the MySql database
include_once 'psl-config.php';   
$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
?>
