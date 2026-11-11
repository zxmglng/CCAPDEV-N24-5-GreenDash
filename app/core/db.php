<?php
$host="localhost";
$dbname="greenDash";
$username="root";
$password= "111";      // CHANGE THIS & CHECK GOOGLE CHATS FOR THE PASSWORD!!!
try {
    $pdo = new PDO("mysql:host=$host; dbname=$dbname; charset=utf8mb4", $use$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e)
{
    die("Connection failed: " . $e->getMessage());
}
?>