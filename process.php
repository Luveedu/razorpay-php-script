<?php
// Check if the POST request contains 'phone'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone'])) {
    $phone = htmlspecialchars($_POST['phone']); // Sanitize the input

    // Pass the phone value to JavaScript
    echo '
    <!DOCTYPE html>
    <html>
        <head>
            <title>Processing...</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <script>
            const phone = "' . $phone . '"; // Pass PHP variable to JavaScript
            console.log("Phone:", phone);

            // Wait for 10 seconds
            setTimeout(() => {
                // Create a form and submit it to make-payment.php
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "make-payment.php";

                // Add the phone field to the form
                const phoneField = document.createElement("input");
                phoneField.type = "hidden";
                phoneField.name = "phone";
                phoneField.value = phone; // Use the phone variable in JavaScript
                form.appendChild(phoneField);

                // Append the form to the body and submit it
                document.body.appendChild(form);
                form.submit();
            }, 1000);
        </script>
        <body style="background: #e7eaff; display: flex; align-items: center; justify-content: center; min-height: 95vh; overflow: hidden;">
            <div>
                <img src="loading.gif" style="height: 50px; width: 50px; mix-blend-mode: multiply;">
                <br>
                <b style="display: block; margin-top: 15px; font-family: Arial;">Loading...</b>
            </div>
        </body>
    </html>
    ';
} else {
    echo "Unable to Process the Order.";
    exit;
}
?>
