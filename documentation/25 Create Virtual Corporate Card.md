Create Virtual Corporate Card
post
https://business.mamopay.com/manage_api/v1/vcc_cards
A Virtual Corporate Card (VCC) is a digital payment solution for businesses to simplify corporate expenses like travel and accommodations. This API is available upon request for a tailored integration. Email us on api@mamopay.com to get started.

Body Params
amount
number
required
The amount on the VCC card. The value can not exceed the card balance.

email
string
required
Cardholder’s email address. Card holder must be completed KYC.

booking_id
string
Booking reference in case the card will be used for a 1 time booking.

verification_email
string
The email address that will be used for verification purposes.

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
url
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
https://sandbox.dev.business.mamopay.com/manage_api/v1/vcc_cards
Sandbox server
https://business.mamopay.com/manage_api/v1/vcc_cards
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/vcc_cards",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => [
    "accept: application/json",
    "content-type: multipart/form-data"
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
  "url": "https://staging.business.mamopay.com/cards/vcc?token=eyJhbGciOiJIUzI1NiJJpc3WFwaS1zZXJ2ZXIiLCJhdWQiOiJtYW1vcGF5LWFwaS1zZXJ2ZXIiLCJpYXQiOjE3MDkxMjY1MDYsInN1YiI6Ik1CT1BSVC1GMDY2MEVEREQ5IiwiZXhwIjoxNzI0Njc4NTA2LCJjYXJkX2lkZW50aWZpZXIiOiJDUkQtNzE2MkU2MkZBNSJ9.twkdqtlzksfeeO3vjD_7-TezDI4x4LZo9aF0Gf7uFIE"
}