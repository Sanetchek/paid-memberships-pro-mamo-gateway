<?php

use PMPro\Mamo\Adapters\MamoAdapter;

if (!defined('ABSPATH')) {
	exit;
}

// Ensure PMPro Order class is available
if (!class_exists('MemberOrder')) {
    if (defined('PMPRO_DIR') && file_exists(PMPRO_DIR . '/classes/class.memberorder.php')) {
        require_once PMPRO_DIR . '/classes/class.memberorder.php';
    } elseif (defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/paid-memberships-pro/classes/class.memberorder.php')) {
        require_once WP_PLUGIN_DIR . '/paid-memberships-pro/classes/class.memberorder.php';
    }
}

// Get JSON payload
$json = file_get_contents('php://input');
$data = json_decode($json, true);

\PMPro\Mamo\Utils\MamoLogger::info('MAMO webhook received', array('event_type' => isset($data['event_type']) ? $data['event_type'] : 'unknown', 'data' => $data));

// Validate secret
$secret = isset($_GET['secret']) ? sanitize_text_field(wp_unslash($_GET['secret'])) : '';
$expected = pmpro_getOption('mamo_webhook_secret');

if (empty($expected) || $secret !== $expected) {
	\PMPro\Mamo\Utils\MamoLogger::warning('Invalid webhook request', array('secret_match' => $secret === $expected));
	status_header(200);
	exit; // silently ignore invalid
}

if (empty($data) || !is_array($data)) {
	\PMPro\Mamo\Utils\MamoLogger::error('Invalid webhook payload', array('json' => $json));
	status_header(200);
	exit;
}

$eventType = isset($data['event_type']) ? $data['event_type'] : '';

// Handle charge events
if (strpos($eventType, 'charge.') === 0) {
	$chargeId = isset($data['id']) ? $data['id'] : '';
	$status = isset($data['status']) ? $data['status'] : '';
	$externalId = isset($data['external_id']) ? $data['external_id'] : '';

	// Find order by external_id (order code) or charge ID
	$morder = null;
	if (!empty($externalId)) {
		$morder = new MemberOrder($externalId);
	}

	if (empty($morder) || empty($morder->id)) {
		// Try to find by charge ID in meta
		global $wpdb;
		$orders = $wpdb->get_results($wpdb->prepare(
			"SELECT p.id FROM {$wpdb->pmpro_membership_orders} p
			 INNER JOIN {$wpdb->pmpro_membership_ordermeta} m ON p.id = m.pmpro_membership_order_id
			 WHERE m.meta_key = '_mamo_charge_id' AND m.meta_value = %s
			 ORDER BY p.id DESC LIMIT 1",
			$chargeId
		));
		if (!empty($orders)) {
			$morder = new MemberOrder($orders[0]->id);
		}
	}

	if (empty($morder) || empty($morder->id)) {
		\PMPro\Mamo\Utils\MamoLogger::error('Order not found for webhook', array('charge_id' => $chargeId, 'external_id' => $externalId, 'event_type' => $eventType));
		status_header(200);
		exit;
	}

	$user = get_userdata($morder->user_id);

	// Process based on event type
	if ($eventType === 'charge.succeeded' || $status === 'captured') {
		// Payment successful
		$morder->status = 'success';
		if (!empty($chargeId)) {
			$morder->payment_transaction_id = $chargeId;
			update_pmpro_membership_order_meta($morder->id, '_mamo_charge_id', sanitize_text_field($chargeId));
		}

		// Save card_id if provided (for recurring payments)
		if (isset($data['payment_method']['card_id']) && !empty($data['payment_method']['card_id'])) {
			update_user_meta($morder->user_id, '_mamo_card_id', sanitize_text_field($data['payment_method']['card_id']));
		}

		// Save subscription_id if provided
		if (isset($data['subscription_id']) && !empty($data['subscription_id'])) {
			update_pmpro_membership_order_meta($morder->id, '_mamo_subscription_id', sanitize_text_field($data['subscription_id']));
			update_user_meta($morder->user_id, '_mamo_subscription_id', sanitize_text_field($data['subscription_id']));
		}

		$morder->saveOrder();

		// Activate membership level
		if (!empty($morder->user_id) && !empty($morder->membership_id)) {
			pmpro_changeMembershipLevel($morder->membership_id, $morder->user_id, 'changed');
		}

		// Send success emails
		if (!empty($morder->user_id)) {
			$invoice = new MemberOrder($morder->id);
			$user = get_userdata($morder->user_id);
			if ($user) {
				$user->membership_level = $morder->membership_level;
				if (class_exists('PMProEmail')) {
					$pmproemail = new PMProEmail();
					$pmproemail->sendCheckoutEmail($user, $invoice);
					$pmproemail = new PMProEmail();
					$pmproemail->sendCheckoutAdminEmail($user, $invoice);
				}
			}
		}

		\PMPro\Mamo\Utils\MamoLogger::info('Payment processed successfully via webhook', array('order_id' => $morder->id, 'charge_id' => $chargeId));
	} elseif ($eventType === 'charge.failed' || $status === 'failed') {
		// Payment failed
		$morder->status = 'error';
		$errorMessage = isset($data['error_message']) ? $data['error_message'] : (isset($data['error_code']) ? $data['error_code'] : 'Payment failed');
		$morder->notes = 'MAMO payment failed: ' . sanitize_text_field($errorMessage);
		$morder->saveOrder();

		// Send failure emails
		try {
			if (class_exists('PMProEmail')) {
				$pmproemail = new PMProEmail();
				if ($user) {
					$pmproemail->sendBillingFailureEmail($user, $morder);
				}
				$pmproemail->sendBillingFailureAdminEmail(get_bloginfo("admin_email"), $morder);
			}
		} catch (Exception $e) {
			\PMPro\Mamo\Utils\MamoLogger::error('Exception sending failure emails', array('error' => $e->getMessage()));
		}

		\PMPro\Mamo\Utils\MamoLogger::error('Payment failed via webhook', array('order_id' => $morder->id, 'charge_id' => $chargeId, 'error' => $errorMessage));
	}
}
// Handle subscription events
elseif (strpos($eventType, 'subscription.') === 0) {
	$subscriptionId = isset($data['subscription_id']) ? $data['subscription_id'] : (isset($data['id']) ? $data['id'] : '');
	$chargeId = isset($data['id']) ? $data['id'] : '';

	// Find user by subscription_id
	global $wpdb;
	$users = $wpdb->get_results($wpdb->prepare(
		"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '_mamo_subscription_id' AND meta_value = %s LIMIT 1",
		$subscriptionId
	));

	if (empty($users)) {
		\PMPro\Mamo\Utils\MamoLogger::error('User not found for subscription webhook', array('subscription_id' => $subscriptionId, 'event_type' => $eventType));
		status_header(200);
		exit;
	}

	$user_id = (int)$users[0]->user_id;
	$user = get_userdata($user_id);

	if (!$user) {
		\PMPro\Mamo\Utils\MamoLogger::error('Invalid user ID for subscription webhook', array('user_id' => $user_id));
		status_header(200);
		exit;
	}

	if ($eventType === 'subscription.succeeded') {
		// Subscription payment succeeded - create order record
		$amount = isset($data['amount']) ? (float)$data['amount'] : 0;
		$levels = pmpro_getMembershipLevelsForUser($user_id);
		if (!empty($levels)) {
			$level = $levels[0];
			$order = new MemberOrder();
			$order->user_id = $user_id;
			$order->membership_id = $level->id;
			$order->PaymentAmount = $amount;
			$order->payment_transaction_id = $chargeId;
			$order->status = 'success';
			$order->gateway = 'mamo';
			$order->payment_type = 'MAMO';
			$order->code = $order->getRandomCode();
			$order->saveOrder();

			if (!empty($chargeId)) {
				update_pmpro_membership_order_meta($order->id, '_mamo_charge_id', sanitize_text_field($chargeId));
			}
			if (!empty($subscriptionId)) {
				update_pmpro_membership_order_meta($order->id, '_mamo_subscription_id', sanitize_text_field($subscriptionId));
			}

			\PMPro\Mamo\Utils\MamoLogger::info('Subscription payment processed successfully', array('order_id' => $order->id, 'charge_id' => $chargeId, 'subscription_id' => $subscriptionId));
		} elseif ($eventType === 'subscription.failed') {
			// Subscription payment failed
			$cancelOnFailed = pmpro_getOption('mamo_cancel_sub_on_failed_charge');
			if ($cancelOnFailed == 1) {
				$levels = pmpro_getMembershipLevelsForUser($user_id);
				if (!empty($levels)) {
					pmpro_cancelMembershipLevel($levels[0]->id, $user_id, 'cancelled');
					\PMPro\Mamo\Utils\MamoLogger::info('Subscription cancelled due to failed payment', array('user_id' => $user_id, 'subscription_id' => $subscriptionId));
				}
			}
		}
	}
}

status_header(200);
exit;

