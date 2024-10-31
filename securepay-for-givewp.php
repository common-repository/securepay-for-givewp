<?php
/**
 * SecurePay for GiveWP.
 *
 * @author  SecurePay Sdn Bhd
 * @license GPL-2.0+
 *
 * @see    https://securepay.net
 */

/*
 * @wordpress-plugin
 * Plugin Name:         SecurePay for GiveWP
 * Plugin URI:          https://www.securepay.my/?utm_source=wp-plugins-givewp&utm_campaign=plugin-uri&utm_medium=wp-dash
 * Version:             1.0.5
 * Description:         SecurePay payment platform plugin for GiveWP
 * Author:              SecurePay Sdn Bhd
 * Author URI:          https://www.securepay.my/?utm_source=wp-plugins-givewps&utm_campaign=author-uri&utm_medium=wp-dash
 * Requires at least:   5.4
 * Requires PHP:        7.2
 * License:             GPL-2.0+
 * License URI:         http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:         securepaygivewp
 * Domain Path:         /languages
 */

if (!\defined('ABSPATH') || \defined('SECUREPAY_GIVEWP_FILE')) {
    exit;
}

\define('SECUREPAY_GIVEWP_VERSION', '1.0.5');
\define('SECUREPAY_GIVEWP_SLUG', 'securepay-for-givewp');
\define('SECUREPAY_GIVEWP_ENDPOINT_LIVE', 'https://securepay.my/api/v1/');
\define('SECUREPAY_GIVEWP_ENDPOINT_SANDBOX', 'https://sandbox.securepay.my/api/v1/');
\define('SECUREPAY_GIVEWP_ENDPOINT_PUBLIC_LIVE', 'https://securepay.my/api/public/v1/');
\define('SECUREPAY_GIVEWP_ENDPOINT_PUBLIC_SANDBOX', 'https://sandbox.securepay.my/api/public/v1/');
\define('SECUREPAY_GIVEWP_FILE', __FILE__);
\define('SECUREPAY_GIVEWP_HOOK', plugin_basename(SECUREPAY_GIVEWP_FILE));
\define('SECUREPAY_GIVEWP_PATH', realpath(plugin_dir_path(SECUREPAY_GIVEWP_FILE)).'/');
\define('SECUREPAY_GIVEWP_URL', trailingslashit(plugin_dir_url(SECUREPAY_GIVEWP_FILE)));

require __DIR__.'/includes/load.php';
SecurePay_GiveWP::attach();
