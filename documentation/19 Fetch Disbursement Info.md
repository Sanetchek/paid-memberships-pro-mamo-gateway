Fetch Disbursement Info
get
https://business.mamopay.com/manage_api/v1/disbursements/{disbursementId}
Allows a user to fetch the disbursement details.

Path Params
disbursementId
string
required
Defaults to MPB-DISBMT-FBA601A8FA
Disbursement ID

MPB-DISBMT-FBA601A8FA
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
number
identifier
string
Defaults to MPB-DISBMT-F2336E5CFB
unique id from the disbursement

reason
string
Defaults to Invoice payment
name
string
Defaults to Homemade Chocolatier
Recipient's name

amount_formatted
string
Defaults to 100.12
Transfer amount

method
string
Defaults to Bank Account
Type of transfer

recipient
string
Defaults to AE080200000123223333121
The recipient's bank account

created_at
string
Defaults to 07/03/2023
The date the disbursement was created at

status
string
enum
Defaults to Processing
Current status of the disbursement

Processing Processed Failed

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
Response body
object
status
number
error
string



Servers
https://sandbox.dev.business.mamopay.com/manage_api/v1/disbursements/{disbursementId}
Sandbox server
https://business.mamopay.com/manage_api/v1/disbursements/{disbursementId}
Production server



<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/disbursements/MPB-DISBMT-FBA601A8FA",
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
    "id": 72
  },
  {
    "identifier": "MPB-DISBMT-F2336E5CFB"
  },
  {
    "reason": "reason"
  },
  {
    "name": "wes e"
  },
  {
    "amount_formatted": "2.00"
  },
  {
    "method": "Bank account"
  },
  {
    "recipient": "AE080200000123223333121"
  },
  {
    "created_at": "07/03/2023"
  },
  {
    "status": "Processing"
  }
]