Fetch Subscription Payments
get
https://business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}/payments
Fetches all subscription payments made against a Recurring Payment item. Developers should use the customer's email address to filter out payments made by a single customer. The creation date can be used to decide whether a customer should be kept on the same plan or downgraded to a lower / free one.

Path Params
subscriptionId
string
required
Defaults to MPB-SUB-B764EDCBA2
Subscription ID

MPB-SUB-B764EDCBA2
Headers
Content-Type
string
Authorization
string
Responses

200
Successful response

Response body
array of objects
object
amount
number
Defaults to 3453
The amount paid by the customer

business_name
string
Defaults to Homemade Chocolatier
The name of the business (recipient) the payment was made for

customer_email
string
Defaults to johns@gmails.com
The email address of the customer making the payment (subscriber). This is the email address collected by Mamo at checkout

customer_name
string
Defaults to Johns Sullivan
Customer's full name

customer_first_name
string
Defaults to John
Customer's first name

customer_last_name
string
Defaults to Sullivan
Customer's last name

currency
string
enum
Defaults to AED
The currency the payment was made in

AED USD EUR

customer_phone
string
Defaults to +971581234567
Customer's phone number including the country code

identifier
string
Defaults to MPB-CHRG-894558D35C
Unique identifier for a payment

payment_purpose
string
The reason behind the payment

payment_url
string
Defaults to https://staging.business.mamopay.com/pay/mybusiness
The Recurring Payment url that the subscription was created on

status
string
enum
Defaults to captured
The status of the payment. Captured indicates that the payment was completed successfully and that the funds have been received.

confirmation_required captured refund_initiated processing failed refunded

created_at
string
Defaults to 2022-08-17-13-51-42
The date the payment was made on


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
https://sandbox.dev.business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}/payments
Sandbox server
https://business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}/payments
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/subscriptions/MPB-SUB-B764EDCBA2/payments",
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
[
  {
    "amount": 3453
  },
  {
    "business_name": "Integration Test Business"
  },
  {
    "customer_email": "johns@gmails.com"
  },
  {
    "customer_name": "Johns Sullivan"
  },
  {
    "customer_first_name": "Johns"
  },
  {
    "customer_last_name": "Sullivan"
  },
  {
    "currency": "AED"
  },
  {
    "customer_phone": "+971581234567"
  },
  {
    "gateway_response_code": null
  },
  {
    "identifier": "MPB-CHRG-894558D35C"
  },
  {
    "payment_purpose": ""
  },
  {
    "payment_url": "https://staging.business.mamopay.com/pay/mybusiness"
  },
  {
    "status": "captured"
  },
  {
    "created_at": "2022-08-17-13-51-42"
  }
]