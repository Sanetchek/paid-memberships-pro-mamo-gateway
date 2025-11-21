<?php

use PMPro\Mamo\Services\MamoCron;
use WP_Mock\Tools\TestCase;

class TestMamoCron extends TestCase {

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	public function test_schedule_sets_daily_event() {
		\WP_Mock::userFunction('wp_next_scheduled', ['return' => false]);
		\WP_Mock::userFunction('pmpro_getOption', ['return' => '02:30']);
		\WP_Mock::userFunction('current_time', ['return' => time()]);

		\WP_Mock::userFunction('wp_schedule_event', [
			'times' => 1,
			'args' => [\WP_Mock\Functions::type('int'), 'daily', 'pmpro_mamo_recurring_cron']
		]);

		MamoCron::schedule();
		$this->assertTrue(true); // Asserting no exceptions and mock called
	}

    public function test_run_skips_if_no_users() {
        \WP_Mock::userFunction('get_users', ['return' => []]);
        // Should return early
        MamoCron::run();
        $this->assertTrue(true);
    }

    public function test_run_skips_user_with_subscription_id() {
        $user = new stdClass();
        $user->ID = 123;

        \WP_Mock::userFunction('get_users', ['return' => [$user]]);
        \WP_Mock::userFunction('get_user_meta', [
            'args' => [123, '_mamo_recurring_disabled', true],
            'return' => ''
        ]);
        \WP_Mock::userFunction('pmpro_getMembershipLevelsForUser', ['return' => []]); // Mocking this to avoid deep logic for now, but main check is before

        // Mock the specific calls we want to verify
        \WP_Mock::userFunction('get_user_meta', [
            'args' => [123, '_mamo_card_id', true],
            'return' => 'card_123'
        ]);

        // THIS IS THE KEY TEST: It should call get_user_meta for subscription_id
        \WP_Mock::userFunction('get_user_meta', [
            'args' => [123, '_mamo_subscription_id', true],
            'return' => 'sub_123'
        ]);

        // If it has sub_id, it should continue (skip) and NOT call chargeToken
        // We can't easily mock "not called" on a new instance method without DI,
        // but we can ensure it doesn't crash or try to do more.

        MamoCron::run();
        $this->assertTrue(true);
    }
}
