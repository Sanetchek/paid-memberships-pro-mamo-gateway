<?php

/**
* Plugin Name: Paid Memberships Pro - MAMO Gateway
* Description: Take credit card payments on your site using MAMO (Payment Links, Webhooks, Subscriptions + Cron).
* Author: Hamamlitz
* Author URI: https://hamamlitz.example/
* Version: 4.0.0
* Requires at least: 5.0
* Tested up to: 6.7
* Text Domain: pmpro-mamo
* Domain Path: /languages
*
*/
define("PMPRO_MAMOGATEWAY_DIR", plugin_dir_path(__FILE__));
define("PMPRO_MAMO_META_KEY", "_pmpro_mamo");

//load payment gateway class


function pmpro_mamo_plugins_loaded()
{
	if (!defined('PMPRO_DIR')) {
		// PMPro not loaded yet, try again later
		add_action('init', 'pmpro_mamo_plugins_loaded', 20);
		return;
	}
	// Set default options for MAMO gateway
	$options = get_option('pmpro_options');
	if (is_array($options)) {
		$changed = false;
		// Ensure Webhook Secret exists
		if (empty($options['mamo_webhook_secret'])) {
			$options['mamo_webhook_secret'] = wp_generate_password(32, false, false);
			$changed = true;
		}
		// Set sensible defaults for cron settings
		if (empty($options['mamo_cron_time'])) {
			$options['mamo_cron_time'] = '02:30';
			$changed = true;
		}
		if (empty($options['mamo_cron_retry_max']) || !is_numeric($options['mamo_cron_retry_max'])) {
			$options['mamo_cron_retry_max'] = '3';
			$changed = true;
		}
		if ($changed) {
			update_option('pmpro_options', $options);
		}
	}
	// MAMO wiring
	require_once(PMPRO_MAMOGATEWAY_DIR . '/inc/Utils/MamoLogger.php');
	require_once(PMPRO_MAMOGATEWAY_DIR . '/inc/Adapters/PaymentProviderInterface.php');
	require_once(PMPRO_MAMOGATEWAY_DIR . '/inc/Adapters/MamoAdapter.php');
	require_once(PMPRO_MAMOGATEWAY_DIR . '/inc/Services/MamoApi.php');
	require_once(PMPRO_MAMOGATEWAY_DIR . '/inc/Services/MamoCron.php');
	require_once(PMPRO_MAMOGATEWAY_DIR . '/inc/Services/MamoAdmin.php');
    // Load the MAMO gateway class only after PMPro base gateway is available
    if (class_exists('PMProGateway')) {
        require_once(PMPRO_MAMOGATEWAY_DIR . '/classes/class.pmprogateway_mamo.php');
    } else {
        add_action('init', function () {
            if (class_exists('PMProGateway')) {
                require_once(PMPRO_MAMOGATEWAY_DIR . '/classes/class.pmprogateway_mamo.php');
            }
        }, 5);
    }
	\PMPro\Mamo\Services\MamoCron::init();
	\PMPro\Mamo\Services\MamoAdmin::init();
}
add_action('plugins_loaded', 'pmpro_mamo_plugins_loaded');

// Before PMPro options are saved, ensure MAMO Webhook Secret is set
add_filter('pre_update_option_pmpro_options', function($value, $old_value, $option){
	if (!is_array($value)) {
		return $value;
	}
	if (empty($value['mamo_webhook_secret'])) {
		if (is_array($old_value) && !empty($old_value['mamo_webhook_secret'])) {
			$value['mamo_webhook_secret'] = $old_value['mamo_webhook_secret'];
		} else {
			$value['mamo_webhook_secret'] = wp_generate_password(32, false, false);
		}
	}
	return $value;
}, 10, 3);

// AJAX: test connection to MAMO
add_action('wp_ajax_pmpro_mamo_test_connection', function(){
    if (!current_user_can('manage_options')) {
        wp_send_json_error('forbidden', 403);
    }
    // Accept both 'security' and '_wpnonce' to be resilient
    $nonce = isset($_REQUEST['security']) ? sanitize_text_field(wp_unslash($_REQUEST['security'])) : '';
    if (empty($nonce) && isset($_REQUEST['_wpnonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
    }
    if (empty($nonce) || !wp_verify_nonce($nonce, 'pmpro_mamo_test_connection')) {
        wp_send_json_error('invalid_nonce', 403);
    }
    try {
        $adapter = new \PMPro\Mamo\Adapters\MamoAdapter();
        $res = $adapter->testCredentials();
        if (is_array($res) && !empty($res['ok'])) {
            wp_send_json_success($res);
        } else {
            // Return detailed error message
            $msg = is_array($res) && isset($res['message']) ? $res['message'] : 'Connection test failed';
            wp_send_json_error($msg);
        }
    } catch (\Throwable $e) {
        wp_send_json_error($e->getMessage(), 500);
    }
});

// Record when users gain the trial level.
function pmpro_mamo_save_trial_level_used($level_id, $user_id)
{
    update_user_meta($user_id, 'pmpro_trial_level_used', $level_id);
}
add_action('pmpro_after_change_membership_level', 'pmpro_mamo_save_trial_level_used', 10, 2);

add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});