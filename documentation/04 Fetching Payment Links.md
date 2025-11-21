Fetching Payment Links
get
https://business.mamopay.com/manage_api/v1/links
Fetches all payment links for a given business

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
name
string
length between 1 and 75
Defaults to Chocolate Box - Small
The title of the payment link

description
string
length ≤ 75
Defaults to 12pcs Chocolate Box
Payment description. This will appear on the payment checkout page.

capacity
integer
Defaults to 1
The number of times a payment link can be used. The capacity will be ignored when the subscription params exist.

active
boolean
Defaults to true
return_url
uri
Defaults to https://myawesomewebsite.com/paymentSuccess
The URL which the customer will be redirected to after a successful payment.

processing_fee_percentage
number
≥ 2
Defaults to 3
amount
number
≥ 2
Defaults to 119.99
amount_currency
string
enum
Defaults to AED
AED USD EUR GBP SAR

link_type
string
enum
Defaults to standalone
Type of link to be created.

standalone modal inline

enable_tabby
boolean
Defaults to false
Enables the ability for customers to buy now and pay later.

enable_message
boolean
Defaults to false
Enables the ability for customers to add a message during the checkout process.

enable_tips
boolean
Defaults to false
Enables the tips option. This will be displayed on the first screen.

enable_customer_details
boolean
Defaults to false
Enables adding customer details such as the name, email, and phone number. This screen will be displayed before the payment details screen.

enable_quantity
boolean
Defaults to false
When enabled, customers can specify the number of items they intend to purchase. This quantity will serve as a multiplier for the base amount.

enable_qr_code
boolean
Defaults to false
Adds the ability to verify a payment through a QR code.

send_customer_receipt
boolean
Defaults to false
Enables the sending of customer receipts.

save_card
string
enum
Defaults to off
Allows the merchant to enable the option to store card details to be used later on for Merchant Initiated Transactions.

off optional required

payment_methods
array
An array of accepted payment methods, card always apart of default option. Example: ['card', 'wallet']

rules
object
Setting the rule for payment link


rules object
subscription
object
to be populated if this payment link is for a recurring payment. Otherwise, this property can be left out. REQUIRES Premium Business Plan to be enabled.


subscription object
first_name
string
The first name of customer which will pre-populate in card info step.

last_name
string
The last name of customer which will pre-populate in card info step.

email
string
The email of customer which will pre-populate in card info step.

payouts_share
object

payouts_share object
custom_data
object
Has additional fields
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
https://sandbox.dev.business.mamopay.com/manage_api/v1/links
Sandbox server
https://business.mamopay.com/manage_api/v1/links
Production server

<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/links",
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


RESPONSE 200:
{
  "data": [
    {
      "name": "Chocolate Box - Small"
    },
    {
      "description": "12pcs Chocolate Box"
    },
    {
      "capacity": 1
    },
    {
      "active": true
    },
    {
      "return_url": "https://myawesomewebsite.com/paymentSuccess"
    },
    {
      "processing_fee_percentage": 3
    },
    {
      "amount": 119.99
    },
    {
      "amount_currency": "AED"
    },
    {
      "enable_message": true
    },
    {
      "enable_tips": true
    },
    {
      "enable_tabby": true
    },
    {
      "link_type": "modal"
    },
    {
      "enable_customer_details": true
    },
    {
      "save_card": "optional"
    },
    {
      "hold_and_charge_later": false
    },
    {
      "payment_methods": [
        "card",
        "wallet"
      ]
    },
    {
      "rules": {
        "allowed": [
          {
            "type": "bins"
          },
          {
            "list": [
              "424242"
            ]
          },
          {
            "decline_message": "Custom decline message for this rule"
          }
        ]
      }
    },
    {
      "subscription": {
        "frequency": "monthly",
        "frequency_interval": 1,
        "end_date": "2023/01/31",
        "payment_quantity": 5
      }
    },
    {
      "first_name": "first name"
    },
    {
      "last_name": "last name"
    },
    {
      "email": "email@mamopay.com"
    },
    {
      "payouts_share": {
        "recipient_id": "REP-123",
        "percentage_to_recipient": 10,
        "recipient_pays_fees": false
      }
    },
    {
      "custom_data": {
        "val1": true,
        "val2": "custom value"
      }
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