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

use Give\Helpers\Form\Utils as FormUtils;

final class GiveWP_SecurePay
{
    public function init()
    {
        add_filter('give_payment_gateways', function ($gateways) {
            $gateways['securepay'] = [
                'admin_label' => esc_html__('SecurePay', 'securepaygivewp'),
                'checkout_label' => esc_html__('SecurePay', 'securepaygivewp'),
            ];

            return $gateways;
        });

        add_filter('give_get_sections_gateways', function ($sections) {
            $sections['securepay-settings'] = esc_html__('SecurePay', 'securepaygivewp');

            return $sections;
        });

        add_action('give_securepay_cc_form', [$this, 'give_securepay_cc_form']);

        add_filter('give_get_settings_gateways', [$this, 'settings_gateways']);
        add_filter('give_forms_securepay_metabox_fields', [$this, 'securepay_metabox_fields']);
        add_filter('give_metabox_form_data_settings', [$this, 'securepay_setting_tab']);
        add_filter('give_donation_form_submit_button', [$this, 'submit_button'], \PHP_INT_MAX, 2);

        add_action('give_gateway_securepay', [$this, 'process_payment']);
        add_action('init', [$this, 'process_callback'], \PHP_INT_MAX);
        add_filter('give_payment_confirm_securepay', [$this, 'give_securepay_success_page_content']);
    }

    public function settings_gateways($settings)
    {
        if ('securepay-settings' !== give_get_current_setting_section()) {
            return $settings;
        }

        $global_settings = [
            [
                'name' => esc_html__('General Settings', 'securepaygivewp'),
                'id' => 'securepay_title_general',
                'type' => 'title',
            ],
            [
                'name' => esc_html__('SecurePay Test Mode', 'securepaygivewp'),
                'desc' => esc_html__('Click "Enabled" to allow testing SecurePay without credentials.', 'securepaygivewp'),
                'id' => 'securepay_testmode',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'name' => esc_html__('Show Bank List', 'securepaygivewp'),
                'desc' => esc_html__('Enables this to show bank list.', 'securepaygivewp'),
                'id' => 'securepay_banklist',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'name' => esc_html__('Use Supported Bank Logo', 'securepaygivewp'),
                'desc' => esc_html__('Enables this to use supported bank logo.', 'securepaygivewp'),
                'id' => 'securepay_banklogo',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'type' => 'sectionend',
                'id' => 'securepay_title_general',
            ],
            [
                'name' => esc_html__('Live Settings', 'securepaygivewp'),
                'id' => 'securepay_title_live',
                'type' => 'title',
            ],
            [
                'name' => esc_html__('Live Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live Token.', 'securepaygivewp'),
                'id' => 'securepay_live_token',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('Live Checksum Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live Checksum Token.', 'securepaygivewp'),
                'id' => 'securepay_live_checksum',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('Live UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live UID.', 'securepaygivewp'),
                'id' => 'securepay_live_uid',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('Live Partner UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live Partner UID (Optional).', 'securepaygivewp'),
                'id' => 'securepay_live_partner_uid',
                'type' => 'text',
            ],
            [
                'type' => 'sectionend',
                'id' => 'securepay_title_live',
            ],
            [
                'name' => esc_html__('Sandbox Settings', 'securepaygivewp'),
                'id' => 'securepay_title_sandbox',
                'type' => 'title',
            ],
            [
                'name' => esc_html__('Sandbox Mode', 'securepaygivewp'),
                'desc' => esc_html__('Click "Enabled" to enable SecurePay Sandbox Mode and override Gateways Test Mode.', 'securepaygivewp'),
                'id' => 'securepay_sandboxmode',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'name' => esc_html__('Sandbox Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox Token.', 'securepaygivewp'),
                'id' => 'securepay_sandbox_token',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('Sandbox Checksum Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox Checksum Token.', 'securepaygivewp'),
                'id' => 'securepay_sandbox_checksum',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('Sandbox UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox UID.', 'securepaygivewp'),
                'id' => 'securepay_sandbox_uid',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('Sandbox Partner UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox Partner UID (Optional).', 'securepaygivewp'),
                'id' => 'securepay_sandbox_partner_uid',
                'type' => 'text',
            ],
            [
                'type' => 'sectionend',
                'id' => 'securepay_title_sandbox',
            ],
            [
                'name' => esc_html__('Reference Settings', 'securepaygivewp'),
                'id' => 'securepay_title_formset',
                'type' => 'title',
            ],
            [
                'name' => esc_html__('Bill Description', 'securepaygivewp'),
                'desc' => esc_html__('Enter default description to be included in the bill.', 'securepaygivewp'),
                'id' => 'securepay_description',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('Billing Fields', 'securepaygivewp'),
                'desc' => esc_html__('This option will enable the billing details section for SecurePay which requires the donor\'s address to complete the donation. These fields are not required by SecurePay to process the transaction, but you may have the need to collect the data.', 'securepaygivewp'),
                'id' => 'securepay_collect_billing',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'type' => 'sectionend',
                'id' => 'securepay_title_formset',
            ],
        ];

        return array_merge($settings, $global_settings);
    }

    public function securepay_metabox_fields($settings)
    {
        if (!give_is_gateway_active('securepay') || \in_array('securepay', (array) give_get_option('gateways'))) {
            return $settings;
        }

        $metabox_settings = [
            [
                'name' => esc_html__('SecurePay Test Mode', 'securepaygivewp'),
                'desc' => esc_html__('Click "Enabled" to allow testing SecurePay without credentials.', 'securepaygivewp'),
                'id' => 'securepay_testmode',
                'type' => 'radio_inline',
                'default' => give_get_option('securepay_testmode', 'disabled'),
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'name' => esc_html__('Show Bank List', 'securepaygivewp'),
                'desc' => esc_html__('Enables this to show bank list.', 'securepaygivewp'),
                'id' => 'securepay_banklist',
                'type' => 'radio_inline',
                'default' => give_get_option('securepay_banklist', 'disabled'),
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'name' => esc_html__('Use Supported Bank Logo', 'securepaygivewp'),
                'desc' => esc_html__('Enables this to use supported bank logo.', 'securepaygivewp'),
                'id' => 'securepay_banklogo',
                'type' => 'radio_inline',
                'default' => give_get_option('securepay_banklogo', 'disabled'),
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'name' => esc_html__('Live Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live Token.', 'securepaygivewp'),
                'id' => 'securepay_live_token',
                'type' => 'text',
                'default' => give_get_option('securepay_live_token', ''),
            ],
            [
                'name' => esc_html__('Live Checksum Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live Checksum Token.', 'securepaygivewp'),
                'id' => 'securepay_live_checksum',
                'type' => 'text',
                'default' => give_get_option('securepay_live_checksum', ''),
            ],
            [
                'name' => esc_html__('Live UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live UID.', 'securepaygivewp'),
                'id' => 'securepay_live_uid',
                'type' => 'text',
                'default' => give_get_option('securepay_live_uid', ''),
            ],
            [
                'name' => esc_html__('Live Partner UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Live Partner UID (Optional).', 'securepaygivewp'),
                'id' => 'securepay_live_partner_uid',
                'type' => 'text',
                'default' => give_get_option('securepay_live_partner_uid', ''),
            ],
            [
                'name' => esc_html__('Sandbox Mode', 'securepaygivewp'),
                'desc' => esc_html__('Click "Enabled" to enable SecurePay Sandbox Mode and override Gateways Test Mode.', 'securepaygivewp'),
                'id' => 'securepay_sandboxmode',
                'type' => 'radio_inline',
                'default' => give_get_option('securepay_sandboxmode', 'disabled'),
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
            [
                'name' => esc_html__('Sandbox Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox Token.', 'securepaygivewp'),
                'id' => 'securepay_sandbox_token',
                'type' => 'text',
                'default' => give_get_option('securepay_sandbox_token', ''),
            ],
            [
                'name' => esc_html__('Sandbox Checksum Token', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox Checksum Token.', 'securepaygivewp'),
                'id' => 'securepay_sandbox_checksum',
                'type' => 'text',
                'default' => give_get_option('securepay_sandbox_checksum', ''),
            ],
            [
                'name' => esc_html__('Sandbox UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox UID.', 'securepaygivewp'),
                'id' => 'securepay_sandbox_uid',
                'type' => 'text',
                'default' => give_get_option('securepay_sandbox_uid', ''),
            ],
            [
                'name' => esc_html__('Sandbox Partner UID', 'securepaygivewp'),
                'desc' => esc_html__('Enter SecurePay Sandbox Partner UID (Optional).', 'securepaygivewp'),
                'id' => 'securepay_sandbox_partner_uid',
                'type' => 'text',
                'default' => give_get_option('securepay_sandbox_partner_uid', ''),
            ],
            [
                'name' => esc_html__('Bill Description', 'securepaygivewp'),
                'desc' => esc_html__('Enter description to be included in the bill.', 'securepaygivewp'),
                'id' => 'securepay_description',
                'type' => 'text',
                'default' => give_get_option('securepay_description', ''),
            ],
            [
                'name' => esc_html__('Billing Fields', 'securepaygivewp'),
                'desc' => esc_html__('This option will enable the billing details section for SecurePay which requires the donor\'s address to complete the donation. These fields are not required by SecurePay to process the transaction, but you may have the need to collect the data.', 'securepaygivewp'),
                'id' => 'securepay_collect_billing',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'securepaygivewp'),
                    'disabled' => esc_html__('Disabled', 'securepaygivewp'),
                ],
            ],
        ];

        return array_merge($settings, $metabox_settings);
    }

    public function securepay_setting_tab($settings)
    {
        if (!give_is_gateway_active('securepay')) {
            return $settings;
        }

        $settings['securepay_options'] = apply_filters('give_forms_securepay_options', [
            'id' => 'securepay_options',
            'title' => esc_html__('SecurePay', 'securepaygivewp'),
            'icon-html' => '<i class="far fa-id-card"></i>',
            'fields' => apply_filters('give_forms_securepay_metabox_fields', []),
        ]);

        return $settings;
    }

    public function give_securepay_cc_form($form_id)
    {
        $bc = give_get_meta($form_id, 'securepay_collect_billing', true);
        if (empty($bc)) {
            $bc = give_get_option('securepay_collect_billing');
        }

        if (give_is_setting_enabled($bc)) {
            give_default_cc_address_fields($form_id);
        }

        if (FormUtils::isLegacyForm($form_id)) {
            return false;
        }

        printf(
        '
        <fieldset class="no-fields">
            <div style="display: flex; justify-content: center; margin-top: 20px;">
                <img src="'.SECUREPAY_GIVEWP_URL.'includes/admin/securepay-logo.png" width=100>
            </div>
            <p style="text-align: center;"><b>%1$s</b></p>
            <p style="text-align: center;">
                <b>%2$s</b> %3$s
            </p>
        </fieldset>
        ',
            esc_html__('Make your donation quickly and securely with SecurePay', 'securepaygivewp'),
            esc_html__('How it works:', 'securepaygivewp'),
            esc_html__('You will be redirected to SecurePay to pay using your online banking. You will then be brought back to this page to view your receipt.', 'securepaygivewp')
        );

        return true;
    }

    private function testmode_enabled($form_id)
    {
        $testmode = give_get_meta($form_id, 'securepay_testmode', true);
        if (empty($testmode)) {
            $testmode = give_get_option('securepay_testmode');
        }

        return give_is_setting_enabled($testmode);
    }

    private function sandboxmode_enabled($form_id)
    {
        $testmode = $this->testmode_enabled($form_id);
        $sandboxmode = give_get_meta($form_id, 'securepay_sandboxmode', true);

        if (empty($sandboxmode)) {
            $sandboxmode = give_get_option('securepay_sandboxmode');
        }

        return give_is_test_mode() || $testmode || give_is_setting_enabled($sandboxmode);
    }

    private function banklist_enabled($form_id)
    {
        $mm = give_get_meta($form_id, 'securepay_banklist', true);
        if (empty($mm)) {
            $mm = give_get_option('securepay_banklist');
        }

        return give_is_setting_enabled($mm);
    }

    private function banklogo_enabled($form_id)
    {
        $mm = give_get_meta($form_id, 'securepay_banklogo', true);
        if (empty($mm)) {
            $mm = give_get_option('securepay_banklogo');
        }

        return give_is_setting_enabled($mm);
    }

    private function sptokens($form_id)
    {
        $securepay_live_checksum = give_get_meta($form_id, 'securepay_live_checksum', true);
        if (empty($securepay_live_checksum)) {
            $securepay_live_checksum = give_get_option('securepay_live_checksum');
        }

        $securepay_live_partner_uid = give_get_meta($form_id, 'securepay_live_partner_uid', true);
        if (empty($securepay_live_partner_uid)) {
            $securepay_live_partner_uid = give_get_option('securepay_live_partner_uid');
        }

        $securepay_live_token = give_get_meta($form_id, 'securepay_live_token', true);
        if (empty($securepay_live_token)) {
            $securepay_live_token = give_get_option('securepay_live_token');
        }

        $securepay_live_uid = give_get_meta($form_id, 'securepay_live_uid', true);
        if (empty($securepay_live_uid)) {
            $securepay_live_uid = give_get_option('securepay_live_uid');
        }

        $securepay_sandbox_checksum = give_get_meta($form_id, 'securepay_sandbox_checksum', true);
        if (empty($securepay_sandbox_checksum)) {
            $securepay_sandbox_checksum = give_get_option('securepay_sandbox_checksum');
        }

        $securepay_sandboxmode = give_get_meta($form_id, 'securepay_sandboxmode', true);
        if (empty($securepay_sandboxmode)) {
            $securepay_sandboxmode = give_get_option('securepay_sandboxmode');
        }

        $securepay_sandbox_partner_uid = give_get_meta($form_id, 'securepay_sandbox_partner_uid', true);
        if (empty($securepay_sandbox_partner_uid)) {
            $securepay_sandbox_partner_uid = give_get_option('securepay_sandbox_partner_uid');
        }

        $securepay_sandbox_token = give_get_meta($form_id, 'securepay_sandbox_token', true);
        if (empty($securepay_sandbox_token)) {
            $securepay_sandbox_token = give_get_option('securepay_sandbox_token');
        }

        $securepay_sandbox_uid = give_get_meta($form_id, 'securepay_sandbox_uid', true);
        if (empty($securepay_sandbox_uid)) {
            $securepay_sandbox_uid = give_get_option('securepay_sandbox_uid');
        }

        $securepay_testmode = give_get_meta($form_id, 'securepay_testmode', true);
        if (empty($securepay_testmode)) {
            $securepay_testmode = give_get_option('securepay_testmode');
        }

        if ($this->testmode_enabled($form_id)) {
            $sp_payment_url = SECUREPAY_GIVEWP_ENDPOINT_SANDBOX;
            $sp_token = 'GFVnVXHzGEyfzzPk4kY3';
            $sp_checksum = '3faa7b27f17c3fb01d961c08da2b6816b667e568efb827544a52c62916d4771d';
            $sp_uid = '4a73a364-6548-4e17-9130-c6e9bffa3081';
            $sp_partner_uid = '';
        } else {
            if ($this->sandboxmode_enabled($form_id)) {
                $sp_payment_url = SECUREPAY_GIVEWP_ENDPOINT_SANDBOX;
                $sp_token = $securepay_sandbox_token;
                $sp_checksum = $securepay_sandbox_checksum;
                $sp_uid = $securepay_sandbox_uid;
                $sp_partner_uid = $securepay_sandbox_uid;
            } else {
                $sp_payment_url = SECUREPAY_GIVEWP_ENDPOINT_LIVE;
                $sp_token = $securepay_live_token;
                $sp_checksum = $securepay_live_checksum;
                $sp_uid = $securepay_live_uid;
                $sp_partner_uid = $securepay_live_uid;
            }
        }

        return (object) [
            'payment_url' => $sp_payment_url,
            'token' => $sp_token,
            'checksum' => $sp_checksum,
            'uid' => $sp_uid,
            'partner_uid' => $sp_partner_uid,
        ];
    }

    private function create_payment($purchase_data)
    {
        $form_id = (int) $purchase_data['post_data']['give-form-id'];
        $price_id = !empty($purchase_data['post_data']['give-price-id']) ? $purchase_data['post_data']['give-price-id'] : '';

        $insert_payment_data = [
            'price' => $purchase_data['price'],
            'give_form_title' => $purchase_data['post_data']['give-form-title'],
            'give_form_id' => $form_id,
            'give_price_id' => $price_id,
            'date' => $purchase_data['date'],
            'user_email' => $purchase_data['user_email'],
            'purchase_key' => $purchase_data['purchase_key'],
            'currency' => give_get_currency($form_id, $purchase_data),
            'user_info' => $purchase_data['user_info'],
            'status' => 'pending',
            'gateway' => 'securepay',
        ];

        $insert_payment_data = apply_filters('give_create_payment', $insert_payment_data);

        return give_insert_payment($insert_payment_data);
    }

    public function process_payment($data)
    {
        give_validate_nonce($data['gateway_nonce'], 'give-gateway');

        give_clear_errors();
        if (give_get_errors()) {
            give_send_back_to_checkout('?payment-mode=securepay');
        }

        unset($data['card_info']);

        $payment_id = $this->create_payment($data);
        if (empty($payment_id)) {
            /* translators: %s: payment data */
            $error = sprintf(esc_html__('Payment creation failed before sending donor to SecurePay. Payment data: %s', 'securepaygivewp'), json_encode($data));
            give_record_gateway_error(__('Payment Error', 'securepaygivewp'), $error, $payment_id);
            give_send_back_to_checkout();
        }

        $amount = $data['price'];

        $buyer_name = '';
        $buyer_email = !empty($data['user_email']) ? $data['user_email'] : '';
        $buyer_phone = '';
        if (!empty($data['user_info'])) {
            if (empty($buyer_email)) {
                $buyer_email = $data['user_info']['email'];
            }

            $buyer_name = $data['user_info']['title'].' '.$data['user_info']['first_name'].' '.$data['user_info']['last_name'];
            $buyer_name = trim($buyer_name);
        }

        $post_data = $data['post_data'];
        if (empty($buyer_name)) {
            $buyer_name = $post_data['give_title'].' '.$post_data['give_first'].' '.$post_data['give_last'];
            $buyer_name = trim($buyer_name);
        }

        if (empty($buyer_email)) {
            $buyer_email = $post_data['give_email'];
        }

        $buyer_bank_code = !empty($post_data['buyer_bank_code']) ? $post_data['buyer_bank_code'] : false;
        $form_id = $post_data['give-form-id'];

        $description = give_get_meta($form_id, 'securepay_description', true);
        if (empty($description)) {
            $description = give_get_option('securepay_description');
        }

        if (empty($description)) {
            $description = $post_data['give-form-title'].' at '.get_bloginfo('name');
        }

        $query_args = 'timeout=0&cancel=0&pid='.$payment_id;
        $query_hash = base64_encode($query_args);
        $redirect_url = add_query_arg('securepay_return', $query_hash, get_bloginfo('url'));
        $callback_url = $redirect_url;

        $query_args = 'timeout=0&cancel=1&pid='.$payment_id;
        $query_hash = base64_encode($query_args);
        $cancel_url = add_query_arg('securepay_return', $query_hash, get_bloginfo('url'));

        $query_args = 'timeout=1&cancel=0&pid='.$payment_id;
        $query_hash = base64_encode($query_args);
        $timeout_url = add_query_arg('securepay_return', $query_hash, get_bloginfo('url'));

        $tokens = $this->sptokens($form_id);
        if (empty($tokens->checksum) || empty($tokens->uid) || empty($tokens->token)) {
            $error = esc_html__('Invalid SecurePay Credentials. Please Check SecurePay settings.', 'securepaygivewp');
            give_record_gateway_error(__('Payment Error', 'securepaygivewp'), $error, $payment_id);
            give_send_back_to_checkout();
        }

        $securepay_args['order_number'] = $payment_id;
        $securepay_args['buyer_name'] = $buyer_name;
        $securepay_args['buyer_email'] = $buyer_email;
        $securepay_args['buyer_phone'] = $buyer_phone;
        $securepay_args['product_description'] = $description;
        $securepay_args['transaction_amount'] = $amount;
        $securepay_args['redirect_url'] = $redirect_url;
        $securepay_args['callback_url'] = $callback_url;
        $securepay_args['cancel_url'] = $cancel_url;
        $securepay_args['timeout_url'] = $timeout_url;
        $securepay_args['token'] = $tokens->token;
        $securepay_args['partner_uid'] = $tokens->partner_uid;
        $securepay_args['checksum'] = $tokens->checksum;
        $securepay_args['uid'] = $tokens->uid;

        if ($this->banklist_enabled($form_id) && !empty($buyer_bank_code)) {
            $securepay_args['buyer_bank_code'] = esc_attr($buyer_bank_code);
        }

        $payment_page = SECUREPAY_GIVEWP_URL.'includes/admin/securepay-payment.php';
        $payment_url = add_query_arg(['p' => $securepay_args, 'u' => $tokens->payment_url], $payment_page);
        $output = wp_get_inline_script_tag('window.onload = function(){window.parent.location = "'.$payment_url.'";}');
        exit($output);
    }

    private function get_bank_list($form_id, $force = false)
    {
        $dosandbox = $this->sandboxmode_enabled($form_id);

        if (is_user_logged_in()) {
            $force = true;
        }

        $bank_list = $force ? false : get_transient(SECUREPAY_GIVEWP_SLUG.'_banklist');
        $endpoint_pub = $dosandbox ? SECUREPAY_GIVEWP_ENDPOINT_PUBLIC_SANDBOX : SECUREPAY_GIVEWP_ENDPOINT_PUBLIC_LIVE;

        if (empty($bank_list)) {
            $remote = wp_remote_get(
                $endpoint_pub.'/banks/b2c?status',
                [
                    'timeout' => 10,
                    'user-agent' => SECUREPAY_GIVEWP_SLUG.'/'.SECUREPAY_GIVEWP_VERSION,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Referer' => home_url(),
                    ],
                ]
            );

            if (!is_wp_error($remote) && isset($remote['response']['code']) && 200 === $remote['response']['code'] && !empty($remote['body'])) {
                $data = json_decode($remote['body'], true);
                if (!empty($data) && \is_array($data) && !empty($data['fpx_bankList'])) {
                    $list = $data['fpx_bankList'];
                    foreach ($list as $arr) {
                        $status = 1;
                        if (empty($arr['status_format2']) || 'offline' === $arr['status_format1']) {
                            $status = 0;
                        }

                        $bank_list[$arr['code']] = [
                            'name' => $arr['name'],
                            'status' => $status,
                        ];
                    }

                    if (!empty($bank_list) && \is_array($bank_list)) {
                        set_transient(SECUREPAY_GIVEWP_SLUG.'_banklist', $bank_list, 60);
                    }
                }
            }
        }

        return !empty($bank_list) && \is_array($bank_list) ? $bank_list : false;
    }

    private function is_bank_list($form_id, &$bank_list = '')
    {
        if ($this->banklist_enabled($form_id)) {
            $bank_list = $this->get_bank_list($form_id, false);

            return !empty($bank_list) && \is_array($bank_list) ? true : false;
        }

        $bank_list = '';

        return false;
    }

    private function banklist_output($form_id)
    {
        $html = '';
        $bank_list = '';

        if ($this->is_bank_list($form_id, $bank_list)) {
            $bank_id = !empty($_POST['buyer_bank_code']) ? sanitize_text_field($_POST['buyer_bank_code']) : false;
            $image = false;
            if ($this->banklogo_enabled($form_id)) {
                $image = SECUREPAY_GIVEWP_URL.'includes/admin/securepay-bank-alt.png';
            }

            $select2_css = SECUREPAY_GIVEWP_PATH.'includes/admin/min/select2.min.css';
            if (@is_file($select2_css)) {
                $html .= '<style>';
                $html .= '/*@'.time().'*/';
                $html .= file_get_contents($select2_css);
                $html .= '</style>'.\PHP_EOL;
            }

            $file_helper_css = SECUREPAY_GIVEWP_PATH.'includes/admin/securepaygivewp.css';
            if (@is_file($file_helper_css)) {
                $html .= '<style>';
                $html .= '/*@'.time().'*/';
                $html .= file_get_contents($file_helper_css);
                $html .= '</style>'.\PHP_EOL;
            }

            $select2_js = SECUREPAY_GIVEWP_PATH.'includes/admin/min/select2.min.js';
            if (@is_file($select2_js)) {
                $html .= wp_get_inline_script_tag('/*@'.time().'*/'.file_get_contents($select2_js));
            }

            $file_helper_js = SECUREPAY_GIVEWP_PATH.'includes/admin/securepaygivewp.js';
            if (@is_file($file_helper_js)) {
                $html .= wp_get_inline_script_tag('/*@'.time().'*/'.file_get_contents($file_helper_js));
            }

            $html .= '<div id="spwfmbody-fpxbank" class="spwfmbody" style="display:none;"><fieldset>';
            $html .= '<legend for="buyer_bank_code">Pay with SecurePay</legend>';

            if (!empty($image)) {
                $html .= '<img src="'.$image.'" class="spwfmlogo">';
            }

            $html .= '<select name="buyer_bank_code" id="buyer_bank_code">';
            $html .= "<option value=''>Please Select Bank</option>";
            foreach ($bank_list as $id => $arr) {
                $name = $arr['name'];
                $status = $arr['status'];

                $disabled = empty($status) ? ' disabled' : '';
                $offline = empty($status) ? ' (Offline)' : '';
                $selected = $id === $bank_id ? ' selected' : '';
                $html .= '<option value="'.$id.'"'.$selected.$disabled.'>'.$name.$offline.'</option>';
            }
            $html .= '</select>';

            $html .= '</fieldset></div>';

            $html .= wp_get_inline_script_tag('if ( "function" === typeof(securepaygivewp_bank_select) ) { securepaygivewp_bank_select(jQuery, "'.SECUREPAY_GIVEWP_URL.'includes/admin/bnk/", '.time().', "'.SECUREPAY_GIVEWP_VERSION.'"); }', ['id' => SECUREPAY_GIVEWP_SLUG.'-bankselect']);
        }

        return $html;
    }

    public function submit_button($html, $form_id)
    {
        if (!give_is_gateway_active('securepay')) {
            return $html;
        }

        $banklist = $this->banklist_output($form_id);

        return $banklist.$html;
    }

    private function sanitize_response()
    {
        $params = [
             'amount',
             'bank',
             'buyer_email',
             'buyer_name',
             'buyer_phone',
             'checksum',
             'client_ip',
             'created_at',
             'created_at_unixtime',
             'currency',
             'exchange_number',
             'fpx_status',
             'fpx_status_message',
             'fpx_transaction_id',
             'fpx_transaction_time',
             'id',
             'interface_name',
             'interface_uid',
             'merchant_reference_number',
             'name',
             'order_number',
             'payment_id',
             'payment_method',
             'payment_status',
             'receipt_url',
             'retry_url',
             'source',
             'status_url',
             'transaction_amount',
             'transaction_amount_received',
             'uid',
             'securepay_return',
         ];

        $response_params = [];
        if (isset($_REQUEST)) {
            foreach ($params as $k) {
                if (isset($_REQUEST[$k])) {
                    $response_params[$k] = sanitize_text_field($_REQUEST[$k]);
                }
            }
        }

        return $response_params;
    }

    private function response_status($response_params)
    {
        if ((isset($response_params['payment_status']) && 'true' === $response_params['payment_status']) || (isset($response_params['fpx_status']) && 'true' === $response_params['fpx_status'])) {
            return true;
        }

        return false;
    }

    private function is_response_callback($response_params)
    {
        if (isset($response_params['fpx_status'])) {
            return true;
        }

        return false;
    }

    private function redirect($redirect)
    {
        if (!headers_sent()) {
            wp_redirect($redirect);
            exit;
        }

        $html = "<script>window.location.replace('".$redirect."');</script>";
        $html .= '<noscript><meta http-equiv="refresh" content="1; url='.$redirect.'">Redirecting..</noscript>';

        echo wp_kses(
            $html,
            [
                'script' => [],
                'noscript' => [],
                'meta' => [
                    'http-equiv' => [],
                    'content' => [],
                ],
            ]
        );
        exit;
    }

    public function process_callback()
    {
        $response_params = $this->sanitize_response();

        if (!empty($response_params) && !empty($response_params['securepay_return'])) {
            $hash = base64_decode($response_params['securepay_return']);
            if (false === $hash) {
                exit('failed to decode securepay_return');
            }

            parse_str($hash, $data);
            if (empty($data) || !\is_array($data) || empty($data['pid'])) {
                exit('failed to decode securepay_return');
            }

            if (!empty($response_params['order_number'])) {
                $success = $this->response_status($response_params);

                $callback = $this->is_response_callback($response_params) ? 'Callback' : 'Redirect';
                $receipt_link = !empty($response_params['receipt_url']) ? $response_params['receipt_url'] : '';
                $status_link = !empty($response_params['status_url']) ? $response_params['status_url'] : '';
                $retry_link = !empty($response_params['retry_url']) ? $response_params['retry_url'] : '';

                $payment_id = $response_params['order_number'];
                $form_id = give_get_payment_form_id($payment_id);

                $trans_id = !empty($response_params['merchant_reference_number']) ? $response_params['merchant_reference_number'] : '';

                if ($success) {
                    $note = 'SecurePay payment successful'.\PHP_EOL;
                    $note .= 'Response from: '.$callback.\PHP_EOL;
                    $note .= 'Transaction ID: '.$trans_id.\PHP_EOL;

                    if (!empty($receipt_link)) {
                        $note .= 'Receipt link: '.$receipt_link.\PHP_EOL;
                    }

                    if (!empty($status_link)) {
                        $note .= 'Status link: '.$status_link.\PHP_EOL;
                    }

                    if ('publish' !== get_post_status($payment_id)) {
                        give_update_payment_status($payment_id, 'publish');
                        give_insert_payment_note($payment_id, $note);
                    }

                    $return = add_query_arg([
                        'payment-confirmation' => 'securepay',
                        'payment-id' => $payment_id,
                    ], get_permalink(give_get_option('success_page')));

                    $this->redirect($return);
                    exit;
                }

                $note = 'SecurePay payment failed'.\PHP_EOL;
                $note .= 'Response from: '.$callback.\PHP_EOL;
                $note .= 'Transaction ID: '.$trans_id.\PHP_EOL;

                if (!empty($retry_link)) {
                    $note .= 'Retry link: '.$retry_link.\PHP_EOL;
                }

                if (!empty($status_link)) {
                    $note .= 'Status link: '.$status_link.\PHP_EOL;
                }

                give_insert_payment_note($payment_id, $note);
                give_update_payment_status($payment_id, 'failed');

                $this->redirect(give_get_failed_transaction_uri('?payment-id='.$payment_id));
                exit;
            }

            // cancel
            if (!empty($data['cancel'])) {
                $note = 'SecurePay payment cancelled'.\PHP_EOL;
                give_insert_payment_note($data['pid'], $note);
                give_update_payment_status($data['pid'], 'cancelled');
                $form_id = give_get_payment_form_id($data['pid']);
                $this->redirect(get_permalink($form_id));
                exit;
            }

            // timeout
            if (!empty($data['timeout'])) {
                $note = 'SecurePay payment timeout'.\PHP_EOL;
                give_insert_payment_note($data['pid'], $note);
                give_update_payment_status($data['pid'], 'abandoned');
                $this->redirect(give_get_failed_transaction_uri('?payment-id='.$data['pid']));
                exit;
            }
        }
    }

    public function give_securepay_success_page_content($content)
    {
        $payment_id = isset($_GET['payment-id']) ? sanitize_text_field($_GET['payment-id']) : false;
        if (!$payment_id && !give_get_purchase_session()) {
            return $content;
        }

        $payment_id = absint($payment_id);

        if (!$payment_id) {
            $session = give_get_purchase_session();
            $payment_id = give_get_donation_id_by_key($session['purchase_key']);
        }

        $payment = get_post($payment_id);
        if ($payment && 'pending' === $payment->post_status) {
            ob_start();
            give_get_template_part('payment', 'processing');
            $content = ob_get_clean();
        }

        return $content;
    }
}
