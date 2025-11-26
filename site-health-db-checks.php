<?php
/*
Plugin Name: Site Health - add database checks
Plugin URI: https://www.damiencarbery.com/
Description: Add some database checks to Site Health - look for orphaned postmeta and large options values.
Author: Damien Carbery
Author URI: https://www.damiencarbery.com
License: GPL v3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: site-health-db-checks
Domain Path: /languages
Version: 0.1.20251126
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
		$this->init();
	}


	// Set up WordPress specfic actions.
	private function init() {
		//add_action( 'init', array( $this, 'load_translations' ) );
		add_filter( 'site_status_tests', array( $this, 'registerTests' ) );
	}


	// This is no longer needed as WordPress 6.7+ will automatically load translations when needed.
	/*public function load_translations() {
		load_plugin_textdomain( 'site-health-db-checks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}*/


	public function registerTests( $tests ) {
		$tests['direct']['dcwd_orphan_postmeta'] = [
			'label' => _x( 'Orphaned Postmeta', 'Title of section in Site Health results', 'site-health-db-checks' ),
			'test'  => array( $this, 'testOrphanedPostmeta' ),
			'skip_cron' => true,
		];

		$tests['direct']['dcwd_large_option_values'] = [
			'label' => _x( 'Large autoloaded option values', 'Title of section in Site Health results', 'site-health-db-checks' ),
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
				'status' => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'site-health-db-checks' ),
					'color' => 'blue',
				),
				'label' => _x( 'Orphaned Postmeta', 'Title of section in Site Health results', 'site-health-db-checks' ),
				'description' => sprintf( '<p>%s %s</p>',
							/* translators: %d: The number of orphaned postmeta rows in the database. */
							sprintf( _n( 'Your database has %d row of orphaned postmeta data.', 'Your database has %d rows of orphaned postmeta data.', $orphans[0][0], 'site-health-db-checks' ), $orphans[0][0] ),
							__( 'This may impact your site performance.', 'site-health-db-checks' ) ),
				//'description' => sprintf( '<p>Your database has ' . _n('%d row', '%d rows', $orphans[0][0], 'site-health-db-checks') . ' of orphaned postmeta data. This may impact your site performance.</p>', $orphans[0][0] ),
				'actions' => sprintf( '<p>%s</p>', __( 'You can install <a href="https://wordpress.org/plugins/advanced-database-cleaner/">Advanced Database Cleaner</a> to delete these orphaned postmeta rows.', 'site-health-db-checks' ) )
			);
		}

		return array(
			'test'=> $test,
			'status' => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'site-health-db-checks' ),
				'color' => 'blue',
			),
			'label' => _x( 'Orphaned Postmeta', 'Title of section in Site Health results', 'site-health-db-checks' ),
			'description' => __( 'Your database does not have any orphaned postmeta data.', 'site-health-db-checks' ),
		);
	}


	public function testLargeOptionValues() {
		$test = 'dcwd_large_option_values';

		global $wpdb;
		$lg_values = $wpdb->get_results( "SELECT COUNT(option_name) FROM {$wpdb->prefix}options WHERE autoload='yes' AND LENGTH(option_value) > 1024", ARRAY_N );
		if ( $lg_values[0][0] > 0 ) {
			return array(
				'test'=> $test,
				'status' => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'site-health-db-checks' ),
					'color' => 'blue',
				),
				'label' => _x( 'Large autoloaded option values', 'Title of section in Site Health results', 'site-health-db-checks' ),
				'description' => sprintf( '<p>%s %s</p>',
						/* translators: %d: The number of large autoloaded options in the wp_options table. */
						sprintf( _n( 'The options table has %d large autoloaded option.', 'The options table has %d large autoloaded options.', $lg_values[0][0], 'site-health-db-checks' ), $lg_values[0][0] ),
						__( 'This may impact your site performance.', 'site-health-db-checks' ) ),
						/* translators: %s: This is the name of the wp_options table. */
				'actions' => sprintf( '<p>%s</p><p>%s</p>', sprintf( __( 'You can examine the large autoloaded rows in the %s table. Some could be set not to be autoloaded.', 'site-health-db-checks' ), '<code>' . $wpdb->prefix . 'options' . '</code>' ),
							__( 'You can install <a href="https://wordpress.org/plugins/aaa-option-optimizer/">AAA Option Optimizer</a> to review unused options and value sizes.', 'site-health-db-checks' ) )
			);
		}

		return array(
			'test'=> $test,
			'status' => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'site-health-db-checks' ),
				'color' => 'blue',
			),
			'label' => _x( 'Large autoloaded option values', 'Title of section in Site Health results', 'site-health-db-checks' ),
			'description' => sprintf( '<p>%s</p>', __( 'Your database does not have any large autoloaded options values.', 'site-health-db-checks' ) ),
		);
	}


}

$SiteHealthDBChecks = SiteHealthDBChecks::get_instance();
