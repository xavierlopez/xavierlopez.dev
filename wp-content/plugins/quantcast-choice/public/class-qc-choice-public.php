<?php

use QCChoice\Values\QC_Choice_Values;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.quantcast.com
 * @since      1.0.0
 *
 * @package    QC_Choice
 * @subpackage QC_Choice/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    QC_Choice
 * @subpackage QC_Choice/public
 * @author     Ryan Baron <rbaron@quantcast.com>
 */
class QC_Choice_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The default language.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $qc_choice_default_language    Language code.
	 */
	private $qc_choice_default_language;
	/**
	 * The display language.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $qc_choice_display_language    Language code.
	 */
	private $qc_choice_display_language;
	
	/**
	 * The avaliable languages.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $qc_choice_avaliable_languages    An array of avaliable language code.
	 */
	private $qc_choice_avaliable_languages;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->qc_choice_values = new QC_Choice_Values();

		// Set the frontend display language.
		$this->set_display_language();

	}

	/**
	* Get the default browser display language.
	*
	* @since 1.0.0
	*/
	public function get_browser_language() {

		$lang = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		$lang = ! empty( $lang ) ? strtolower( substr( $lang, 0, 2) ) : '';

		return $lang;

	}
	/**
	* Set the language for frontend display.
	*
	* @since 1.0.0
	*/
	public function set_display_language() {

		$this->qc_choice_default_language = $this->get_option_qc_choice( 'qc_choice_language' );
		$qc_choice_auto_localize = $this->get_option_qc_choice( 'qc_choice_auto_localize' );

		if( 'auto-localize-language' == $qc_choice_auto_localize ) {

			$browser_lang = $this->get_browser_language();
			$this->qc_choice_avaliable_languages = $this->qc_choice_values->get_avaliable_languages();

			// Check if the WPML plugin has set a display language, if so use that to overwrite the frontend display language (only if it is a supported language).
			if( defined ('ICL_LANGUAGE_CODE') ) {
				if( in_array( strtolower( ICL_LANGUAGE_CODE ), $this->qc_choice_avaliable_languages ) ) {
					$this->qc_choice_display_language = ICL_LANGUAGE_CODE;
				}
				else {
					// If the WPML language is not a supported language, try the browser default language.
					if( ! empty( $browser_lang ) && in_array( $browser_lang, $this->qc_choice_avaliable_languages ) ) {
						$this->qc_choice_display_language = $browser_lang;
					}
					else {
						// If the WPML language, and browser language are not supported language, use the admin selected default language.
						$this->qc_choice_display_language = strtolower( $this->qc_choice_default_language ) ;
					}
				}
			}
			elseif( ! empty( $browser_lang ) && in_array( $browser_lang, $this->qc_choice_avaliable_languages ) ) {
				// If there is no language set by the WPML plugin, use the browser default language if it is supported.
				$this->qc_choice_display_language = $browser_lang;
			}
			else {
				// Use the admin selected default language.
				$this->qc_choice_display_language = strtolower( $this->qc_choice_default_language) ;
			}
		}
		else {
			$this->qc_choice_display_language = strtolower( $this->qc_choice_default_language) ;
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/style.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/script.min.js', array(), $this->version, true );
		wp_enqueue_script( 'qc-choice-init', plugin_dir_url( __FILE__ ) . 'js/script.async.min.js', array(), $this->version, true );

		// localize the qc-choice-init script and pass in the qc_choice_init values
		$qc_choice_init = $this->get_cmp_init_values();
		wp_localize_script( 'qc-choice-init', 'qc_choice_init', $qc_choice_init );

	}

	/**
	 * get_cmp_init_values
	 *
	 * @since    1.0.0
	 */
	public function get_cmp_init_values() {

		$cmp_init_vals = array();

		$qc_choice_purpose = get_option( 'qc_choice_purpose' );
		if( isset ( $qc_choice_purpose ) && ! empty ( $qc_choice_purpose ) && is_array( $qc_choice_purpose ) ) {
			$arr = array();
			foreach ( $qc_choice_purpose as $key => $value ) {
				array_push($arr, (int)$value);
			}
			$cmp_init_vals['Publisher Purpose IDs'] = array_values( $arr );
		}

		$vendor_list_type = get_option( 'qc_choice_vendor_list_type' );
		$vendors = get_option( 'qc_choice_vendors' );

		if( isset ( $vendors ) && ! empty ( $vendors ) && is_array( $vendors ) ) {
			$vendor_array = array();
			foreach ( $vendors as $key => $value ) {
				array_push($vendor_array, (int)$key);
			}
			$cmp_init_vals['Vendor White List or Black List']['isWhitelist'] = true;
			$cmp_init_vals['Vendor White List or Black List']['vendorIds'] = array_values( $vendor_array );
		}

		$qc_choice_initial_screen_no_option = get_option( 'qc_choice_initial_screen_no_option' );
		if( isset ( $qc_choice_initial_screen_no_option ) && ! empty ( $qc_choice_initial_screen_no_option ) ) {
			if( $qc_choice_initial_screen_no_option === 'true' ) {
				$cmp_init_vals['No Option'] = true;
			} elseif ( $qc_choice_initial_screen_no_option === 'false' ) {
				$cmp_init_vals['No Option'] = false;
			}
		}

		$qc_choice_post_consent_page = get_option( 'qc_choice_post_consent_page' );
		if( isset ( $qc_choice_post_consent_page ) && ! empty ( $qc_choice_post_consent_page ) ) {
			$cmp_init_vals['Post Consent Page'] = $qc_choice_post_consent_page;
		}

		$qc_choice_display_ui = get_option( 'qc_choice_display_ui' );
		if( isset ( $qc_choice_display_ui ) && ! empty ( $qc_choice_display_ui ) ) {
			$cmp_init_vals['Display UI'] = $qc_choice_display_ui;
		}
		else {
			$cmp_init_vals['Display UI'] = $this->qc_choice_values->get_default_array_values( 'qc_choice_display_ui', $this->qc_choice_display_language );
		}

		$qc_choice_min_days_between_ui_displays = get_option( 'qc_choice_min_days_between_ui_displays' );
		if( isset ( $qc_choice_min_days_between_ui_displays ) && ! empty ( $qc_choice_min_days_between_ui_displays ) ) {
			$cmp_init_vals['Min Days Between UI Displays'] = (int)$qc_choice_min_days_between_ui_displays;
		}

		$qc_choice_non_consent_display_frequency = get_option( 'qc_choice_non_consent_display_frequency' );
		if( isset ( $qc_choice_non_consent_display_frequency ) && ! empty ( $qc_choice_non_consent_display_frequency ) ) {
			$cmp_init_vals['Non-Consent Display Frequency'] = (int)$qc_choice_non_consent_display_frequency;
		}

		$qc_choice_google_personalisation = get_option( 'qc_choice_google_personalisation' );
		if( isset ( $qc_choice_google_personalisation ) && ! empty ( $qc_choice_google_personalisation ) ) {
			if( $qc_choice_google_personalisation === 'true' ) {
				$cmp_init_vals['Google Personalization'] = true;
			} elseif ( $qc_choice_google_personalisation === 'false' ) {
				$cmp_init_vals['Google Personalization'] = false;
			}
		}

		$qc_choice_publisher_name = get_option( 'qc_choice_publisher_name' );
		if( isset ( $qc_choice_publisher_name ) && ! empty ( $qc_choice_publisher_name ) ) {
			$cmp_init_vals['Publisher Name'] = $qc_choice_publisher_name;
		}

		$publisher_purpose_ids = get_option( 'publisher_purpose_ids' );
		if( isset ( $publisher_purpose_ids ) && ! empty ( $publisher_purpose_ids ) ) {
			$cmp_init_vals['Publisher Purpose IDs'] = $publisher_purpose_ids;
		}

		$qc_choice_publisher_logo = get_option( 'qc_choice_publisher_logo' );
		if( isset ( $qc_choice_publisher_logo ) && ! empty ( $qc_choice_publisher_logo ) ) {
			$cmp_init_vals['Publisher Logo'] = $qc_choice_publisher_logo;
		}

		$qc_choice_initial_screen_title_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_title_text' );
		if( isset ( $qc_choice_initial_screen_title_text ) && ! empty ( $qc_choice_initial_screen_title_text ) ) {
			$cmp_init_vals['Initial Screen Title Text'] = $qc_choice_initial_screen_title_text;
		}

		$qc_choice_initial_screen_body_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_body_text' );
		$qc_choice_initial_screen_body_text = isset( $qc_choice_initial_screen_body_text ) && ! empty( $qc_choice_initial_screen_body_text ) && is_numeric( $qc_choice_initial_screen_body_text )
			? $qc_choice_initial_screen_body_text
			: 1;
		$qc_choice_initial_screen_body_text_choices = $this->qc_choice_values->get_default_array_values( 'qc_choice_initial_screen_body_text', $this->qc_choice_display_language );
		$qc_choice_initial_screen_body_text = isset( $qc_choice_initial_screen_body_text_choices[$qc_choice_initial_screen_body_text]) && ! empty( $qc_choice_initial_screen_body_text_choices[$qc_choice_initial_screen_body_text] )
			? $qc_choice_initial_screen_body_text_choices[$qc_choice_initial_screen_body_text]
			: $qc_choice_initial_screen_body_text_choices[1];

		if( isset ( $qc_choice_initial_screen_body_text ) && ! empty ( $qc_choice_initial_screen_body_text ) ) {
			// Replace [Company Name] with the $qc_choice_publisher_name value
			if( isset( $qc_choice_publisher_name ) && ! empty ( $qc_choice_publisher_name ) ) {
				$qc_choice_initial_screen_body_text = str_replace("[Company Name]", $qc_choice_publisher_name, $qc_choice_initial_screen_body_text );
			}
			$cmp_init_vals['Initial Screen Body Text'] = $qc_choice_initial_screen_body_text;
		}

		$qc_choice_initial_screen_reject_button_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_reject_button_text' );
		if( isset ( $qc_choice_initial_screen_reject_button_text ) && ! empty ( $qc_choice_initial_screen_reject_button_text ) ) {
			$cmp_init_vals['Initial Screen Reject Button Text'] = $qc_choice_initial_screen_reject_button_text;
		}

		$qc_choice_initial_screen_accept_button_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_accept_button_text' );
		if( isset ( $qc_choice_initial_screen_accept_button_text ) && ! empty ( $qc_choice_initial_screen_accept_button_text ) ) {
			$cmp_init_vals['Initial Screen Accept Button Text'] = $qc_choice_initial_screen_accept_button_text;
		}

		$qc_choice_initial_screen_purpose_link_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_purpose_link_text' );
		if( isset ( $qc_choice_initial_screen_purpose_link_text ) && ! empty ( $qc_choice_initial_screen_purpose_link_text ) ) {
			$cmp_init_vals['Initial Screen Purpose Link Text'] = $qc_choice_initial_screen_purpose_link_text;
		}

		$qc_choice_purpose_screen_header_title_text = $this->get_option_qc_choice( 'qc_choice_purpose_screen_header_title_text' );
		if( isset ( $qc_choice_purpose_screen_header_title_text ) && ! empty ( $qc_choice_purpose_screen_header_title_text ) ) {
			$cmp_init_vals['Purpose Screen Header Title Text'] = $qc_choice_purpose_screen_header_title_text;
		}

		$qc_choice_purpose_screen_title_text = $this->get_option_qc_choice( 'qc_choice_purpose_screen_title_text' );
		if( isset ( $qc_choice_purpose_screen_title_text ) && ! empty ( $qc_choice_purpose_screen_title_text ) ) {
			$cmp_init_vals['Purpose Screen Title Text'] = $qc_choice_purpose_screen_title_text;
		}

		$qc_choice_purpose_screen_body_text = $this->get_option_qc_choice( 'qc_choice_purpose_screen_body_text' );
		if( isset ( $qc_choice_purpose_screen_body_text ) && ! empty ( $qc_choice_purpose_screen_body_text ) ) {
			// Replace [Company Name] with the $qc_choice_publisher_name value
			if( isset( $qc_choice_publisher_name ) && ! empty ( $qc_choice_publisher_name ) ) {
				$qc_choice_purpose_screen_body_text = str_replace("[Company Name]", $qc_choice_publisher_name, $qc_choice_purpose_screen_body_text );
			}
			$cmp_init_vals['Purpose Screen Body Text'] = $qc_choice_purpose_screen_body_text;
		}

		$qc_choice_purpose_screen_enable_all_button_text = $this->get_option_qc_choice( 'qc_choice_purpose_screen_enable_all_button_text' );
		if( isset ( $qc_choice_purpose_screen_enable_all_button_text ) && ! empty ( $qc_choice_purpose_screen_enable_all_button_text ) ) {
			$cmp_init_vals['Purpose Screen Enable All Button Text'] = $qc_choice_purpose_screen_enable_all_button_text;
		}

		$qc_choice_purpose_screen_vendor_link_text = $this->get_option_qc_choice( 'qc_choice_purpose_screen_vendor_link_text' );
		if( isset ( $qc_choice_purpose_screen_vendor_link_text ) && ! empty ( $qc_choice_purpose_screen_vendor_link_text ) ) {
			$cmp_init_vals['Purpose Screen Vendor Link Text'] = $qc_choice_purpose_screen_vendor_link_text;
		}

		$qc_choice_purpose_screen_cancel_button_text = $this->get_option_qc_choice( 'qc_choice_purpose_screen_cancel_button_text' );
		if( isset ( $qc_choice_purpose_screen_cancel_button_text ) && ! empty ( $qc_choice_purpose_screen_cancel_button_text ) ) {
			$cmp_init_vals['Purpose Screen Cancel Button Text'] = $qc_choice_purpose_screen_cancel_button_text;
		}

		$qc_choice_purpose_screen_save_and_exit_button_text = $this->get_option_qc_choice( 'qc_choice_purpose_screen_save_and_exit_button_text' );
		if( isset ( $qc_choice_purpose_screen_save_and_exit_button_text ) && ! empty ( $qc_choice_purpose_screen_save_and_exit_button_text ) ) {
			$cmp_init_vals['Purpose Screen Save and Exit Button Text'] = $qc_choice_purpose_screen_save_and_exit_button_text;
		}

		$qc_choice_vendor_screen_title_text = $this->get_option_qc_choice( 'qc_choice_vendor_screen_title_text' );
		if( isset ( $qc_choice_vendor_screen_title_text ) && ! empty ( $qc_choice_vendor_screen_title_text ) ) {
			$cmp_init_vals['Vendor Screen Title Text'] = $qc_choice_vendor_screen_title_text;
		}

		$qc_choice_vendor_screen_body_text = $this->get_option_qc_choice( 'qc_choice_vendor_screen_body_text' );
		if( isset ( $qc_choice_vendor_screen_body_text ) && ! empty ( $qc_choice_vendor_screen_body_text ) ) {
			// Replace [Company Name] with the $qc_choice_publisher_name value
			if( isset( $qc_choice_publisher_name ) && ! empty ( $qc_choice_publisher_name ) ) {
				$qc_choice_vendor_screen_body_text = str_replace("[Company Name]", $qc_choice_publisher_name, $qc_choice_vendor_screen_body_text );
			}
			$cmp_init_vals['Vendor Screen Body Text'] = $qc_choice_vendor_screen_body_text;
		}

		$qc_choice_vendor_screen_reject_all_button_text = $this->get_option_qc_choice( 'qc_choice_vendor_screen_reject_all_button_text' );
		if( isset ( $qc_choice_vendor_screen_reject_all_button_text ) && ! empty ( $qc_choice_vendor_screen_reject_all_button_text ) ) {
			$cmp_init_vals['Vendor Screen Reject All Button Text'] = $qc_choice_vendor_screen_reject_all_button_text;
		}

		$qc_choice_vendor_screen_accept_all_button_text = $this->get_option_qc_choice( 'qc_choice_vendor_screen_accept_all_button_text' );
		if( isset ( $qc_choice_vendor_screen_accept_all_button_text ) && ! empty ( $qc_choice_vendor_screen_accept_all_button_text ) ) {
			$cmp_init_vals['Vendor Screen Accept All Button Text'] = $qc_choice_vendor_screen_accept_all_button_text;
		}

		$qc_choice_vendor_screen_purposes_link_text = $this->get_option_qc_choice( 'qc_choice_vendor_screen_purposes_link_text' );
		if( isset ( $qc_choice_vendor_screen_purposes_link_text ) && ! empty ( $qc_choice_vendor_screen_purposes_link_text ) ) {
			$cmp_init_vals['Vendor Screen Purposes Link Text'] = $qc_choice_vendor_screen_purposes_link_text;
		}

		$qc_choice_vendor_screen_cancel_button_text = $this->get_option_qc_choice( 'qc_choice_vendor_screen_cancel_button_text' );
		if( isset ( $qc_choice_vendor_screen_cancel_button_text ) && ! empty ( $qc_choice_vendor_screen_cancel_button_text ) ) {
			$cmp_init_vals['Vendor Screen Cancel Button Text'] = $qc_choice_vendor_screen_cancel_button_text;
		}

		$qc_choice_vendor_screen_save_and_exit_button_text = $this->get_option_qc_choice( 'qc_choice_vendor_screen_save_and_exit_button_text' );
		if( isset ( $qc_choice_vendor_screen_save_and_exit_button_text ) && ! empty ( $qc_choice_vendor_screen_save_and_exit_button_text ) ) {
			$cmp_init_vals['Vendor Screen Save and Exit Button Text'] = $qc_choice_vendor_screen_save_and_exit_button_text;
		}

		if( isset ( $this->qc_choice_display_language ) && ! empty ( $this->qc_choice_display_language ) ) {
			$cmp_init_vals['Language'] = strtoupper( $this->qc_choice_display_language );
		}

		$qc_choice_initial_screen_body_text_option = $this->get_option_qc_choice( 'qc_choice_initial_screen_body_text_option' );
		if( isset ( $qc_choice_initial_screen_body_text_option ) && ! empty ( $qc_choice_initial_screen_body_text_option ) ) {
			$cmp_init_vals['Initial Screen Body Text Option'] = $qc_choice_initial_screen_body_text_option;
		}

		$qc_choice_display_layout = get_option( 'qc_choice_display_layout' );
		if( isset ( $qc_choice_display_layout ) && ! empty ( $qc_choice_display_layout ) ) {
			$cmp_init_vals['UI Layout'] = $qc_choice_display_layout;
		}

		$qc_choice_initial_screen_custom_link_1_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_1_text' );
		$qc_choice_initial_screen_custom_link_1_url = $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_1_url' );
		if( isset ( $qc_choice_initial_screen_custom_link_1_text ) && ! empty ( $qc_choice_initial_screen_custom_link_1_text ) &&  isset ( $qc_choice_initial_screen_custom_link_1_url ) && ! empty ( $qc_choice_initial_screen_custom_link_1_url ) ) {
			$cmp_init_vals['Custom Links Displayed on Initial Screen'][] = "[" . $qc_choice_initial_screen_custom_link_1_text . "](" . $qc_choice_initial_screen_custom_link_1_url . ")";
		}

		$qc_choice_initial_screen_custom_link_2_text = $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_2_text' );
		$qc_choice_initial_screen_custom_link_2_url = $this->get_option_qc_choice( 'qc_choice_initial_screen_custom_link_2_url' );
		if( isset ( $qc_choice_initial_screen_custom_link_2_text ) && ! empty ( $qc_choice_initial_screen_custom_link_2_text ) &&  isset ( $qc_choice_initial_screen_custom_link_2_url ) && ! empty ( $qc_choice_initial_screen_custom_link_2_url ) ) {
			$cmp_init_vals['Custom Links Displayed on Initial Screen'][] = "[" . $qc_choice_initial_screen_custom_link_2_text . "](" . $qc_choice_initial_screen_custom_link_2_url . ")";
		}

		$vendor_file = WP_PLUGIN_DIR."/".QC_CHOICE_VENDOR_FILE;
		if( file_exists ( $vendor_file ) ) {
			$vendor_file_url = plugins_url( QC_CHOICE_VENDOR_FILE, '' );
			$cmp_init_vals['Publisher Vendor List URL'] = "$vendor_file_url";
		}

		$cmp_init_vals = apply_filters( 'qcchoice_init_values', $cmp_init_vals );

		return json_encode( $cmp_init_vals );

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
	public function get_option_qc_choice( $field_name ) {

		// Get the value from the option table.
		$val = get_option( $field_name );

		// If the value is an array of values, assume language_code is the array.
		if( is_array( $val ) ) {
			$val = isset( $val[$this->qc_choice_display_language] ) ? $val[$this->qc_choice_display_language] : "";
		}

		// Set the value to the default value if the $val variable is empty.
		$val = isset( $val ) && ! empty( $val )
			? $val
			: $this->qc_choice_values->get_default_value( $field_name, $this->qc_choice_display_language );

			return $val;
	}
}
