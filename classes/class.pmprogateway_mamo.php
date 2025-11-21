<?php

// Require the default PMPro Gateway Class.
if (!class_exists('PMProGateway')) {
    if (defined('PMPRO_DIR') && file_exists(PMPRO_DIR . '/classes/gateways/class.pmprogateway.php')) {
        require_once PMPRO_DIR . '/classes/gateways/class.pmprogateway.php';
    } elseif (defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/paid-memberships-pro/classes/gateways/class.pmprogateway.php')) {
        require_once WP_PLUGIN_DIR . '/paid-memberships-pro/classes/gateways/class.pmprogateway.php';
    } else {
        // Base class not available; bail out to avoid fatal
        return;
    }
}

use PMPro\Mamo\Adapters\MamoAdapter;
use PMPro\Mamo\Services\MamoApi;

if (!defined('ABSPATH')) {
	exit;
}

class PMProGateway_mamo extends PMProGateway
{
	public static function init()
	{
		add_filter('pmpro_gateways', array(__CLASS__, 'pmpro_gateways'));
		add_filter('pmpro_payment_options', array(__CLASS__, 'pmpro_payment_options'));
		add_filter('pmpro_payment_option_fields', array(__CLASS__, 'pmpro_payment_option_fields'), 10, 2);
		// Hide native CC fields when MAMO selected
		$gateway = function_exists('pmpro_getGateway') ? pmpro_getGateway() : '';
		$use_popup = (int)pmpro_getOption('mamo_use_popup') === 1;
		if ($gateway === 'mamo') {
			add_filter('pmpro_include_payment_information_fields', '__return_false');
			add_filter('pmpro_required_billing_fields', array(__CLASS__, 'pmpro_required_billing_fields'));
			// Only wire popup assets/endpoints when "Popup (IFRAME)" is enabled
			if ($use_popup) {
				add_action('pmpro_checkout_after_form', array(__CLASS__, 'pmpro_checkout_after_form'));
				add_action('pmpro_checkout_after_form', array(__CLASS__, 'pmpro_checkout_preheader'));
				add_action('wp_ajax_nopriv_pmpro_mamo_get_redirect', array(__CLASS__, 'wp_ajax_get_redirect'));
				add_action('wp_ajax_pmpro_mamo_get_redirect', array(__CLASS__, 'wp_ajax_get_redirect'));
			} else {
				// Ensure popup script knows not to hijack submit
				add_action('pmpro_checkout_after_form', function(){
					echo '<script>window.pmproMamoVars=window.pmproMamoVars||{};if(pmproMamoVars.data){pmproMamoVars.data.ajax=false;}</script>';
				});
			}
		}
		add_action('wp_ajax_nopriv_pmpro_mamo_webhook', array(__CLASS__, 'wp_ajax_webhook'));
		add_action('wp_ajax_pmpro_mamo_webhook', array(__CLASS__, 'wp_ajax_webhook'));
		add_action('wp_ajax_nopriv_pmpro_mamo_iframe', array(__CLASS__, 'wp_ajax_iframe'));
		add_action('wp_ajax_pmpro_mamo_iframe', array(__CLASS__, 'wp_ajax_iframe'));
		add_action('update_option_pmpro_options', array(__CLASS__, 'options_updated'), 10, 2);
		add_action('pmpro_after_change_membership_level', array(__CLASS__, 'after_change_membership_level'), 10, 2);
	}

	/**
	 * PMPro payment processing entrypoint. Creates MAMO Payment Link and redirects the member.
	 */
	public function process(&$order)
	{
		// Ensure order has a code and is saved as pending prior to offsite redirect
		if (empty($order->code)) {
			$order->code = $order->getRandomCode();
		}
		$order->payment_type = 'MAMO';
		$order->status = 'pending';

		// Ensure order has calculated totals with discount before saving
		if (method_exists($order, 'getTotal')) {
			$order->getTotal(); // This recalculates total, subtotal, discount_amount
		}

		$order->saveOrder();

		// IMPORTANT: Use total (with discount) if available, fallback to InitialPayment
		$amount = 0.00;
		if (!empty($order->total) && (float)$order->total > 0) {
			$amount = (float)$order->total;
		} elseif (!empty($order->InitialPayment)) {
			$amount = (float)$order->InitialPayment;
		}

		// Log order details for debugging
		\PMPro\Mamo\Utils\MamoLogger::info('MAMO process() - Order amount calculation', array(
			'order_code' => $order->code,
			'subtotal' => isset($order->subtotal) ? $order->subtotal : 'N/A',
			'discount_code' => isset($order->discount_code) ? $order->discount_code : 'none',
			'discount_amount' => isset($order->discount_amount) ? $order->discount_amount : 'N/A',
			'total' => isset($order->total) ? $order->total : 'N/A',
			'calculated_amount' => $amount,
			'initial_payment' => $order->InitialPayment
		));

		// Check for zero/negative amount - MAMO cannot process free orders
		if ($amount <= 0) {
			\PMPro\Mamo\Utils\MamoLogger::warning('MAMO process() - Zero or negative amount detected', array(
				'amount' => $amount,
				'order_code' => $order->code,
				'discount_code' => isset($order->discount_code) ? $order->discount_code : 'none'
			));
			$order->error = __('This level is free or has an invalid amount. No payment required.', 'pmpro-mamo');
			$order->shorterror = __('Free level', 'pmpro-mamo');
			return false;
		}

		$webhookSecret = pmpro_getOption('mamo_webhook_secret');
		$webhookUrl = add_query_arg(
			array(
				'action' => 'pmpro_mamo_webhook',
				'secret' => $webhookSecret,
				'order_code' => $order->code,
			),
			admin_url('admin-ajax.php')
		);

		$memberName = trim(($order->FirstName ?? '') . ' ' . ($order->LastName ?? ''));
		if ($memberName === '') {
			$memberName = get_bloginfo('name');
		}

		$email = !empty($order->Email) ? $order->Email : (is_user_logged_in() ? wp_get_current_user()->user_email : '');

		// Determine if this is a recurring payment
		$isRecurring = !empty($order->membership_level->billing_amount) && !empty($order->membership_level->cycle_number) && !empty($order->membership_level->cycle_period);
		$usePopup = (int)pmpro_getOption('mamo_use_popup') === 1;
		$linkType = $usePopup ? 'modal' : 'standalone';

		$req = array(
			'ProductName' => $memberName,
			'SumToBill' => number_format(max($amount, 0.00), 2, '.', ''),
			'ReturnValue' => $order->code,
			'SuccessRedirectUrl' => pmpro_url('confirmation'),
			'ErrorRedirectUrl' => pmpro_url('checkout'),
			'CardOwnerEmail' => $email,
			'CardOwnerName' => $memberName,
			'InvoiceLines1.Description' => (!empty($order->membership_level->name) ? $order->membership_level->name : __('Membership', 'pmpro-mamo')),
			'InvoiceLines1.Price' => number_format(max($amount, 0.00), 2, '.', ''),
			'InvoiceLines1.Quantity' => 1,
			'link_type' => $linkType,
			'save_card' => $isRecurring ? 'optional' : 'off',
		);

		// Add subscription object if recurring
		if ($isRecurring) {
			$frequency = MamoApi::mapCyclePeriodToFrequency($order->membership_level->cycle_period);
			$req['subscription'] = array(
				'frequency' => $frequency,
				'frequency_interval' => (int)$order->membership_level->cycle_number,
			);
			// Set start date to today
			$req['subscription']['start_date'] = MamoApi::formatDate(current_time('timestamp'));
			// If there's an end date, add it
			if (!empty($order->membership_level->expiration_number) && !empty($order->membership_level->expiration_period)) {
				$endDate = strtotime('+' . $order->membership_level->expiration_number . ' ' . $order->membership_level->expiration_period, current_time('timestamp'));
				$req['subscription']['end_date'] = MamoApi::formatDate($endDate);
			}
		}

		\PMPro\Mamo\Utils\MamoLogger::info('MAMO process() Payment Link request', $req);
		$adapter = new MamoAdapter();
		$res = $adapter->createPaymentPage($req);
		\PMPro\Mamo\Utils\MamoLogger::info('MAMO process() Payment Link response', $res);
		$responseCode = isset($res['ResponseCode']) ? (int)$res['ResponseCode'] : -1;

		// Get payment URL from response
		$redirectUrl = '';
		foreach (array('URL', 'Url', 'url', 'payment_url') as $k) {
			if (!empty($res[$k])) { $redirectUrl = $res[$k]; break; }
		}

		if ($responseCode === 0 && !empty($redirectUrl)) {
			// Save payment link ID for reference
			if (!empty($res['id'])) {
				update_pmpro_membership_order_meta($order->id, '_mamo_payment_link_id', sanitize_text_field($res['id']));
			}

			if ($usePopup) {
				$iframeUrl = add_query_arg(array('action' => 'pmpro_mamo_iframe', 'u' => rawurlencode($redirectUrl)), admin_url('admin-ajax.php'));
				wp_redirect($iframeUrl);
				exit;
			} else {
				wp_redirect($redirectUrl);
				exit;
			}
		}

		// Fail soft: leave order pending and surface MAMO description
		if (is_object($order)) {
			$order->error = isset($res['Description']) ? ('MAMO: ' . sanitize_text_field($res['Description'])) : __('MAMO: unexpected response', 'pmpro-mamo');
			$order->shorterror = isset($res['Description']) ? sanitize_text_field($res['Description']) : 'MAMO error';
		}
		\PMPro\Mamo\Utils\MamoLogger::error('MAMO process() failed', $res);
		return false;
	}

	public static function pmpro_gateways($gateways)
	{
		if (empty($gateways['mamo'])) {
			$gateways['mamo'] = __('MAMO', 'paid-memberships-pro');
		}
		return $gateways;
	}

	public static function getGatewayOptions()
	{
		return array(
			'mamo_api_key',
			'mamo_test_api_key',
			'mamo_webhook_secret',
			'mamo_use_popup',
			'mamo_logging',
			'mamo_cron_time',
			'mamo_cron_retry_max',
		);
	}

	public static function pmpro_payment_options($options)
	{
		return array_merge(self::getGatewayOptions(), $options);
	}

	public static function pmpro_payment_option_fields($values, $gateway)
	{
		?>
		<tr class="pmpro_settings_divider gateway gateway_mamo" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<td colspan="2">
				<h2 class="title"><?php esc_html_e('MAMO Settings', 'paid-memberships-pro'); ?></h2>
				<p class="description">
					<?php _e('Use the "Gateway Environment" selector above to switch between Sandbox/Testing and Live/Production credentials.', 'paid-memberships-pro'); ?>
				</p>
			</td>
		</tr>
		<tr class="pmpro_settings_divider gateway gateway_mamo gateway_mamo_live" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<td colspan="2"><h3><?php esc_html_e('Live (Production) Credentials', 'paid-memberships-pro'); ?></h3></td>
		</tr>
		<tr class="gateway gateway_mamo gateway_mamo_live" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<th scope="row" valign="top"><label for="mamo_api_key"><?php _e('API Key', 'paid-memberships-pro'); ?>:</label></th>
			<td>
				<input type="text" id="mamo_api_key" name="mamo_api_key" value="<?php echo esc_attr($values['mamo_api_key']); ?>" class="regular-text code" />
				<p class="description">
					<?php _e('Get your API Key from the MAMO dashboard:', 'paid-memberships-pro'); ?>
					<a href="https://business.mamopay.com" target="_blank" rel="noopener">https://business.mamopay.com</a>
				</p>
			</td>
		</tr>
		<tr class="pmpro_settings_divider gateway gateway_mamo gateway_mamo_sandbox" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<td colspan="2"><h3><?php esc_html_e('Sandbox (Testing) Credentials', 'paid-memberships-pro'); ?></h3></td>
		</tr>
		<tr class="gateway gateway_mamo gateway_mamo_sandbox" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<th scope="row" valign="top"><label for="mamo_test_api_key"><?php _e('Test API Key', 'paid-memberships-pro'); ?>:</label></th>
			<td>
				<input type="text" id="mamo_test_api_key" name="mamo_test_api_key" value="<?php echo esc_attr($values['mamo_test_api_key']); ?>" class="regular-text code" />
				<p class="description">
					<?php _e('Your test API key from MAMO sandbox environment. Contact support@mamopay.com for sandbox access.', 'paid-memberships-pro'); ?>
				</p>
			</td>
		</tr>
		<tr class="gateway gateway_mamo" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<th scope="row" valign="top"><label for="mamo_webhook_secret"><?php _e('Webhook Secret', 'paid-memberships-pro'); ?>:</label></th>
			<td>
				<p>
					<button type="button" class="button" id="pmpro-mamo-test-connection"><?php _e('Test MAMO Connection', 'paid-memberships-pro'); ?></button>
					<span id="pmpro-mamo-test-result" style="margin-left:8px;"></span>
				</p>
			</td>
		</tr>
		<tr class="gateway gateway_mamo" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<th scope="row" valign="top"><label for="mamo_webhook_secret"><?php _e('Webhook Secret', 'paid-memberships-pro'); ?>:</label></th>
			<td>
				<?php $mamo_webhook_secret_value = !empty($values['mamo_webhook_secret']) ? $values['mamo_webhook_secret'] : wp_generate_password(32, false, false); ?>
				<input type="hidden" id="mamo_webhook_secret" name="mamo_webhook_secret" value="<?php echo esc_attr($mamo_webhook_secret_value); ?>" />
				<?php
					$webhook_secret = isset($mamo_webhook_secret_value) ? $mamo_webhook_secret_value : '';
					$webhook_url = add_query_arg(array('action' => 'pmpro_mamo_webhook', 'secret' => rawurlencode($webhook_secret)), admin_url('admin-ajax.php'));
				?>
				<p class="description"><?php _e('Webhook URL (Configure this in your MAMO dashboard under Webhooks):', 'paid-memberships-pro'); ?> <code><?php echo esc_url($webhook_url); ?></code>
					<a href="https://business.mamopay.com" target="_blank" rel="noopener"><?php _e('Open MAMO dashboard', 'paid-memberships-pro'); ?></a>
				</p>
			</td>
		</tr>
		<tr class="gateway gateway_mamo" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<th scope="row" valign="top"><label for="mamo_use_popup"><?php _e('Display Mode', 'paid-memberships-pro'); ?>:</label></th>
			<td>
				<?php $popup = isset($values['mamo_use_popup']) ? (int)$values['mamo_use_popup'] : 0; ?>
				<select id="mamo_use_popup" name="mamo_use_popup">
					<option value="1" <?php selected($popup, 1); ?>><?php _e('Popup (IFRAME)', 'paid-memberships-pro'); ?></option>
					<option value="0" <?php selected($popup, 0); ?>><?php _e('Redirect', 'paid-memberships-pro'); ?></option>
				</select>
			</td>
		</tr>
		<input type="hidden" id="mamo_cron_time" name="mamo_cron_time" value="<?php echo esc_attr($values['mamo_cron_time'] ?? '02:30'); ?>" />
		<input type="hidden" id="mamo_cron_retry_max" name="mamo_cron_retry_max" value="<?php echo esc_attr($values['mamo_cron_retry_max'] ?? '3'); ?>" />
		<tr class="gateway gateway_mamo" <?php if ($gateway != 'mamo') { ?>style="display:none;"<?php } ?>>
			<th scope="row" valign="top"><label for="mamo_logging"><?php _e('Enable debug log', 'paid-memberships-pro'); ?>:</label></th>
			<td>
				<?php $lg = isset($values['mamo_logging']) ? (int)$values['mamo_logging'] : 0; ?>
				<select id="mamo_logging" name="mamo_logging">
					<option value="0" <?php selected($lg, 0); ?>><?php _e('No', 'paid-memberships-pro'); ?></option>
					<option value="1" <?php selected($lg, 1); ?>><?php _e('Yes', 'paid-memberships-pro'); ?></option>
				</select>
				<p class="description"><?php _e('Writes MAMO requests/responses (sensitive fields redacted) to PHP error log.', 'paid-memberships-pro'); ?></p>
			</td>
		</tr>
		<script>
		jQuery(document).ready(function($){
			function ensureCurrencyVisibleForMamo(){
				var gw = $('#gateway').val();
				var $row = $('select[name="currency"]').closest('tr');
				if (!$row.length) return;
				if (gw === 'mamo'){
					$row.addClass('gateway_mamo');
					$row.show(); // override any inline display:none
				}
			}
			ensureCurrencyVisibleForMamo();
			$('#gateway').on('change', ensureCurrencyVisibleForMamo);
			if (typeof pmpro_changeGateway === 'function') {
				pmpro_changeGateway($('#gateway').val());
			}
			$('#pmpro-mamo-test-connection').on('click', function(){
				var $btn = $(this), $res = $('#pmpro-mamo-test-result');
				var environment = $('#gateway_environment').val();
				var modeLabel = (environment === 'sandbox') ? '<?php echo esc_js(__('Sandbox', 'paid-memberships-pro')); ?>' : '<?php echo esc_js(__('Live', 'paid-memberships-pro')); ?>';
				$btn.prop('disabled', true);
				$res.html('<?php echo esc_js(__('Testing...', 'paid-memberships-pro')); ?>');

				$.post(ajaxurl, { action: 'pmpro_mamo_test_connection', security: '<?php echo wp_create_nonce('pmpro_mamo_test_connection'); ?>' }, function(resp){
					if (resp && resp.success) {
						var details = [];
						var mode = (resp.data && resp.data.test_mode) ? '<?php echo esc_js(__('Sandbox', 'paid-memberships-pro')); ?>' : '<?php echo esc_js(__('Live', 'paid-memberships-pro')); ?>';
						details.push('<strong>' + mode + ' Mode</strong>');
						if (resp.data && resp.data.message && resp.data.message !== 'Connected successfully') {
							details.push(resp.data.message);
						}
						if (resp.data && resp.data.response_code !== undefined) {
							details.push('Code: ' + resp.data.response_code);
						}
						$res.html('<span style="color:#46b450;font-weight:bold;">✓ Connected</span> (' + details.join(', ') + ')');
					} else {
						var msg = '<?php echo esc_js(__('Unknown error', 'paid-memberships-pro')); ?>';
						if (typeof resp.data === 'string') {
							msg = resp.data;
						} else if (resp && resp.data && resp.data.message) {
							msg = resp.data.message;
						}
						var mode = '';
						if (resp && resp.data && typeof resp.data === 'object') {
							mode = resp.data.test_mode ? '<?php echo esc_js(__('Sandbox', 'paid-memberships-pro')); ?>' : '<?php echo esc_js(__('Live', 'paid-memberships-pro')); ?>';
						}
						var modeText = mode ? ' (<strong>' + mode + ' Mode</strong>)' : '';
						$res.html('<span style="color:#dc3232;font-weight:bold;">✗ Failed</span>' + modeText + ': ' + msg);

						console.group('MAMO Connection Test Failed');
						console.log('Error message:', msg);
						if (resp && resp.data) {
							if (resp.data.response_code !== undefined) {
								console.log('Response code:', resp.data.response_code);
							}
							if (resp.data.raw) {
								console.log('Raw response:', resp.data.raw);
							}
							if (resp.data.parsed) {
								console.log('Parsed response:', resp.data.parsed);
							}
						}
						console.groupEnd();
					}
				}).fail(function(xhr){
					$res.html('<span style="color:#dc3232;font-weight:bold;">✗ Network Error</span>');
					console.error('AJAX request failed:', xhr);
				}).always(function(){
					$btn.prop('disabled', false);
				});
			});
		});
		</script>
		<?php
	}

	public static function pmpro_required_billing_fields($fields)
	{
		unset($fields['CardType']);
		unset($fields['AccountNumber']);
		unset($fields['ExpirationMonth']);
		unset($fields['ExpirationYear']);
		unset($fields['CVV']);
		return $fields;
	}

	public static function pmpro_checkout_preheader()
	{
		// Enqueue Bootstrap and our assets with correct plugin URLs
		wp_register_script('mamo_bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'));
		wp_enqueue_script('mamo_bootstrap');
		wp_register_style('mamo_modal_css', plugins_url('../css/modal.css', __FILE__));
		wp_enqueue_style('mamo_modal_css');
		wp_register_script('pmpro_mamo_js', plugins_url('../js/pmpro-mamo.js', __FILE__), array('jquery'), '1.0');
		$localize = array(
			'data' => array(
				'url' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('ajax-nonce' . pmpro_getOption('gateway')),
				'action' => 'pmpro_mamo_get_redirect',
				'ajax' => true,
			)
		);
		wp_localize_script('pmpro_mamo_js', 'pmproMamoVars', $localize);
		wp_enqueue_script('pmpro_mamo_js');
	}

	public static function pmpro_checkout_after_form()
	{
		echo '<div class="modal fade" id="mamo_payment_popup" tabindex="-1" role="dialog" data-backdrop="false">'
			. '<div class="modal-dialog modal-lg" role="document">'
				. '<div class="modal-content">'
					. '<div class="modal-body">'
						. '<iframe id="wc_mamo_iframe" name="wc_mamo_iframe" width="100%" height="620px" style="border: 0;" allow="payment"></iframe>'
					. '</div>'
					. '<div class="modal-footer">'
						. '<button type="button" class="btn btn-default" data-dismiss="modal">' . __('Close', 'pmpro-mamo') . '</button>'
					. '</div>'
				. '</div>'
			. '</div>'
		. '</div>';
	}

	public static function wp_ajax_get_redirect()
	{
		if (empty($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ajax-nonce' . pmpro_getOption('gateway'))) {
			wp_send_json_error(array('message' => __('Invalid nonce', 'pmpro-mamo')));
		}
		parse_str(isset($_POST['form_data']) ? $_POST['form_data'] : '', $form_data);
		$level_id = isset($form_data['pmpro_level']) ? intval($form_data['pmpro_level']) : 0;

		// Extract discount code from form data
		$discount_code = isset($form_data['discount_code']) ? sanitize_text_field($form_data['discount_code']) : '';
		if (empty($discount_code) && isset($form_data['pmpro_discount_code'])) {
			$discount_code = sanitize_text_field($form_data['pmpro_discount_code']);
		}

		// Use PMPro's function to get level WITH discount applied
		if (function_exists('pmpro_getLevelAtCheckout')) {
			$pmpro_level = pmpro_getLevelAtCheckout($level_id, $discount_code);
		} else {
			global $wpdb;
			$pmpro_level = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->pmpro_membership_levels} WHERE id=%d LIMIT 1", $level_id));
		}

		if (empty($pmpro_level)) {
			wp_send_json_error(array('message' => __('Level not found', 'pmpro-mamo')));
		}

		\PMPro\Mamo\Utils\MamoLogger::info('Popup: Level retrieved with discount', array(
			'level_id' => $level_id,
			'discount_code' => !empty($discount_code) ? $discount_code : 'none',
			'initial_payment' => isset($pmpro_level->initial_payment) ? $pmpro_level->initial_payment : 'N/A',
			'billing_amount' => isset($pmpro_level->billing_amount) ? $pmpro_level->billing_amount : 'N/A',
		));

		// Check if level is free
		if (function_exists('pmpro_isLevelFree') && pmpro_isLevelFree($pmpro_level)) {
			\PMPro\Mamo\Utils\MamoLogger::info('Popup: Level is FREE, no payment required', array('level_id' => $level_id));
			wp_send_json_error(array('message' => __('This level is free. No payment required.', 'pmpro-mamo')));
		}

		// Double-check: if initial_payment and billing_amount are both 0, reject
		if ((float)$pmpro_level->initial_payment <= 0 && (float)$pmpro_level->billing_amount <= 0) {
			\PMPro\Mamo\Utils\MamoLogger::warning('Popup: Zero amount detected', array(
				'level_id' => $level_id,
				'initial_payment' => $pmpro_level->initial_payment,
				'billing_amount' => $pmpro_level->billing_amount
			));
			wp_send_json_error(array('message' => __('Invalid amount. Level appears to be free.', 'pmpro-mamo')));
		}

		// Create a real pending order for popup mode
		if (!class_exists('MemberOrder')) {
			wp_send_json_error(array('message' => __('MemberOrder class not found', 'pmpro-mamo')));
		}

		// Create user account if not logged in
		$user_id = 0;
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
		} else {
			$email = isset($form_data['bemail']) ? sanitize_email($form_data['bemail']) : '';
			$first_name = isset($form_data['first_name']) ? sanitize_text_field($form_data['first_name']) : (isset($form_data['bfirstname']) ? sanitize_text_field($form_data['bfirstname']) : '');
			$last_name = isset($form_data['last_name']) ? sanitize_text_field($form_data['last_name']) : (isset($form_data['blastname']) ? sanitize_text_field($form_data['blastname']) : '');

			if (!empty($email)) {
				$existing_user = get_user_by('email', $email);
				if ($existing_user) {
					$user_id = $existing_user->ID;
				} else {
					$username = sanitize_user($email);
					if (username_exists($username)) {
						$username = $username . '_' . rand(1000, 9999);
					}
					$password = isset($form_data['password']) ? $form_data['password'] : wp_generate_password(12, true, true);
					$user_id = wp_create_user($username, $password, $email);
					if (!is_wp_error($user_id)) {
						wp_update_user(array(
							'ID' => $user_id,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'display_name' => trim($first_name . ' ' . $last_name) ?: $username,
						));
						$user = new WP_User($user_id);
						$user->set_role('subscriber');
						wp_set_current_user($user_id, $username);
						wp_set_auth_cookie($user_id, true);
						do_action('wp_login', $username, $user);
					}
				}
			}
		}

		// Create order
		$morder = new MemberOrder();
		$morder->membership_id = $level_id;
		$morder->membership_level = $pmpro_level;
		$morder->user_id = $user_id;
		$morder->InitialPayment = $pmpro_level->initial_payment;
		$morder->PaymentAmount = $pmpro_level->billing_amount;
		if (!empty($discount_code)) {
			$morder->discount_code = $discount_code;
		}
		$morder->FirstName = $first_name;
		$morder->LastName = $last_name;
		$morder->Email = $email;
		$morder->status = 'pending';
		$morder->gateway = 'mamo';
		$morder->payment_type = 'MAMO';
		$morder->code = $morder->getRandomCode();
		$morder->saveOrder();

		$amount = 0.00;
		if ($pmpro_level->initial_payment > 0) {
			$amount = (float)$pmpro_level->initial_payment;
		} else {
			$amount = (float)$pmpro_level->billing_amount;
		}

		$memberName = trim($first_name . ' ' . $last_name);
		if ($memberName === '') $memberName = get_bloginfo('name');

		$isRecurring = !empty($pmpro_level->billing_amount) && !empty($pmpro_level->cycle_number) && !empty($pmpro_level->cycle_period);
		$req = array(
			'ProductName' => $memberName,
			'SumToBill' => number_format(max($amount, 0.00), 2, '.', ''),
			'ReturnValue' => $morder->code,
			'SuccessRedirectUrl' => pmpro_url('confirmation'),
			'ErrorRedirectUrl' => pmpro_url('checkout'),
			'CardOwnerEmail' => $email,
			'CardOwnerName' => $memberName,
			'InvoiceLines1.Description' => $pmpro_level->name ?? __('Membership', 'pmpro-mamo'),
			'InvoiceLines1.Price' => number_format(max($amount, 0.00), 2, '.', ''),
			'InvoiceLines1.Quantity' => 1,
			'link_type' => 'modal',
			'save_card' => $isRecurring ? 'optional' : 'off',
		);

		if ($isRecurring) {
			$frequency = MamoApi::mapCyclePeriodToFrequency($pmpro_level->cycle_period);
			$req['subscription'] = array(
				'frequency' => $frequency,
				'frequency_interval' => (int)$pmpro_level->cycle_number,
				'start_date' => MamoApi::formatDate(current_time('timestamp')),
			);
		}

		$adapter = new MamoAdapter();
		$res = $adapter->createPaymentPage($req);
		$responseCode = isset($res['ResponseCode']) ? (int)$res['ResponseCode'] : -1;
		$redirectUrl = '';
		foreach (array('URL', 'Url', 'url', 'payment_url') as $k) { if (!empty($res[$k])) { $redirectUrl = $res[$k]; break; } }
		if ($responseCode === 0 && !empty($redirectUrl)) {
			if (!empty($res['id'])) {
				update_pmpro_membership_order_meta($morder->id, '_mamo_payment_link_id', sanitize_text_field($res['id']));
			}
			\PMPro\Mamo\Utils\MamoLogger::info('Popup: Payment Link success', array('order_code' => $morder->code));
			wp_send_json_success(array('redirectUrl' => $redirectUrl, 'orderCode' => $morder->code));
		}

		\PMPro\Mamo\Utils\MamoLogger::error('Popup: Payment Link failed', array('response_code' => $responseCode, 'description' => $res['Description'] ?? 'Unknown'));
		wp_send_json_error(array('message' => isset($res['Description']) ? $res['Description'] : __('MAMO error', 'pmpro-mamo')));
	}

	public static function wp_ajax_webhook()
	{
		require_once plugin_dir_path(__DIR__) . 'includes/mamo_webhook.php';
		exit;
	}

	public static function wp_ajax_iframe()
	{
		$url = isset($_GET['u']) ? esc_url_raw(wp_unslash($_GET['u'])) : '';
		header('Content-Type: text/html; charset=utf-8');
		echo '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"><title>MAMO Checkout</title><style>html,body{margin:0;padding:0;height:100%;}iframe{border:0;width:100%;height:100vh;display:block;}</style></head><body>';
		if (!empty($url)) {
			echo '<iframe allow="payment" src="' . esc_url($url) . '"></iframe>';
		} else {
			echo '<p style="padding:16px;">Missing checkout URL.</p>';
		}
		echo '</body></html>';
		exit;
	}

	public static function options_updated($old_value, $value)
	{
		if (!is_array($value)) {
			return;
		}
		$cronTime = isset($value['mamo_cron_time']) ? $value['mamo_cron_time'] : null;
		if ($cronTime) {
			\PMPro\Mamo\Services\MamoCron::schedule($cronTime);
		}
	}

	public static function after_change_membership_level($level_id, $user_id)
	{
		if (empty($level_id) || (int)$level_id === 0) {
			update_user_meta($user_id, '_mamo_recurring_disabled', 1);
			delete_user_meta($user_id, '_mamo_next_due');
		}
	}
}

add_action('init', array('PMProGateway_mamo', 'init'));

