<?php

use QCChoice\Values\QC_Choice_Values;

/**
 * Plugin admin pages functionality of the plugin.
 *
 * @link       http://www.quantcast.com
 * @since      1.0.0
 *
 * @package    QC_Choice
 * @subpackage QC_Choice/admin
 */

/**
 * QC Choice admin pages.
 *
 * @package    QC_Choice
 * @subpackage QC_Choice/admin
 * @author     Ryan Baron <rbaron@quantcast.com>
 */
class QC_Choice_Admin_Pages {

	/**
	 * The qc_choice options group name for the plugin settings.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $qc_choice_options    The qc_choice_options options group name for the plugin settings.
	 */
	private $qc_choice_options;

	/**
	 * QC Choice Values Class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $qc_choice_values    QC Choice Values Class
	 */
	private $qc_choice_values;

	/**
	 * Selected language.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $qc_choice_language    Selected language.
	 */
	private $qc_choice_language;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->qc_choice_values = new QC_Choice_Values();

		$this->qc_choice_language_default = "en";
		$this->qc_choice_language = esc_attr( get_option( 'qc_choice_language' ) );

		$this->qc_choice_language = isset( $this->qc_choice_language ) && ! empty( $this->qc_choice_language )
			? $this->qc_choice_language
			: $this->qc_choice_language_default;

		add_action( 'admin_init', array( $this, 'qc_choice_options_page_init' ) );
		add_action( 'admin_menu', array( $this, 'add_qc_choice_admin_pages' ) );
		add_action( 'update_option_qc_choice_vendors', array( $this, 'qc_choice_vendors_update' ), 10, 2 );
		add_action( 'add_option_qc_choice_vendors', array( $this, 'qc_choice_vendors_add' ), 10, 2 );

	}

	/**
	 * Retrieve the current vendor list version from: https://vendorlist.consensu.org/vendorlist.json.
	 *
	 * @since    1.2.0
	 */
	private function get_vendor_list_version() {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_URL, 'https://vendorlist.consensu.org/vendorlist.json' );
		$result = curl_exec( $ch );

		if ( curl_errno( $ch ) ) {
			curl_close( $ch );

			// curl error, return array with error data.
			return array(
				'success' => false,
				'msg'     => '<h3>' . __( 'Somehting went wrong.', 'qc-choice' ) .'</h3><p><strong>' . __( 'The pubvendors.json was NOT updated.' ) . '</strong></p><p>' . __( 'We were unable to retrieve the vendor list version number, please try saving again.', 'qc-choice' ) . '</p>',
				'error'   => curl_error( $ch ),
				'version' => 0
			);
		}
		else {
			// check the HTTP status code of the request
			$resultStatus = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			curl_close( $ch );
			if ( $resultStatus != 200 ) {
				// http error, return array with error data.
				return array(
					'success' => false,
					'msg'     => '<h3>' . __( 'Somehting went wrong.', 'qc-choice' ) .'</h3><p><strong>' . __( 'The pubvendors.json was NOT updated.' ) . '</strong></p><p>' . __( 'We were unable to retrieve the vendor list version number, please try saving again.', 'qc-choice' ) . '</p>',
					'error'   => __( 'HTTP status code:', 'qc-choice' ) . ' ' . $resultStatus,
					'version' => 0
				);
			}
			else{
				$obj = json_decode( $result );
				if ( ! property_exists( $obj, 'vendorListVersion' ) ) {
					// missing vendorListVersion error, return array with error data.
					return array(
						'success' => false,
						'msg'     => '<h3>' . __( 'Somehting went wrong.', 'qc-choice' ) .'</h3><p><strong>' . __( 'The pubvendors.json was NOT updated.' ) . '</strong></p><p>' . __( 'We were unable to retrieve the vendor list version number, please try saving again.', 'qc-choice' ) . '</p>',
						'error'   => __( 'Unknown error.', 'qc-choice' ),
						'version' => 0
					);
				}
				else {
					// success, return the data.
					return array(
						'success' => true,
						'msg'     => __( 'Vendor List Version successfully retrieved.', 'qc-choice' ),
						'error'   => __( 'None.', 'qc-choice' ),
						'version' => $obj->vendorListVersion
					);
				}
			}
		}
	}

	/**
	 * Retrieve the current vendor list version from: https://vendorlist.consensu.org/vendorlist.json.
	 *
	 * @since   1.2.0
	 * @param   string $old_value  contains the original qc_choice_vendors value.
	 * @param   string $new_value  contains the new qc_choice_vendors value.
	 */
	private function generate_pubvendors_file( $new_vendor_array = array(), $old_vendor_array = array() ) {
		$vendors = array();

		// Build the vendor array in the necessary structure for pubvendors.json
		foreach ( $new_vendor_array as $value ) {
			$vendors[] = array(
				"id" => $value
			);
		}

		// Get the vendor list version number.
		$global_vendor_list_version = $this->get_vendor_list_version();

		// Display an error and kill wp.
		if( ! $global_vendor_list_version['success'] ) {
			echo $global_vendor_list_version['msg'];
			echo $global_vendor_list_version['error'];
			wp_die();
		}

		$qc_choice_vendor_list_version = get_option( 'qc_choice_vendor_list_version' );
		if( $qc_choice_vendor_list_version ) {
			$qc_choice_vendor_list_version++; // increment the vendor list version.
		}
		else {
			$qc_choice_vendor_list_version = 1; // set the vendor list version to 1.
		}
		// Update the qc_choice_vendor_list_version option value.
		update_option( 'qc_choice_vendor_list_version', $qc_choice_vendor_list_version );

		return array(
			"publisherVendorsVersion" => PUBLISHER_VENDORS_VERSION,
			"version"                 => $qc_choice_vendor_list_version,
			"globalVendorListVersion" => $global_vendor_list_version['version'],
			"updatedAt"               => gmdate( "Y-m-d\TH:i:s\Z" ),
			"vendors"                 => $vendors
		);
	}

	/**
	 * Update the quantcast-choice/.well-known/pubvendors.json file when qc_choice_vendors is updated.
	 *
	 * @since   1.2.0
	 * @param   string $old_value  contains the original qc_choice_vendors value.
	 * @param   string $new_value  contains the new qc_choice_vendors value.
	 */
	public function qc_choice_vendors_update( $old_value, $new_value ) {

		// Get the file contents, and json_encode the array.
		$content = json_encode( $this->generate_pubvendors_file( $new_value, $old_value ) );

		// Put the content into the quantcast-choice/.well-known/pubvendors.json file
		$file = WP_PLUGIN_DIR."/".QC_CHOICE_VENDOR_FILE;
		$save = file_put_contents($file, $content);

		// If the file was not saved, display an error message.
		if( ! $save ) {
			add_settings_error( 'qc_choice_vendors', 'qc_choice_vendors', __( 'There was a problem saving the vendor file.', 'qc-choice' ), 'error' );
		}

	}

	/**
	 * Update the quantcast-choice/.well-known/pubvendors.json file when qc_choice_vendors is updated.
	 *
	 * @since   1.2.0
	 * @param   string $name   the option name.
	 * @param   string $value  contains the option value.
	 */
	public function qc_choice_vendors_add( $name, $value ) {

		// Get the file contents, and json_encode the array.
		$content = json_encode( $this->generate_pubvendors_file( $value ) );

		// Put the content into the quantcast-choice/.well-known/pubvendors.json file
		$file = WP_PLUGIN_DIR."/".QC_CHOICE_VENDOR_FILE;
		$save = file_put_contents($file, $content);

		// If the file was not saved, display an error message.
		if( ! $save ) {
			add_settings_error( 'qc_choice_vendors', 'qc_choice_vendors', __( 'There was a problem saving the vendor file.', 'qc-choice' ), 'error' );
		}

	}

	/**
	 * QC Choice admin options
	 *
	 * @since    1.0.0
	 */
	public function qc_choice_options() { ?>

		<div class="wrap wrap-qc-choice-options">
			<div class="admin-header">
				<div class="left"><h1><?php _e( 'Quantcast Choice Options', 'qc-choice' ); ?></h1></div>
				<div class="right">
					<div class='logo'>
						<div>
							<a target="_blank" href="<?php _e( 'https://www.quantcast.com', 'qc-choice' ); ?>" target="_blank">
								<img src="<?php echo plugins_url( 'quantcast-choice/admin/img/quantcast-logo.png' ); ?>" />
							</a>
						</div>
						<div>
							<a target="_blank" href="<?php _e( 'https://www.quantcast.com/gdpr', 'qc-choice' ); ?>" target="_blank"><?php _e( 'More About GDPR', 'qc-choice' ); ?></a>
						</div>
						<div>
							<a target="_blank" href="<?php _e( 'https://www.quantcast.com/gdpr/consent-management-solution/', 'qc-choice' ); ?>" target="_blank"><?php _e( 'More about Quantcast Choice', 'qc-choice' ); ?></a>
						</div>
						<div>
							<a target="_blank" href="<?php _e( 'https://www.quantcast.com/terms/quantcast-choice-terms-of-service/', 'qc-choice' ); ?>" target="_blank"><?php _e( 'Quantcast Choice Terms of Service', 'qc-choice' ); ?></a>
						</div>
					</div>
				</div>
				<div class="clear"></div>
			</div>

			<?php settings_errors(); ?>

			<?php
				if( isset( $_GET[ 'tab' ] ) ) {
					$active_tab = $_GET[ 'tab' ];
				} else {
					$active_tab = 'general_configuration';
				}
			?>

			<h2 class="nav-tab-wrapper">
				<?php if ( 'general_configuration' === $active_tab ) { ?>
					<a href="?page=qc-choice-options&tab=general_configuration" class="nav-tab active"><?php _e( 'General Settings', 'qc-choice' ); ?></a>
				<?php } else { ?>
					<a href="?page=qc-choice-options&tab=general_configuration" class="nav-tab"><?php _e( 'General Settings', 'qc-choice' ); ?></a>
				<?php }?>

				<?php if ( 'initial_screen' === $active_tab ) { ?>
					<a href="?page=qc-choice-options&tab=initial_screen" class="nav-tab active"><?php _e( 'Initial Screen', 'qc-choice' ); ?></a>
				<?php } else { ?>
					<a href="?page=qc-choice-options&tab=initial_screen" class="nav-tab"><?php _e( 'Initial Screen', 'qc-choice' ); ?></a>
				<?php }?>

				<?php if ( 'purpose_screen' === $active_tab ) { ?>
					<a href="?page=qc-choice-options&tab=purpose_screen" class="nav-tab active"><?php _e( 'Purpose Screen', 'qc-choice' ); ?></a>
				<?php } else { ?>
					<a href="?page=qc-choice-options&tab=purpose_screen" class="nav-tab"><?php _e( 'Purpose Screen', 'qc-choice' ); ?></a>
				<?php }?>

				<?php if ( 'vendor_screen' === $active_tab ) { ?>
					<a href="?page=qc-choice-options&tab=vendor_screen" class="nav-tab active"><?php _e( 'Vendor Screen', 'qc-choice' ); ?></a>
				<?php } else { ?>
					<a href="?page=qc-choice-options&tab=vendor_screen" class="nav-tab"><?php _e( 'Vendor Screen', 'qc-choice' ); ?></a>
				<?php }?>

				<?php if ( 'buttons' === $active_tab ) { ?>
					<a href="?page=qc-choice-options&tab=buttons" class="nav-tab active"><?php _e( 'Buttons', 'qc-choice' ); ?></a>
				<?php } else { ?>
					<a href="?page=qc-choice-options&tab=buttons" class="nav-tab"><?php _e( 'Buttons', 'qc-choice' ); ?></a>
				<?php }?>
			</h2>

			<form method="post" action="options.php">

				<?php if( $active_tab === 'general_configuration' ) { ?>

					<?php settings_fields( 'qc-choice-general-config' ); ?>
					<?php do_settings_sections( 'qc-choice-general-config' ); ?>

					<!-- Start - UI Configuration Section -->
					<table class="form-table options-form-table">

						<tr valign="top">
							<th scope="row" colspan="2">
								<h2><?php _e( 'General Settings', 'qc-choice' ); ?></h2>
								<hr>
							</th>
						</tr>

						<?php $qc_choice_language = $this->get_option_qc_choice( 'qc_choice_language', $this->qc_choice_language ); ?>
						<tr class="table-top-row" valign="top">
							<th scope="row">
								<?php _e( 'UI Default Language', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'Select your language for default localized values.', 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<select name="qc_choice_language">
									<option value="en" <?php selected( $qc_choice_language, 'en' ); ?>><?php _e( 'English', 'qc-choice' ); ?></option>
									<option value="fr" <?php selected( $qc_choice_language, 'fr' ); ?>><?php _e( 'French', 'qc-choice' ); ?></option>
									<option value="de" <?php selected( $qc_choice_language, 'de' ); ?>><?php _e( 'German', 'qc-choice' ); ?></option>
									<option value="it" <?php selected( $qc_choice_language, 'it' ); ?>><?php _e( 'Italian', 'qc-choice' ); ?></option>
									<option value="es" <?php selected( $qc_choice_language, 'es' ); ?>><?php _e( 'Spanish', 'qc-choice' ); ?></option>
								</select>
							</td>
						</tr>

						<?php $qc_choice_auto_localize = $this->get_option_qc_choice( 'qc_choice_auto_localize', $this->qc_choice_language ); ?>
						<tr class="table-top-row" valign="top">
							<th scope="row">
								<?php _e( 'Auto UI Language Localization', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'Attempt to detect the users site/browser language and localize content for supported languages, with a fallback to the "UI Default Language".', 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<select name="qc_choice_auto_localize">
									<option value="auto-localize-language" <?php selected( $qc_choice_auto_localize, "auto-localize-language" ); ?>><?php _e( 'Auto-Localize Language', 'qc-choice' ); ?></option>
									<option value="always-use-default-language" <?php selected( $qc_choice_auto_localize, "always-use-default-language" ); ?>><?php _e( 'Always Use the Default Language', 'qc-choice' ); ?></option>
								</select>
							</td>
						</tr>

						<?php $qc_choice_display_ui = $this->get_option_qc_choice( 'qc_choice_display_ui', $this->qc_choice_language ); ?>
						<tr class="table-top-row" valign="top">
							<th scope="row">
								<?php _e( 'Display UI', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'Determines when and to whom the ui will be displayed.', 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<select name="qc_choice_display_ui">
									<option value="never" <?php selected( $qc_choice_display_ui, 'never' ); ?>><?php _e( 'Never', 'qc-choice' ); ?></option>
									<option value="always" <?php selected( $qc_choice_display_ui, 'always' ); ?>><?php _e( 'Always', 'qc-choice' ); ?></option>
									<option value="inEU" <?php selected( $qc_choice_display_ui, 'inEU' ); ?>><?php _e( 'EU Only', 'qc-choice' ); ?></option>
								</select>
								<div class="desc">
									<ul>
										<li><?php _e( 'EU Only - UI will be displayed to users in the EU when consent is required.', 'qc-choice' ); ?></li>
										<li><?php _e( 'Always - UI will be displayed to everyone when consent is required.', 'qc-choice' ); ?></li>
										<li><?php _e( 'Never - UI will never be shown.', 'qc-choice' ); ?></li>
									</ul>
								</div>
							</td>
						</tr>

						<?php $qc_choice_display_layout = $this->get_option_qc_choice( 'qc_choice_display_layout', $this->qc_choice_language ); ?>
						<tr class="table-top-row" valign="top">
							<th scope="row">
								<?php _e( 'Display Layout', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'Determines the QC Choice display layout.', 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<select name="qc_choice_display_layout">
									<option value="popup" <?php selected( $qc_choice_display_layout, 'popup' ); ?>><?php _e( 'Popup Modal', 'qc-choice' ); ?></option>
									<option value="banner" <?php selected( $qc_choice_display_layout, 'banner' ); ?>><?php _e( 'Bottom Banner', 'qc-choice' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Minimum Days Between UI Displays', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( 'Determines how often the CMP will check for an updated vendor list and get updated consent using the ui if there is an updated list.', 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_min_days_between_ui_displays" type="number" min="1" max="365" value="<?php echo $this->get_option_qc_choice( 'qc_choice_min_days_between_ui_displays', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Non-Consent Display Frequency', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( 'The re-prompt frequency, in days, for users who have not given any positive consents; i.e. users who rejected all purposes and vendors.', 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_non_consent_display_frequency" type="number" min="1" max="365" value="<?php echo $this->get_option_qc_choice( 'qc_choice_non_consent_display_frequency', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<?php $qc_choice_google_personalisation = $this->get_option_qc_choice( 'qc_choice_google_personalisation', $this->qc_choice_language ); ?>
						<tr class="table-top-row" valign="top">
							<th scope="row">
								<?php _e( 'Google Personalisation', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'Allow Google and their technology partners to collect data and use cookies for ad personalisation and measurement.', 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<select name="qc_choice_google_personalisation">
									<option value="true" <?php selected( $qc_choice_google_personalisation, "true" ); ?>><?php _e( 'Enable Google Personalisation', 'qc-choice' ); ?></option>
									<option value="false" <?php selected( $qc_choice_google_personalisation, "false" ); ?>><?php _e( 'Disable Google Personalisation', 'qc-choice' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Post Consent Page URL', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( 'Determines if the cmp will redirect a user to different page when consent is not given for ALL global vendors and purposes. If any yes consents are given, the cmp will not redirect the user to the specified url.', 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_post_consent_page" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_post_consent_page', $this->qc_choice_language ); ?>">
								<div class="desc">
									<strong><?php _e( 'Example:', 'qc-choice' ); ?></strong> <?php _e( 'https://www.example.com/no-consnet', 'qc-choice' ); ?>
								</div>
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Publisher Name', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'The name of the company using the CMP, to be displayed in the UI.', 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<input name="qc_choice_publisher_name" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_publisher_name', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Publisher Logo', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'A link to the logo of the company using the CMP, to be displayed in the UI.', 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<input name="qc_choice_publisher_logo" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_publisher_logo', $this->qc_choice_language ); ?>">
								<div class="desc">
									<strong><?php _e( 'Example:', 'qc-choice' ); ?></strong> <?php _e( 'https://www.example.com/logo.png', 'qc-choice' ); ?>
								</div>
							</td>
						</tr>

					</table>
					<!-- END - UI Configuration Section -->

				<?php } elseif ($active_tab === 'initial_screen') { ?>

					<?php settings_fields( 'qc-choice-initial-screen' ); ?>
					<?php do_settings_sections( 'qc-choice-initial-screen' ); ?>

					<!-- START - Initial Screen Settings -->
					<table class="form-table options-form-table">

						<tr valign="top">
							<th scope="row" colspan="2">
								<h2><?php _e( 'Initial Screen', 'qc-choice' ); ?> - (<?php echo $this->qc_choice_language; ?>)</h2>
								<hr>
							</th>
						</tr>

						<tr class="table-top-row" valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Initial Screen Title Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The title text on the initial global consent screen to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_title_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_title_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Body Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The main body text on the initial global consent screen to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<?php $qc_choice_initial_screen_body_text_choices = $this->qc_choice_values->get_default_array_values( 'qc_choice_initial_screen_body_text', $this->qc_choice_language ); ?>
								<?php
								$qc_choice_initial_screen_body_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_body_text', $this->qc_choice_language );
								if( is_array( $qc_choice_initial_screen_body_text ) ) {
									$qc_choice_initial_screen_body_text = 1;
								}
								?>
								<select class="select-lg" name="qc_choice_initial_screen_body_text[<?php echo $this->qc_choice_language; ?>]">
									<?php if( is_array( $qc_choice_initial_screen_body_text_choices ) ) {
										foreach ($qc_choice_initial_screen_body_text_choices as $key => $value) { ?>
											<option value="<?php echo $key; ?>" <?php selected( $key, $qc_choice_initial_screen_body_text ); ?>><?php echo $value; ?></option>
										<?php }
									} ?>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Reject Button Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The reject consent button text on the initial global consent screen to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_reject_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_reject_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<?php $qc_choice_initial_screen_no_option = $this->get_option_qc_choice( 'qc_choice_initial_screen_no_option', $this->qc_choice_language ); ?>
						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'No Option', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "Determines if the the reject consent button will be displayed on the initial global consent page.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<select name="qc_choice_initial_screen_no_option">
									<option value="false" <?php selected( $qc_choice_initial_screen_no_option, 'false' ); ?>><?php _e( 'Do not display the Reject All Consent button on the initial screen.', 'qc-choice' ); ?></option>
									<option value="true" <?php selected( $qc_choice_initial_screen_no_option, 'true' ); ?>><?php _e( 'Display the Reject All Consent button on the initial screen.', 'qc-choice' ); ?></option>
								</select>
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Accept Button Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The accept consent button text on the initial global consent screen to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_accept_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_accept_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Purpose Link Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The purpose link text on the initial global consent screen to be displayed in the ui. When clicked, the ui updates to display the purpose consent screen.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_purpose_link_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_purpose_link_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Custom Link 1 Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "Add a custom link to the initial screen.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_custom_link_1_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_1_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Custom Link 1 URL ', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "Add a custom link to the initial screen.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_custom_link_1_url[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_1_url', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Custom Link 2 Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "Add a custom link to the initial screen.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_custom_link_2_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_2_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Custom Link 2 URL ', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "Add a custom link to the initial screen.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_initial_screen_custom_link_2_url[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_2_url', $this->qc_choice_language ); ?>">
							</td>
						</tr>

					</table>
					<!-- END - Initial Screen Settings -->

				<?php } elseif ($active_tab === 'purpose_screen') { ?>

					<?php settings_fields( 'qc-choice-purpose-screen' ); ?>
					<?php do_settings_sections( 'qc-choice-purpose-screen' ); ?>

					<!-- START - Purpose Screen Settings -->
					<table class="form-table options-form-table">

						<tr valign="top">
							<th scope="row" colspan="2">
								<h2><?php _e( 'Purpose Screen', 'qc-choice' ); ?> - (<?php echo $this->qc_choice_language; ?>)</h2>
								<hr>
							</th>
						</tr>

						<tr class="table-top-row" valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Header Title Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The title text in the purposes header on the purposes consent page to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_purpose_screen_header_title_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_purpose_screen_header_title_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Title Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The title text on the purposes consent page to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_purpose_screen_title_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_purpose_screen_title_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Body Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The main body text on the purposes consent page to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								 <textarea name="qc_choice_purpose_screen_body_text[<?php echo $this->qc_choice_language; ?>]"><?php echo $this->get_option_qc_choice( 'qc_choice_purpose_screen_body_text', $this->qc_choice_language ); ?></textarea>
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Enable All Button Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The accept all purpose consents button text on the purposes consent page to be displayed in the ui. When clicked, all of the purpose consent toggles are set to on.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_purpose_screen_enable_all_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_purpose_screen_enable_all_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Vendor Link Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The title text on the initial global consent screen to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_purpose_screen_vendor_link_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_purpose_screen_vendor_link_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Cancel Button Text', 'qc-choice' ); ?>
								<div class="desc"><?php _e( "The title text on the initial global consent screen to be displayed in the ui.", 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<input name="qc_choice_purpose_screen_cancel_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_purpose_screen_cancel_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Save and Exit Button Text', 'qc-choice' ); ?>
								<div class="desc"><?php _e( "The save consent and exit the ui button text on the purposes consent page to be displayed in the ui.", 'qc-choice' ); ?></div>
							</th>
							<td class="col-2">
								<input name="qc_choice_purpose_screen_save_and_exit_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_purpose_screen_save_and_exit_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<?php $qc_choice_purpose = get_option( 'qc_choice_purpose' ); ?>

						<?php
						$accessing_a_device = isset( $qc_choice_purpose["accessing_a_device"] ) && ! empty( $qc_choice_purpose["accessing_a_device"] )
							? esc_attr( $qc_choice_purpose["accessing_a_device"] )
							: $this->qc_choice_values->get_default_value('purpose_accessing_a_device');
						?>
						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Accessing a Device', 'qc-choice' ); ?>
							</th>
							<td class="col-2">
								<label>
									<input name="qc_choice_purpose[accessing_a_device]" type="checkbox" value="1" <?php checked( $accessing_a_device, '1' ); ?>>
									<?php _e( "Allow storing or accessing information on a user's device.", 'qc-choice' ); ?>
								</label>
							</td>
						</tr>

						<?php
						$advertising_personalisation = isset( $qc_choice_purpose["advertising_personalisation"] ) && ! empty( $qc_choice_purpose["advertising_personalisation"] )
							? esc_attr( $qc_choice_purpose["advertising_personalisation"] )
							: $this->qc_choice_values->get_default_value('purpose_advertising_personalisation');
						?>
						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Advertising Personalisation', 'qc-choice' ); ?>
							</th>
							<td class="col-2">
								<label>
									<input name="qc_choice_purpose[advertising_personalisation]" type="checkbox" value="2" <?php checked( $advertising_personalisation, '2' ); ?>>
									<?php _e( "Allow processing of a user's data to provide and inform personalised advertising (including delivery, measurement, and reporting) based on a user's preferences or interests known or inferred from data collected across multiple sites, apps, or devices; and/or accessing or storing information on devices for that purpose.", 'qc-choice' ); ?>
								</label>
							</td>
						</tr>

						<?php
						$analytics = isset( $qc_choice_purpose["analytics"] ) && ! empty( $qc_choice_purpose["analytics"] )
							? esc_attr( $qc_choice_purpose["analytics"] )
							: $this->qc_choice_values->get_default_value('purpose_analytics');
						?>
						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Analytics', 'qc-choice' ); ?>
							</th>
							<td class="col-2">
								<label>
									<input name="qc_choice_purpose[analytics]" type="checkbox" value="3" <?php checked( $analytics, '3' ); ?>>
									<?php _e( "Allow processing of a user's data to deliver content or advertisements and measure the delivery of such content or advertisements, extract insights and generate reports to understand service usage; and/or accessing or storing information on devices for that purpose.", 'qc-choice' ); ?>
								</label>
							</td>
						</tr>

						<?php
						$content_personalisation = isset( $qc_choice_purpose["content_personalisation"] ) && ! empty( $qc_choice_purpose["content_personalisation"] )
							? esc_attr( $qc_choice_purpose["content_personalisation"] )
							: $this->qc_choice_values->get_default_value('purpose_content_personalisation');
						?>
						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Content Personalisation', 'qc-choice' ); ?>
							</th>
							<td class="col-2">
								<label>
									<input name="qc_choice_purpose[content_personalisation]" type="checkbox" value="4" <?php checked( $content_personalisation, '4' ); ?>>
									<?php _e( "Allow processing of a user's data to provide and inform personalised content (including delivery, measurement, and reporting) based on a user's preferences or interests known or inferred from data collected across multiple sites, apps, or devices; and/or accessing or storing information on devices for that purpose.", 'qc-choice' ); ?>
								</label>
							</td>
						</tr>

						<?php
						$measurement = isset( $qc_choice_purpose["measurement"] ) && ! empty( $qc_choice_purpose["measurement"] )
							? esc_attr( $qc_choice_purpose["measurement"] )
							: $this->qc_choice_values->get_default_value('purpose_measurement');
						?>
						<tr valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Measurement', 'qc-choice' ); ?>
							</th>
							<td class="col-2">
								<label>
									<input name="qc_choice_purpose[measurement]" type="checkbox" value="5" <?php checked( $measurement, '5' ); ?>>
									<?php _e( "Allow processing of a user's data for measurement purposes.", 'qc-choice' ); ?>
								</label>
							</td>
						</tr>

					</table>
					<!-- END - Purpose Screen Settings -->

				<?php } elseif ($active_tab === 'vendor_screen') { ?>

					<?php settings_fields( 'qc-choice-vendor-screen' ); ?>
					<?php do_settings_sections( 'qc-choice-vendor-screen' ); ?>

					<!-- START - Vendor Screen Settings -->
					<table class="form-table options-form-table">

						<tr valign="top">
							<th scope="row" colspan="2">
								<h2><?php _e( 'Vendor Screen', 'qc-choice' ); ?> - (<?php echo $this->qc_choice_language; ?>)</h2>
								<hr>
							</th>
						</tr>

						<tr class="table-top-row" valign="top">
							<th class="col-1" scope="row">
								<?php _e( 'Title Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The title text on the vendors consent page to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td class="col-2">
								<input name="qc_choice_vendor_screen_title_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_vendor_screen_title_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Body Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The main body text on the vendors consent page to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td>
								 <textarea name="qc_choice_vendor_screen_body_text[<?php echo $this->qc_choice_language; ?>]"><?php echo $this->get_option_qc_choice( 'qc_choice_vendor_screen_body_text', $this->qc_choice_language ); ?></textarea>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Accept All Button Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The accept all vendor consents button text on the vendors consent page to be displayed in the ui. When clicked, all of the vendors consent toggles are set to on.", 'qc-choice' ); ?>
								</div>
							</th>
							<td>
								<input name="qc_choice_vendor_screen_accept_all_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_vendor_screen_accept_all_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Reject All Button Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The reject all vendor consents button text on the vendors consent page to be displayed in the ui. When clicked, all of the vendors consent toggles are set to off.", 'qc-choice' ); ?>
								</div>
							</th>
							<td>
								<input name="qc_choice_vendor_screen_reject_all_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_vendor_screen_reject_all_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Purposes Link Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The purpose link text on the vendors consent page to be displayed in the ui. When clicked, the ui updates to display the purpose consent page.", 'qc-choice' ); ?>
								</div>
							</th>
							<td>
								<input name="qc_choice_vendor_screen_purposes_link_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_vendor_screen_purposes_link_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Cancel Button Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The cancel link text on the vendors consent page to be displayed in the ui. When clicked, the ui updates to display the initial global consent page.", 'qc-choice' ); ?>
								</div>
							</th>
							<td>
								<input name="qc_choice_vendor_screen_cancel_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_vendor_screen_cancel_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Save and Exit Button Text', 'qc-choice' ); ?>
								<div class="desc">
									<?php _e( "The save consent and exit the ui button text on the vendors consent page to be displayed in the ui.", 'qc-choice' ); ?>
								</div>
							</th>
							<td>
								<input name="qc_choice_vendor_screen_save_and_exit_button_text[<?php echo $this->qc_choice_language; ?>]" type="text" value="<?php echo $this->get_option_qc_choice( 'qc_choice_vendor_screen_save_and_exit_button_text', $this->qc_choice_language ); ?>">
							</td>
						</tr>

						<tr valign="top">
							<th scope="row" colspan="2">
								<h3><?php _e( 'Vendors', 'qc-choice' ); ?></h3>
							</th>
						</tr>

						<tr valign="top">
							<th scope="row">
								<?php _e( 'Vendor List', 'qc-choice' ); ?>
								<div class="desc"><?php _e( 'Select the vendors to be included.', 'qc-choice' ); ?></div>

								<?php
								$qc_choice_vendors = get_option( 'qc_choice_vendors' );
								$qc_vendors = '';

								$cnt = 1;
								if( is_array( $qc_choice_vendors ) ) {
									$qc_vendors = "";
									$array_cnt = count($qc_choice_vendors);

									foreach ($qc_choice_vendors as $value) {
										$qc_vendors .= $value;

										if($array_cnt > $cnt ) {
											$qc_vendors .= ",";
										}

										$cnt++;
									}
								}
								 ?>
							</th>
							<td>
								<div id="vendor_list" data-checked-vendors="<?php echo $qc_vendors; ?>">
									<div id="vendor_list_loader" class="qc-loader">
										<div class="double-bounce1"></div>
										<div class="double-bounce2"></div>
									</div>
								</div>
							</td>
						</tr>

					</table>
					<!-- END - Vendor Screen Settigns -->

				<?php } elseif ($active_tab === 'about_gdpr') { ?>

					<!-- START - About GDPR Tab -->
					<table class="form-table options-form-table">
						<tr>
							<th><h3><?php _e( 'About GDPR', 'qc-choice' ); ?></h3></th>
						</tr>
					</table>
					<!-- END - About GDPR Tab -->

				<?php } elseif ($active_tab === 'buttons') { ?>

					<!-- START - About GDPR Tab -->
					<table class="form-table options-form-table">
						<tr>
							<th><h3><?php _e( 'Displaying the UI', 'qc-choice' ); ?></h3></th>
						</tr>
						<tr>
							<th><?php _e( 'Adding Change Consent to menu items.', 'qc-choice' ); ?></th>
							<td><?php _e( 'Add a custom link to any', 'qc-choice') ?> <a target="_blank" href="./nav-menus.php"><?php _e( 'menu', 'qc-choice') ?></a> <?php _e( 'and set the url value to "#displayConsentUI" to open the UI when clicked', 'qc-choice') ?></td>
						</tr>
						<tr>
							<th><?php _e( 'Adding Change Consnet to dom elements.', 'qc-choice' ); ?></th>
							<td>
								<div><?php _e( 'Add "onclick="window.__cmp(\'displayConsentUi\');" to any dom element to open the UI when clicked.', 'qc-choice') ?></div>
								<div><strong><?php _e( 'Example:', 'qc-choice' ); ?></strong> <code> &lt;a onclick="window.__cmp('displayConsentUi');"&gt;<?php _e( 'Change Consent', 'qc-choice' ); ?>&lt;/a&gt;</code></div>
							</td>
						</tr>
					</table>
					<!-- END - About GDPR Tab -->

				<?php } ?>

				<?php if ( $active_tab !== 'about_gdpr' && $active_tab !== 'buttons' ) { ?>

					<table class="form-table options-form-table">
						<tr>
							<td><?php submit_button(); ?></td>
						</tr>
					</table>

				<?php } ?>
			</form>
		</div>
		<?php

	}

	/**
	 * QC Choice admin pages
	 *
	 * @since    1.0.0
	 */
	public function add_qc_choice_admin_pages() {

		// Adding the top levelqc_choice page
		$qc_choice_admin_page = add_menu_page(
			'QC Choice',
			'QC Choice',
			'administrator',
			'qc-choice-options',
			array( $this, 'qc_choice_options' ),
			plugins_url( 'quantcast-choice/admin/img/quantcast-icon.png' )
		);

	}

	/**
	 * Register QC Choice admin option fields
	 *
	 * @since    1.0.0
	 */
	public function qc_choice_options_page_init() {

		// START - Register General Config Fields
		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_language', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);
		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_vendor_list_version', // Option name
			array(
				'type' => 'number'
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_auto_localize', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_display_ui', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_display_layout', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_min_days_between_ui_displays', // Option name
			array(
				'type' => 'number',
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_non_consent_display_frequency', // Option name
			array(
				'type' => 'number',
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_google_personalisation', // Option name
			array(
				'type' => 'string',
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_post_consent_page', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_url' )
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_publisher_name', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'qc-choice-general-config', // Option group
			'qc_choice_publisher_logo', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);
		// END - Register General Config Fields

		// START - Register Initial Screen Fields
		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_title_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_title_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_body_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_body_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_reject_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_reject_button_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_no_option', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_accept_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_accept_button_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_purpose_link_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_purpose_link_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_custom_link_1_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_custom_link_1_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_custom_link_1_url', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_custom_link_1_url' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_custom_link_2_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_custom_link_2_text' )
			)
		);

		register_setting(
			'qc-choice-initial-screen', // Option group
			'qc_choice_initial_screen_custom_link_2_url', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_initial_screen_custom_link_2_url' )
			)
		);
		// END - Register Initial Screen Fields

		// START - Register Purpose Screen Fields
		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose_screen_header_title_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_purpose_screen_header_title_text' )
			)
		);

		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose_screen_title_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_purpose_screen_title_text' )
			)
		);

		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose_screen_body_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_purpose_screen_body_text' )
			)
		);

		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose_screen_enable_all_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_purpose_screen_enable_all_button_text' )
			)
		);

		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose_screen_vendor_link_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_purpose_screen_vendor_link_text' )
			)
		);

		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose_screen_cancel_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_purpose_screen_cancel_button_text' )
			)
		);

		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose_screen_save_and_exit_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_purpose_screen_save_and_exit_button_text' )
			)
		);

		register_setting(
			'qc-choice-purpose-screen', // Option group
			'qc_choice_purpose' // Option name
		);
		// END - Register Purpose Screen Fields

		// START - Register Vendor Screen Fields
		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendor_screen_title_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_vendor_screen_title_text' )
			)
		);

		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendor_screen_body_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_vendor_screen_body_text' )
			)
		);

		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendor_screen_accept_all_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_vendor_screen_accept_all_button_text' )
			)
		);

		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendor_screen_reject_all_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_vendor_screen_reject_all_button_text' )
			)
		);

		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendor_screen_purposes_link_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_vendor_screen_purposes_link_text' )
			)
		);

		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendor_screen_cancel_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_vendor_screen_cancel_button_text' )
			)
		);

		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendor_screen_save_and_exit_button_text', // Option name
			array(
				'type' => 'string',
				'sanitize_callback' => array( $this, 'sanitize_qc_choice_vendor_screen_save_and_exit_button_text' )
			)
		);
		
		register_setting(
			'qc-choice-vendor-screen', // Option group
			'qc_choice_vendors' // Option name
		);
		// END - Register Vendor Screen Fields

	}

	/**
	 * Sanitize the qc_choice textarea fields.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input  $input contains submitted textarea input
	 */
	public function sanitize_textarea( $input ) {

		$input = sanitize_textarea_field( $input );
		return $input;

	}

	/**
	 * Sanitize the qc_choice text fields.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input  $input contains submitted textarea input
	 */
	public function sanitize_text( $input ) {

		$input = sanitize_text_field( $input );
		return $input;

	}

	/**
	 * Sanitize the qc_choice url fields.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input  $input contains submitted textarea input
	 */
	public function sanitize_url( $input ) {

		$input = esc_url( $input );
		return $input;

	}

	/**
	 * Sanitize a qc_choice language array text field.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input.
	 * @param   string $field_name the name of the input field to sanitize.
	 *
	 * @return  array    return the sanitized array of language values.
	 */
	public function sanitize_text_language_array( $input, $field_name ) {

		$saniztized_input = array();

		// Get the current field values.
		// We get them here so we don't lose the other language values on save
		$orig_values = get_option( $field_name, true );

		if( is_array( $input ) ) {

			// Loop through all of the field values, there should only be 1 for the current selected language
			foreach ($input as $key => $value) {

				$saniztized_input[$key] = sanitize_text_field( $value );

				// Make sure the original value is an array.
				if( is_array( $orig_values ) ) {

					$orig_values[$key] = $saniztized_input[$key]; // add the new, sanitized value to the orig_values array
				}

			}

			// If the orig_values is an array, return that
			if( is_array( $orig_values ) ) {

				$input = $orig_values;

			}
			else {

				$input = $saniztized_input;

			}
		}

		return $input;

	}

	/**
	 * Sanitize a qc_choice language array textarea field.
	 *
	 * @since   1.0.0
	 *
	 * @param   string   $input contains submitted field input.
	 * @param   string   $field_name the name of the input field to sanitize.
	 *
	 * @return  array    return the sanitized array of language values.
	 */
	public function sanitize_textarea_language_array( $input, $field_name ) {

		$saniztized_input = array();

		// Get the current field values.
		// We get them here so we don't lose the other language values on save.
		$orig_values = get_option( $field_name, true );

		if( is_array( $input ) ) {

			// Loop through all of the field values, there should only be 1 for the current selected language.
			foreach ($input as $key => $value) {

				$saniztized_input[$key] = wp_kses( $value, wp_kses_allowed_html( 'post' ) );

				// Make sure the original value is an array.
				if( is_array( $orig_values ) ) {

					$orig_values[$key] = $saniztized_input[$key]; // add the new, sanitized value to the orig_values array.
				}

			}

			// If the orig_values is an array, return that.
			if( is_array( $orig_values ) ) {

				$input = $orig_values;

			}
			else {

				$input = $saniztized_input;

			}
		}

		return $input;

	}
	
	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_title_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_title_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_title_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_body_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_body_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_body_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_reject_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_reject_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_reject_button_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_accept_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
		public function sanitize_qc_choice_initial_screen_accept_button_text( $input ) {

			return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_accept_button_text' );

		}

	/**
	 * Sanitize the qc_choice_initial_screen_purpose_link_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_purpose_link_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_purpose_link_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_custom_link_1_text text array.
	 *
	 * @since   1.0.2
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_custom_link_1_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_custom_link_1_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_custom_link_1_url text array.
	 *
	 * @since   1.0.2
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_custom_link_1_url( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_custom_link_1_url' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_custom_link_2_text text array.
	 *
	 * @since   1.0.2
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_custom_link_2_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_custom_link_2_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_initial_screen_custom_link_2_url text array.
	 *
	 * @since   1.0.2
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_initial_screen_custom_link_2_url( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_initial_screen_custom_link_2_url' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_purpose_screen_header_title_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_purpose_screen_header_title_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_purpose_screen_header_title_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_purpose_screen_title_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_purpose_screen_title_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_purpose_screen_title_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_purpose_screen_body_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_purpose_screen_body_text( $input ) {

		return $this->sanitize_textarea_language_array( $input, 'qc_choice_purpose_screen_body_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_purpose_screen_enable_all_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_purpose_screen_enable_all_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_purpose_screen_enable_all_button_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_purpose_screen_vendor_link_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_purpose_screen_vendor_link_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_purpose_screen_vendor_link_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_purpose_screen_cancel_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_purpose_screen_cancel_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_purpose_screen_cancel_button_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_purpose_screen_save_and_exit_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_purpose_screen_save_and_exit_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_purpose_screen_save_and_exit_button_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_vendor_screen_title_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_vendor_screen_title_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_vendor_screen_title_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_vendor_screen_body_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_vendor_screen_body_text( $input ) {

		return $this->sanitize_textarea_language_array( $input, 'qc_choice_vendor_screen_body_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_vendor_screen_accept_all_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_vendor_screen_accept_all_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_vendor_screen_accept_all_button_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_vendor_screen_reject_all_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_vendor_screen_reject_all_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_vendor_screen_reject_all_button_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_vendor_screen_purposes_link_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_vendor_screen_purposes_link_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_vendor_screen_purposes_link_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_vendor_screen_cancel_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_vendor_screen_cancel_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_vendor_screen_cancel_button_text' );

	}

	/**
	 * Sanitize the sanitize_qc_choice_vendor_screen_save_and_exit_button_text text array.
	 *
	 * @since   1.0.0
	 *
	 * @param   string $input contains submitted field input
	 */
	public function sanitize_qc_choice_vendor_screen_save_and_exit_button_text( $input ) {

		return $this->sanitize_text_language_array( $input, 'qc_choice_vendor_screen_save_and_exit_button_text' );

	}

	/**
	 * Get QC Choice option values, or return the default value if no value.
	 *
	 * @since   1.0.0
	 *
	 * @param   string   $field_name the name of the option field to get the value of
	 * @param   string   $language_code the name of the option field to get the value of
	 * @param   boolean  $esc_attr boolean value for using the esc_attr function on the returned value
	 *
	 * @return  the field value, or default value if no field value has been saved
	 */
	public function get_option_qc_choice( $field_name, $language_code, $esc_attr = true ) {

		// Get the value from the option table.
		$val = get_option( $field_name );
		
		// If the value is an array of values, assume language_code is the array.
		if( is_array( $val ) ) {
			$val = isset( $val[$this->qc_choice_language] ) ? $val[$this->qc_choice_language] : "";
		}

		// Escape attributes, if $esc_attr is true
		if( $esc_attr ) {
			$val = esc_attr( $val );
		}

		// Set the value to the default value if the $val variable is empty.
		$val = isset( $val ) && ! empty( $val )
			? $val
			: $this->qc_choice_values->get_default_value( $field_name, $this->qc_choice_language );

			return $val;
	}
}
