Quick start - https://mamopay.readme.io/reference/get_
get
https://sandbox.dev.business.mamopay.com/manage_api/v1/
Get up and running with Mamo's APIs in less than 5 minutes.

In this example, we will use the Business Details endpoint to ensure we have the right permissions to interact with Mamo's APIs.

How to Use Mamo's API Docs
👈 To the left, you will find all documents, including individual endpoint reference docs. These will include information on various endpoints, their associated methods and the various responses that your code will need to handle.

👉 To the right, there's the search bar, language, base URL, code generator and a try it from the browser section.

Use the language section to select the language that you plan on writing your code in.
Use the base URL to select the environment you would like the docs to target. We would recommend that you would start with the sandbox environment while you build and test your app. And later on switch to the production URL once your flows are tested and are working as expected. Hint, you need a dedicated sandbox account and API Key, if you don't have one please reach out to us using the chat bubble 💬 on the bottom right.
The code generator will automatically create a working code snippet based on the values that you provide on a given page. Feel free to copy and paste this straight into your code. You can also test this using the Try It! button below each section. Note that for production requests, you will be required to provide a valid API Key.
Payment Link Generation
To generate payment links, you can follow the guide on Create Payment Link.

The simplest way to test your integration, is by creating a vanilla payment link that has no custom settings.

curl --location 'https://sandbox.dev.business.mamopay.com/manage_api/v1/links' --header 'Content-Type: application/json' --header 'Authorization: Bearer sk-8d88fac2-d3cf-4060-9eaf-ce6b61434c39' --data '{ "title": "My Business Name", "return_url": "https://mamopay.com", "amount": 10 }'

Receive Payments On Your E-Commerce Website
To make payments right on your page, code snipets can be downloaded from your dashboard

FAQs
API Integration FAQs
WooCommerce Integration Guide
Shopify Integration Guide
Response
200
Successful response



Language: PHP
URL:
 - Sandbox server: https://sandbox.dev.business.mamopay.com/manage_api/v1
 - Production server: https://business.mamopay.com/manage_api/v1

<?php

$curl = curl_init();

curl_setopt_array($curl, [
  CURLOPT_URL => "https://business.mamopay.com/manage_api/v1/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
