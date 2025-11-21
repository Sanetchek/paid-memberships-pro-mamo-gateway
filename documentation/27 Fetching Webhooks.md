Fetching Webhooks
get
https://business.mamopay.com/manage_api/v1/webhooks
Fetches all registered webhooks for a given business.

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
Defaults to MB-WH-D8B07FB8D7
The identifier of webhook

url
uri
Defaults to https://webhook.site/e9145367-01cc-45f9-bebb-69775badb883
The URL of webhook which customer will be recieved the notification

enabled_events
array
Defaults to charge.failed,charge.succeeded,charge.refund_initiated,charge.refunded,charge.refund_failed,subscription.failed,subscription.succeeded
the options for notifying

auth_header
string
length between 1 and 50
Defaults to header
authentication header


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
https://sandbox.dev.business.mamopay.com/manage_api/v1/webhooks
Sandbox server
https://business.mamopay.com/manage_api/v1/webhooks
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/webhooks",
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
    "url": "https://webhook.site/e9145367-01cc-45f9-bebb-69775badb883"
  },
  {
    "enabled_events": [
      "charge.failed",
      "charge.succeeded",
      "charge.refund_initiated",
      "charge.refunded",
      "charge.refund_failed",
      "subscription.failed",
      "subscription.succeeded"
    ]
  },
  {
    "auth_header": "header"
  },
  {
    "id": "MB-WH-D8B07FB8D7"
  }
]