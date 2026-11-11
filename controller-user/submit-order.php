<?php
require __DIR__ . "/../app/core/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $orderItems = $_POST["orderItems"];
    $pickupTime = $_POST["pickupTime"];
    $paymentMethod = $_POST["paymentMethod"];
    $referenceNumber = isset($_POST["referenceNumber"]) ? $_POST["referenceNumber"] : null;

    // Process the order submission logic here
    $orderItems["pickupTime"] = $pickupTime;
    $orderItems["paymentMethod"] = $paymentMethod;
    $orderItems["referenceNumber"] = $referenceNumber;
    $orderItems["orderItems"] = $orderItems;

}
?>