<?php

use PMPro\Mamo\Adapters\MamoAdapter;

if (!defined('ABSPATH')) {
	exit;
}

$secret = isset($_GET['secret']) ? sanitize_text_field(wp_unslash($_GET['secret'])) : '';
$expected = pmpro_getOption('mamo_webhook_secret');

\PMPro\Mamo\Utils\MamoLogger::info('Indicator called', array(
	'has_secret' => !empty($secret),
	'secret_match' => ($secret === $expected),
	'get_params' => $_GET
));

// Enforce secret
if (empty($expected) || $secret !== $expected) {
	\PMPro\Mamo\Utils\MamoLogger::error('Indicator validation failed', array(
		'reason' => empty($expected) ? 'no_secret_configured' : 'secret_mismatch'
	));
	status_header(200);
	exit;
}

$chargeId = isset($_GET['transactionId']) ? sanitize_text_field(wp_unslash($_GET['transactionId'])) : '';
$paymentLinkId = isset($_GET['paymentLinkId']) ? sanitize_text_field(wp_unslash($_GET['paymentLinkId'])) : '';
$status = isset($_GET['status']) ? sanitize_text_field(wp_unslash($_GET['status'])) : '';
$orderCode = isset($_GET['order_code']) ? sanitize_text_field(wp_unslash($_GET['order_code'])) : '';

// Ensure PMPro Order class is available
if (!class_exists('MemberOrder') && defined('PMPRO_MAMOGATEWAY_DIR')) {
	require_once PMPRO_MAMOGATEWAY_DIR . '/classes/class.memberorder.php';
}

// Get credentials
$credentials = \PMPro\Mamo\Services\MamoApi::getCredentials();

\PMPro\Mamo\Utils\MamoLogger::info('Indicator pulling data', array(
	'mode' => $credentials['is_test_mode'] ? 'Sandbox' : 'Live',
	'charge_id' => $chargeId,
	'payment_link_id' => $paymentLinkId,
	'status' => $status,
	'order_code' => $orderCode
));

// If we have a charge ID, fetch transaction details
if (!empty($chargeId)) {
	$adapter = new MamoAdapter();
	$res = $adapter->pullIndicator('', '', $chargeId);
	\PMPro\Mamo\Utils\MamoLogger::info('Indicator response received', array(
		'status' => isset($res['status']) ? $res['status'] : 'N/A',
		'charge_id' => isset($res['id']) ? $res['id'] : 'N/A'
	));

	$status = isset($res['status']) ? $res['status'] : $status;
}

// Map MAMO status to success/failure
$ok = ($status === 'captured' || $status === 'success');

// Find order
$morder = null;
if (!empty($orderCode)) {
	$morder = new MemberOrder($orderCode);
} elseif (!empty($chargeId)) {
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
} elseif (!empty($paymentLinkId)) {
	// Try to find by payment link ID
	global $wpdb;
	$orders = $wpdb->get_results($wpdb->prepare(
		"SELECT p.id FROM {$wpdb->pmpro_membership_orders} p
		 INNER JOIN {$wpdb->pmpro_membership_ordermeta} m ON p.id = m.pmpro_membership_order_id
		 WHERE m.meta_key = '_mamo_payment_link_id' AND m.meta_value = %s
		 ORDER BY p.id DESC LIMIT 1",
		$paymentLinkId
	));
	if (!empty($orders)) {
		$morder = new MemberOrder($orders[0]->id);
	}
}

if (empty($morder) || empty($morder->id)) {
	\PMPro\Mamo\Utils\MamoLogger::error('Indicator: Order not found', array(
		'charge_id' => $chargeId,
		'payment_link_id' => $paymentLinkId,
		'order_code' => $orderCode
	));
	status_header(200);
	exit;
}

if ($ok) {
	\PMPro\Mamo\Utils\MamoLogger::info('Indicator: Payment successful', array('order_id' => $morder->id));

	$morder->getMembershipLevel();
	$morder->getUser();
	$morder->status = 'success';
	if (!empty($chargeId)) {
		$morder->payment_transaction_id = $chargeId;
		update_pmpro_membership_order_meta($morder->id, '_mamo_charge_id', sanitize_text_field($chargeId));
	}
	if (!empty($paymentLinkId)) {
		update_pmpro_membership_order_meta($morder->id, '_mamo_payment_link_id', sanitize_text_field($paymentLinkId));
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
} else {
	\PMPro\Mamo\Utils\MamoLogger::error('Indicator: Payment failed', array(
		'order_id' => $morder->id,
		'status' => $status
	));

	$morder->status = 'error';
	$morder->notes = 'MAMO payment failed: ' . sanitize_text_field($status);
	$morder->saveOrder();
}

status_header(200);
exit;

