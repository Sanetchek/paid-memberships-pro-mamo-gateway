<?php
/**
 * PHPUnit bootstrap file
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define constants usually defined by WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', '/tmp/wordpress/');
}
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', '/tmp/wordpress/wp-content');
}
if (!defined('PMPRO_MAMOGATEWAY_DIR')) {
    define('PMPRO_MAMOGATEWAY_DIR', dirname(__DIR__) . '/');
}

// Load WP_Mock
WP_Mock::setUsePatchwork(true);
WP_Mock::bootstrap();

// Define common WP functions that WP_Mock doesn't provide by default
if (!function_exists('wp_json_encode')) {
	function wp_json_encode($data, $options = 0, $depth = 512) {
		return json_encode($data, $options, $depth);
	}
}

if (!function_exists('date_i18n')) {
	function date_i18n($format, $timestamp_with_offset = false, $gmt = false) {
		$timestamp = $timestamp_with_offset === false ? time() : $timestamp_with_offset;
		return date($format, $timestamp);
	}
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return false;
    }
}

if (!function_exists('wp_remote_retrieve_response_code')) {
    function wp_remote_retrieve_response_code($response) {
        return isset($response['response']['code']) ? $response['response']['code'] : '';
    }
}

if (!function_exists('wp_remote_retrieve_body')) {
    function wp_remote_retrieve_body($response) {
        return isset($response['body']) ? $response['body'] : '';
    }
}
