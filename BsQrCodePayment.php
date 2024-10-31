<?php

use Endroid\QrCode\QrCode;

class BsQrCodePayment
{
	public function __construct()
	{
		add_action('plugins_loaded', [$this, 'loadPluginTextDomain']);
		add_action('admin_menu', [$this, 'submenuPage']);
		add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
		add_action('woocommerce_thankyou', [$this, 'showQrCodeTemplate'], 4);
	}

	public function loadPluginTextDomain(): void
	{
		load_plugin_textdomain( BS_QRCODE_PAYMENT_PLUGIN_SLUG, false, basename(__DIR__) . '/languages/' );
	}

	public function submenuPage(): void
	{
		add_submenu_page(
			'woocommerce',
			BS_QRCODE_PLUGIN_TITLE,
			BS_QRCODE_PLUGIN_TITLE,
			'manage_options',
			'woo-subpage-qrcode',
			[$this, 'submenuCallback']
		);
	}

	public function submenuCallback(): void
	{
		if (isset($_POST['qrcode_submit'])) {
			update_option('bs_qrcode_receiver_name', sanitize_text_field($_POST['qrcode_receiver_name']));
			update_option('bs_qrcode_title', sanitize_text_field($_POST['qrcode_title']));
			update_option('bs_qrcode_promotion_text', sanitize_text_field($_POST['qrcode_promotion_text']));

			echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
				<p>
					<strong>' . __('Settings have been saved.', BS_QRCODE_PAYMENT_PLUGIN_SLUG) . '</strong>
				</p>
				<button type="button" class="notice-dismiss">
					<span class="screen-reader-text">'
			            . __('Hide this information.', BS_QRCODE_PAYMENT_PLUGIN_SLUG) .
			        '</span>
				</button>
				</div>';
		}

		echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
		echo '<h2>'. BS_QRCODE_PLUGIN_TITLE . ' - ' . __('Settings', BS_QRCODE_PAYMENT_PLUGIN_SLUG) . '</h2>';
		echo '<form method="post" action="admin.php?page=woo-subpage-qrcode" novalidate="novalidate">
			<table class="form-table" role="presentation">
				<tbody>
				<tr>
					<th scope="row">
						<label for="qrcode_receiver_name">'
		                    . __('Recipient of the transfer', BS_QRCODE_PAYMENT_PLUGIN_SLUG) .
		                '</label>
					</th>
					<td>
						<input name="qrcode_receiver_name" type="text" id="qrcode_receiver_name" 
							value="'. get_option('bs_qrcode_receiver_name') .'" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="qrcode_title">'
		                    . __('Transfer title', BS_QRCODE_PAYMENT_PLUGIN_SLUG) .
		                '</label>
					</th>
					<td>
						<input name="qrcode_title" type="text" id="qrcode_title" 
							value="'. get_option('bs_qrcode_title') .'" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="qrcode_promotion_text">'
		                    . __('Encouraging text', BS_QRCODE_PAYMENT_PLUGIN_SLUG) .
		                '</label>
					</th>
					<td>
						<input name="qrcode_promotion_text" type="text" id="qrcode_promotion_text" 
							value="'. get_option('bs_qrcode_promotion_text') .'" class="regular-text">
					</td>
				</tr>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" name="qrcode_submit" id="qrcode_submit" class="button button-primary" 
					value="' . __('Save changes', BS_QRCODE_PAYMENT_PLUGIN_SLUG) . '">
			</p>
			</form>
			</div>';
	}

	public function showQrCodeTemplate($order_id): void
	{
		$account_details = get_option('woocommerce_bacs_accounts');

		if ($account_details && count($account_details) > 0 && get_option('bs_qrcode_receiver_name')) {
			$order = wc_get_order($order_id);
			echo '<h2>' . get_option('bs_qrcode_promotion_text') . '</h2>';
			foreach ($account_details as $id => $account) {
				$total = (string)($order->get_total()*100);
				if (strlen($total) < 6) {
					$difference = 6 - strlen($total);
					for ($i = 1; $i <= $difference; $i++) {
						$total = '0' . $total;
					}
				}
				$qrcode_data = [
					'', // NIP
					'PL', // country id
					$account['account_number'] ?: $account['iban'], // account number
					$total, // payment total
					get_option('bs_qrcode_receiver_name'), // name of receiver
					sprintf(get_option('bs_qrcode_title'), $order_id), // title
				];

				if ($account['account_number'] || $account['iban']) {
					$qrCode = new QrCode(implode('|', $qrcode_data) . '|||');
					$qrCode->setSize(250);
					$qrCode->setMargin(1);
					$qrCode->setEncoding('UTF-8');

					echo '<div class="bs-qrcode">';
					echo '<h3>'. $account['account_name'] . '</h3>';
					echo '<img src="data:image/png;charset=utf-8;base64, '
					     . base64_encode($qrCode->writeString())
					     . '" />';
					echo '</div>';
				}
			}
		}
	}

	public function enqueueStyles(): void
	{
		wp_enqueue_style(
			BS_QRCODE_PLUGIN_TITLE,
			plugin_dir_url( __FILE__ ) . 'css/'. BS_QRCODE_PAYMENT_PLUGIN_SLUG .'-public.css',
			[],
			BS_QRCODE_PAYMENT_VERSION
		);
	}
}