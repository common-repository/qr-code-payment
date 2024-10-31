<?php
/**
 * Plugin Name:       QR Code Payment
 * Plugin URI:        https://bluemedia.pl
 * Description:       Adds QRCode to payments by regular transfer.
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.1
 * Author:            Blue Services
 * Author URI:        https://bluemedia.pl
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       qrcode-payment
 * Domain Path:       /languages
 */

defined('ABSPATH') or die('No script kiddies please!');

define('BS_QRCODE_PAYMENT_VERSION', '1.0.1');
define('BS_QRCODE_PAYMENT_PATH', __DIR__);
define('BS_QRCODE_PLUGIN_TITLE', 'QRCode Payment');
define('BS_QRCODE_PAYMENT_PLUGIN_SLUG', 'qrcode-payment');

require_once BS_QRCODE_PAYMENT_PATH . '/BsQrCodePayment.php';
require_once BS_QRCODE_PAYMENT_PATH . '/vendor/autoload.php';

function activate_bs_qr_code_payment()
{
	if (!get_option('bs_qrcode_promotion_text')) {
		update_option(
			'bs_qrcode_promotion_text',
			__('Scan the QR code in your bank\'s mobile application', BS_QRCODE_PAYMENT_PLUGIN_SLUG)
		);
	}

	if (!get_option('bs_qrcode_title')) {
		update_option(
			'bs_qrcode_title',
			__('Order number: %s', BS_QRCODE_PAYMENT_PLUGIN_SLUG)
		);
	}
}

function deactivate_bs_qr_code_payment()
{

}

register_activation_hook( __FILE__, 'activate_bs_qr_code_payment' );
register_deactivation_hook( __FILE__, 'deactivate_bs_qr_code_payment' );

new BsQrCodePayment();