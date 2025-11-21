# Paid Memberships Pro - MAMO Gateway

WordPress plugin that integrates Paid Memberships Pro with MAMO payment gateway.

## Description

This plugin enables Paid Memberships Pro to accept payments through MAMO, a modern payment gateway supporting payment links, subscriptions, and webhooks.

## Features

- Payment Links (standalone, modal, inline)
- Recurring payments via native subscriptions or MIT (Merchant Initiated Transactions)
- Webhook support for real-time payment notifications
- Refund support
- Sandbox/Production environment support
- Debug logging

## Installation

1. Upload the plugin files to `/wp-content/plugins/pmpro-mamo-gateway`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the gateway in Paid Memberships Pro → Settings → Payment Gateway

## Configuration

1. Get your API Key from [MAMO Dashboard](https://business.mamopay.com)
2. Go to Paid Memberships Pro → Settings → Payment Gateway
3. Select "MAMO" as your gateway
4. Enter your API Key (Live and/or Sandbox)
5. Configure Webhook URL in MAMO dashboard (shown in settings)
6. Enable logging if needed for debugging


## Requirements

- WordPress 5.0+
- Paid Memberships Pro plugin
- PHP 7.4+
- cURL extension

## Support

For issues and questions, contact support or check the documentation.

## Changelog

See CHANGELOG.md for version history.

