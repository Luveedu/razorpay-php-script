<!-- ANALYTICS -->
<head>


</head>
<!--- ANALYTICS -->
<?php
session_start();
require 'vendor/autoload.php';

use Razorpay\Api\Api;

$apiKey = 'rzp_live_CEIKkk6nuGfWTF';
$apiSecret = 'T4ruBaRZSvLJlpVIxq6qfMse';
$api = new Api($apiKey, $apiSecret);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);
    
    $orderData = [
        'receipt'         => uniqid('order_'),
        'amount'          => 9900,
        'currency'        => 'INR',
        'payment_capture' => 1 
    ];
    $order = $api->order->create($orderData);
    $orderId = $order['id'];
    $_SESSION['razorpay_order_id'] = $orderId; // Save for later verification

    // Step 3: Display Razorpay checkout
    echo <<<HTML
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <form id="razorpay-form" method="POST">
        <input type="hidden" name="order_id" value="{$orderId}">
        <input type="hidden" name="phone" value="{$phone}">
    </form>
    <script>
        var options = {
            "key": "{$apiKey}",
            "amount": "{$orderData['amount']}",
            "currency": "INR",
            "name": "Free Shop",
            "description": "Payment to Continue to Work",
            "order_id": "{$orderId}",
            "prefill": {
                "contact": "{$phone}"
            },
            "theme": {
                "color": "#F37254"
            },
            "handler": function (response) {
                // Submit payment details to this same file
                var form = document.getElementById("razorpay-form");
                form.action = "make-payment.php";
                var payment_id = document.createElement("input");
                payment_id.type = "hidden";
                payment_id.name = "razorpay_payment_id";
                payment_id.value = response.razorpay_payment_id;
                form.appendChild(payment_id);

                var signature = document.createElement("input");
                signature.type = "hidden";
                signature.name = "razorpay_signature";
                signature.value = response.razorpay_signature;
                form.appendChild(signature);

                form.submit();
            },
            "modal": {
                "ondismiss": function () {
                    window.location.href = document.referrer; // Go back on cancel
                }
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.open();
    </script>
    HTML;
    exit;
}

// Step 4: Handle payment success
if (isset($_POST['razorpay_payment_id']) && isset($_POST['razorpay_signature'])) {
    $razorpayPaymentId = $_POST['razorpay_payment_id'];
    $razorpayOrderId = $_SESSION['razorpay_order_id'];
    $razorpaySignature = $_POST['razorpay_signature'];

    // Verify payment signature
    $generatedSignature = hash_hmac('sha256', $razorpayOrderId . "|" . $razorpayPaymentId, $apiSecret);
    if ($generatedSignature === $razorpaySignature) {
        // Payment success
        unset($_SESSION['razorpay_order_id']);
        header('Location: https://payaurshop.shop/dl.php');
        exit;
    } else {
        // Payment verification failed
        echo "Payment verification failed.";
        exit;
    }
}

// Default case: Invalid request
echo "Invalid request.";
