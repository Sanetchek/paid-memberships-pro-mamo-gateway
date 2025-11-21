Fetch Subscribers
get
https://business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}/subscribers
Fetches all subscribers of subscription.

Path Params
subscriptionId
string
required
Defaults to MPB-SUBSCRIBER-4C26489D5C
Subscription ID

MPB-SUBSCRIBER-4C26489D5C
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
id
string
Defaults to MPB-CUS-9CF4E6571C
Unique identifier of subscription

status
string
status of subscription

customer
object

customer object
number_of_payments
number
number of payments

total_paid
string
total paid for subscription

next_payment_date
string
the date for next payment will be paid


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
https://sandbox.dev.business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}/subscribers
Sandbox server
https://business.mamopay.com/manage_api/v1/subscriptions/{subscriptionId}/subscribers
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/subscriptions/MPB-SUBSCRIBER-4C26489D5C/subscribers",
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
    "id": "MPB-SUBSCRIBER-4C26489D5C\""
  },
  {
    "status": "Active"
  },
  {
    "customer": {
      "id": "MPB-SUBSCRIBER-4C26489D5C\"",
      "name": "KO ko",
      "email": "customer@mamopay.com"
    }
  },
  {
    "number_of_payments": 1
  },
  {
    "total_paid": "AED 11.00"
  },
  {
    "next_payment_date": "2023-04-19"
  }
]