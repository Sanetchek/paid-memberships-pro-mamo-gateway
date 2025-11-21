Reverse Payment
post
https://business.mamopay.com/manage_api/v1/charges/{chargeId}/reverses
API to reverse an "On hold" charge.

Path Params
chargeId
string
required
Defaults to CHG-D8B07FB8D7
Transaction ID / Charge ID

CHG-D8B07FB8D7
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
id
string
Defaults to CHG-D8B07FB8D7
status
string
Defaults to voided
amount
number
Defaults to 122.87
amount_currency
string
Defaults to AED
refund_amount
string
Defaults to 0
refund_status
string
Defaults to No refund
billing_descriptor
string
custom_data
object
Has additional fields
created_date
string
subscription_id
string
Value is set for recurring payments only. For one-time payments, this will be null.

next_payment_date
string
Value is set for recurring payments only. For one-time payments, this will be null.

settlement_amount
number
settlement_currency
string
settlement_date
string
customer_details
object
name
string
email
string
phone_number
string
comment
string
payment_method
object
type
string
card_holder_name
string
card_last4
string
origin
string
settlement_fee
string
settlement_vat
string
payment_link_id
string
payment_link_url
string
error_code
string
error_message
string

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
Response body
object
status
number
error
string



Servers
https://sandbox.dev.business.mamopay.com/manage_api/v1/charges/{chargeId}/reverses
Sandbox server
https://business.mamopay.com/manage_api/v1/charges/{chargeId}/reverses
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/charges/CHG-D8B07FB8D7/reverses",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
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
  "id": "CHG-D8B07FB8D7",
  "status": "voided",
  "amount": 122.87,
  "amount_currency": "AED",
  "refund_amount": "string",
  "refund_status": "No refund",
  "billing_descriptor": "string",
  "custom_data": {},
  "created_date": "string",
  "subscription_id": "string",
  "next_payment_date": "string",
  "settlement_amount": 0,
  "settlement_currency": "string",
  "settlement_date": "string",
  "customer_details": {
    "name": "string",
    "email": "string",
    "phone_number": "string",
    "comment": "string"
  },
  "payment_method": {
    "type": "string",
    "card_holder_name": "string",
    "card_last4": "string",
    "origin": "string"
  },
  "settlement_fee": "string",
  "settlement_vat": "string",
  "payment_link_id": "string",
  "payment_link_url": "string",
  "error_code": "string",
  "error_message": "string"
}