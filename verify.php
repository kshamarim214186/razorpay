<?php

require('config.php');
require('vendor/autoload.php');
session_start();

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

$success = true;

$error = "Payment Failed";

if (empty($_POST['razorpay_payment_id']) === false)
{
    $api = new Api($keyId, $keySecret);

    try
    {
        // Please note that the razorpay order ID must
        // come from a trusted source (session here, but
        // could be database or something else)
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );

        $api->utility->verifyPaymentSignature($attributes);
    }
    catch(SignatureVerificationError $e)
    {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true){

    $common->pdo_update('update', 'payment_details', array("transaction_id" => $_POST['razorpay_payment_id'],'status'=>'success'), array("order_id" => $_SESSION['razorpay_order_id']), "Where order_id = :order_id");
    $html = "<p>Your payment was successful</p>
             <p>Payment ID: {$_POST['razorpay_payment_id']}</p>";
}else{
    $common->pdo_update('update', 'payment_details', array("transaction_id" => $_POST['razorpay_payment_id'],'status'=>'fail'), array("order_id" => $_SESSION['razorpay_order_id']), "Where order_id = :order_id");
    $html = "<p>Your payment failed</p>
             <p>{$error}</p>";
}

echo $html;
