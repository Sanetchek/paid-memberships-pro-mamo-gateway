Refund Payment
post
https://business.mamopay.com/manage_api/v1/charges/{chargeId}/refunds
API to refund the charge.

Path Params
chargeId
string
required
Defaults to MB-LINK-D8B07FB8C7
Transaction ID / Charge ID

MB-LINK-D8B07FB8C7
Body Params
amount
number
required
≥ 1
Defaults to 10
Amount to be refunded. Only AED transfers supported

10
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
refund_status
string
refund_amount
number

400
Bad Request


403
Unauthorised
Response body
object
messages
array of strings
error_code
string

422
Unprocessable Entity
Response body
object
messages
array of strings
error_code
string

500
Unexpected error

https://sandbox.dev.business.mamopay.com/manage_api/v1/charges/{chargeId}/refunds
Sandbox server
https://business.mamopay.com/manage_api/v1/charges/{chargeId}/refunds
Production server

<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/charges/MB-LINK-D8B07FB8C7/refunds",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'amount' => 10
  ]),
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "content-type: application/json"
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
  "refund_amount": "20.0,",
  "refund_status": "success"
}