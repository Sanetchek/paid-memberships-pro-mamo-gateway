Fetching Recipients
get
https://business.mamopay.com/manage_api/v1/accounts/recipients
Fetches all recipients for a given business.

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
identifier
string
recipient_type
string
name
string
relationship
string
email
string
eid_number
string
reason
string
address
object

address object
bank
object

bank object
created_at
string
recipient_meta
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
https://sandbox.dev.business.mamopay.com/manage_api/v1/accounts/recipients
Sandbox server
https://business.mamopay.com/manage_api/v1/accounts/recipients
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/accounts/recipients",
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
      "identifier": "REP-D8B07FB8C7"
    },
    {
      "name": "first name last name"
    },
    {
      "recipient_type": "individual"
    },
    {
      "relationship": "others"
    },
    {
      "reason": "reason"
    },
    {
      "email": null
    },
    {
      "eid_number": null
    },
    {
      "address": null
    },
    {
      "bank": {
        "iban": "AE080200000123223333121",
        "country": "AE",
        "name": null,
        "account_number": null,
        "address": null,
        "city": null,
        "bic_code": null
      }
    },
    {
      "created_at": "19/02/2024"
    },
    {
      "recipient_meta": {}
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