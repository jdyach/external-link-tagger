<?php
/**
 * Plugin Name:       External Link Tagger
 * Plugin URI:        https://github.com/jdyach/external-link-tagger
 * Description:       Automatically adds target="_blank" and rel="noopener noreferrer" to external links in post content, excerpts, and widgets.
 * Version:           1.0.0
 * Author:            Jacqui Dyachenko
 * Author URI:        https://jacquid.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       external-link-tagger
 * Requires at least: 6.0
 * Requires PHP:      8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ELT_VERSION',    '1.0.0' );
define( 'ELT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ELT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ELT_OPTIONS_KEY', 'elt_settings' );

require_once ELT_PLUGIN_DIR . 'includes/class-elt-tagger.php';
require_once ELT_PLUGIN_DIR . 'includes/class-elt-settings.php';

function elt_init(): void {
    ELT_Settings::init();
    ELT_Tagger::init();
}
add_action( 'plugins_loaded', 'elt_init' );

/**
 * Set default options on activation.
 */
register_activation_hook( __FILE__, function (): void {
    if ( false === get_option( ELT_OPTIONS_KEY ) ) {
        add_option( ELT_OPTIONS_KEY, [
            'tag_content'  => '1',
            'tag_excerpts' => '1',
            'tag_widgets'  => '1',
            'add_noopener' => '1',
        ] );
    }
} );
