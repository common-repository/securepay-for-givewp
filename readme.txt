=== SecurePay For GiveWP ===
Contributors: SecurePay
Tags: payment gateway, payment platform, Malaysia, online banking, fpx
Requires at least: 5.4
Tested up to: 6.3
Requires PHP: 7.2
Stable tag: 1.0.5
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SecurePay payment platform plugin for GiveWP.

== Description ==

Install this plugin to extends the [GiveWP](https://wordpress.org/plugins/give/) plugin to accept payments with the [SecurePay Payment Platform](https://www.securepay.my/?utm_source=wp-plugins-givewp&utm_campaign=author-uri&utm_medium=wp-dash) for Malaysians.

If you have any questions or suggestions about this plugin, please contact us directly through email at **hello@securepay.my** . Our friendly team will gladly reply as soon as possible.

Other Integrations:

- [SecurePay For WooCommerce](https://wordpress.org/plugins/securepay/)
- [SecurePay For GravityForms](https://wordpress.org/plugins/securepay-for-gravityforms/)
- [SecurePay For WPJobster](https://wordpress.org/plugins/securepay-for-wpjobster/)
- [SecurePay For Restrict Content Pro](https://wordpress.org/plugins/securepay-for-restrictcontentpro)
- [SecurePay For Paid Memberships Pro](https://wordpress.org/plugins/securepay-for-paidmembershipspro)

== Installation ==

Make sure that you already have GiveWP plugin installed and activated.

- Login to your *WordPress Dashboard*
- Go to **Plugins > Add New**
- Search **SecurePay For GiveWP** and click **Install**
- **Activate** the plugin through the 'Plugins' screen in WordPress.

Contact us through email hello@securepay.my if you have any questions or comments about this plugin.


== Changelog ==
= 1.0.5 (17-12-2021) =
- Fixed: global securepay_live_checksum conditional logic -> GiveWP_SecurePay::sptokens().

= 1.0.4 (02-11-2021) =
- Fixed: Undefined variable "output" -> GiveWP_SecurePay::process_payment().
- Fixed: Log and return to checkout if invalid credentials -> GiveWP_SecurePay::process_payment().
- Added: SecurePay description at non legacy forms -> GiveWP_SecurePay::give_securepay_cc_form().

= 1.0.3 (01-11-2021) =
- Fixed: multistep form.

= 1.0.2 (27-10-2021) =
- Fixed: typo at securepay_metabox_fields(), settings_gateways().
- Fixed: banklist_output -> js securepaybankgivewp function checking.

= 1.0.1 (25-08-2021) =
- Fixed: bank list select script.
- Fixed: handle bank image not exists.
- Fixed: is_bank_list -> get_bank_list missing form_id.

= 1.0.0 (23-08-2021) =
- Initial release.
