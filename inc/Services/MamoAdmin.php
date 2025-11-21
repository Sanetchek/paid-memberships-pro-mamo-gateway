<?php

namespace PMPro\Mamo\Services;

if (!defined('ABSPATH')) {
	exit;
}

class MamoAdmin
{
	public static function init()
	{
		if (is_admin()) {
			add_action('admin_menu', array(__CLASS__, 'menu'));
			add_action('admin_footer', array(__CLASS__, 'inject_orders_list_refund_js'));
			add_action('wp_ajax_pmpro_mamo_refund', array(__CLASS__, 'ajax_refund'));
		}
	}

	public static function menu()
	{
		add_submenu_page(
			'memberships-settings',
			'MAMO Tools',
			'MAMO Tools',
			'manage_options',
			'pmpro-mamo-tools',
			array(__CLASS__, 'render')
		);
	}

	public static function render()
	{
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		$nonce = wp_create_nonce('pmpro_mamo_run_cron');
		$ajax = add_query_arg(array('action' => 'pmpro_mamo_run_cron', 'nonce' => $nonce), admin_url('admin-ajax.php'));
		?>
		<div class="wrap">
			<h1>PMPro → MAMO Tools</h1>
			<p>
				<button class="button button-primary" id="pmpro-mamo-run-cron">Run Cron Now</button>
				<span id="pmpro-mamo-run-status" style="margin-left:8px;"></span>
			</p>
			<h2>Status</h2>
			<ul>
				<li>Next due (per-user): see user meta `_mamo_next_due`</li>
				<li>Retry count: user meta `_mamo_retry_count`</li>
				<li>Last charge status: user meta `_mamo_last_charge_status`</li>
			</ul>
			<script>
			(function(){
				document.getElementById('pmpro-mamo-run-cron').addEventListener('click', function(){
					var btn = this;
					var st = document.getElementById('pmpro-mamo-run-status');
					btn.disabled = true; st.textContent = 'Running...';
					fetch('<?php echo esc_url($ajax); ?>', { credentials: 'same-origin' })
						.then(function(r){ return r.json(); })
						.then(function(j){
							if (j && j.success) { st.textContent = 'Done'; }
							else { st.textContent = 'Error'; }
							btn.disabled = false;
						})
						.catch(function(){ st.textContent = 'Error'; btn.disabled = false; });
				});
			})();
			</script>
		</div>
		<?php
	}

	/**
	 * Adds a small JS helper on the PMPro Orders list to show a "Refund" button per row.
	 */
	public static function inject_orders_list_refund_js()
	{
		$screen = function_exists('get_current_screen') ? get_current_screen() : null;
		if (!$screen || strpos($screen->id, 'pmpro') === false) {
			return;
		}
		if (strpos($screen->id, 'pmpro-orders') === false && strpos($screen->id, 'pmpro_page_pmpro-orders') === false && strpos($screen->base, 'pmpro-orders') === false) {
			return;
		}
		$nonce = wp_create_nonce('pmpro_mamo_refund_ajax');
		$ajaxUrl = admin_url('admin-ajax.php');
		?>
		<style>
		#pmpro-mamo-refund-modal{position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100000;display:none}
		#pmpro-mamo-refund-modal .inner{background:#fff;width:420px;max-width:90%;margin:10% auto;padding:16px;border-radius:4px;box-shadow:0 4px 16px rgba(0,0,0,.2)}
		#pmpro-mamo-refund-modal .actions{text-align:right;margin-top:12px}
		#pmpro-mamo-refund-modal .field{margin:8px 0}
		</style>
		<script>
		(function(){
			var ajaxUrl = '<?php echo esc_js($ajaxUrl); ?>';
			var nonce = '<?php echo esc_js($nonce); ?>';
			function ensureModal(){
				if (document.getElementById('pmpro-mamo-refund-modal')) return;
				var modal = document.createElement('div');
				modal.id = 'pmpro-mamo-refund-modal';
				modal.innerHTML = '\n  <div class="inner">\n    <h2>MAMO Refund</h2>\n    <form id="pmpro-mamo-refund-form">\n      <div class="field">\n        <label for="pmpro-mamo-refund-amount">Amount to refund (leave empty for full):</label><br>\n        <input type="text" id="pmpro-mamo-refund-amount" class="regular-text" />\n      </div>\n      <div class="actions">\n        <button type="button" class="button" id="pmpro-mamo-refund-cancel">Cancel</button>\n        <button type="submit" class="button button-primary" id="pmpro-mamo-refund-submit">Refund</button>\n      </div>\n    </form>\n  </div>';
				document.body && document.body.appendChild(modal);
			}
			function openModal(orderId){
				ensureModal();
				var modal = document.getElementById('pmpro-mamo-refund-modal');
				var amount = document.getElementById('pmpro-mamo-refund-amount');
				if (!modal) return;
				modal.dataset.orderId = orderId;
				modal.style.display = 'block';
				if (amount) { amount.value = ''; amount.focus(); }
			}
			function closeModal(){
				var modal = document.getElementById('pmpro-mamo-refund-modal');
				if (modal) modal.style.display = 'none';
			}
			function bindModalEvents(){
				var modal = document.getElementById('pmpro-mamo-refund-modal');
				if (!modal) return;
				var cancelBtn = document.getElementById('pmpro-mamo-refund-cancel');
				var form = document.getElementById('pmpro-mamo-refund-form');
				cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });
				form.addEventListener('submit', function(e){
					e.preventDefault();
					var orderId = modal.dataset.orderId;
					var amount = (document.getElementById('pmpro-mamo-refund-amount').value || '').trim();
					if (!confirm('Confirm refund' + (amount ? (' of ' + amount) : ' (full)') + ' for order #' + orderId + '?')) return;
					var fd = new FormData();
					fd.append('action','pmpro_mamo_refund');
					fd.append('nonce',nonce);
					fd.append('order_id',orderId);
					fd.append('amount',amount || '');
					fetch(ajaxUrl,{method:'POST',credentials:'same-origin',body:fd}).then(function(r){return r.json();}).then(function(j){
						if (j && j.success) { alert('Refund processed.'); closeModal(); }
						else { alert('Refund failed: ' + (j && j.data ? j.data : 'unknown')); }
					}).catch(function(){ alert('Network error'); });
				});
			}
			function addRefundButtons(){
				var rows = document.querySelectorAll('table.wp-list-table tbody tr');
				rows.forEach(function(row){
					if (row.dataset.mamoRefundAdded) return;
					var link = row.querySelector('a[href*="page=pmpro-orders"][href*="order="]');
					if (!link) return;
					var m = link.href.match(/order=(\d+)/);
					if (!m) return;
					var orderId = m[1];
					var btn = document.createElement('button');
					btn.type = 'button';
					btn.textContent = 'Refund';
					btn.className = 'button';
					btn.style.marginLeft = '8px';
					btn.addEventListener('click', function(e){ e.preventDefault(); openModal(orderId); bindModalEvents(); });
					link.parentNode && link.parentNode.appendChild(btn);
					row.dataset.mamoRefundAdded = '1';
				});
			}
			addRefundButtons();
			document.addEventListener('DOMContentLoaded', addRefundButtons);
		})();
		</script>
		<?php
	}

	public static function ajax_refund()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error('forbidden', 403);
		}
		$nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
		if (empty($nonce) || !wp_verify_nonce($nonce, 'pmpro_mamo_refund_ajax')) {
			wp_send_json_error('invalid_nonce', 403);
		}
		$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
		$amount = isset($_POST['amount']) ? trim(wp_unslash($_POST['amount'])) : '';
		if ($order_id <= 0) {
			wp_send_json_error('bad_params', 400);
		}
		if (!class_exists('\\MemberOrder')) {
			if (defined('PMPRO_DIR') && file_exists(PMPRO_DIR . '/classes/class.memberorder.php')) {
				require_once PMPRO_DIR . '/classes/class.memberorder.php';
			} elseif (defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/paid-memberships-pro/classes/class.memberorder.php')) {
				require_once WP_PLUGIN_DIR . '/paid-memberships-pro/classes/class.memberorder.php';
			}
		}
		$order = new \MemberOrder($order_id);
		if (empty($order) || empty($order->id)) {
			wp_send_json_error('order_not_found', 404);
		}
		$chargeId = get_pmpro_membership_order_meta($order->id, '_mamo_charge_id', true);
		if (empty($chargeId)) {
			$chargeId = !empty($order->payment_transaction_id) ? $order->payment_transaction_id : '';
		}
		if (empty($chargeId)) {
			wp_send_json_error('no_transaction_id', 400);
		}
		$sumToRefund = '';
		if ($amount !== '') {
			$sumToRefund = number_format((float)$amount, 2, '.', '');
		}
		try {
			$adapter = new \PMPro\Mamo\Adapters\MamoAdapter();
			$req = array(
				'charge_id' => sanitize_text_field($chargeId),
			);
			if ($sumToRefund !== '') {
				$req['amount'] = $sumToRefund;
			}
			$res = $adapter->refundByTransactionId($req);
			$ok = isset($res['ResponseCode']) && (int)$res['ResponseCode'] === 0;
			if ($ok) {
				$order->notes = trim($order->notes . "\n" . 'Refunded via MAMO (list): ' . ($sumToRefund !== '' ? $sumToRefund : 'full'));
				$order->saveOrder();
				wp_send_json_success(true);
			} else {
				$msg = isset($res['Description']) ? $res['Description'] : 'Unknown error';
				wp_send_json_error($msg, 400);
			}
		} catch (\Throwable $e) {
			wp_send_json_error($e->getMessage(), 500);
		}
	}
}

