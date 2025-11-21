<?php

namespace PMPro\Mamo\Adapters;

use PMPro\Mamo\Services\MamoApi;

if (!defined('ABSPATH')) {
	exit;
}

class MamoAdapter implements PaymentProviderInterface
{
	private $endpointBase;

	public function __construct()
	{
		$isTestMode = MamoApi::isTestMode();
		$this->endpointBase = $isTestMode
			? 'https://sandbox.dev.business.mamopay.com/manage_api/v1'
			: 'https://business.mamopay.com/manage_api/v1';
	}

	/**
	 * Create Payment Link
	 * @param array $req Request parameters
	 * @return array Response array
	 */
	public function createPaymentPage($req)
	{
		$credentials = MamoApi::getCredentials();
		$apiKey = $credentials['api_key'];

		// Map request to MAMO Payment Link format
		$body = array(
			'title' => isset($req['ProductName']) ? $req['ProductName'] : 'Membership Payment',
			'description' => isset($req['InvoiceLines1.Description']) ? $req['InvoiceLines1.Description'] : 'Membership subscription',
			'amount' => isset($req['SumToBill']) ? (float)$req['SumToBill'] : 0,
			'amount_currency' => MamoApi::getCurrencyCode(),
			'return_url' => isset($req['SuccessRedirectUrl']) ? $req['SuccessRedirectUrl'] : '',
			'failure_return_url' => isset($req['ErrorRedirectUrl']) ? $req['ErrorRedirectUrl'] : '',
			'link_type' => isset($req['link_type']) ? $req['link_type'] : 'standalone',
			'save_card' => isset($req['save_card']) ? $req['save_card'] : 'optional',
			'external_id' => isset($req['ReturnValue']) ? $req['ReturnValue'] : '',
		);

		// Add subscription if provided
		if (isset($req['subscription']) && is_array($req['subscription'])) {
			$body['subscription'] = $req['subscription'];
		}

		// Add customer details if provided
		if (isset($req['CardOwnerEmail'])) {
			$body['email'] = $req['CardOwnerEmail'];
		}
		if (isset($req['CardOwnerName'])) {
			$nameParts = explode(' ', $req['CardOwnerName'], 2);
			if (isset($nameParts[0])) {
				$body['first_name'] = $nameParts[0];
			}
			if (isset($nameParts[1])) {
				$body['last_name'] = $nameParts[1];
			}
		}

		$url = $this->endpointBase . '/links';
		$headers = MamoApi::getHeaders($apiKey);

		\PMPro\Mamo\Utils\MamoLogger::info('MAMO createPaymentPage request', array('url' => $url, 'body' => $body));

		$response = wp_remote_post($url, array(
			'timeout' => 70,
			'headers' => $headers,
			'body' => wp_json_encode($body),
		));

		return MamoApi::parseJsonResponse($response);
	}

	/**
	 * Pull indicator - Get transaction status by charge ID
	 * @param string $terminalNumber Not used for MAMO (kept for interface compatibility)
	 * @param string $userName Not used for MAMO (kept for interface compatibility)
	 * @param string $chargeId MAMO charge ID or payment link ID
	 * @return array Response array
	 */
	public function pullIndicator($terminalNumber, $userName, $chargeId)
	{
		$credentials = MamoApi::getCredentials();
		$apiKey = $credentials['api_key'];

		// If it looks like a payment link ID, we need to get the charge from it
		// Otherwise, treat it as a charge ID
		$url = $this->endpointBase . '/charges/' . urlencode($chargeId);
		$headers = MamoApi::getHeaders($apiKey);

		\PMPro\Mamo\Utils\MamoLogger::info('MAMO pullIndicator request', array('url' => $url, 'chargeId' => $chargeId));

		$response = wp_remote_get($url, array(
			'timeout' => 70,
			'headers' => $headers,
		));

		return MamoApi::parseJsonResponse($response);
	}

	/**
	 * Charge using stored card_id (MIT - Merchant Initiated Transaction)
	 * @param array $req Request parameters
	 * @return array Response array
	 */
	public function chargeToken($req)
	{
		$credentials = MamoApi::getCredentials();
		$apiKey = $credentials['api_key'];

		// Map request to MAMO charge format
		$body = array(
			'card_id' => isset($req['TokenToCharge.Token']) ? $req['TokenToCharge.Token'] : (isset($req['card_id']) ? $req['card_id'] : ''),
			'amount' => isset($req['TokenToCharge.SumToBill']) ? (float)$req['TokenToCharge.SumToBill'] : (isset($req['amount']) ? (float)$req['amount'] : 0),
			'currency' => isset($req['currency']) ? $req['currency'] : MamoApi::getCurrencyCode(),
			'send_customer_receipt' => isset($req['send_customer_receipt']) ? $req['send_customer_receipt'] : true,
		);

		// Add external_id if provided
		if (isset($req['external_id'])) {
			$body['external_id'] = $req['external_id'];
		} elseif (isset($req['TokenToCharge.UniqAsmachta'])) {
			$body['external_id'] = $req['TokenToCharge.UniqAsmachta'];
		}

		// Add custom_data if provided
		if (isset($req['custom_data']) && is_array($req['custom_data'])) {
			$body['custom_data'] = $req['custom_data'];
		}

		$url = $this->endpointBase . '/charges';
		$headers = MamoApi::getHeaders($apiKey);

		\PMPro\Mamo\Utils\MamoLogger::info('MAMO chargeToken request', array('url' => $url, 'body' => $body));

		$response = wp_remote_post($url, array(
			'timeout' => 70,
			'headers' => $headers,
			'body' => wp_json_encode($body),
		));

		return MamoApi::parseJsonResponse($response);
	}

	/**
	 * Create invoice (not directly supported by MAMO, but kept for interface compatibility)
	 * @param array $req Request parameters
	 * @return array Response array
	 */
	public function createInvoice($req)
	{
		// MAMO doesn't have a separate invoice API
		// Invoices are handled automatically with payment links
		return array(
			'ResponseCode' => 0,
			'Description' => 'Invoice creation not required for MAMO',
		);
	}

	/**
	 * Refund by transaction ID (charge ID)
	 * @param array $req Request parameters with 'InternalDealNumber' or 'charge_id' and optional 'SumToRefund' or 'amount'
	 * @return array Response array
	 */
	public function refundByTransactionId($req)
	{
		$credentials = MamoApi::getCredentials();
		$apiKey = $credentials['api_key'];

		$chargeId = isset($req['InternalDealNumber']) ? $req['InternalDealNumber'] : (isset($req['charge_id']) ? $req['charge_id'] : '');
		if (empty($chargeId)) {
			return array(
				'ResponseCode' => -1,
				'Description' => 'Charge ID is required',
			);
		}

		$body = array();
		$amount = isset($req['SumToRefund']) ? $req['SumToRefund'] : (isset($req['amount']) ? $req['amount'] : '');
		if (!empty($amount)) {
			$body['amount'] = (float)$amount;
		}

		$url = $this->endpointBase . '/charges/' . urlencode($chargeId) . '/refunds';
		$headers = MamoApi::getHeaders($apiKey);

		\PMPro\Mamo\Utils\MamoLogger::info('MAMO refundByTransactionId request', array('url' => $url, 'body' => $body));

		$response = wp_remote_post($url, array(
			'timeout' => 70,
			'headers' => $headers,
			'body' => !empty($body) ? wp_json_encode($body) : '',
		));

		return MamoApi::parseJsonResponse($response);
	}

	/**
	 * Test credentials by calling GET /me endpoint
	 * @return array Response with 'ok' boolean and 'message'
	 */
	public function testCredentials()
	{
		$credentials = MamoApi::getCredentials();
		$apiKey = $credentials['api_key'];
		$mode = $credentials['is_test_mode'] ? 'Sandbox' : 'Live';

		\PMPro\Mamo\Utils\MamoLogger::info('MAMO testCredentials start', array('mode' => $mode));

		// Validate that API key is not empty
		if (empty($apiKey)) {
			$msg = 'Missing ' . $mode . ' API Key';
			\PMPro\Mamo\Utils\MamoLogger::error('MAMO testCredentials: ' . $msg, array('mode' => $mode));
			return array('ok' => false, 'message' => $msg, 'test_mode' => $credentials['is_test_mode']);
		}

		$url = $this->endpointBase . '/me';
		$headers = MamoApi::getHeaders($apiKey);

		\PMPro\Mamo\Utils\MamoLogger::info('MAMO testCredentials request', array('mode' => $mode, 'url' => $url));

		$response = wp_remote_get($url, array(
			'timeout' => 30,
			'headers' => $headers,
		));

		if (is_wp_error($response)) {
			\PMPro\Mamo\Utils\MamoLogger::error('MAMO testCredentials WP_Error', array('message' => $response->get_error_message()));
			return array('ok' => false, 'message' => $response->get_error_message(), 'test_mode' => $credentials['is_test_mode']);
		}

		$code = (int)wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);

		if ($code === 200) {
			$parsed = json_decode($body, true);
			if ($parsed && !isset($parsed['error_code'])) {
				\PMPro\Mamo\Utils\MamoLogger::info('MAMO testCredentials success', array('mode' => $mode));
				return array(
					'ok' => true,
					'message' => 'Connected successfully',
					'response_code' => $code,
					'parsed' => $parsed,
					'test_mode' => $credentials['is_test_mode'],
				);
			} else {
				$errorMsg = isset($parsed['messages']) ? implode(', ', $parsed['messages']) : 'Unknown error';
				\PMPro\Mamo\Utils\MamoLogger::error('MAMO testCredentials API error', array('mode' => $mode, 'error' => $errorMsg));
				return array('ok' => false, 'message' => $errorMsg, 'test_mode' => $credentials['is_test_mode']);
			}
		} else {
			\PMPro\Mamo\Utils\MamoLogger::error('MAMO testCredentials HTTP error', array('code' => $code, 'body' => substr($body, 0, 500)));
			$parsed = json_decode($body, true);
			$errorMsg = 'HTTP ' . $code;
			if ($parsed && isset($parsed['messages'])) {
				$errorMsg .= ': ' . implode(', ', $parsed['messages']);
			}
			return array('ok' => false, 'message' => $errorMsg, 'test_mode' => $credentials['is_test_mode']);
		}
	}
}

