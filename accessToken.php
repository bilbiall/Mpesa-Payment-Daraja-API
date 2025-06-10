<?php
//MPESA API KEYS 
$consumerKey = ''; //Fill with your app Consumer Key from developer portal
$consumerSecret = ''; //Fill with your app Consumer Secret from saf developer portal

//for better security get them from a database , I assume the connection has been done
// Assuming $db is your mysqli object

/*$query = $db->query("SELECT consumer_key, consumer_secret FROM mpesa_credentials LIMIT 1");
$creds = $query->fetch_assoc();

$consumerKey = $creds['consumer_key'];
$consumerSecret = $creds['consumer_secret'];

if (!$consumerKey || !$consumerSecret) {
    die("❌ M-Pesa credentials missing in DB");
}
*/

//ACCESS TOKEN URL from dev portal // M-Pesa Sandbox OAuth URL used to request an access token (part of API authentication)
$access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

// HTTP header to tell the API you're sending/receiving JSON
$headers = ['Content-Type:application/json; charset=utf8'];


// Initialize a new cURL session targeting the M-Pesa OAuth endpoint
$curl = curl_init($access_token_url);


// Set HTTP headers for the request (telling Safaricom API we're working with JSON)
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

// Tells cURL to return the response as a string instead of printing it out
curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

// Do not include the header in the response output
curl_setopt($curl, CURLOPT_HEADER, FALSE);


/* Provide the app credentials (Basic Auth) in the format: username:password
 In this case, it's consumerKey:consumerSecret*/
curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);

// Execute the HTTP request and get the response from Safaricom
$result = curl_exec($curl);


// Get the HTTP status code (e.g., 200 OK, 400 Bad Request)
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);


// Decode the JSON response into a PHP object
$result = json_decode($result);

// Access and display the access_token from the decoded response, to be sure the access token is fetched
echo $access_token = $result->access_token;


// Close the cURL session to free up system resource
curl_close($curl);


