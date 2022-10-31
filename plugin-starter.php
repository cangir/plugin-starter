<?php
/**
 * The plugin bootstrap file
 *
 * @wordpress-plugin
 * Plugin Name:       Plugin Starter
 * Plugin URI:        http://wp-plugins.dinoloper.com/plugin-starter
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Dinoloper
 * Author URI:        http://dinoloper.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       plugin-starter
 * Domain Path:       /languages
 *
 * @author      Dinoloper <info@dinoloper.com>
 * @package     Plugin_Starter
 * @version     1.0.0
 */

namespace PluginStarter;

defined( 'ABSPATH' ) || exit; // Cannot access directly.

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_STARTER_VERSION', '1.0.0' );

// Define Constants.
define( 'PLUGIN_STARTER_DIR', plugin_dir_path( __FILE__ ) );                // Plugin path.
define( 'PLUGIN_STARTER_URL', plugin_dir_url( __FILE__ ) );                 // Plugin url.
define( 'PLUGIN_STARTER_BASE', dirname( plugin_basename( __FILE__ ) ) );    // dino.

require_once plugin_dir_path( __FILE__ ) . 'src/utils/class-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'src/utils/class-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'src/utils/class-i18n.php';
require_once plugin_dir_path( __FILE__ ) . 'src/utils/class-loader.php';
require_once plugin_dir_path( __FILE__ ) . 'src/class-app.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-plugin-name-activator.php
 */
function plugin_starter_activate() {
	utils\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function plugin_starter_deactivate() {
	utils\Deactivator::deactivate();
}

register_activation_hook( __FILE__, '\PluginStarter\plugin_starter_activate' );
register_deactivation_hook( __FILE__, '\PluginStarter\plugin_starter_deactivate' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function plugin_starter_run() {
	$plugin = new App();
	$plugin->run();
}
plugin_starter_run();
