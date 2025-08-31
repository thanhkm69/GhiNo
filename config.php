<?php 
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit;
}
const HOST = 'localhost';
const USERNAME = 'root';
const DBNAME = 'ghino';
const PASSWORD = '';
?>