<?php

namespace PMPro\Mamo\Adapters;

if (!defined('ABSPATH')) {
	exit;
}

interface PaymentProviderInterface
{
	/** Create hosted payment page (Redirect/IFRAME). */
	public function createPaymentPage($request);

	/** Pull final status for transaction by charge ID. */
	public function pullIndicator($terminalNumber, $userName, $chargeId);

	/** Charge a stored card (MIT - Merchant Initiated Transaction). */
	public function chargeToken($request);

	/** Optional: Create invoice after charge. */
	public function createInvoice($request);

	/** Refund by transaction ID (Charge ID). */
	public function refundByTransactionId($request);
}

