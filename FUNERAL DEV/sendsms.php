<?php
// Include API credentials
include("config.php");

function sendSMS($phone, $message) {
    $apiKey = "atsk_fa79c9045bd3ea6f005afcc2d12f53cd9e4764f66f90283bd7ba76c2061cedbca243a82e";
    $username = "lastrespect";
    $sender = "lastrespect";

    $url = "https://api.africastalking.com/version1/messaging";
    $data = [
        "username" => "lastrespect",
        "to" => "0714506855",
        "message" => "hello hello",
        "from" => "0718743157"
    ];

    $headers = [
        "Accept: application/json",
        "apiKey: $apiKey",
        "Content-Type: application/x-www-form-urlencoded"
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute the request and get response
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST["phone"];
    $message = $_POST["message"];

    // Send the SMS
    $response = sendSMS($phone, $message);
    echo "<h3>Response:</h3>";
    echo "<pre>$response</pre>";
}
?>
