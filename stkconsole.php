<?php 

/**
 * ajax -> payments -> mpesa
 * 
 * @package Sngine
 */

// fetch bootstrap
//require('../../../bootstrap.php');

//include access token
include 'accessToken.php';

//timezone
date_default_timezone_set('Africa/Nairobi');


// check AJAX Request
//is_ajax();

// user access
//user_access(true, true);

// check if M-Pesa is enabled
/*if (!$system['mpesa_enabled']) {
  modal("MESSAGE", __("Error"), __("M-Pesa payments are currently disabled"));
}*/

// return array
//$return = [];

// load M-Pesa credentials from database
// $consumerKey = $system['mpesa_consumer_key'];
// $consumerSecret = $system['mpesa_consumer_secret'];
// $passkey = $system['mpesa_passkey'];
// $BusinessShortCode = $system['mpesa_shortcode'];
// $callbackurl = $system['mpesa_callback_url'];

$consumerKey = 'MAxBeOgjN88E9cyQMsDn5SAdUl38Cwc67qf2osJCAf1ATO5M';
$consumerSecret = '00jmlC99rdzclj36EV3qxOKdeQQHjIAl1Al9VuUuy4AGEpGB5gk2gA1aqTcC8zwX';
$passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
$BusinessShortCode = '174379';
$callbackurl = 'mpesa_callback_url';
$timestamp = date('YmdHis');
$reference =  '25Flow.ke';
$desc = 'STK Push';

$processrequestUrl = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest'; //mpesa daraja api merchant express after the get,,the link apo juu


//regex for formatting the phone number
/*if (!isset($_POST['phone']) || !preg_match('/^2547[0-9]{8}$/', $_POST['phone'])) {
  _error(__("Error"), __("Invalid phone number"));
}*/

//data from ajax inputs and session eg when the handle = wallet etc
// $phone = $_POST['phone'];
// $amount = (int) $_POST['price'];

$phone = '254707629433';
$amount = '1';

//encrypt password
$password = base64_encode($BusinessShortCode . $passkey . $timestamp);

$PartyA = $phone;
$PartyB = '254708374149';
$headers = [
  'Content-Type:application/json',
   'Authorization:Bearer ' . $access_token]; //access token used here

//initialize curl 
$curl = curl_init($processrequestUrl);
curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers); //setting custom header  

$postData = array(
  //request parameters
  'BusinessShortCode' => $BusinessShortCode,
  'Password' => $password,
  'Timestamp' => $timestamp,
  'TransactionType' => 'CustomerPayBillOnline',
  'Amount' => $amount,
  'PartyA' => $PartyA,
  'PartyB' => $BusinessShortCode,
  'PhoneNumber' => $PartyA,
  'CallBackURL' => $callbackurl,
  'AccountReference' => $reference,
  'TransactionDesc' => $desc
);

//encode the data and store in data_string
//$data_string = json_encode($postData);

curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


//execute curl
$curl_response = curl_exec($curl);

//decode the json response
$response = json_decode($curl_response);
$CheckoutRequestID = $response->CheckoutRequestID;
echo $CheckoutRequestID;
curl_close($curl);
return $response;


// 4. HANDLE REQUEST HERE – controller logic ie if is wallet, pay, donate etc
/*try {
  if (!isset($_POST['handle'])) {
    _error(400);
  }

  $access_token = $access_token
  if (!$access_token) {
    throw new Exception("Failed to generate M-Pesa access token");
  }

  switch ($_POST['handle']) {
    case 'wallet':
      if (!isset($_POST['price']) || !is_numeric($_POST['price']) || !isset($_POST['phone'])) {
        _error(400);
      }
      $amount = $_POST['price'];
      $phone = $_POST['phone'];
      $desc = "Wallet top-up";
      $ref = "WalletUser" . $user->_data['user_id'];
      $response = sendStkPush($phone, $amount, $ref, $desc, $access_token, $BusinessShortCode, $passkey, $callbackurl);
      //$return['checkout_id'] = $response->CheckoutRequestID ?? null;
      $return = [
       'success' => true,
       'message' => 'STK Push initiated',
       'phone' => $phone,
       'amount' => $amount,
       'checkout_id' => $response->CheckoutRequestID ?? null
      ];
      break;

    case 'packages':
      if (!isset($_POST['package_id']) || !is_numeric($_POST['package_id']) || !isset($_POST['phone'])) {
        _error(400);
      }
      $package = $user->get_package($_POST['package_id']);
      if (!$package) {
        _error(400);
      }
      $phone = $_POST['phone'];
      $amount = $package['price'];
      $desc = "Subscription Package";
      $ref = "Package" . $package['package_id'];
      $response = sendStkPush($phone, $amount, $ref, $desc, $access_token, $BusinessShortCode, $passkey, $callbackurl);
      //$return['checkout_id'] = $response->CheckoutRequestID ?? null;
      $return = [
       'success' => true,
       'message' => 'STK Push initiated',
       'phone' => $phone,
       'amount' => $amount,
       'checkout_id' => $response->CheckoutRequestID ?? null
      ];
      break;
    

    case 'donate':
      if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id']) || !isset($_POST['phone']) || !isset($_POST['price'])) {
        _error(400);
      }
      $post = $user->get_post($_POST['post_id']);
      if (!$post) {
        _error(400);
      }
      $phone = $_POST['phone'];
      $amount = $_POST['price'];
      $desc = "Post Donation";
      $ref = "DonatePost" . $_POST['post_id'];
      $response = sendStkPush($phone, $amount, $ref, $desc, $access_token, $BusinessShortCode, $passkey, $callbackurl);
      //$return['checkout_id'] = $response->CheckoutRequestID ?? null;
      $return = [
       'success' => true,
       'message' => 'STK Push initiated',
       'phone' => $phone,
       'amount' => $amount,
       'checkout_id' => $response->CheckoutRequestID ?? null
      ];
      break;

    case 'subscribe':
      if (!isset($_POST['plan_id']) || !is_numeric($_POST['plan_id']) || !isset($_POST['phone'])) {
        _error(400);
      }
      $plan = $user->get_monetization_plan($_POST['plan_id'], true);
      if (!$plan) {
        _error(400);
      }
      $phone = $_POST['phone'];
      $amount = $plan['price'];
      $desc = "Creator Subscription";
      $ref = "Plan" . $plan['node_id'];
      $response = sendStkPush($phone, $amount, $ref, $desc, $access_token, $BusinessShortCode, $passkey, $callbackurl);
      //$return['checkout_id'] = $response->CheckoutRequestID ?? null;
      $return = [
       'success' => true,
       'message' => 'STK Push initiated',
       'phone' => $phone,
       'amount' => $amount,
       'checkout_id' => $response->CheckoutRequestID ?? null
      ];
      
      break;

    default:
      _error(400);
  }

  echo json_encode($return);

} catch (Exception $e) {
  _error(__("Error in Mpesa"), $e->getMessage()); */
//}














