<?php
/*
Plugin Name: Site Health - add database checks
Plugin URI: https://www.damiencarbery.com/
Description: Add some database checks to Site Health - look for orphaned postmeta and large options values.
Author: Damien Carbery
Author URI: https://www.damiencarbery.com
Version: 0.1.20251118
*/

defined( 'ABSPATH' ) || exit;


class SiteHealthDBChecks {
	private static $instance;
	

	// Returns an instance of this class. 
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		} 
		return self::$instance;
	}


	// Initialize the plugin variables.
	private function __construct() {
		//$this->privateUploads = false;

		$this->init();
	}


	// Set up WordPress specfic actions.
	private function init() {
		add_filter( 'site_status_tests', array( $this, 'registerTests' ) );
	}


	public function registerTests( $tests ) {
		//error_log( var_export( $tests, true ) );
		$tests['direct']['dcwd_orphan_postmeta'] = [
			'label' => 'Orphaned Postmeta',
			'test'  => array( $this, 'testOrphanedPostmeta' ),
			'skip_cron' => true,
		];

		$tests['direct']['dcwd_large_option_values'] = [
			'label' => 'Large autoloaded option values',
			'test'  => array( $this, 'testLargeOptionValues' ),
			'skip_cron' => true,
		];

		return $tests;
	}


	public function testOrphanedPostmeta() {
		$test = 'dcwd_orphan_postmeta';

		global $wpdb;
		$orphans = $wpdb->get_results( "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta pm LEFT JOIN {$wpdb->prefix}posts p on pm.post_id = p.ID where p.ID IS NULL", ARRAY_N );
		if ( $orphans[0][0] > 0 ) {
			return array(
				'test'=> $test,
				'status' => 'critical', // 'good', 'recommended'
				'badge'       => array(
					'label' => 'Performance',
					'color' => 'blue',
				),
				'label' => 'Orphaned Postmeta',
				'description' => sprintf( '<p>Your database has ' . _n('%d row', '%d rows', $orphans[0][0], 'site-health-db-checks') . ' of orphaned postmeta data. This may impact your site performance.</p>', $orphans[0][0] ),
				'actions' => '<p>You can install <a href="https://wordpress.org/plugins/advanced-database-cleaner/">Advanced Database Cleaner</a> to delete these orphaned postmeta rows.</p>'
			);		}

		return array(
			'test'=> $test,
			'status' => 'good',
			'badge'       => array(
				'label' => 'Performance',
				'color' => 'blue',
			),
			'label' => 'Orphaned Postmeta',
			'description' => 'Your database does not have any orphaned postmeta data.',
		);
	}


	public function testLargeOptionValues() {
		$test = 'dcwd_large_option_values';

		global $wpdb;
		$lg_values = $wpdb->get_results( "SELECT COUNT(option_name) FROM {$wpdb->prefix}options WHERE autoload='yes' AND LENGTH(option_value) > 1024", ARRAY_N );
		if ( $lg_values[0][0] > 0 ) {
			return array(
				'test'=> $test,
				'status' => 'critical', // 'good', 'recommended'
				'badge'       => array(
					'label' => 'Performance',
					'color' => 'blue',
				),
				'label' => 'Large autoloaded options',
				'description' => sprintf( '<p>The options table has ' . _n('%d large autoloaded option', '%d large autoloaded options', $lg_values[0][0], 'site-health-db-checks') . '. This may impact your site performance.</p>', $lg_values[0][0] ),
				'actions' => sprintf( '<p>You can examine the large autoloaded rows in the %soptions table. Some could be set not to be autoloaded.</p>', $wpdb->prefix )
			);		}

		return array(
			'test'=> $test,
			'status' => 'good',
			'badge'       => array(
				'label' => 'Performance',
				'color' => 'blue',
			),
			'label' => 'Large autoloaded options',
			'description' => 'Your database does not have any large autoloaded options values.',
		);
	}


}

$SiteHealthDBChecks = SiteHealthDBChecks::get_instance();
