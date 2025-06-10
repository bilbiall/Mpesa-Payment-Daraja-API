include 'accessToken.php';
date_default_timezone_set('Africa/Nairobi');

if (!isset($access_token) || empty($access_token)) {
    die("Failed to get access token");
}

/*if (!isset($_POST['phone']) || !preg_match('/^2547[0-9]{8}$/', $_POST['phone'])) {
    die("Invalid phone number");
}*/

//$phone = $_POST['phone'];
//$amount = (int) $_POST['price'];

$phone = '254707629433';
$amount = '1';

$BusinessShortCode = '174379';
$passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$callbackurl = 'https://yourdomain.com/callbacks/mpesa.php';
$reference = '25Flow.ke';
$desc = 'STK Push';
$timestamp = date('YmdHis');
$password = base64_encode($BusinessShortCode . $passkey . $timestamp);

$postData = [
    'BusinessShortCode' => $BusinessShortCode,
    'Password' => $password,
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phone,
    'PartyB' => $BusinessShortCode,
    'PhoneNumber' => $phone,
    'CallBackURL' => $callbackurl,
    'AccountReference' => $reference,
    'TransactionDesc' => $desc
];

$headers = [
    'Content-Type:application/json',
    'Authorization:Bearer ' . $access_token
];

$curl = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$curl_response = curl_exec($curl);
$response = json_decode($curl_response);
curl_close($curl);

if (isset($response->ResponseCode) && $response->ResponseCode == "0") {
    echo "STK Push Sent. CheckoutRequestID: " . $response->CheckoutRequestID;
} else {
    error_log("STK Push Error: " . print_r($response, true));
    echo "STK Push failed: " . ($response->errorMessage ?? 'Unknown error');
}
