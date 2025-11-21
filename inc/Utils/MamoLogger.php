<?php

namespace PMPro\Mamo\Utils;

if (!defined('ABSPATH')) {
	exit;
}

class MamoLogger
{
	private static function isEnabled()
	{
		return (int)pmpro_getOption('mamo_logging') === 1;
	}

	/**
	 * Get log file path
	 * @return string
	 */
	private static function getLogFile()
	{
		// Use logs/ folder in plugin directory
		$log_dir = defined('PMPRO_MAMOGATEWAY_DIR') ? PMPRO_MAMOGATEWAY_DIR . 'logs/' : '';
		if (!$log_dir || !is_dir($log_dir)) {
			// Fallback to wp-content if logs dir doesn't exist
			return WP_CONTENT_DIR . '/mamo-debug.log';
		}
		return $log_dir . 'mamo-' . date('Y-m-d') . '.log';
	}

	/**
	 * Write to log file
	 * @param string $message
	 */
	private static function writeLog($message)
	{
		$log_file = self::getLogFile();
		$timestamp = date('Y-m-d H:i:s');
		$log_entry = "[{$timestamp}] {$message}\n";

		// Ensure logs directory exists and is writable
		$log_dir = dirname($log_file);
		if (!file_exists($log_dir)) {
			@mkdir($log_dir, 0755, true);
		}

		// Write to file
		@file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
	}

	private static function redact($data)
	{
		if (is_array($data)) {
			$copy = $data;
			$keys = array('Token', 'CardNumber', 'CardOwnerEmail', 'CardOwnerPhone', 'UserName', 'Password', 'ApiKey', 'api_key', 'card_id', 'Authorization');
			foreach ($keys as $k) {
				foreach ($copy as $key => $val) {
					if (stripos($key, $k) !== false) {
						$copy[$key] = '***';
					}
				}
			}
			return $copy;
		}
		if (is_string($data)) {
			return '[redacted]';
		}
		return $data;
	}

	public static function info($message, $context = array())
	{
		if (!self::isEnabled()) return;
		$ctx = self::redact($context);
		$log_message = '[MAMO][INFO] ' . $message . ' ' . (empty($ctx) ? '' : wp_json_encode($ctx));

		// Write to both error_log (for WordPress debug.log) and custom log file
		error_log($log_message);
		self::writeLog($log_message);
	}

	public static function error($message, $context = array())
	{
		$ctx = self::redact($context);
		$log_message = '[MAMO][ERROR] ' . $message . ' ' . (empty($ctx) ? '' : wp_json_encode($ctx));

		// Write to both error_log and custom log file (errors always logged)
		error_log($log_message);
		self::writeLog($log_message);
	}

	public static function warning($message, $context = array())
	{
		if (!self::isEnabled()) return;
		$ctx = self::redact($context);
		$log_message = '[MAMO][WARNING] ' . $message . ' ' . (empty($ctx) ? '' : wp_json_encode($ctx));

		error_log($log_message);
		self::writeLog($log_message);
	}
}

