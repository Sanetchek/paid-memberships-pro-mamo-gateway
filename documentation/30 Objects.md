Charge Object
You can expect the below object when you register a webhook for the following events:

charge.failed
charge.succeeded
charge.refund_initiated
charge.refunded
charge.refund_failed
charge.card_verified
Raw sample
JSON

{
  "status": "captured",
  "id": "MPB-CHRG-D65B203ABD",
  "amount": 33.99,
  "amount_currency": "AED",
  "refund_amount": 0,
  "refund_status": "No refund",
  "custom_data": {},
  "created_date": "2023-12-25-14-38-53",
  "subscription_id": null,
  "settlement_amount": "31.99",
  "settlement_currency": "AED",
  "settlement_date": "2024-01-01",
  "customer_details": {
    "name": "",
    "email": "support@mamopay.com",
    "phone_number": "-",
    "comment": "-"
  },
  "payment_method": {
    "card_id": null,
    "type": "CREDIT VISA",
    "card_holder_name": "John Doe",
    "card_last4": "1111",
    "origin": "International card"
  },
  "settlement_fee": "AED 1.90",
  "settlement_vat": "AED 0.10",
  "payment_link_id": "MB-LINK-1EC52247EE",
  "payment_link_url": "https://staging.business.mamopay.com/pay/mamosandbox-8acfdf",
  "external_id": null,
  "error_code": null,
  "error_message": null,
  "next_payment_date": null,
  "event_type": "charge.succeeded"
}
Field Descriptions
Field	Type	Description
status	String	Status of the payment (confirmation_required-captured-refund_initiated-processing-failed-refunded)
id	String	Mamo Charge ID
amount	Number	Amount
amount_currency	String	Currency
refund_amount	Number	Refund Amount (for a refund charge)
refund_status	String	Refund Status (for a refund charge)
custom_data	Dictionary	Custom Data sent upon creation
created_date	String (Format: YYYY-MM-DD-HH-MM-SS)	Charge creation time
subscription_id	String	Subscription ID (in case of a subscription payment)
settlement_amount	String	Settlement Amount
settlement_currency	String	Settlement Currency
settlement_date	String (Format: YYYY-MM-DD)	Settlement Date
customer_details	Dictionary	Customer Details (name-email-phone_number-comment)
external_id	String	External ID if sent
payment_method	String	Payment Method (card_id-type-card_holder_name-card_last4-origin)
settlement_fee	String	Settlement Fee
settlement_vat	String	Settlement VAT
payment_link_id	String	ID of payment link used
payment_link_url	String	Payment Link Used
error_code	String	Error Code (In case of failure)
error_message	String	Error Message (In case of failure)
next_payment_date	String	Next Payment Date (In case of failure)
event_type	String	Corresponding Webhook Event



Payment Link Object
You can expect the below object when you register a webhook for the following events:

payment_link.create
Raw sample
JSON

{
  "description": "12pcs Chocolate Box",
  "capacity": null,
  "active": true,
  "return_url": null,
  "failure_return_url": null,
  "send_customer_receipt": true,
  "custom_data": {},
  "amount_currency": "AED",
  "platform": "client",
  "prefilled_customer": {},
  "title": "Chocolate Box - Small",
  "external_id": null,
  "name": "Chocolate Box - Small",
  "enable_tabby": true,
  "is_widget": true,
  "id": "MB-LINK-0C6885CBB3",
  "amount": 3000,
  "processing_fee_percentage": 0,
  "processing_fee_amount": null,
  "enable_message": true,
  "enable_tips": false,
  "enable_quantity": false,
  "payment_methods": [
    "card",
    "wallet"
  ],
  "rules": null,
  "enable_customer_details": false,
  "payment_url": "https://staging.business.mamopay.com/widget/pay/mamosandbox-acf1e5",
  "save_card": "optional",
  "created_at": "2023-12-28-16-21-24",
  "event_type": "payment_link.create",
  "subscription": null
}
Field Descriptions
Field	Type	Description
description	String	Payment link description
capacity	Number	Payment Link capacity
active	String	Payment Link activity (True or false )
return_url	String	The URL that the customer will be redirected to after a successful payment
failure_return_url	String	The URL that the customer will be redirected to after a failed payment
send_customer_receipt	String	Enables the sending of customer receipts
custom_data	Dictionary	Custom Data sent in the request
amount_currency	String	Currency of the payments
platform	String	Platform where the payment link was generated
title	String	Payment link title
prefilled_customer	Dictionary	Prefilled customer details (name - email)
external_id	String	External ID sent in the request
name	String	Payment Link name
enable_tabby	String	Whether the option to enable Taby is true or false
is_widget	String	Whether the payment link is a widget or not
id	String	Payment Link ID
amount	Number	Amount of the Payment Link
processing_fee_percentage	Number	Processing fee percentage sent in the request
processing_fee_amount	Number	Calculated processing amount
enable_message	String	Whether the customer can add a note or not
enable_tips	String	Whether the option to enable tips is allowed or not
enable_quantity	String	Whether the customer can add a quantity
payment_methods	List<String>	List of allowed payment methods (card - wallet)
rules	String	Payment link rules
enable_customer_details	String	Whether the customers can enter their details
payment_url	String	Payment Link
save_card	String	Whether the card's details will be saved or not (optional - required - off)
created_at	String (Format: YYYY-MM-DD-HH-MM-SS)	The creation time of the Payment Link
event_type	String	Webhook event type
subscription	Dictionary	Subscription details in case of subscriptions (identifier-frequency_interval-end_date-payment_quantity-start_date-frequency)





Payout Object
You can expect the below object when you register a webhook for the following events:

payout.failed
payout.processed
Raw sample
JSON

{
  "reason": "Invoice payment",
  "id": "PYT-E30EE749B0",
  "name": "Homemade Chocolatier",
  "amount": 100.12,
  "amount_currency": "AED",
  "method": "Bank account",
  "recipient": "AE080200000123223333121",
  "recipient_id": "REP-54A15E6B47",
  "created_at": "2023-12-28-17-22-53",
  "status": "processed",
  "event_type": "payout.processed"
}
Field Descriptions
Field	Type	Description
reason	String	Payout reason
id	String	Payout ID
name	String	Recipient name
amount	Number	Payout amount
amount_currency	String	Payout currency
recipient	String	Recipient IBAN
recipient_id	String	Recipient ID
method	String	Payout method (BANK_ACCOUNT)
created_at	String (Format: YYYY-MM-DD-HH-MM-SS)	Payout creation time
status	String	Payout Status
event_type	String	Webhook event type