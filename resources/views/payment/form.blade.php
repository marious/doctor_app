<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter Payment Info</title>
</head>
<body>

<h2>Enter Your Card & Shipping Details</h2>
<p>No charge will be made now.</p>

<iframe
    id="payment_iframe"
    src="https://test.authorize.net/customer/addPayment?token={{ $token }}"
    frameborder="0"
    width="100%"
    height="650px"
    scrolling="no">
</iframe>

<script>
    // Listen for iFrame resize/communication messages from Authorize.net
    window.addEventListener('message', function(event) {
        if (event.data === 'successfulSave' || event.data.includes('transId')) {
            // optionally redirect or show success
        }
    });
</script>

</body>
</html>