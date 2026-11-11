<?php
$host="localhost";
$dbname="greenDash";
$username="root";
$password= "";      // CHANGE THIS & CHECK GOOGLE CHATS FOR THE PASSWORD!!!
try {
    $pdo = new PDO("mysql:host=$host; dbname=$dbname; charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e)
{
    // Controllers can return an appropriate response instead of emitting database errors.
    throw new RuntimeException('Database connection failed.', 0, $e);
}
?>
