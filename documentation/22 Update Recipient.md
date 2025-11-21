Update Recipient
patch
https://business.mamopay.com/manage_api/v1/accounts/recipients/{recipientIdentifier}
Allows a user to update recipient details.

Path Params
recipientIdentifier
string
required
Defaults to REP-6BB7CA8DC7
Recipient Identifier

REP-6BB7CA8DC7
Body Params
recipient_type
string
enum
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
Recipient's relationship


others

Show 11 enum values
reason
string
Reason for the payouts issued to this recipient.

eid_number
string
Recipient's Emirates ID number.

bank
object

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
https://sandbox.dev.business.mamopay.com/manage_api/v1/accounts/recipients/{recipientIdentifier}
Sandbox server
https://business.mamopay.com/manage_api/v1/accounts/recipients/{recipientIdentifier}
Production server


<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/accounts/recipients/REP-6BB7CA8DC7",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "PATCH",
  CURLOPT_POSTFIELDS => json_encode([
    'recipient_type' => 'individual',
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