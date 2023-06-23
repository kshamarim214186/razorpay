<?php
require('config.php');
require('vendor/autoload.php');
$orderId = base64_decode($_GET['order_id']);

$orderDataval = $common->get_single_qry(
    $orderId,
    "order_id",
    "payment_details"
);


//echo "<pre>"; print_r($orderDataval);die;

use Razorpay\Api\Api;

$api = new Api($keyId, $keySecret);
$orderData = [
    'receipt'         => $orderDataval['id'],
    'amount'          => 10 * 100, // 2000 rupees in paise
    'currency'        => 'INR',
    'payment_capture' => 1 // auto capture
];

$razorpayOrder = $api->order->create($orderData);

$razorpayOrderId = $razorpayOrder['id'];

$_SESSION['razorpay_order_id'] = $razorpayOrderId;

$displayAmount = $amount = $orderData['amount'];

if ($displayCurrency !== 'INR'){
    $url = "https://api.fixer.io/latest?symbols=$displayCurrency&base=INR";
    $exchange = json_decode(file_get_contents($url), true);

    $displayAmount = $exchange['rates'][$displayCurrency] * $amount / 100;
}

$checkout = 'automatic';

if (isset($_GET['checkout']) and in_array($_GET['checkout'], ['automatic', 'manual'], true)){
    $checkout = $_GET['checkout'];
}

$data = [
    "key"               => $keyId,
    "amount"            => $amount,
    "name"              => $orderDataval['email_id'],
    "description"       => "",
    "image"             => "http://jhm2023.com/images/logo-wide.png",
    "prefill"           => [
    "name"              => "",
    "email"             => $orderDataval['email_id'],
    "contact"           => $orderDataval['phone_number'],
    ],
    "notes"             => [
    "address"           => "Hello World",
    "merchant_order_id" => "12312321",
    ],
    "theme"             => [
    "color"             => "#1C75BC"
    ],
    "order_id"          => $razorpayOrderId,
];

if ($displayCurrency !== 'INR'){
    $data['display_currency']  = $displayCurrency;
    $data['display_amount']    = $displayAmount;
}

$json = json_encode($data);

include "header.html";
?>

<section class="page-header page-header-text-light ">
      <div class="container"style="padding-top:0px; padding-bottom:0px;">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h1>Payment</h1>
          </div>
          <div class="col-md-4">
            <ul class="breadcrumb justify-content-start justify-content-md-end mb-0">
              <li><a href="#">Home</a></li>
              <li>Payment</li>
            </ul>
          </div>
        </div>
      </div>
      
                <div class="container" style="padding-top:0px; padding-bottom:0px;">
        <div class="bg-light shadow-md rounded px-4 pt-4">            
          <div class="form-row">
              <div class="col-sm-6 col-md-6">
                <div class="featured-box">
                  <h4 class="padtp_sw"style="color:darkred;"> <span class="amt_size_ar">Are you sure to pay </span>
                      <i class="fas fa-rupee-sign rupees_sybl_ar"></i>
                      <span class="amt_size_ar"><?php echo $orderDataval['amount']; ?></span>
                  </h4>
                </div>
              </div>
              <div class="col-md-6 col-lg-3 form-group">
                <input id="rzp-button1" class="btn btn-primary btn-block" type="Submit" value="Payment" name="submit"/>
              </div>
              <div class="col-md-6 col-lg-3 form-group">
                <input class="btn btn-primary btn-block" type="button" value="Cancle" name="cancle" onClick="window.location.href = '../load.php'"/>
              </div>
          </div>
      </div>
      </div>
     
    </section>
    </div>
    <?php include "footer.html"; ?>
</div>
</body>
</html>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<form name='razorpayform' action="http://jhm2023.com/verify.php" method="POST">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
    <input type="hidden" name="razorpay_orderId"  id="razorpay_orderId"  value="<?php echo $orderDataval['order_id'] ?>">
    <input type="hidden" name="paidAmount"  id="paidAmount"  value="<?php echo $orderDataval['amount'] ?>">
    <input type="hidden" name="UserId"  id="UserId"  value="<?php echo $orderDataval['id'] ?>">
</form>
<script>
// Checkout details as a json
var options = <?php echo $json?>;

/**
 * The entire list of Checkout fields is available at
 * https://docs.razorpay.com/docs/checkout-form#checkout-fields
 */
options.handler = function (response){
    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
    document.getElementById('razorpay_signature').value = response.razorpay_signature;
    document.razorpayform.submit();
};

// Boolean whether to show image inside a white frame. (default: true)
options.theme.image_padding = false;

options.modal = {
    ondismiss: function() {
        console.log("This code runs when the popup is closed");
    },
    // Boolean indicating whether pressing escape key 
    // should close the checkout form. (default: true)
    escape: true,
    // Boolean indicating whether clicking translucent blank
    // space outside checkout form should close the form. (default: false)
    backdropclose: false
};

var rzp = new Razorpay(options);

document.getElementById('rzp-button1').onclick = function(e){
    rzp.open();
    e.preventDefault();
}
</script>
