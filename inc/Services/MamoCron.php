<?php

namespace PMPro\Mamo\Services;

use PMPro\Mamo\Adapters\MamoAdapter;
use PMPro\Mamo\Utils\MamoLogger;

if (!defined('ABSPATH')) {
	exit;
}

class MamoCron
{
	const HOOK = 'pmpro_mamo_recurring_cron';

    public static function init()
	{
		add_action(self::HOOK, array(__CLASS__, 'run'));
        self::schedule();
		// Admin-only AJAX trigger for manual runs
		add_action('wp_ajax_pmpro_mamo_run_cron', array(__CLASS__, 'ajax_run'));
	}

    public static function schedule($time = null)
	{
        // Clear existing schedule
        while ($timestamp = wp_next_scheduled(self::HOOK)) {
            wp_unschedule_event($timestamp, self::HOOK);
        }

        // Determine next run timestamp based on configured time-of-day
        $hhmm = $time;
        if ($hhmm === null || !preg_match('/^\d{2}:\d{2}$/', (string)$hhmm)) {
            $hhmm = pmpro_getOption('mamo_cron_time');
        }
        if (empty($hhmm) || !preg_match('/^(\d{2}):(\d{2})$/', (string)$hhmm, $m)) {
            $m = [null, '02', '30'];
        }
        $hour = (int)$m[1];
        $min = (int)$m[2];
        $now = current_time('timestamp');
        $today = date_i18n('Y-m-d', $now);
        $targetTs = strtotime($today . sprintf(' %02d:%02d:00', $hour, $min));
        if ($targetTs === false || $targetTs <= $now) {
            $targetTs = strtotime('+1 day', $targetTs ? $targetTs : $now);
        }
        wp_schedule_event($targetTs, 'daily', self::HOOK);
	}

	public static function run()
	{
        $adapter = new MamoAdapter();

        $users = get_users(array(
            'meta_key' => '_mamo_card_id',
            'meta_compare' => 'EXISTS',
            'fields' => array('ID')
        ));
        if (empty($users)) {
            return;
        }
        foreach ($users as $u) {
            $user_id = (int)$u->ID;
            // Skip if recurring was disabled by cancellation handler
            $disabled = get_user_meta($user_id, '_mamo_recurring_disabled', true);
            if (!empty($disabled)) {
                continue;
            }
            $levels = function_exists('pmpro_getMembershipLevelsForUser') ? pmpro_getMembershipLevelsForUser($user_id) : array();
            if (empty($levels)) continue;
            // pick first active recurring level
            $level = null;
            foreach ($levels as $lv) {
                if (!empty($lv->billing_amount) && !empty($lv->cycle_number) && !empty($lv->cycle_period)) {
                    $level = $lv; break;
                }
            }
            if (!$level) continue;

            $cardId = get_user_meta($user_id, '_mamo_card_id', true);
            if (empty($cardId)) continue;

            // Skip if user has an active MAMO subscription (handled by MAMO, not us)
            $subId = get_user_meta($user_id, '_mamo_subscription_id', true);
            if (!empty($subId)) continue;

            $next_due = get_user_meta($user_id, '_mamo_next_due', true);
            if (empty($next_due)) {
                $next_due_ts = strtotime("+ {$level->cycle_number} {$level->cycle_period}", current_time('timestamp'));
            } else {
                $next_due_ts = strtotime($next_due);
            }
            if ($next_due_ts === false || current_time('timestamp') < $next_due_ts) {
                continue; // not due yet
            }

            // Build charge request using card_id (MIT)
            $sum = (float)$level->billing_amount;
            $uniq = 'pmpro_' . $user_id . '_' . $level->id . '_' . wp_generate_password(6, false);
            $req = array(
                'card_id' => $cardId,
                'amount' => $sum,
                'currency' => MamoApi::getCurrencyCode(),
                'external_id' => $uniq,
                'send_customer_receipt' => true,
            );

            MamoLogger::info('Cron chargeToken request', $req);
            $res = $adapter->chargeToken($req);
            MamoLogger::info('Cron chargeToken response', $res);

            $responseCode = isset($res['ResponseCode']) ? (int)$res['ResponseCode'] : 0;
            $status = isset($res['status']) ? $res['status'] : '';
            $ok = ($responseCode === 0 && ($status === 'captured' || $status === 'success'));

            if ($ok) {
                // create PMPro order record
                // create PMPro order record
                if (!class_exists('MemberOrder')) {
                    if (defined('PMPRO_DIR') && file_exists(PMPRO_DIR . '/classes/class.memberorder.php')) {
                        require_once PMPRO_DIR . '/classes/class.memberorder.php';
                    } elseif (defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/paid-memberships-pro/classes/class.memberorder.php')) {
                        require_once WP_PLUGIN_DIR . '/paid-memberships-pro/classes/class.memberorder.php';
                    }
                }
                $order = new \MemberOrder();
                $order->user_id = $user_id;
                $order->membership_id = $level->id;
                $order->PaymentAmount = $sum;
                $chargeId = isset($res['id']) ? $res['id'] : $uniq;
                $order->payment_transaction_id = $chargeId;
                $order->status = 'success';
                $order->gateway = 'mamo';
                $order->payment_type = 'MAMO';
                $order->code = $order->getRandomCode();
                $order->saveOrder();

                if (!empty($chargeId)) {
                    update_pmpro_membership_order_meta($order->id, '_mamo_charge_id', sanitize_text_field($chargeId));
                }

                // advance next due
                $next_due_ts = strtotime("+ {$level->cycle_number} {$level->cycle_period}", current_time('timestamp'));
                update_user_meta($user_id, '_mamo_next_due', date('Y-m-d H:i:s', $next_due_ts));
                update_user_meta($user_id, '_mamo_last_charge_date', current_time('mysql'));
                update_user_meta($user_id, '_mamo_last_charge_status', 'success');
            } else {
                update_user_meta($user_id, '_mamo_last_charge_status', 'failed');
                // retry/backoff: increment counter and reschedule next_due +1 day if under retry max
                $retryMax = (int)(pmpro_getOption('mamo_cron_retry_max') ?: 3);
                $retryCount = (int)get_user_meta($user_id, '_mamo_retry_count', true);
                $retryCount++;
                update_user_meta($user_id, '_mamo_retry_count', $retryCount);
                if ($retryCount <= $retryMax) {
                    $bump = strtotime('+1 day', current_time('timestamp'));
                    update_user_meta($user_id, '_mamo_next_due', date('Y-m-d H:i:s', $bump));
                } else {
                    // Cancel membership after exceeding retries
                    if (function_exists('pmpro_cancelMembershipLevel')) {
                        pmpro_cancelMembershipLevel($level->id, $user_id);
                    }
                    update_user_meta($user_id, '_mamo_recurring_disabled', 1);
                }
            }
        }
	}

	/**
	 * Secure admin AJAX endpoint to trigger a manual cron run.
	 */
	public static function ajax_run()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('error' => 'forbidden'), 403);
		}
		$nonce = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';
		if (empty($nonce) || !wp_verify_nonce($nonce, 'pmpro_mamo_run_cron')) {
			wp_send_json_error(array('error' => 'invalid_nonce'), 403);
		}
		self::run();
		wp_send_json_success(array('status' => 'ok'));
	}
}

// WP-CLI command: wp pmpro-mamo cron-run
if (defined('WP_CLI') && WP_CLI) {
	\WP_CLI::add_command('pmpro-mamo cron-run', function () {
		\PMPro\Mamo\Services\MamoCron::run();
		\WP_CLI::success('PMPro MAMO cron executed.');
	});
}

