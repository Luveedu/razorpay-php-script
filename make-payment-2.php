<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'vendor/autoload.php';

use Razorpay\Api\Api;

// Function to generate random Gmail addresses by mixing first and last names with numbers
function generateRandomGmail()
{
    $firstNames = ['aarav', 'vihaan', 'adi', 'reyansh', 'arjun', 'vivaan', 'siddharth', 'rohan', 'krishna', 'dev', 
                   'parth', 'aniket', 'yash', 'manan', 'harsh', 'varun', 'raj', 'amit', 'vikas', 'ansh', 'aakash', 
                   'arnav', 'shivansh', 'samarth', 'om', 'vedant', 'siddhi', 'nisha', 'shruti', 'sonal', 'meera', 
                   'kritika', 'anu', 'ishita', 'priyanka', 'simran', 'neha', 'tanvi', 'saumya', 'ridhima', 'roshni', 
                   'manisha', 'payal', 'jhanvi', 'sakshi', 'navya', 'pranjal', 'pihu', 'deepika', 'aarti'];

    $lastNames = ['sharma', 'kumar', 'patel', 'rathod', 'varma', 'singh', 'rao', 'pandit', 'mehta', 'desai', 'nair', 
                  'joshi', 'gupta', 'bhat', 'shetty', 'agarwal', 'malik', 'bhushan', 'kumawat', 'yadav', 'dhillon', 
                  'gupta', 'kapoor', 'saxena', 'tiwari', 'shukla', 'pandya', 'chauhan', 'deshmukh', 'chaudhary', 'jain', 
                  'narayan', 'bhalla', 'singhal', 'gupta', 'prasad', 'bhagat', 'mehrotra', 'saxena', 'chawla', 'kashyap', 
                  'agarwal', 'khandelwal', 'rani', 'rathi', 'thakur', 'mishra', 'chaudhary', 'kapur', 'batra'];

    // Select random first name, last name
    $randomFirstName = $firstNames[array_rand($firstNames)];
    $randomLastName = $lastNames[array_rand($lastNames)];

    // Generate a random number with 2 to 5 digits
    $numDigits = rand(2, 5); // Random number of digits between 2 and 5
    $randomNumber = rand(pow(10, $numDigits - 1), pow(10, $numDigits) - 1); // Generate a number with that many digits

    // Combine them into a unique email
    $combinedName = $randomFirstName . $randomLastName . $randomNumber;

    return $combinedName . '@gmail.com';
}

$email = generateRandomGmail();
?>

<script>
// Pass the generated email from PHP to JavaScript and log it to the console
var generatedEmail = "<?php echo $email; ?>";
console.log("Generated Email: " + generatedEmail);
</script>

<?php
// Razorpay API Key and Secret
$apiKey = 'your api key';
$apiSecret = 'your api secrect';

$api = new Api($apiKey, $apiSecret);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: Sanitize phone input
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);

    // Step 2: Generate random email address
    $randomEmail = generateRandomGmail();

    // Step 3: Create an order
    try {
        $orderData = [
            'receipt'         => uniqid('order_'),
            'amount'          => 9900, // Amount in paise (e.g., 9900 = ₹99.00)
            'currency'        => 'INR',
            'payment_capture' => 1 // Auto-capture enabled
        ];
        $order = $api->order->create($orderData);
        $orderId = $order['id'];
        $_SESSION['razorpay_order_id'] = $orderId; // Save for later verification
    } catch (Exception $e) {
        echo "Order creation error: " . $e->getMessage();
        exit;
    }

    // Step 4: Display Razorpay checkout
    echo <<<HTML
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <form id="razorpay-form" method="POST">
        <input type="hidden" name="order_id" value="{$orderId}">
        <input type="hidden" name="phone" value="{$phone}">
        <input type="hidden" name="email" value="{$randomEmail}">
    </form>
    <script>
        var options = {
            "key": "{$apiKey}",
            "amount": "{$orderData['amount']}",
            "currency": "INR",
            "name": "My F99 Shop",
            "description": "Payment to Continue to Work",
            "order_id": "{$orderId}",
            "prefill": {
                "contact": "{$phone}",
                "email": "{$randomEmail}"
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

// Step 5: Handle payment success
if (isset($_POST['razorpay_payment_id']) && isset($_POST['razorpay_signature'])) {
    $razorpayPaymentId = $_POST['razorpay_payment_id'];
    $razorpayOrderId = $_SESSION['razorpay_order_id'] ?? '';
    $razorpaySignature = $_POST['razorpay_signature'];

    // Verify payment signature
    $generatedSignature = hash_hmac('sha256', $razorpayOrderId . "|" . $razorpayPaymentId, $apiSecret);

    if ($generatedSignature === $razorpaySignature) {
        // Payment success
        unset($_SESSION['razorpay_order_id']); // Clear session data
        header('Location: https://example.com/dl.php');
        exit;
    } else {
        // Payment verification failed
        echo "Payment verification failed. Signature mismatch.";
        exit;
    }
}

// Default case: Invalid request
echo "Invalid request.";
