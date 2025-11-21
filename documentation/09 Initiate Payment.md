Initiate Payment
post
https://business.mamopay.com/manage_api/v1/charges
API to initiate transactions by merchant.

About MIT (Merchant Initiated Transaction)
Merchant Initiated Transactions (MIT) allows a business to use card details, that were stored during previous transactions, to charge their customers.

How do MITs work?
1- You request a payment link with the option to save the card details. 2- You save the charge ID. 3- You get the charge details (redirect, GET /charge, or webhook) which will include the ID of the card used to make the payment, you can save either one of the IDs so you always have access to the card ID. 4- You call the below API to initiate a transaction using the same card.

Body Params
card_id
string
required
Saved card ID retrieved from the initial transaction made with MIT-enabled link.

amount
number
required
≥ 1
Defaults to 10
Amount to be charged.

10
currency
string
Defaults to AED
The three-letter ISO currency code, default is AED

AED
send_customer_receipt
boolean
Defaults to true
Enables the sending of customer receipts.


true
custom_data
object
Key-value object that can be used to pass custom data.


custom_data object
external_id
string
The external ID allows non Mamo IDs to be associated with a given charge.

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
status
string
enum
captured failed

id
string
amount
number
amount_currency
string
refund_amount
number
refund_status
string
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
https://sandbox.dev.business.mamopay.com/manage_api/v1/charges
Sandbox server
https://business.mamopay.com/manage_api/v1/charges
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/charges",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'amount' => 10,
    'currency' => 'AED',
    'send_customer_receipt' => true
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
  "status": "captured",
  "id": "MPB-CHRG-E0CE93E071",
  "amount": 100,
  "amount_currency": "AED",
  "refund_amount": 0,
  "refund_status": "No refund",
  "billing_descriptor": "Mamo*Merchant",
  "custom_data": {
    "internal_customer_id": "0114120d-c394-4cce-9cdb-4ccbae605748",
    "val1": true,
    "val2": "custom value"
  },
  "created_date": "2023-05-31-11-18-57",
  "subscription_id": "MPB-SUB-B764EDCBA2",
  "next_payment_date": "05/06/2023",
  "settlement_amount": 356.42,
  "settlement_currency": "AED",
  "settlement_date": "05/06/2023",
  "customer_details": {
    "name": "Mamo User",
    "email": "email@mamopay.com",
    "phone_number": "+971551234567",
    "comment": "Dolore voluptate possimus et."
  },
  "payment_method": {
    "type": "CREDIT VISA",
    "card_holder_name": "Mamo User",
    "card_last4": "•••• 4242",
    "origin": "UAE card"
  },
  "error_code": "generic",
  "error_message": "Unknown reason - user should contact their bank to find out"
}