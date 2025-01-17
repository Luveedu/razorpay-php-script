# Razorpay Checkout Script

Just install Razorpay SDK using composer

```
composer install razorpay
```

And then simply Update the make-payment.php file in the location.

An in form header add this
```
<form action="make-payment.php" method="POST">
```

Make sure the input field has a name="phone" for getting the phone number.
```
<input type="text" name="phone">
```

-----------

## make-payment.php
This php code simply pulls the phone number from the from and then use it and request the Razorpay Checkout.

-----------

## make-payment-2.php
This php code also pulls the phone number from the form, but also it generates a human alike generated email address and use the phone number and email in the checkout.

-----------

## process.php
This PHP code is a small loading which helps to create a blank loading screen and wait for a second and then redirect to the make-payment.php file and loads the checkout.
