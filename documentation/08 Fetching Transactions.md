Fetching Transactions
get
https://business.mamopay.com/manage_api/v1/charges
Fetches all transactions for a given business

Query Params
page
integer
per_page
integer
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
data
array of objects
object
id
string
Defaults to MPB-CHRG-D8B07FB8D7
status
string
Defaults to captured
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

customer_details object
payment_method
object

payment_method object
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
pagination_meta
object
page
number
per_page
number
total_pages
number
next_page
number
prev_page
number
from
number
to
number
total_count
number

403
Unauthorised
Response body
object
messages
array of strings
error_code
string

500
Unexpected error


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
  "data": [
    {
      "status": "captured"
    },
    {
      "id": "MPB-CHRG-E0CE93E071"
    },
    {
      "amount": 100
    },
    {
      "refund_amount": 0
    },
    {
      "refund_status": "No refund"
    },
    {
      "billing_descriptor": "Mamo*Merchant"
    },
    {
      "custom_data": {
        "internal_customer_id": "0114120d-c394-4cce-9cdb-4ccbae605748",
        "val1": true,
        "val2": "custom value"
      }
    },
    {
      "created_date": "2023-05-31-11-18-57"
    },
    {
      "subscription_id": "MPB-SUB-B764EDCBA2"
    },
    {
      "next_payment_date": "05/06/2023"
    },
    {
      "settlement_amount": 356.42
    },
    {
      "settlement_currency": "AED"
    },
    {
      "settlement_date": "05/06/2023"
    },
    {
      "customer_details": {
        "name": "Mamo User",
        "email": "email@mamopay.com",
        "phone_number": "+971551234567",
        "comment": "Dolore voluptate possimus et."
      }
    },
    {
      "payment_method": {
        "type": "CREDIT VISA",
        "card_holder_name": "Mamo User",
        "card_last4": "•••• 4242",
        "origin": "UAE card"
      }
    },
    {
      "settlement_fee": "AED 3.20"
    },
    {
      "settlement_vat": "AED 0.16"
    },
    {
      "payment_link_id": "MB-LINK-01E6ADB6DE"
    },
    {
      "payment_link_url": "https://staging.business.mamopay.com/pay/dong22-579f10"
    },
    {
      "error_code": "generic"
    },
    {
      "error_message": "Unknown reason - user should contact their bank to find out"
    }
  ],
  "pagination_meta": {
    "page": 1,
    "per_page": 10,
    "total_pages": 5,
    "next_page": 2,
    "prev_page": null,
    "from": 1,
    "to": 10,
    "total_count": 47
  }
}