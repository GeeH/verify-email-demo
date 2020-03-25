<?php declare(strict_types=1);
session_start();
require __DIR__ . './../vendor/autoload.php';

use Twilio\Rest\Client;

$verifyToken = (string)$_GET['token'] ?? false;
$email = (string)$_SESSION['email_address_verify'] ?? false;
// add validation of code and email for production systems!

if (!$email || !$verifyToken) {
    throw new \Exception('Email or code not set');
}

$sid = getenv('TWILIO_ACCOUNT_SID');
$token = getenv('TWILIO_AUTH_TOKEN');
$client = new Client($sid, $token);

// send the verification check request to the Twilio API
try {
    $verification = $client->verify
        ->v2
        // service id of the verification service we created - we'll probably want this
        // stored in a config file somewhere
        ->services("<YOUR TWILIO VERIFY SERVICE SID>")
        ->verificationChecks
        ->create($verifyToken, ["to" => $email]);
    // update your user in the database to set the verified flag

    if ($verification->status === 'approved') {
        $message = 'Thanks for validating your email, you can now login.';
        unset($_SESSION['email_address_verify']);
    } else {
        $message = 'Sorry, the code you entered is not valid';
    }
} catch (\Twilio\Exceptions\RestException $e) {
    $message = 'Sorry, the code you entered may have expired, <a href="register.html">Click Here</a> to resend the code.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register for Pied Piper</title>
    <style>
        html, body {
            height: 100%;
            width: 100%;
        }

        body, body {
            display: flex;
        }

        h1 {
            margin: auto;
        }
    </style>
</head>
<body>
<h1><?= $message; ?></h1>
</body>
</html>
