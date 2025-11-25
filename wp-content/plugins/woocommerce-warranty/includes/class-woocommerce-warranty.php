<?php
/**
 * Initiation class file.
 *
 * @package WooCommerce_Warranty
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WooCommerce\Warranty\Product_Editor;

/**
 * Plugin initiation class.
 */
class WooCommerce_Warranty {

	/**
	 * Plugin file.
	 *
	 * @var string
	 */
	public static $plugin_file = WOOCOMMERCE_WARRANTY_FILE;

	/**
	 * Plugin base folder path.
	 *
	 * @var string
	 */
	public static $base_path = WOOCOMMERCE_WARRANTY_ABSPATH;

	/**
	 * Plugin includes/ folder path.
	 *
	 * @var string
	 */
	public static $includes_path = WOOCOMMERCE_WARRANTY_INCLUDES_PATH;

	/**
	 * Warranty_Admin object.
	 *
	 * @var Warranty_Admin
	 */
	public static $admin;

	/**
	 * Database version.
	 *
	 * @var string
	 */
	public static $db_version = '20240707';

	/**
	 * Class constructor.
	 * Setup the WC_Warranty extension.
	 */
	public function __construct() {

		self::maybe_create_warranty_uploads_directories();

		$this->include_files();

		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );
		add_filter( 'woocommerce_translations_updates_for_woocommerce-warranty', '__return_true' );
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );

		new Product_Editor();
	}

	/**
	 * Initialization logic.
	 */
	public function init() {
		require_once self::$includes_path . '/class-warranty-privacy.php';
	}

	/**
	 * Declare High-Performance Order Storage (HPOS) compatibility
	 *
	 * @see https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book#declaring-extension-incompatibility
	 *
	 * @return void
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'woocommerce-warranty/woocommerce-warranty.php' );
		}
	}

	/**
	 * Load Localisation files.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-warranty', false, plugin_basename( WOOCOMMERCE_WARRANTY_ABSPATH ) . '/languages' );
	}

	/**
	 * Enqueue script and style in the frontend.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wc_warranty', plugins_url( 'assets/css/front.css', self::$plugin_file ), array(), WOOCOMMERCE_WARRANTY_VERSION );
	}

	/**
	 * Create a directory, and it's index.html file if they don't exist.
	 *
	 * @param string $directory Directory path.
	 *
	 * @return void
	 */
	private static function maybe_create_directory_and_index_file( $directory ) {

		$index_file = trailingslashit( $directory ) . 'index.html';

		// Create the directory, if it doesn't exist.
		if ( ! file_exists( $directory ) ) {
			wp_mkdir_p( $directory );
		}

		// Create the index.html file, if it doesn't exist.
		if ( ! file_exists( $index_file ) ) {
			$file_handle = @fopen( $index_file, 'wb' ); // phpcs:ignore --- Needed to create a file.
			if ( $file_handle ) {
				fwrite( $file_handle, '' ); // phpcs:ignore --- Needed to create a file.
				fclose( $file_handle ); // phpcs:ignore --- Needed to create a file.
			}
		}
	}

	/**
	 * Create the directory for warranty uploads if it does not yet exist.
	 */
	public static function maybe_create_warranty_uploads_directories() {

		// Create the main directory.
		self::maybe_create_directory_and_index_file( self::get_warranty_uploads_directory() );

		// Create the subdirectories.
		foreach ( self::get_valid_warranty_uploads_subdirectories() as $subdir ) {
			self::maybe_create_directory_and_index_file( self::get_warranty_uploads_directory( $subdir ) );
		}
	}

	/**
	 * Get an array of valid warranty uploads subdirectories.
	 *
	 * @return array
	 */
	public static function get_valid_warranty_uploads_subdirectories() {
		/**
		 * Filter to modify sub directory for WC Warranty upload folder.
		 *
		 * @param array List of subfolders.
		 *
		 * @since 2.1.1
		 */
		return apply_filters(
			'wc_warranty_uploads_subdirectories',
			array(
				'labels',
				'customer',
			)
		);
	}

	/**
	 * Checks that the passed subdirectory is a valid warranty_uploads directory. Otherwise, it
	 * returns the default subdirectory 'labels'
	 *
	 * @param string $subdir The subdirectory to validate.
	 *
	 * @return string
	 */
	private static function validate_warranty_uploads_subdirectory( $subdir ) {
		if ( empty( $subdir ) ) {
			return '';
		}

		if ( ! in_array( $subdir, self::get_valid_warranty_uploads_subdirectories(), true ) ) {
			$subdir = 'labels';
		}

		return trailingslashit( $subdir );
	}

	/**
	 * Get warranty uploads directory.
	 *
	 * @param string $subdir The subdirectory to retrieve.
	 *
	 * @return string
	 */
	public static function get_warranty_uploads_directory( $subdir = '' ) {
		$subdir     = self::validate_warranty_uploads_subdirectory( $subdir );
		$upload_dir = wp_upload_dir();
		$base_dir   = trailingslashit( $upload_dir['basedir'] );

		return $base_dir . 'warranty_uploads/' . $subdir;
	}

	/**
	 * Get plugin URL.
	 *
	 * @return string
	 */
	public static function get_plugin_url(): string {
		return plugins_url( '/', self::$plugin_file );
	}

	/**
	 * Get warranty uploads url.
	 *
	 * @param string $subdir The subdirectory to retrieve.
	 *
	 * @return string
	 */
	public static function get_warranty_uploads_url( $subdir = '' ) {
		$subdir     = self::validate_warranty_uploads_subdirectory( $subdir );
		$upload_dir = wp_upload_dir();
		$base_url   = trailingslashit( $upload_dir['baseurl'] );

		return $base_url . 'warranty_uploads/' . $subdir;
	}

	/**
	 * Get warranty form builder tips.
	 *
	 * @return array
	 */
	public static function get_tips(): array {
		/**
		 * Filter to modify the default form builder tips.
		 *
		 * @param array List of form builder tips.
		 *
		 * @since 1.7
		 */
		return apply_filters(
			'wc_warranty_form_builder_tips',
			array(
				'name'     => __( 'The name of the field that gets displayed on the Warranty Requests Table (Admin Panel)', 'woocommerce-warranty' ),
				'label'    => __( 'The label of the field displayed to the user when requesting for an RMA (Frontend)', 'woocommerce-warranty' ),
				'default'  => __( 'The initial value of the field', 'woocommerce-warranty' ),
				'required' => __( 'Check this to make this field required', 'woocommerce-warranty' ),
				'multiple' => __( 'Check this to allow users to select one or more options', 'woocommerce-warranty' ),
				'options'  => __( 'One option per line', 'woocommerce-warranty' ),
			)
		);
	}

	/**
	 * Inserts an attachment for a file uploaded to the
	 * warranty_uploads directory.
	 *
	 * @param string $filename the file path to the uploaded file.
	 * @param string $subdir The subdirectory to retrieve.
	 *
	 * @return int
	 */
	public static function insert_attachment_for_warranty_upload( $filename, $subdir = '' ) {

		$attachment = self::build_attachment_args( $filename, $subdir );

		return wp_insert_attachment( $attachment, $filename );
	}

	/**
	 * Updates an attachment guid for a file uploaded to the
	 * warranty_uploads directory.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $filename the file path to the uploaded file.
	 * @param string $subdir The subdirectory to retrieve.
	 *
	 * @return void
	 */
	public static function update_attachment_guid_for_warranty_upload( $attachment_id, $filename, $subdir = '' ) {
		global $wpdb;

		$guid = self::get_warranty_uploads_url( $subdir ) . basename( $filename );

		$wpdb->update( $wpdb->posts, array( 'guid' => $guid ), array( 'ID' => $attachment_id ) ); // phpcs:ignore --- Updating GUID.
	}

	/**
	 * Take the filename and subdirectory and build an args array
	 * to pass to functions like wp_insert_attachment and wp_update_post.
	 *
	 * @param string $filename Filename.
	 * @param string $subdir Sub directory of the filename.
	 *
	 * @return array
	 */
	public static function build_attachment_args( $filename, $subdir = '' ) {
		$file_basename = basename( $filename );

		// Get the file extension and mime type.
		$filetype = wp_check_filetype( $file_basename );

		return array(
			'guid'           => self::get_warranty_uploads_url( $subdir ) . $file_basename,
			'post_mime_type' => $filetype['type'],
			'post_title'     => basename( $file_basename, $filetype['ext'] ),
			'post_content'   => '',
			'post_status'    => 'private',
		);
	}

	/**
	 * Return an anchor tag linked to the uploaded warranty file.
	 *
	 * @param string $filename Filename.
	 * @param string $subdir The subdirectory to retrieve.
	 *
	 * @return string
	 */
	public static function get_uploaded_file_anchor_tag( $filename, $subdir = '' ) {
		return '<a href="' . esc_url( self::get_warranty_uploads_url( $subdir ) . basename( $filename ) ) . '" target="_blank">' . esc_html( basename( $filename ) ) . '</a>';
	}

	/**
	 * Returns the default warranty statuses.
	 *
	 * @return array
	 */
	public static function get_default_statuses() {
		/**
		 * Filter to modify the warranty request statuses.
		 *
		 * @param array List of warranty statuses.
		 *
		 * @since 1.7
		 */
		return apply_filters(
			'wc_warranty_default_statuses',
			array(
				__( 'New', 'woocommerce-warranty' ),
				__( 'Reviewing', 'woocommerce-warranty' ),
				__( 'Processing', 'woocommerce-warranty' ),
				__( 'Completed', 'woocommerce-warranty' ),
				__( 'Rejected', 'woocommerce-warranty' ),
			)
		);
	}

	/**
	 * Get shipping providers.
	 *
	 * @return array
	 */
	public static function get_providers() {
		/**
		 * Filter to modify shipment providers and their URL.
		 *
		 * @since 1.5
		 */
		return apply_filters(
			'wc_shipment_tracking_get_providers',
			array(
				'Australia'      => array(
					'Australia Post'   => 'http://auspost.com.au/track/track.html?id=%1$s',
					'Fastway Couriers' => 'http://www.fastway.com.au/courier-services/track-your-parcel?l=%1$s',
				),
				'Austria'        => array(
					'post.at' => 'http://www.post.at/sendungsverfolgung.php?pnum1=%1$s',
					'dhl.at'  => 'http://www.dhl.at/content/at/de/express/sendungsverfolgung.html?brand=DHL&AWB=%1$s',
					'DPD.at'  => 'https://tracking.dpd.de/parcelstatus?locale=de_AT&query=%1$s',
				),
				'Belgium'        => array(
					'bpost' => 'http://track.bpost.be/etr/light/showSearchPage.do?oss_language=EN',
				),
				'Brazil'         => array(
					'Correios' => 'http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=%1$s',
				),
				'Canada'         => array(
					'Canada Post' => 'http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=%1$s',
				),
				'Czech Republic' => array(
					'PPL.cz'      => 'http://www.ppl.cz/main2.aspx?cls=Package&idSearch=%1$s',
					'Česká pošta' => 'http://www.ceskaposta.cz/cz/nastroje/sledovani-zasilky.php?barcode=%1$s&locale=CZ&send.x=52&send.y=8&go=ok',
					'DHL.cz'      => 'http://www.dhl.cz/content/cz/cs/express/sledovani_zasilek.shtml?brand=DHL&AWB=%1$s',
					'DPD.cz'      => 'https://tracking.dpd.de/cgi-bin/delistrack?pknr=%1$s&typ=32&lang=cz',
				),
				'Finland'        => array(
					'Itella' => 'http://www.posti.fi/itemtracking/posti/search_by_shipment_id?lang=en&ShipmentId=%1$s',
				),
				'France'         => array(
					'Colissimo' => 'http://www.colissimo.fr/portail_colissimo/suivre.do?language=fr_FR&colispart=%1$s',
				),
				'Germany'        => array(
					'DHL Intraship (DE)' => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=%1$s&rfn=&extendedSearch=true',
					'Hermes'             => 'https://tracking.hermesworld.com/?TrackID=%1$s',
					'Deutsche Post DHL'  => 'http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&idc=%1$s',
					'UPS Germany'        => 'http://wwwapps.ups.com/WebTracking/processInputRequest?sort_by=status&tracknums_displayed=1&TypeOfInquiryNumber=T&loc=de_DE&InquiryNumber1=%1$s',
					'DPD.de'             => 'https://tracking.dpd.de/parcelstatus?query=%1$s&locale=en_DE',
				),
				'Ireland'        => array(
					'DPD'     => 'http://www2.dpd.ie/Services/QuickTrack/tabid/222/ConsignmentID/%1$s/Default.aspx',
					'An Post' => 'https://track.anpost.ie/TrackingResults.aspx?rtt=1&items=%1$s',
				),
				'Italy'          => array(
					'BRT (Bartolini)' => 'http://as777.brt.it/vas/sped_det_show.hsm?referer=sped_numspe_par.htm&Nspediz=%1$s',
					'DHL Express'     => 'http://www.dhl.it/it/express/ricerca.html?AWB=%1$s&brand=DHL',
				),
				'India'          => array(
					'DTDC' => 'http://www.dtdc.in/dtdcTrack/Tracking/consignInfo.asp?strCnno=%1$s',
				),
				'Netherlands'    => array(
					'PostNL' => 'https://mijnpakket.postnl.nl/Claim?Barcode=%1$s&Postalcode=%2$s&Foreign=False&ShowAnonymousLayover=False&CustomerServiceClaim=False',
					'DPD.NL' => 'http://track.dpdnl.nl/?parcelnumber=%1$s',
				),
				'New Zealand'    => array(
					'Courier Post' => 'http://trackandtrace.courierpost.co.nz/Search/%1$s',
					'NZ Post'      => 'http://www.nzpost.co.nz/tools/tracking?trackid=%1$s',
					'Fastways'     => 'http://www.fastway.co.nz/courier-services/track-your-parcel?l=%1$s',
					'PBT Couriers' => 'http://www.pbt.com/nick/results.cfm?ticketNo=%1$s',
				),
				'Poland'         => array(
					'InPost'        => 'https://inpost.pl/sledzenie-przesylek?number=%1$s',
					'DPD.PL'        => 'https://tracktrace.dpd.com.pl/parcelDetails?p1=%1$s',
					'Poczta Polska' => 'https://emonitoring.poczta-polska.pl/?numer=%1$s',
				),
				'Romania'        => array(
					'Fan Courier'   => 'https://www.fancourier.ro/awb-tracking/?xawb=%1$s',
					'DPD Romania'   => 'https://tracking.dpd.de/parcelstatus?query=%1$s&locale=ro_RO',
					'Urgent Cargus' => 'https://app.urgentcargus.ro/Private/Tracking.aspx?CodBara=%1$s',
				),
				'South African'  => array(
					'SAPO' => 'http://sms.postoffice.co.za/TrackingParcels/Parcel.aspx?id=%1$s',
				),
				'Sweden'         => array(
					'Posten AB'   => 'http://www.posten.se/sv/Kundservice/Sidor/Sok-brev-paket.aspx?search=%1$s',
					'DHL.se'      => 'http://www.dhl.se/content/se/sv/express/godssoekning.shtml?brand=DHL&AWB=%1$s',
					'Bring.se'    => 'http://tracking.bring.se/tracking.html?q=%1$s',
					'UPS.se'      => 'http://wwwapps.ups.com/WebTracking/track?track=yes&loc=sv_SE&trackNums=%1$s',
					'DB Schenker' => 'http://privpakportal.schenker.nu/TrackAndTrace/packagesearch.aspx?packageId=%1$s',
				),
				'United Kingdom' => array(
					'InterLink'                 => 'http://www.interlinkexpress.com/apps/tracking/?reference=%1$s&postcode=%2$s#results',
					'DHL'                       => 'http://www.dhl.com/content/g0/en/express/tracking.shtml?brand=DHL&AWB=%1$s',
					'DPD'                       => 'http://www.dpd.co.uk/tracking/trackingSearch.do?search.searchType=0&search.parcelNumber=%1$s',
					'ParcelForce'               => 'http://www.parcelforce.com/portal/pw/track?trackNumber=%1$s',
					'Royal Mail'                => 'https://www.royalmail.com/track-your-item/?trackNumber=%1$s',
					'TNT Express (consignment)' => 'http://www.tnt.com/webtracker/tracking.do?requestType=GEN&searchType=CON&respLang=en&
		respCountry=GENERIC&sourceID=1&sourceCountry=ww&cons=%1$s&navigation=1&g
		enericSiteIdent=',
					'TNT Express (reference)'   => 'http://www.tnt.com/webtracker/tracking.do?requestType=GEN&searchType=REF&respLang=en&r
		espCountry=GENERIC&sourceID=1&sourceCountry=ww&cons=%1$s&navigation=1&gen
		ericSiteIdent=',
					'UK Mail'                   => 'https://old.ukmail.com/ConsignmentStatus/ConsignmentSearchResults.aspx?SearchType=Reference&SearchString=%1$s',
				),
				'United States'  => array(
					'Fedex'         => 'http://www.fedex.com/Tracking?action=track&tracknumbers=%1$s',
					'FedEx Sameday' => 'https://www.fedexsameday.com/fdx_dotracking_ua.aspx?tracknum=%1$s',
					'OnTrac'        => 'http://www.ontrac.com/trackingdetail.asp?tracking=%1$s',
					'UPS'           => 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=%1$s',
					'USPS'          => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=%1$s',
					'DHL US'        => 'https://beta.dhl.com/us-en/home/tracking/tracking-ecommerce.html?tracking-id=%1$s',
				),
			)
		);
	}

	/**
	 * Include core files.
	 */
	public function include_files() {
		require_once self::$includes_path . '/class-wc-warranty-compatibility.php';
		require_once self::$includes_path . '/functions.php';
		require_once self::$includes_path . '/class-warranty-install.php';
		require_once self::$includes_path . '/class-warranty-shortcodes.php';
		require_once self::$includes_path . '/class-warranty-query.php';
		require_once self::$includes_path . '/class-warranty-order.php';
		require_once self::$includes_path . '/class-warranty-item.php';
		require_once self::$includes_path . '/class-rest-controller.php';
		require_once self::$includes_path . '/class-product-editor.php';

		if ( is_admin() ) {
			require_once self::$includes_path . '/class-warranty-coupons.php';
			require_once self::$includes_path . '/class-warranty-settings.php';
			self::$admin = include self::$includes_path . '/class-warranty-admin.php';
		} else {
			include_once self::$includes_path . '/class-warranty-frontend.php';
			include_once self::$includes_path . '/class-warranty-cart.php';
		}

		if ( defined( 'DOING_AJAX' ) ) {
			require_once self::$includes_path . '/class-warranty-ajax.php';
		}
	}

	/**
	 * Helper method to properly format a warranty string (e.g. 5 months)
	 *
	 * @param int    $duration Warranty duration.
	 * @param string $unit Duration unit.
	 *
	 * @return string Formatted warranty string
	 */
	public function get_warranty_string( $duration, $unit = 'days' ) {
		$unit = warranty_duration_i18n( $unit, $duration );

		return $duration . ' ' . $unit;
	}

	/**
	 * Clear warranty informations from all products.
	 */
	public static function clear_all_product_warranties() {
		global $wpdb;

		$products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'meta_query'     => array( // phpcs:ignore --- meta_query is needed to get the product that has warranty
					'key'     => '_warranty',
					'compare' => 'EXISTS',
				),
			)
		);

		foreach ( $products as $product ) {
			delete_post_meta( $product->ID, '_warranty' );
			delete_post_meta( $product->ID, '_warranty_type' );
			delete_post_meta( $product->ID, '_warranty_unit' );
			delete_post_meta( $product->ID, '_warranty_duration' );
			delete_post_meta( $product->ID, '_warranty_label' );
		}
	}

	/**
	 * Render warranty form.
	 *
	 * @param string $extra_key Extra key for the field ID in HTML.
	 */
	public static function render_warranty_form( $extra_key = '' ) {
		$defaults = array(
			'fields' => array(),
			'inputs' => '',
		);
		$form     = get_option( 'warranty_form', $defaults );
		$inputs   = array();

		if ( ! empty( $form['inputs'] ) ) {
			$inputs = json_decode( $form['inputs'] );
		}

		foreach ( $inputs as $input ) {
			$key  = esc_attr( $input->key );
			$type = esc_attr( $input->type );

			self::render_warranty_form_field( $type, $key, $form['fields'][ $key ], $extra_key );
		}
	}

	/**
	 * Render warranty form field.
	 *
	 * @param string $type Type of field.
	 * @param string $key Field key or ID.
	 * @param array  $field Field data.
	 * @param string $extra_key Extra key for the field ID in HTML.
	 */
	public static function render_warranty_form_field( $type, $key, $field, $extra_key = '' ) {
		echo '<div class="wfb-field-div wfb-field-div-' . esc_attr( $type ) . '" id="wfb-field-' . esc_attr( $key . $extra_key ) . '-div">';

		$required      = '';
		$required_note = '';
		if ( ! empty( $field['required'] ) && 'yes' === $field['required'] ) {
			$required      = 'data-required="true" required';
			$required_note = ' <span class="required">*</span>';
		}

		$name  = 'wfb-field[' . esc_attr( $key ) . ']';
		$value = isset( $field['default'] ) ? $field['default'] : '';

		if ( $extra_key ) {
			$name         = 'wfb-field[' . $key . '][' . $extra_key . ']';
			$request_data = warranty_request_data();
			if ( ! empty( $request_data['wfb-field'][ $key ][ $extra_key ] ) ) {
				$value = $request_data['wfb-field'][ $key ][ $extra_key ];
			}
		}

		switch ( $type ) {
			case 'paragraph':
				echo '<p class="wfb-field-para" id="wfb-field-' . esc_attr( $key . $extra_key ) . '">' . esc_html( $field['text'] ) . '</p>';
				break;

			case 'text':
				echo '<label
					for="wfb-field-' . esc_attr( $key . $extra_key ) . '"
					id="wfb-field-label-' . esc_attr( $key . $extra_key ) . '"
					>
					' . esc_html( $field['label'] ) . wp_kses_post( $required_note ) . '
					</label>';
				echo '<input
					type="text"
					name="' . esc_attr( $name ) . '"
					id="wfb-field-' . esc_attr( $key . $extra_key ) . '"
					value="' . esc_attr( $value ) . '"
					' . esc_attr( $required ) . '
					class="wfb-field"
					/>';
				break;

			case 'textarea':
				echo '<label
					for="wfb-field-' . esc_attr( $key ) . '"
					id="wfb-field-label-' . esc_attr( $key . $extra_key ) . '"
					>
					' . esc_html( $field['label'] ) . wp_kses_post( $required_note ) . '
					</label>';
				echo '<textarea
					name="' . esc_attr( $name ) . '"
					id="wfb-field-' . esc_attr( $key . $extra_key ) . '"
					rows="' . esc_attr( $field['rows'] ) . '"
					cols="' . esc_attr( $field['cols'] ) . '"
					' . esc_attr( $required ) . '
					class="wfb-field">' . esc_textarea( $value ) . '</textarea>';
				break;

			case 'select':
				$select_name = ( isset( $field['multiple'] ) && 'yes' === $field['multiple'] ) ? $name . '[]' : $name;
				$multiple    = ( isset( $field['multiple'] ) && 'yes' === $field['multiple'] ) ? 'multiple' : '';
				$options     = preg_split( "/(\r\n|\n|\r)/", $field['options'] );
				echo '<label
					for="wfb-field-' . esc_attr( $key ) . '"
					id="wfb-field-label-' . esc_attr( $key ) . '">
					' . esc_html( $field['label'] ) . wp_kses_post( $required_note ) . '
					</label>';
				echo '<select
					name="' . esc_attr( $select_name ) . '"
					id="wfb-field-' . esc_attr( $key ) . '"
					' . esc_attr( $multiple ) . '
					' . esc_attr( $required ) . '
					class="wfb-field"
					>';

				foreach ( $options as $option ) {
					echo '<option value="' . esc_attr( $option ) . '">' . esc_html( $option ) . '</option>';
				}

				echo '</select>';
				break;

			case 'file':
				echo '<label
					for="wfb-field-' . esc_attr( $key . $extra_key ) . '"
					id="wfb-field-label-' . esc_attr( $key . $extra_key ) . '"
					>
					' . esc_html( $field['label'] ) . wp_kses_post( $required_note ) . '
					</label>';
				echo '<input
					type="file"
					name="' . esc_attr( $name ) . '"
					id="wfb-field-' . esc_attr( $key . $extra_key ) . '"
					' . esc_attr( $required ) . '
					class="wfb-field"
					/>';
				break;
		}

		echo '</div>';
	}

	/**
	 * Render old warranty form.
	 */
	public static function render_old_warranty_form() {
		$reasons      = get_option( 'warranty_reason', '' );
		$reason_array = preg_split( "/(\r\n|\n|\r)/", $reasons );

		if ( ! empty( $reasons ) && ! empty( $reason_array ) ) {
			?>
			<p><?php esc_html_e( 'Select reason to request for warranty', 'woocommerce-warranty' ); ?><br /> <select name="warranty_reason">
					<?php
					foreach ( $reason_array as $reason ) {
						if ( empty( $reason ) ) {
							continue;
						}
						echo '<option value="' . esc_attr( trim( $reason ) ) . '">' . esc_attr( trim( $reason ) ) . '</option>';
					}
					?>
				</select>
			</p>
			<?php
		} else {
			echo '<input type="hidden" name="warranty_reason" value="" />';
		}

		$question = get_option( 'warranty_question', '' );
		$required = get_option( 'warranty_require_question', 'no' );

		if ( $question ) :
			?>
			<p>
				<?php echo esc_html( $question ); ?>
				<?php
				if ( 'yes' === $required ) {
					echo '<b>(*)</b>';
				}
				?>
				<br /> <textarea style="width:250px;" rows="4" name="warranty_answer" id="warranty_answer"></textarea>
			</p>
		<?php endif; ?>

		<?php
		$upload = get_option( 'warranty_upload', 'no' );

		if ( 'yes' === $upload ) {
			$title    = get_option( 'warranty_upload_title', 'Upload Attachment' );
			$required = get_option( 'warranty_require_upload', 'no' );
			?>
			<p>
				<?php echo esc_html( $title ); ?>
				<?php
				if ( 'yes' === $required ) {
					echo '<b>(*)</b>';
				}
				?>
				<br /> <input type="file" name="warranty_upload" />
			</p>
			<?php
		}
	}

	/**
	 * Process and save data from fields generated by the form builder
	 *
	 * @param int $request_id Warranty request ID.
	 *
	 * @return bool|WP_Error
	 */
	public static function process_warranty_form( $request_id ) {
		$defaults = array(
			'fields' => array(),
			'inputs' => '',
		);

		$form   = get_option( 'warranty_form', $defaults );
		$inputs = array();
		$errors = array();
		$data   = array();

		if ( ! empty( $form['inputs'] ) ) {
			$inputs = json_decode( $form['inputs'] );
		}

		// Enable restriction of MIME types - start.
		add_filter( 'upload_mimes', array( 'WooCommerce_Warranty', 'restrict_allowed_mime_types' ) );

		foreach ( $inputs as $input ) {
			$key   = $input->key;
			$type  = $input->type;
			$field = $form['fields'][ $key ];

			/**
			 * We are verifying the nonce in the calling method so do not need to do it again here.
			 * This method is called by both frontend and admin methods that process different forms with different
			 * nonces.
			 */
			$posted = isset( $_POST['wfb-field'] ) ? wc_clean( wp_unslash( $_POST['wfb-field'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( 'paragraph' === $type ) {
				continue;
			}

			if ( 'file' === $type ) {
				$required = isset( $field['required'] ) && 'yes' === $field['required'];
				// Skipping sanitization here because the field is sanitized below.
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
				$files            = isset( $_FILES['wfb-field'] ) ? wp_unslash( $_FILES['wfb-field'] ) : false;
				$is_uploaded_file = is_uploaded_file( $files['tmp_name'][ $key ] );

				if ( $required && ! $is_uploaded_file ) {
					// translators: field name.
					$errors[] = sprintf( esc_html__( 'The field "%s" is required', 'woocommerce-warranty' ), $field['label'] );
					continue;
				}

				if ( $is_uploaded_file ) {
					$filename      = sanitize_file_name( $files['name'][ $key ] );
					$validate_file = wp_check_filetype_and_ext( $files['tmp_name'][ $key ], $filename );

					// Check the file type, before we do anything further.
					if ( empty( $validate_file['ext'] ) || empty( $validate_file['type'] ) ) {
						$errors[] = __( 'The file you selected is not permitted. Please select another.', 'woocommerce-warranty' );
						continue;
					}

					// Randomize the filename for added security.
					$filename = self::get_randomized_filename( $filename );

					$upload_dir = self::get_warranty_uploads_directory( 'customer' );
					$filename   = wp_unique_filename( $upload_dir, $filename );
					$new_path   = $upload_dir . $filename;

					if ( move_uploaded_file( $files['tmp_name'][ $key ], $new_path ) ) {
						$data[ $key ] = $new_path;
					}
				}
			} else {
				$value    = isset( $posted[ $key ] ) ? wc_clean( $posted[ $key ] ) : false;
				$required = isset( $field['required'] ) && 'yes' === $field['required'];

				if ( $required && ! $value ) {
					// translators: field name.
					$errors[] = sprintf( esc_html__( 'The field "%s" is required', 'woocommerce-warranty' ), $field['label'] );
				} elseif ( $value ) {
					$data[ $key ] = $value;
				}
			}
		}

		// Enable restriction of MIME types - end.
		remove_filter( 'upload_mimes', array( 'WooCommerce_Warranty', 'restrict_allowed_mime_types' ) );

		if ( ! empty( $errors ) ) {
			$wp_error = new WP_Error();
			foreach ( $errors as $idx => $error ) {
				$wp_error->add( 'wfb-error-' . $idx, $error );
			}

			return $wp_error;
		} else {
			// Store data.
			if ( ! empty( $data ) ) {
				foreach ( $data as $key => $value ) {
					update_post_meta( $request_id, '_field_' . $key, $value );
				}
			}

			return true;
		}
	}

	/**
	 * Returns a randomized filename.
	 *
	 * @param string $filename Filename.
	 *
	 * @return string
	 */
	public static function get_randomized_filename( $filename ) {
		$name = substr( pathinfo( $filename, PATHINFO_FILENAME ), 0, 16 );
		$hash = '-' . substr( md5( $filename ), 0, 16 );
		$ext  = '.' . pathinfo( $filename, PATHINFO_EXTENSION );

		return $name . $hash . $ext;
	}

	/**
	 * Make sure we restrict to a specific subset of MIME types, before doing the validation check.
	 *
	 * @param array $mimes list of supported mimes, by default.
	 *
	 * @return array restricted list of mimes.
	 */
	public static function restrict_allowed_mime_types( $mimes ) {
		$mimes = array(
			'jpg|jpeg|jpe'    => 'image/jpeg',
			'png'             => 'image/png',
			'gif'             => 'image/gif',
			'pdf'             => 'application/pdf',
			'doc'             => 'application/msword',
			'docx'            => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'docm'            => 'application/vnd.ms-word.document.macroEnabled.12',
			'pot|pps|ppt'     => 'application/vnd.ms-powerpoint',
			'pptx'            => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'pptm'            => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'odt'             => 'application/vnd.oasis.opendocument.text',
			'ppsx'            => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'ppsm'            => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
			'xlsx'            => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xlsm'            => 'application/vnd.ms-excel.sheet.macroEnabled.12',
			'xlsb'            => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'key'             => 'application/vnd.apple.keynote',
		);

		return $mimes;
	}

	/**
	 * Get the datetime format to use throughout our views
	 *
	 * @return string
	 */
	public static function get_datetime_format() {
		return sprintf( '%s %s', wc_date_format(), wc_time_format() );
	}
}
