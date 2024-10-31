<?php
/**
 * This file served as a wrapper to solve the issue with the X-Frame-Options header.
 * This file will receive input from GiveWP_SecurePay::process_payment() and send the payment data to the SecurePay end-point.
 * The input should send as $_GET query and not as HTML form.
 *
 * References:
 *  https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Frame-Options
 *  includes/src/GiveWP_SecurePay.php
*/
\defined('ABSPATH') && exit;

if (empty($_GET['u']) || empty($_GET['p']) || !\is_array($_GET['p'])) {
    exit;
}

if (false === strpos($_GET['u'], 'securepay.my')) {
    exit;
}

function calculate_sign($checksum, $a, $b, $c, $d, $e, $f, $g, $h, $i)
{
    $str = $a.'|'.$b.'|'.$c.'|'.$d.'|'.$e.'|'.$f.'|'.$g.'|'.$h.'|'.$i;

    return hash_hmac('sha256', $str, $checksum);
}

$payment_url = $_GET['u'];
$args = $_GET['p'];

$checksum = $args['checksum'];
$buyer_email = $args['buyer_email'];
$buyer_name = $args['buyer_name'];
$buyer_phone = $args['buyer_phone'];
$redirect_url = $args['redirect_url'];
$payment_id = $args['order_number'];
$description = $args['product_description'];
$uid = $args['uid'];
$amount = $args['transaction_amount'];
$securepay_sign = calculate_sign($checksum, $buyer_email, $buyer_name, $buyer_phone, $redirect_url, $payment_id, $description, $redirect_url, $amount, $uid);

$args['checksum'] = $securepay_sign;
$args['payment_source'] = 'givewp';

$output = '<!doctype html><html><head><title>SecurePay</title>';
$output .= '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate, max-age=0, s-maxage=0, proxy-revalidate">';
$output .= '<meta http-equiv="Expires" content="0">';
$output .= '</head><body>';
$output .= '<form name="order" id="securepay_payment" method="post" action="'.$payment_url.'payments">';
foreach ($args as $f => $v) {
    $output .= '<input type="hidden" name="'.$f.'" value="'.$v.'">';
}

$output .= '</form>';
$output .= '<script>document.getElementById( "securepay_payment" ).submit();</script>';
$output .= '</body></html>';

exit($output);