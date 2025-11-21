Fetch Transaction Info
get
https://business.mamopay.com/manage_api/v1/charges/{chargeId}
This API enables you to retrieve detailed information about a specific charge by providing the charge ID. It is designed to give you a comprehensive view of transaction details.

Path Params
chargeId
string
required
Defaults to MPB-CHRG-E0CE93E071
Transaction ID / Charge ID

MPB-CHRG-E0CE93E071
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
confirmation_required captured refund_initiated processing failed refunded

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
enum
CREDIT MASTERCARD CREDIT VISA CREDIT AMERICAN EXPRESS DEBIT MASTERCARD DEBIT VISA DEBIT AMERICAN EXPRESS

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
https://sandbox.dev.business.mamopay.com/manage_api/v1/charges/{chargeId}
Sandbox server
https://business.mamopay.com/manage_api/v1/charges/{chargeId}
Production server

<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/charges/MPB-CHRG-E0CE93E071",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
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