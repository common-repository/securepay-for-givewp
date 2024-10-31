<?php
/**
 * SecurePay for GiveWP.
 *
 * @author  SecurePay Sdn Bhd
 * @license GPL-2.0+
 *
 * @see    https://securepay.net
 */
\defined('ABSPATH') || exit;

final class SecurePay_GiveWP
{
    private static function register_locale()
    {
        add_action(
            'plugins_loaded',
            function () {
                load_plugin_textdomain(
                    'securepaygivewp',
                    false,
                    SECUREPAY_GIVEWP_PATH.'languages/'
                );
            },
            0
        );
    }

    public static function register_admin_hooks()
    {
        add_action(
            'plugins_loaded',
            function () {
                if (current_user_can(apply_filters('capability', 'manage_options'))) {
                    add_action('all_admin_notices', [__CLASS__, 'callback_compatibility'], \PHP_INT_MAX);
                }
            }
        );
    }

    public static function register_addon_hooks()
    {
        add_action('init', function () {
            if (self::is_givewp_activated()) {
                require_once SECUREPAY_GIVEWP_PATH.'/includes/src/GiveWP_SecurePay.php';
                (new GiveWP_SecurePay())->init();
            }
        });
    }

    private static function is_givewp_activated()
    {
        return class_exists('Give', false) && \function_exists('give');
    }

    private static function register_autoupdates()
    {
        add_filter(
            'auto_update_plugin',
            function ($update, $item) {
                if (SECUREPAY_GIVEWP_SLUG === $item->slug) {
                    return !\defined('SECUREPAY_GIVEWP_AUTOUPDATE_DISABLED') || !SECUREPAY_GIVEWP_AUTOUPDATE_DISABLED ? true : false;
                }

                return $update;
            },
            \PHP_INT_MAX,
            2
        );
    }

    public static function callback_compatibility()
    {
        if (!self::is_givewp_activated()) {
            $html = '<div id="securepay-notice" class="notice notice-error is-dismissible">';
            $html .= '<p>'.esc_html__('SecurePay require GiveWP plugin. Please install and activate.', 'securepay').'</p>';
            $html .= '</div>';
            echo wp_kses_post($html);
        }
    }

    public static function activate()
    {
        return true;
    }

    public static function deactivate()
    {
        return true;
    }

    public static function uninstall()
    {
        return true;
    }

    public static function register_plugin_hooks()
    {
        register_activation_hook(SECUREPAY_GIVEWP_HOOK, [__CLASS__, 'activate']);
        register_deactivation_hook(SECUREPAY_GIVEWP_HOOK, [__CLASS__, 'deactivate']);
        register_uninstall_hook(SECUREPAY_GIVEWP_HOOK, [__CLASS__, 'uninstall']);
    }

    public static function attach()
    {
        self::register_locale();
        self::register_admin_hooks();
        self::register_addon_hooks();
        self::register_autoupdates();
    }
}
