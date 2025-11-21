<?php

namespace PMPro\Mamo\Services;

if (!defined('ABSPATH')) {
	exit;
}

class MamoApi
{
	/**
	 * Check if MAMO is in test mode (sandbox)
	 * Uses PMPro standard gateway_environment setting
	 *
	 * @return bool
	 */
	public static function isTestMode()
	{
		if (!function_exists('pmpro_getOption')) {
			return false;
		}
		$environment = pmpro_getOption('gateway_environment');
		return ($environment === 'sandbox' || $environment === 'test');
	}

	/**
	 * Get appropriate credentials based on test mode
	 *
	 * @return array
	 */
	public static function getCredentials()
	{
		$isTestMode = self::isTestMode();
		$prefix = $isTestMode ? 'mamo_test_' : 'mamo_';

		return array(
			'api_key' => pmpro_getOption($prefix . 'api_key'),
			'is_test_mode' => $isTestMode,
		);
	}

	/**
	 * Get HTTP headers for MAMO API requests
	 *
	 * @param string $apiKey API Key for authentication
	 * @return array Headers array
	 */
	public static function getHeaders($apiKey)
	{
		return array(
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $apiKey,
		);
	}

	/**
	 * Parse JSON response from MAMO API
	 *
	 * @param array|\WP_Error $response WordPress HTTP response
	 * @return array Parsed response array
	 */
	public static function parseJsonResponse($response)
	{
		if (is_wp_error($response)) {
			return array(
				'ResponseCode' => -1,
				'Description' => 'Transport error: ' . $response->get_error_message(),
				'raw' => $response,
			);
		}

		$code = (int)wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);

		if (empty($body)) {
			return array(
				'ResponseCode' => -1,
				'Description' => 'Empty response',
				'http_code' => $code,
			);
		}

		$parsed = json_decode($body, true);

		if (json_last_error() !== JSON_ERROR_NONE) {
			return array(
				'ResponseCode' => -1,
				'Description' => 'JSON parse error: ' . json_last_error_msg(),
				'http_code' => $code,
				'raw' => substr($body, 0, 500),
			);
		}

		// Map MAMO response to standard format for compatibility
		$result = array(
			'raw' => $body,
			'http_code' => $code,
		);

		// Check for errors in response
		if (isset($parsed['error_code']) || isset($parsed['messages'])) {
			$result['ResponseCode'] = isset($parsed['error_code']) ? $parsed['error_code'] : -1;
			$result['Description'] = isset($parsed['messages']) ? implode(', ', $parsed['messages']) : 'API error';
			$result = array_merge($result, $parsed);
			return $result;
		}

		// Success response - map common fields
		$result['ResponseCode'] = ($code >= 200 && $code < 300) ? 0 : $code;

		// For payment link creation, map payment_url to URL
		if (isset($parsed['payment_url'])) {
			$result['URL'] = $parsed['payment_url'];
			$result['Url'] = $parsed['payment_url'];
			$result['url'] = $parsed['payment_url'];
		}

		// For charge responses, map status
		if (isset($parsed['status'])) {
			$result['status'] = $parsed['status'];
			// Map status to ResponseCode-like format
			if ($parsed['status'] === 'captured') {
				$result['ResponseCode'] = 0;
			} elseif ($parsed['status'] === 'failed') {
				$result['ResponseCode'] = -1;
				$result['Description'] = isset($parsed['error_message']) ? $parsed['error_message'] : 'Payment failed';
			}
		}

		// For refund responses
		if (isset($parsed['refund_status'])) {
			if ($parsed['refund_status'] === 'success') {
				$result['ResponseCode'] = 0;
			} else {
				$result['ResponseCode'] = -1;
				$result['Description'] = 'Refund failed';
			}
		}

		// Merge all parsed data
		$result = array_merge($result, $parsed);

		return $result;
	}

	/**
	 * Get currency code for MAMO API
	 * Maps PMPro currency to MAMO currency codes
	 *
	 * @return string Currency code (AED, USD, EUR, GBP, SAR)
	 */
	public static function getCurrencyCode()
	{
		if (!function_exists('pmpro_getOption')) {
			return 'AED';
		}

		$currency = pmpro_getOption('currency');
		if (empty($currency)) {
			return 'AED';
		}

		$cur = strtoupper($currency);

		// MAMO supports: AED, USD, EUR, GBP, SAR
		$supported = array('AED', 'USD', 'EUR', 'GBP', 'SAR');
		if (in_array($cur, $supported)) {
			return $cur;
		}

		// Default to AED if currency not supported
		return 'AED';
	}

	/**
	 * Map PMPro cycle period to MAMO frequency
	 *
	 * @param string $cyclePeriod PMPro cycle period (Day, Week, Month, Year)
	 * @return string MAMO frequency (daily, weekly, monthly, yearly)
	 */
	public static function mapCyclePeriodToFrequency($cyclePeriod)
	{
		$map = array(
			'Day' => 'daily',
			'Days' => 'daily',
			'Week' => 'weekly',
			'Weeks' => 'weekly',
			'Month' => 'monthly',
			'Months' => 'monthly',
			'Year' => 'yearly',
			'Years' => 'yearly',
		);

		$period = ucfirst($cyclePeriod);
		return isset($map[$period]) ? $map[$period] : 'monthly';
	}

	/**
	 * Format date for MAMO API (YYYY/MM/DD)
	 *
	 * @param string|int $date Date string or timestamp
	 * @return string Formatted date
	 */
	public static function formatDate($date)
	{
		if (is_numeric($date)) {
			return date('Y/m/d', $date);
		}
		$timestamp = strtotime($date);
		if ($timestamp === false) {
			return date('Y/m/d');
		}
		return date('Y/m/d', $timestamp);
	}
}

