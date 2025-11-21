Cancel Recurring Payment
delete
https://business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}
Cancels an existing recurring payment. This is NOT to unsubscribe a customer from a recurring payment that they have subscribed to. This deletes a previously created subscription for a business.

Path Params
subscriptionId
string
required
Subscription ID

Headers
Content-Type
string
Authorization
string
Responses

200
Successful response

Response body
object
success
boolean

403
Unauthorised
Response body
object
messages
array of strings
error_code
string

404
Not Found
Response body
object
messages
array of strings
error_code
string

500
Unexpected error




Servers
https://sandbox.dev.business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}
Sandbox server
https://business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}
Production server



<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/subscriptions/subscriptionId",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "DELETE",
  CURLOPT_HTTPHEADER => [
    "accept: application/json"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}



Response 200:
{
  "success": true
}