<?php
require_once 'vendor/autoload.php'; // Make sure Composer's autoloader is included

use AfricasTalking\SDK\AfricasTalking;

function sendSMS($to, $message)
{
    // TODO: Initialize Africa's Talking
    $username = 'lastrespect'; // use 'sandbox' for testing
    $apiKey   = 'atsk_fa79c9045bd3ea6f005afcc2d12f53cd9e4764f66f90283bd7ba76c2061cedbca243a82e';

    $AT       = new AfricasTalking($username, $apiKey);
    $sms      = $AT->sms();

    try {
        // TODO: Send message
        $result = $sms->send([
            'to'      => $to,
            'message' => $message
        ]);

        return [
            'status' => 'success',
            'response' => $result
        ];

    } catch (Exception $e) {
        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}
