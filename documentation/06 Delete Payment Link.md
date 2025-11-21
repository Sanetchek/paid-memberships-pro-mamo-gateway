Delete Payment Link
delete
https://business.mamopay.com/manage_api/v1/links/{linkId}
Allows a user to delete payment link

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
success
boolean

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
  CURLOPT_CUSTOMREQUEST => "DELETE",
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
  "success": true
}