Update Payment Link
patch
https://business.mamopay.com/manage_api/v1/links/{linkId}
Allows a user to update payment link details

Path Params
linkId
string
required
Defaults to MB-LINK-6BB7CA8DC7
Payment link ID

MB-LINK-6BB7CA8DC7
Body Params
name
string
length between 1 and 75
Defaults to Chocolate Box - Small
The title of the payment link

Chocolate Box - Small
description
string
length ≤ 75
Defaults to 12pcs Chocolate Box
Payment description. This will appear on the payment checkout page.

12pcs Chocolate Box
capacity
integer
Defaults to 1
The number of times a payment link can be used. The capacity will be ignored when the subscription params exist.

1
active
boolean
Defaults to true

true
return_url
uri
Defaults to https://myawesomewebsite.com/paymentSuccess
The URL which the customer will be redirected to after a successful payment.

https://myawesomewebsite.com/paymentSuccess
failure_return_url
uri
Defaults to https://failureurl.com/paymentFailure
The URL which the customer will be redirected to after a failure payment.

https://failureurl.com/paymentFailure
processing_fee_percentage
number
≥ 2
Defaults to 3
3
amount
number
≥ 2
Defaults to 119.99
amount could be 0 with save_card 'required' option for card verification

119.99
amount_currency
string
enum
Defaults to AED

AED
Allowed:

AED

USD

EUR

GBP

SAR
link_type
string
enum
Defaults to standalone
Type of link to be created.


standalone
Allowed:

standalone

modal

inline
enable_tabby
boolean
Defaults to false
Enables the ability for customers to buy now and pay later.


false
enable_message
boolean
Defaults to false
Enables the ability for customers to add a message during the checkout process.


false
enable_tips
boolean
Defaults to false
Enables the tips option. This will be displayed on the first screen.


false
save_card
string
enum
Defaults to off
Allows the merchant to enable the option to store card details to be used later on for Merchant Initiated Transactions.


off
Allowed:

off

optional

required
enable_customer_details
boolean
Defaults to false
Enables adding customer details such as the name, email, and phone number. This screen will be displayed before the payment details screen.


false
enable_quantity
boolean
Defaults to false
When enabled, customers can specify the number of items they intend to purchase. This quantity will serve as a multiplier for the base amount.


false
enable_qr_code
boolean
Defaults to false
Adds the ability to verify a payment through a QR code.


false
send_customer_receipt
boolean
Defaults to false
Enables the sending of customer receipts.


false
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

external_id
string
hold_and_charge_later
boolean
Defaults to false
Indicates whether to place the payment on hold and charge it later using the "captures" API.


false
custom_data
object

custom_data object
payouts_share
object

payouts_share object
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
name
string
description
string
capacity
integer
active
boolean
return_url
string
failure_return_url
string
processing_fee_percentage
number
link_type
string
amount
number
amount_currency
string
send_customer_receipt
boolean
enable_tabby
boolean
enable_message
boolean
enable_tips
boolean
save_card
string
enable_quantity
boolean
enable_customer_details
boolean
payment_url
string
first_name
string
last_name
string
email
string
external_id
string
custom_data
object
Has additional fields
payment_methods
array
rules
object
allowed
object

allowed object
subscription
object
identifier
string
repeats_every
string
frequency_interval
integer
start_date
string
end_date
string
payment_quantity
integer
frequency
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
Invalid request

500
Unexpected error


Servers
https://sandbox.dev.business.mamopay.com/manage_api/v1/links/{linkId}
Sandbox server
https://business.mamopay.com/manage_api/v1/links/{linkId}
Production server

<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/links/MB-LINK-6BB7CA8DC7",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PATCH",
  CURLOPT_POSTFIELDS => json_encode([
    'name' => 'Chocolate Box - Small',
    'description' => '12pcs Chocolate Box',
    'capacity' => 1,
    'active' => true,
    'return_url' => 'https://myawesomewebsite.com/paymentSuccess',
    'failure_return_url' => 'https://failureurl.com/paymentFailure',
    'processing_fee_percentage' => 3,
    'amount' => 119.99,
    'amount_currency' => 'AED',
    'link_type' => 'standalone',
    'enable_tabby' => false,
    'enable_message' => false,
    'enable_tips' => false,
    'save_card' => 'off',
    'enable_customer_details' => false,
    'enable_quantity' => false,
    'enable_qr_code' => false,
    'send_customer_receipt' => false,
    'hold_and_charge_later' => false
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
  "id": "MB-LINK-D8B07FB8C7",
  "name": "Chocolate Box - Small",
  "description": "12pcs Chocolate Box",
  "capacity": 15,
  "active": true,
  "return_url": "https://myawesomewebsite.com/paymentSuccess",
  "failure_return_url": "https://failurewebsite.com/paymentFailure",
  "amount": 219.99,
  "send_customer_receipt": true,
  "payment_url": "https://staging.business.mamopay.com/pay/dong22-579f10",
  "external_id": "exteral_id_1",
  "payment_methods": [
    "card",
    "wallet"
  ],
  "hold_and_charge_later": false,
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
  },
  "subscription": {
    "identifier": "MPB-SUB-2162171A86",
    "frequency": "monthly",
    "frequency_interval": 1,
    "start_date": "2023/01/01",
    "end_date": "2023/01/31",
    "payment_quantity": 5
  },
  "custom_data": {
    "internal_customer_id": "0114120d-c394-4cce-9cdb-4ccbae605748",
    "val1": true,
    "val2": "custom value"
  }
}