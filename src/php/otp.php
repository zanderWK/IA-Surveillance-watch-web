<?php
require 'api.php';
session_start();
// Assuming $_POST['otp'] is sanitized appropriately before use
$otp = isset($_POST['otp']) ? (string)$_POST['otp'] : null;

header('Content-Type: application/json');

if(!empty($otp)) {
    $endpoint = 'verify-otp';
    $data = array('otp' => $otp);

    try {
        $response = postData($endpoint, $data);
        if ($response['status'] == 200) {
            // Handle successful OTP verification

            $msg = [
                'body' => 'OTP Verified. Logging In',
                'status' => 'success',
                'url' => '/watch.php'
            ];
            $_SESSION['token'] = $response['body']['session'];
            $_SESSION['active'] = true;
            echo json_encode(['body' => $msg, 'status' => 'success']);
        } else {
            // Handle failed OTP verification
            $msg = [
                'body' => 'Failed to verify OTP. Please try again.',
                'status' => 'error'
            ];
            echo json_encode(['body' => $msg, 'status' => 'error']);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        // Handle exception during OTP verification
        $msg = [
            'body' => 'An error occurred, please try again.',
            'status' => 'error'
        ];
        echo json_encode(['body' => $msg, 'status' => 'error']);
    }
} else {
    // OTP not set or empty
    $msg = [
        'body' => 'OTP is required.',
        'status' => 'error'
    ];
    echo json_encode(['body' => $msg, 'status' => 'error']);
}
