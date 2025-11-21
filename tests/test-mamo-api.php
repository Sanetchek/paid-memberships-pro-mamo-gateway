<?php

use PMPro\Mamo\Services\MamoApi;
use WP_Mock\Tools\TestCase;

class TestMamoApi extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_isTestMode_returns_false_if_function_missing() {
		$this->assertFalse(MamoApi::isTestMode());
	}

	public function test_isTestMode_returns_true_for_sandbox() {
		\WP_Mock::userFunction('pmpro_getOption', [
			'args' => ['gateway_environment'],
			'return' => 'sandbox'
		]);
		$this->assertTrue(MamoApi::isTestMode());
	}

	public function test_getCredentials_returns_test_keys() {
		\WP_Mock::userFunction('pmpro_getOption', [
			'args' => ['gateway_environment'],
			'return' => 'sandbox'
		]);
		\WP_Mock::userFunction('pmpro_getOption', [
			'args' => ['mamo_test_api_key'],
			'return' => 'test_key_123'
		]);

		$creds = MamoApi::getCredentials();
		$this->assertEquals('test_key_123', $creds['api_key']);
		$this->assertTrue($creds['is_test_mode']);
	}

	public function test_getCurrencyCode_defaults_to_AED() {
		\WP_Mock::userFunction('pmpro_getOption', [
			'args' => ['currency'],
			'return' => 'XYZ' // Unsupported
		]);
		$this->assertEquals('AED', MamoApi::getCurrencyCode());
	}

	public function test_getCurrencyCode_returns_supported() {
		\WP_Mock::userFunction('pmpro_getOption', [
			'args' => ['currency'],
			'return' => 'USD'
		]);
		$this->assertEquals('USD', MamoApi::getCurrencyCode());
	}

	public function test_mapCyclePeriodToFrequency() {
		$this->assertEquals('daily', MamoApi::mapCyclePeriodToFrequency('Day'));
		$this->assertEquals('monthly', MamoApi::mapCyclePeriodToFrequency('Month'));
		$this->assertEquals('yearly', MamoApi::mapCyclePeriodToFrequency('Year'));
		$this->assertEquals('monthly', MamoApi::mapCyclePeriodToFrequency('Unknown'));
	}
}
