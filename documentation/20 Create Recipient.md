Create Recipient
post
https://business.mamopay.com/manage_api/v1/accounts/recipients
Allows a user to create recipient.

Body Params
recipient_type
string
enum
required
Defaults to individual
The type of the recipient.


individual
Allowed:

individual

business
email
string
Recipient's email.

business_name
string
Name of the recipient when recipient_type is business.

first_name
string
First name of the recipient when recipient_type is individual.

last_name
string
Last name of the recipient when recipient_type is individual.

relationship
string
enum
required
Recipient's relationship


business_associate_partner

Show 11 enum values
reason
string
required
Reason for the payouts issued to this recipient.

eid_number
string
Recipient's Emirates ID number.

bank
object
required

bank object
address
object

address object
recipient_meta
object

recipient_meta object
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
address_line1
string
address_line2
string
city
string
state
string
country
string
bank
object
iban
string
account_number
string
name
string
bic_code
string
address
string
country
string
created_at
string
recipient_meta
object
Has additional fields

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
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode([
    'recipient_type' => 'individual',
    'relationship' => 'business_associate_partner',
    'bank' => [
        'iban' => 'AE080200000123223333121',
        'country' => 'AE'
    ]
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
  "identifier": "REP-D8B07FB8C7",
  "name": "first name last name",
  "recipient_type": "individual",
  "relationship": "others",
  "reason": "reason",
  "email": null,
  "eid_number": null,
  "address": null,
  "bank": {
    "iban": "AE080200000123223333121",
    "country": "AE",
    "name": null,
    "account_number": null,
    "address": null,
    "city": null,
    "bic_code": null
  },
  "created_at": "19/02/2024",
  "recipient_meta": {}
}