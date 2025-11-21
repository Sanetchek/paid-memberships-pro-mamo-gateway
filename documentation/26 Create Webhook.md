Create Webhook
post
https://business.mamopay.com/manage_api/v1/webhooks
Webhook registration for updates on one-off payment statuses and subscription payment statues.

Body Params
url
uri
required
Defaults to https://webhook.site/e9145367-01cc-45f9-bebb-69775badb883
The URL of webhook which customer will be recieved the notification

https://webhook.site/e9145367-01cc-45f9-bebb-69775badb883
enabled_events
array of strings
required
Defaults to charge.failed,charge.succeeded,charge.refund_initiated,charge.refunded,charge.refund_failed,subscription.failed,subscription.succeeded,payment_link.create
Event types details: charge.failed: Notification for failed one-off payments charge.succeeded: Notification for successful one-off payments charge.refund_initiated: Notification for initializing the refund charge.refunded: Notification for the successful refund charge.refund_failed: Notification for the failed refund charge.card_verified: Notification for card verification payment charge.authorized: Notification for when a charge is placed on hold subscription.failed: Notification for failed subscription payments subscription.succeeded: Notification for successful subscription payments payment_link.create: Notification for successfully create payment link payout.processed: Notification for settled payout payout.failed: Notification for rejected payout


string

charge.failed

string

charge.succeeded

string

charge.refund_initiated

string

charge.refunded

string

charge.refund_failed

string

subscription.failed

string

subscription.succeeded

string

payment_link.create

ADD string
auth_header
string
length between 1 and 50
Defaults to authentication header
authentication header

authentication header
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
url
uri
enabled_events
array
auth_header
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


422
Unprocessable entity
Response body
object
messages
array
error_code
string
errors
object
name
array of strings

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
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'url' => 'https://webhook.site/e9145367-01cc-45f9-bebb-69775badb883',
    'enabled_events' => [
        'charge.failed',
        'charge.succeeded',
        'charge.refund_initiated',
        'charge.refunded',
        'charge.refund_failed',
        'subscription.failed',
        'subscription.succeeded',
        'payment_link.create'
    ],
    'auth_header' => 'authentication header'
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
  "url": "https://webhook.site/e9145367-01cc-45f9-bebb-69775badb883",
  "enabled_events": [
    "charge.failed",
    "charge.succeeded",
    "charge.refund_initiated",
    "charge.refunded",
    "charge.refund_failed",
    "subscription.failed",
    "subscription.succeeded"
  ],
  "auth_header": "authentication header",
  "id": "MB-WH-D8B07FB8D7"
}