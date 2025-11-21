<?php

use PMPro\Mamo\Adapters\MamoAdapter;
use WP_Mock\Tools\TestCase;

class TestMamoAdapter extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_createPaymentPage_success() {
		// Mock MamoApi static methods
		\WP_Mock::userFunction('pmpro_getOption', [
			'return' => 'sandbox'
		]);

		// Mock wp_remote_post
		\WP_Mock::userFunction('wp_remote_post', [
			'return' => [
				'response' => ['code' => 200],
				'body' => json_encode(['payment_url' => 'https://mamo.com/pay/123'])
			]
		]);

		$adapter = new MamoAdapter();
		$res = $adapter->createPaymentPage(['SumToBill' => 100]);

		$this->assertEquals(0, $res['ResponseCode']);
		$this->assertEquals('https://mamo.com/pay/123', $res['URL']);
	}

	public function test_chargeToken_success() {
		\WP_Mock::userFunction('pmpro_getOption', ['return' => 'sandbox']);

		\WP_Mock::userFunction('wp_remote_post', [
			'return' => [
				'response' => ['code' => 200],
				'body' => json_encode(['status' => 'captured'])
			]
		]);

		$adapter = new MamoAdapter();
		$res = $adapter->chargeToken(['card_id' => 'tok_123', 'amount' => 50]);

		$this->assertEquals(0, $res['ResponseCode']);
		$this->assertEquals('captured', $res['status']);
	}
}
