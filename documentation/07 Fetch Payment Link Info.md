Fetch Payment Link Info
get
https://business.mamopay.com/manage_api/v1/links/{linkId}
Allows a user to fetch payment link details

Path Params
linkId
string
required
Defaults to MB-LINK-6BB7CA8DC7
Payment link ID

MB-LINK-6BB7CA8DC7
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

allowed
array of objects
An array of one or more rules

object
type
string
enum
name of rules

bins

list
array
value of bins

decline_message
string
Custom decline message for this rule

subscription
object
to be populated if this payment link is for a recurring payment. Otherwise, this property can be left out. REQUIRES Premium Business Plan to be enabled.

frequency
string
enum
defines the interval that this subscription will be run on.

annually monthly weekly

frequency_interval
integer
≥ 1
defines how often this subscription will run. This will be based on the frequency property defined above.

end_date
string
the last date this subscription could run on.

payment_quantity
integer
number of times this subscription will occur. If end_date defined, end_date takes precedence.

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
recipient_id
string
The ID of an already added recipient that the transaction amount will be shared with.

percentage_to_recipient
number
The percentage of the transaction amount that will be sent to the recipient.

recipient_pays_fees
boolean
Whether Mamo fees for a given transaction will be passed on to the recipient.

custom_data
object
Has additional fields
charges
array of objects
object
status
string
enum
confirmation_required captured refund_initiated processing failed refunded

id
string
amount
number
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

403
Unauthorised
Response body
object
messages
array of strings
error_code
string

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
  "name": "Chocolate Box - Small",
  "description": "12pcs Chocolate Box",
  "capacity": 1,
  "active": true,
  "return_url": "https://myawesomewebsite.com/paymentSuccess",
  "processing_fee_percentage": 3,
  "amount": 119.99,
  "amount_currency": "AED",
  "enable_message": true,
  "enable_tips": true,
  "enable_tabby": true,
  "link_type": "modal",
  "enable_customer_details": true,
  "save_card": "optional",
  "hold_and_charge_later": false,
  "payment_methods": [
    "card",
    "wallet"
  ],
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
    "frequency": "monthly",
    "frequency_interval": 1,
    "end_date": "2023/01/31",
    "payment_quantity": 5
  },
  "first_name": "first name",
  "last_name": "last name",
  "email": "email@mamopay.com",
  "payouts_share": {
    "recipient_id": "REP-123",
    "percentage_to_recipient": 10,
    "recipient_pays_fees": false
  },
  "custom_data": {
    "val1": true,
    "val2": "custom value"
  },
  "charges": [
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
    }
  ]
}