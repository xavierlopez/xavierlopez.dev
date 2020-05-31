<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.quantcast.com
 * @since             1.0.0
 * @package           QC_Choice
 *
 * @wordpress-plugin
 * Plugin Name:       Quantcast Choice
 * Plugin URI:        https://www.quantcast.com/gdpr/consent-management-solution/
 * Description:       The QC Choice plugin implements the Quantcast Choice GDPR Consent tool.
 * Version:           1.2.2
 * Author:            Quantcast
 * Author URI:        https://www.quantcast.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       qc-choice
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'QC_CHOICE_VERSION', '1.2.2' );
define( 'PUBLISHER_VENDORS_VERSION', 1 );
define( 'QC_CHOICE_VENDOR_FILE', 'quantcast-choice/.well-known/pubvendors.json' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-qc-choice-activator.php
 */
function activate_qc_choice() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-qc-choice-activator.php';
	QC_Choice_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-qc-choice-deactivator.php
 */
function deactivate_qc_choice() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-qc-choice-deactivator.php';
	QC_Choice_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_qc_choice' );
register_deactivation_hook( __FILE__, 'deactivate_qc_choice' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-qc-choice.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_qc_choice() {

	$plugin = new QC_Choice();
	$plugin->run();

}
run_qc_choice();
