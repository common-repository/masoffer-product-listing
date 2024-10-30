<?php
/**
 * Plugin Name: MasOffer Product Listing
 * Plugin URI: https://masoffer.com
 * Description: Plugin hỗ trợ hiển thị sản phẩm
 * Version: 1.2.8
 * Author: MasOffer
 * License: GPLv2 or later
 */
?>
<?php
if ( ! class_exists( 'MasOffer_ProductListing' ) ) {
	class MasOffer_ProductListing {
        const PLUGIN_VERSION = '1.2.8';
        const DB_VERSION = '3.0';
        const DATA_OPTION_VERSION = '2.0';

		const PREFIX_FILE = 'mo_prod_';

		const TABLE_URL = 'mo_product_listing_url';
		const TABLE_SHORTCODE = 'mo_product_listing_shortcode';

		const OFFER_SHOPEE = 'shopee';
		const SHOPEE_DOMAIN = 'shopee.vn';
        const SHOPEE_API_GET_ITEM = 'https://shopee.vn/api/v2/item/get';
        const SHOPEE_API_GET_SHOP = 'https://shopee.vn/api/v2/shop/get';
        const SHOPEE_URL_IMAGE = 'https://cf.shopee.vn/file/';
        const SHOPEE_IF_NONE_MATCH_PREFIX = '55b03';

		const OFFER_TIKI = 'tiki';
		const TIKI_DOMAIN = 'tiki.vn';
        const TIKI_API_GET_ITEM = 'https://tiki.vn/api/v2/products/';

		const OFFER_LAZADA = 'lazada';
		const LAZADA_DOMAIN = 'lazada.vn';

		const OFFER_SENDO = 'sendo';
		const SENDO_DOMAIN = 'sendo.vn';
		const SENDO_API_GET_ITEM = 'https://www.sendo.vn/m/wap_v2/full/san-pham/';

		const SHOW_SHOP_LOGO_DEFAULT = 1;
		const SHOW_SHOP_NAME_DEFAULT = 1;
        const SHOW_PRICE_DEFAULT = 1;
        const BUTTON_TITLE_DEFAULT = 'Buy now';

		private $tableNameShortcode;
		private $tableNameUrl;

		const TEMPLATE_TYPE = [
			0 => 'List',
			1 => 'Slider (show 1 item)',
			2 => 'Compare products (1 image)'
		];

		public function __construct() {
			global $wpdb;
			$this->tableNameShortcode = $wpdb->prefix . self::TABLE_SHORTCODE;
			$this->tableNameUrl = $wpdb->prefix . self::TABLE_URL;

			// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
			register_activation_hook( __FILE__, array( $this, 'activate' ) );

			register_deactivation_hook( __FILE__, array( $this, 'deactivated' ) );

			//check version db then update
			add_action( 'plugins_loaded', array( $this, 'mo_prod_plugin_update_db_check' ) );

			//Shortocde button init
			add_action( 'init', array( $this, 'mo_shortcode_button_init' ) );

			//Add shortcode
			add_shortcode( 'mo_product_listing', array( $this, 'shortcode_mo_producte_listing' ) );

			// Add extra submenu to the admin panel
			add_action( 'admin_menu', array( $this, 'create_menu_admin_panel' ) );

			// Handle POST request, admin_action_($action)
			add_action( 'admin_action_masoffer_product_action', array( $this, 'masoffer_prod_admin_action' ) );

			// Handle POST request, admin_action_($action) add_short_code
			add_action( 'admin_action_mo_prod_add_short_code_action', array(
				$this,
				'mo_prod_add_short_code_admin_action'
			) );

			//Add API get product list
			add_action( 'rest_api_init', function () {
				register_rest_route( 'mo_get_product/v1', '/getFromS', array(
					'methods'  => 'GET',
					'callback' => array( $this, 'ajaxAPIGetProductList' )
				) );
			} );

			//Add API add short code ajax
			add_action( 'rest_api_init', function () {
				register_rest_route( 'mo_get_product/v1', '/addShortcode', array(
					'methods'  => 'POST',
					'callback' => array( $this, 'ajaxAPIAddShortcode' )
				) );
			} );

			//Add API edit short code ajax
			add_action( 'rest_api_init', function () {
				register_rest_route( 'mo_get_product/v1', '/editShortcode', array(
					'methods'  => 'POST',
					'callback' => array( $this, 'ajaxAPIEditShortcode' )
				) );
			} );

			//Add API check now ajax
			add_action( 'rest_api_init', function () {
				register_rest_route( 'mo_get_product/v1', '/checkNow', array(
					'methods'  => 'POST',
					'callback' => array( $this, 'ajaxAPICheckNow' )
				) );
			} );

			//Add API update now ajax
			add_action( 'rest_api_init', function () {
				register_rest_route( 'mo_get_product/v1', '/updateNow', array(
					'methods'  => 'POST',
					'callback' => array( $this, 'ajaxAPIUpdateNow' )
				) );
			} );

			//cronjob
			add_action( 'mo_product_notice_cron', array( $this, 'notice_product_cron_function' ) );
			add_action( 'mo_product_update_cron', array( $this, 'update_product_cron_function' ) );
		}

		public function activate( $network_wide ) {
			// if the WordPress version is older than 2.6, deactivate this plugin
			// admin_action_ hook appearance 2.6
			if ( version_compare( get_bloginfo( 'version' ), '2.6', '<' ) ) {
				deactivate_plugins( basename( __FILE__ ) );
			} else {
				$data = array(
					'key'        => '',
					'domain'     => 'gotrackecom.info',
					'notice'     => '',
					'updated_at' => date( "d-m-Y H:i:s" ),
					'send_to_email' => get_bloginfo('admin_email'),
					'button_title' => self::BUTTON_TITLE_DEFAULT,
					'show_shop_logo' => self::SHOW_SHOP_LOGO_DEFAULT,
					'show_shop_name' => self::SHOW_SHOP_NAME_DEFAULT,
                    'show_price' => self::SHOW_PRICE_DEFAULT,
                );
				add_option( 'masoffer_product_listing', $data, '', 'no' );
				add_option( "mo_prod_db_version", self::DB_VERSION );

				if ( ! wp_next_scheduled( 'mo_product_update_cron' ) ) {
					wp_schedule_event( time(), 'twicedaily', 'mo_product_update_cron' );
				}

				if ( ! wp_next_scheduled( 'mo_product_notice_cron' ) ) {
					wp_schedule_event( time()+46800, 'twicedaily', 'mo_product_notice_cron' );
				}
			}
		}

		public function deactivated(){
			wp_clear_scheduled_hook( 'mo_product_update_cron' );
			wp_clear_scheduled_hook( 'mo_product_notice_cron' );
		}

		public function notice_product_cron_function() {
			$dataOption = get_option( 'masoffer_product_listing' );
			$notice = $dataOption['notice'];
			if($notice != 'true'){
				return false;
			}
			$query = "SELECT * FROM $this->tableNameUrl WHERE stock = 0 OR status_shop = 0";
			global $wpdb;
			$listUrl = $wpdb->get_results($query, ARRAY_A);
			if(empty($listUrl)){
				return true;
			}

			$blogName = get_bloginfo();
			$wpUrl = get_bloginfo('wpurl');
			$body = "<h1>Info error</h1>";
			foreach ($listUrl as $val){
				$shortcodeId = $val['shortcode_id'];
				$editLink = "$wpUrl/wp-admin/admin.php?page=mo-prod-page-edit-short-code&id=$shortcodeId";

				$body .= "<div>
						<p>Shortcode ID: $shortcodeId</p>
						<p>Link: {$val['url']}</p>
						<p>Stock: {$val['stock']}</p>
						<p>Status shop: {$val['status_shop']}</p>
						<p>Edit here: $editLink</p>
					</div><hr>";
			}

			$subject = "$blogName - Error Url alert";
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			wp_mail( $dataOption['send_to_email'], $subject, $body, $headers );
		}

		public function update_product_cron_function() {
			global $wpdb;
			$query = "SELECT * FROM $this->tableNameUrl";
			$listUrl = $wpdb->get_results($query, ARRAY_A);
			if(empty($listUrl)){
				return true;
			}

			foreach ($listUrl as $index => $val){
				if($val['offer'] === self::OFFER_SHOPEE){
					$dataUpdate = $this->getProductShopee($val);
					$wpdb->update( $this->tableNameUrl, $dataUpdate, [ 'id'=>$val['id'] ]);
				}
				if($val['offer'] === self::OFFER_TIKI) {
					$dataUpdate = $this->getProductTiki($val['item_id']);
					$wpdb->update( $this->tableNameUrl, $dataUpdate, [ 'id'=>$val['id'] ]);
				}
				if($val['offer'] === self::OFFER_LAZADA) {
					$dataUpdate = $this->parseLazada($val['url']);
					$wpdb->update( $this->tableNameUrl, $dataUpdate, [ 'id'=>$val['id'] ]);
				}
				if($val['offer'] === self::OFFER_SENDO) {
					$parseUrl = parse_url($val['url']);
//					$dataUpdate = $this->parseSendo($parseUrl);
					//ToDo: Turn off SenDo
					$dataUpdate = ['stock' => 0];
					$wpdb->update( $this->tableNameUrl, $dataUpdate, [ 'id'=>$val['id'] ]);
				}
				usleep(500);
			}
		}

		public function genIfNonMatch($shopId, $itemId) {
			$ifNonMatch = md5("itemid={$itemId}&shopid={$shopId}");
			$ifNonMatch = md5(self::SHOPEE_IF_NONE_MATCH_PREFIX . $ifNonMatch . self::SHOPEE_IF_NONE_MATCH_PREFIX);
			$ifNonMatch = self::SHOPEE_IF_NONE_MATCH_PREFIX . '-' . $ifNonMatch;
			return $ifNonMatch;
		}

		public function getProductShopee($val){
			$apiProductShopee  = self::SHOPEE_API_GET_ITEM."?itemid={$val['item_id']}&shopid={$val['shop_id']}";
			$ifNonMatch = $this->genIfNonMatch($val['shop_id'], $val['item_id']);
			$data = wp_remote_get( $apiProductShopee,
				array(
				       'headers' => array( 'if-none-match-' => $ifNonMatch )
				) );
			if ( !is_array( $data ) && is_wp_error( $data ) ) {
				return false;
			}

			$data = json_decode($data['body'],true);
			if(empty($data) || $data['item']['stock'] <= 0 || !empty($data['error_msg']) || !empty($data['error'])){
				$data['item']['stock'] = 0;
			}

			$statusShop = 1;
			if($data['item']['stock'] > 0){
				$apiShopShopee  = self::SHOPEE_API_GET_SHOP."?is_brief=1&shopid={$val['shop_id']}";
				$dataShop = wp_remote_get( $apiShopShopee );
				if ( !is_array( $dataShop ) && is_wp_error( $dataShop ) ) {
					return false;
				}
				$dataShop = json_decode($dataShop['body'],true);
				if(empty($dataShop['data']) || $dataShop['data']['vacation'] == true){
					$statusShop = 0;
				}
			}
			return [
				'status_shop' => $statusShop,
				'item_name' => $data['item']['name'],
				'image' => self::SHOPEE_URL_IMAGE.$data['item']['image'],
				'price' => (float)$data['item']['price_before_discount']/100000,
				'sale_price' => (float)$data['item']['price']/100000,
				'stock' => $data['item']['stock'],
			];
		}

		public function getProductTiki($itemId){
			$apiTiki  = self::TIKI_API_GET_ITEM.$itemId;
			$args = array(
				'headers'     => array(
					'content-type' => 'application/json; charset=utf-8',
				),
				'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:54.0) Gecko/20100101 Firefox/54.0',
			);
			$data = (wp_remote_retrieve_body(wp_remote_get($apiTiki, $args) ));

			if(empty($data)) {
				return false;
			}
			$data = json_decode($data,true);

            if(!isset($data['inventory_status']) || $data['inventory_status'] !== 'available' || $data['stock_item']['qty'] <= 0){
				return false;
			}

			$shopId = @$data['current_seller']['store_id'];
			$statusShop = 1;
			return [
				'item_id' => $itemId,
				'shop_id' => $shopId,
				'status_shop' => $statusShop,
				'item_name' => $data['name'],
				'image' => $data['thumbnail_url'],
				'price' => $data['list_price'],
				'sale_price' => $data['price'],
				'stock' => $data['stock_item']['qty'],
				'offer' => self::OFFER_TIKI
			];
		}

		//create database
		public function mo_db_install() {
			global $wpdb;
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $this->tableNameUrl (
                    id int NOT NULL AUTO_INCREMENT,
					shortcode_id int NOT NULL,
				    url text NOT NULL,
				    shop_id varchar(255),
				    item_id varchar(255),
				    status_shop int,
				    item_name  text,
				    image text,
				    price float,
				    sale_price float,
				    stock int,
				    offer varchar(255),
				    rating float,
				    PRIMARY KEY (id)
                ) $charset_collate;";

			$sql .= " CREATE TABLE $this->tableNameShortcode (
                    id int NOT NULL AUTO_INCREMENT,
				    name varchar(255),
				    aff_sub1 varchar(255),
				    aff_sub2 varchar(255),
				    aff_sub3 varchar(255),
				    aff_sub4 varchar(255),
				    show_shop_logo int,
				    show_shop_name int,
				    show_price int,
				    button_title varchar(255),
				    type int,
				    status int,
				    PRIMARY KEY (id)
                ) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

		public function mo_check_exist_db(){
			global $wpdb;
			$this->tableNameShortcode = $wpdb->prefix . self::TABLE_SHORTCODE;
			$this->tableNameUrl = $wpdb->prefix . self::TABLE_URL;
			$query = "SELECT * from $this->tableNameShortcode, $this->tableNameUrl limit 1";
			$data = $wpdb->get_results($query, ARRAY_A);
			if(empty($data)){
				return false;
			}
			return true;
		}
		//check version database
		public function mo_prod_plugin_update_db_check() {

			if ( get_option( 'mo_prod_db_version' ) != self::DB_VERSION || !$this->mo_check_exist_db()) {
				update_option( 'mo_prod_db_version', self::DB_VERSION );
                $this->mo_db_install();
			}

			if ( get_option( 'mo_prod_data_option_version' ) != self::DATA_OPTION_VERSION ) {
				update_option( 'mo_prod_data_option_version', self::DATA_OPTION_VERSION );

				$dataOptionOld = get_option( 'masoffer_product_listing');
				$dataOptionOld['button_title'] = self::BUTTON_TITLE_DEFAULT;
				$dataOptionOld['show_shop_logo'] = self::SHOW_SHOP_LOGO_DEFAULT;
				$dataOptionOld['show_shop_name'] = self::SHOW_SHOP_NAME_DEFAULT;
                $dataOptionOld['show_price'] = self::SHOW_PRICE_DEFAULT;
                update_option( 'masoffer_product_listing', $dataOptionOld );
			}
		}

		//Shortcode button
		public function mo_shortcode_button_init() {
			//Abort early if the user will never see TinyMCE
			if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) && get_user_option( 'rich_editing' ) == 'true' ) {
				return;
			}

			//Add a callback to regiser our tinymce plugin
			add_filter( "mce_external_plugins", array( $this, "mo_register_tinymce_plugin" ) );

			// Add a callback to add our button to the TinyMCE toolbar
			add_filter( 'mce_buttons', array( $this, 'mo_add_tinymce_button' ) );
		}

		//Add shortcode
		public function shortcode_mo_producte_listing( $atts, $content = null ) {
			extract( shortcode_atts( array(
				'id' => 1
			), $atts ) );

			$shortcodeId = $atts['id'];

			ob_start();
			include 'views/products.php';
			$cssSrc           = plugins_url( 'css/view.css', __FILE__ );
			$cssOwlCarousel   = plugins_url( 'css/owlcarousel/owl.carousel.min.css', __FILE__ );
			$cssOwlCarouselTheme   = plugins_url( 'css/owlcarousel/owl.theme.default.min.css', __FILE__ );
			wp_enqueue_style( self::PREFIX_FILE.'view', $cssSrc, [], self::PLUGIN_VERSION, false );
			wp_enqueue_style( self::PREFIX_FILE.'owlcarousel', $cssOwlCarousel, [], self::PLUGIN_VERSION, false );
			wp_enqueue_style( self::PREFIX_FILE.'owlcarousel-theme', $cssOwlCarouselTheme, [], self::PLUGIN_VERSION, false );

			$scriptProducts = plugins_url( 'js/products.js', __FILE__ );
			$scriptOwlCarousel = plugins_url( 'js/owlcarousel/owl.carousel.min.js', __FILE__ );
			if (!wp_script_is('jquery', 'enqueued')) {
				wp_enqueue_script('jquery-3.4.1', 'https://code.jquery.com/jquery-3.4.1.min.js', array(), self::PLUGIN_VERSION, true);
			}
			wp_enqueue_script(self::PREFIX_FILE.'owlcarousel', $scriptOwlCarousel, array(), self::PLUGIN_VERSION, true);
			wp_enqueue_script( self::PREFIX_FILE.'script-product', $scriptProducts, array(), self::PLUGIN_VERSION, true );
			$content = ob_get_clean();

			return $content;
		}

		//This callback registers our plug-in
		public function mo_register_tinymce_plugin( $plugin_array ) {
			$plugin_array['mo_product_listing_button'] = plugins_url( 'js/tinymce_shortcode.js?v='.self::PLUGIN_VERSION, __FILE__ );

			return $plugin_array;
		}

		//This callback adds our button to the toolbar
		public function mo_add_tinymce_button( $buttons ) {
			//Add the button ID to the $button array
			$buttons[] = "mo_product_listing_button";

			return $buttons;
		}

		public function create_menu_admin_panel() {
			add_menu_page( 'MasOffer Product Listing', 'MasOffer Product',
				'edit_others_posts', 'mo-prod-page-short-code', array( $this, 'mo_prod_view_short_code' ), plugin_dir_url(__FILE__)."images/fav_mo20.png");

			add_submenu_page( 'mo-prod-page-short-code', 'Shortcode', 'Shortcode',
				'edit_others_posts', 'mo-prod-page-short-code', array( $this, 'mo_prod_view_short_code' ) );

			add_submenu_page( 'mo-prod-page-short-code', 'Add Shortcode', 'Add Shortcode',
				'edit_others_posts', 'mo-prod-page-add-short-code', array( $this, 'mo_prod_view_add_short_code' ) );

			add_submenu_page( null, 'Edit Shortcode', 'Edit Shortcode',
				'edit_others_posts', 'mo-prod-page-edit-short-code', array( $this, 'mo_prod_view_edit_short_code' ) );

			add_submenu_page( 'mo-prod-page-short-code', 'Settings', 'Settings',
				'edit_others_posts', 'mo-prod-page-settings', array( $this, 'mo_prod_view_settings' ) );
		}

		public function mo_prod_view_short_code() {
			global $wpdb;
			$shortcodeId = @sanitize_text_field($_GET['id']);
			$deleteStatus = @sanitize_text_field($_GET['delete']);
			if(!empty($shortcodeId) && $deleteStatus == 'true'){
				$queryDeleteShortcode = "DELETE FROM $this->tableNameShortcode WHERE id = $shortcodeId";
				$wpdb->get_results($queryDeleteShortcode, ARRAY_A);

				$queryDeleteUrl = "DELETE FROM $this->tableNameUrl WHERE shortcode_id = $shortcodeId";
				$wpdb->get_results($queryDeleteUrl, ARRAY_A);
			}

			$shortcodeTable = new MasOffer_Shortcode_List_Table();
			$shortcodeTable->prepare_items();

			include 'views/short_code.php';
			$scriptProduct = plugins_url( 'js/short_code.js', __FILE__ );
			wp_enqueue_script( self::PREFIX_FILE.'script-short-code', $scriptProduct, array(), self::PLUGIN_VERSION, true );
		}

		public function mo_prod_view_add_short_code() {
			$template = self::TEMPLATE_TYPE;
			$dataOption = get_option( 'masoffer_product_listing' );

			include 'views/add_short_code.php';
			$cssSrc           = plugins_url( 'css/admin.css', __FILE__ );
			wp_enqueue_style( self::PREFIX_FILE.'view-css', $cssSrc, [], self::PLUGIN_VERSION, false );

			$scriptProduct = plugins_url( 'js/add_short_code.js', __FILE__ );
			wp_enqueue_script( self::PREFIX_FILE.'script-add-short-code', $scriptProduct, array(), self::PLUGIN_VERSION, true );
		}

		public function mo_prod_view_edit_short_code() {
			global $wpdb;
			$shortcodeId = @sanitize_text_field($_GET['id']);
			if(empty($shortcodeId)){
				return 'Không tồn tại shortcode';
			}

			$queryGetShortcode = "SELECT * FROM $this->tableNameShortcode WHERE id = $shortcodeId";
			$shortcodeInfo = $wpdb->get_results($queryGetShortcode, ARRAY_A);

			$queryGetUrl = "SELECT * FROM $this->tableNameUrl WHERE shortcode_id = $shortcodeId";
			$dataUrls = $wpdb->get_results($queryGetUrl, ARRAY_A);
			$urls = [];
			$urlErrors = [];
			foreach ($dataUrls as $val){
				$urls[] = $val['url'];
				if($val['stock'] == 0 || $val['status_shop'] != 1){
				    $urlErrors[] = $val['url'];
                }
			}
			$urls = implode("\n", $urls);

			$template = self::TEMPLATE_TYPE;
			$dataOption = get_option( 'masoffer_product_listing' );

			include 'views/edit_short_code.php';
			$cssSrc           = plugins_url( 'css/admin.css', __FILE__ );
			wp_enqueue_style( self::PREFIX_FILE.'view-css', $cssSrc, [], self::PLUGIN_VERSION, false );

			$scriptProduct = plugins_url( 'js/edit_short_code.js', __FILE__ );
			wp_enqueue_script( self::PREFIX_FILE.'script-edit-short-code', $scriptProduct, array(), self::PLUGIN_VERSION, true );
		}

		public function mo_prod_view_settings() {
			$data = get_option( 'masoffer_product_listing' );

			$domains = [
				'gotrackecom.info',
				'gotrackecom.asia',
				'gotrackecom.biz',
				'gotrackecom.xyz',
				'rutgon.me',
			];
			include 'views/admin.php';
		}

		public function masoffer_prod_admin_action() {
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'update-info-mo_prod_' ) ) {
				wp_die( __( 'You do not have sufficient permission to save this form.' ) );
			}
			if ( isset( $_POST['key'] ) ) {
				$data        = get_option( 'masoffer_product_listing' );
				$notice = sanitize_text_field($_POST['notice']);
				$showLogoShop = sanitize_text_field($_POST['show_shop_logo']);
				$showNameShop = sanitize_text_field($_POST['show_shop_name']);
                $showPrice = sanitize_text_field($_POST['show_price']);

				$data['key'] = sanitize_text_field( $_POST['key'] );
				$data['domain'] = sanitize_text_field( $_POST['domain'] );
				$data['notice'] = empty($notice) ? '' : $notice;
				$data['send_to_email'] = sanitize_text_field( $_POST['send_to_email'] );
				$data['show_shop_logo'] = empty($showLogoShop) ? 0 : (int)$showLogoShop;
				$data['show_shop_name'] = empty($showNameShop) ? 0 : (int)$showNameShop;
                $data['show_price'] = empty($showPrice) ? 0 : (int)$showPrice;
				$data['button_title'] = sanitize_text_field( $_POST['button_title'] );

				$data['updated_at'] = date( "d-m-Y H:i:s" );
				update_option( 'masoffer_product_listing', $data );
			}
			wp_safe_redirect( '/wp-admin/admin.php?page=mo-prod-page-settings' );
			exit();
		}

		public function mo_prod_add_short_code_admin_action() {
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'add-short-code-mo_prod_' ) ) {
				wp_die( __( 'You do not have sufficient permission to save this form.' ) );
			}
			wp_safe_redirect( '/wp-admin/admin.php?page=mo-prod-page-settings' );
			exit();
		}

		public function ajaxAPIGetProductList( WP_REST_Request $request ) {
			global $wpdb;
			$result = [
                'status' => 'true',
                'message' => '',
                'data' => []
            ];
			$parameters = $request->get_query_params();
			$shortcodeId = @$parameters['shortcodeId'];
            if(empty($shortcodeId)){
	            $result['status'] = 'false';
	            $result['message'] = 'Empty shortcode_id';
            }

			$query =    "SELECT $this->tableNameUrl.* , $this->tableNameShortcode.aff_sub1 , $this->tableNameShortcode.aff_sub2 ,
						$this->tableNameShortcode.aff_sub3 , $this->tableNameShortcode.aff_sub4 , $this->tableNameShortcode.type,
						$this->tableNameShortcode.button_title, $this->tableNameShortcode.show_shop_name, $this->tableNameShortcode.show_shop_logo, $this->tableNameShortcode.show_price
						FROM $this->tableNameUrl
						INNER JOIN $this->tableNameShortcode ON $this->tableNameUrl.shortcode_id = $this->tableNameShortcode.id
						WHERE shortcode_id = $shortcodeId AND stock > 0 AND status_shop = 1";

            $listUrl = $wpdb->get_results($query, ARRAY_A);
            if(empty($listUrl)){
	            $result['status'] = 'false';
	            $result['message'] = 'Empty Url';
            }
			$dataOption = get_option( 'masoffer_product_listing' );
            $publisherKey = $dataOption['key'];
            $result['data']['aff_url'] = "https://{$dataOption['domain']}/v0/$publisherKey";
            $result['data']['logo_offer'] = [
                self::OFFER_TIKI => plugin_dir_url( dirname( __FILE__ ) ) . 'masoffer-product-listing/images/icon_tiki-vn.png',
                self::OFFER_SHOPEE => plugin_dir_url( dirname( __FILE__ ) ) . 'masoffer-product-listing/images/icon_shopee-vn.png',
                self::OFFER_LAZADA => plugin_dir_url( dirname( __FILE__ ) ) . 'masoffer-product-listing/images/icon_lazada-vn.png',
                self::OFFER_SENDO => plugin_dir_url( dirname( __FILE__ ) ) . 'masoffer-product-listing/images/icon_sendo-vn.png',
			];
            $result['data']['mo_source'] = 'wp-product-listing';
            $result['data']['button_title'] = empty($listUrl[0]['button_title']) ? $dataOption['button_title'] : $listUrl[0]['button_title'];

            $result['data']['show_shop_logo'] = ($listUrl[0]['show_shop_logo'] == null) ? $dataOption['show_shop_logo'] : (int)$listUrl[0]['show_shop_logo'];
            $result['data']['show_shop_name'] = ($listUrl[0]['show_shop_name'] == null) ? $dataOption['show_shop_name'] : (int)$listUrl[0]['show_shop_name'];
            $result['data']['show_price'] = ($listUrl[0]['show_price'] == null) ? $dataOption['show_price'] : (int)$listUrl[0]['show_price'];

			$result['data']['data_link'] = $listUrl;
			return $result;
		}

		public function ajaxAPIAddShortcode(){
		    $result = [
                'status' => 'true',
                'message'=> '',
                'data' => '',
            ];
			if ( !isset( $_POST['name'] ) || !isset( $_POST['urls'] ) ) {
			    $result['status'] = 'false';
			    $result['message'] = 'Empty param!';
			    return $result;
			}

            $data = $this->checkUrl(sanitize_textarea_field($_POST['urls']));
			$totalUrl = count($data['urlChecked']);
			if(!empty($data['urlError']) || $totalUrl <= 0){
	            $result['status'] = 'false';
	            $result['message'] = 'This url is not a product or out of stock.';
	            $result['data'] = $data['urlError'];
	            return $result;
            }

			//save shortcode
			$shortcodeId = $this->insertShortcodeToDb($_POST);

            if($shortcodeId === false){
	            $result['status'] = 'false';
	            $result['message'] = 'Can not save shortcode';
	            $result['data'] = '';
	            return $result;
            }

            //save url
            $this->saveUrlToDb($data['urlChecked'], $shortcodeId);

			$result['data'] = $shortcodeId;
			return $result;
		}

		public function ajaxAPIEditShortcode(){
			global $wpdb;
			$result = [
                'status' => 'true',
                'message'=> '',
                'data' => '',
            ];

			if ( !isset( $_POST['name'] ) || !isset( $_POST['urls'] ) || !isset( $_POST['id'] ) ) {
			    $result['status'] = 'false';
			    $result['message'] = 'Empty param!';
			    return $result;
			}

            $data = $this->checkUrl(sanitize_textarea_field($_POST['urls']));

			$totalUrl = count($data['urlChecked']);
			if(!empty($data['urlError']) || $totalUrl <= 0){
	            $result['status'] = 'false';
	            $result['message'] = 'This url is not a product or out of stock.';
	            $result['data'] = $data['urlError'];
	            return $result;
            }

			$id = sanitize_text_field($_POST['id']);
			//update name
			$this->updateShortcodeToDb($_POST);
			//delete url
			$wpdb->delete( $this->tableNameUrl, [ 'shortcode_id' => $id]);
            //save new url
            $this->saveUrlToDb($data['urlChecked'], $id);

			$result['data'] = $id;
			return $result;
		}

		public function ajaxAPICheckNow(){
			$result = [
                'status' => 'true',
                'message'=> '',
                'data' => '',
            ];
			$this->runCronJob('mo_product_notice_cron');
			return $result;
		}

		public function ajaxAPIUpdateNow(){
			$result = [
                'status' => 'true',
                'message'=> '',
                'data' => '',
            ];
			$this->runCronJob('mo_product_update_cron');
			return $result;
		}

		public function checkUrl($urls){
			$urls = preg_replace("~\s*[\r\n]+~", ', ', $urls);
			$urls = array_filter(explode(', ',$urls));
            $rs = [
                'urlError' => '',
                'urlChecked' => [],
            ];
			foreach ($urls as $url){
				$dataUrl = $this->parseUrl($url);
				if(empty($dataUrl)){
					$rs['urlError'] = $url;
					break;
				}
				$itemName = esc_sql($dataUrl['item_name']);
				$rs['urlChecked'][] = "'$url', '{$dataUrl['shop_id']}', '{$dataUrl['item_id']}', {$dataUrl['status_shop']},
				'{$itemName}', '{$dataUrl['image']}', {$dataUrl['price']}, {$dataUrl['sale_price']}, {$dataUrl['stock']}, '{$dataUrl['offer']}'";
			}
			return $rs;
        }

        public function parseUrl($url){
	        $parseUrl = parse_url($url);
	        $host = @$parseUrl['host'];
	        if(empty($parseUrl['path'])){
		        return false;
	        }
			$data = [];
	        if(strpos($host,self::SHOPEE_DOMAIN) !== false){
				$data = $this->parseShopee($parseUrl);
	        }

	        if(strpos($host,self::TIKI_DOMAIN) !== false){
		        $data = $this->parseTiki($parseUrl);
	        }

	        if(strpos($host,self::LAZADA_DOMAIN) !== false){
		        $data = $this->parseLazada($url);
	        }

	        if(strpos($host,self::SENDO_DOMAIN) !== false){
//		        $data = $this->parseSendo($parseUrl);
		        //ToDo: Turn off SenDo
		        $data = [];
	        }

	        return $data;
        }

        public function parseShopee($parseUrl){
	        $urlDotArray    = array_slice( explode( '.', $parseUrl['path'] ), -2 );
            $itemId         = trim( isset($urlDotArray[1]) ? $urlDotArray[1] : '');
            $shopId         = trim( isset($urlDotArray[0]) ? $urlDotArray[0] : '');
	        $apiShopee  = self::SHOPEE_API_GET_ITEM."?itemid={$itemId}&shopid={$shopId}";
	        $ifNonMatch = $this->genIfNonMatch($shopId, $itemId);
	        $data = wp_remote_get( $apiShopee, array(
		       'headers' => array( 'if-none-match-' => $ifNonMatch )
			) );
	        if ( !is_array( $data ) && is_wp_error( $data ) ) {
		        return false;
	        }
	        $data = json_decode($data['body'],true);
	        if(empty($data) || $data['item']['stock'] <= 0 || !empty($data['error_msg']) || !empty($data['error'])){
				return false;
	        }

	        $statusShop = 1;
	        $apiShopShopee  = self::SHOPEE_API_GET_SHOP."?is_brief=1&shopid={$shopId}";
	        $dataShop = wp_remote_get( $apiShopShopee );
	        if ( !is_array( $dataShop ) && is_wp_error( $dataShop ) ) {
		        return false;
	        }
	        $dataShop = json_decode($dataShop['body'],true);
	        if(empty($dataShop['data']) || $dataShop['data']['vacation'] == true){
		        return false;
	        }

	        return [
	        	'shop_id' => $shopId,
		        'item_id' => $itemId,
		        'status_shop' => $statusShop,
		        'item_name' => $data['item']['name'],
		        'image' => self::SHOPEE_URL_IMAGE.$data['item']['image'],
		        'price' => (float)$data['item']['price_before_discount']/100000,
		        'sale_price' => (float)$data['item']['price']/100000,
		        'stock' => $data['item']['stock'],
		        'offer' => self::OFFER_SHOPEE
	        ];
        }

        public function parseTiki($parseUrl){
	        $urlDotArray    = array_slice( explode( '.', $parseUrl['path'] ), -2 );
	        preg_match("/[^p]+$/", $urlDotArray[0], $match);
	        $itemId = $match[0];
	        if(empty($itemId)){
	        	return false;
	        }

	        return $this->getProductTiki($itemId);
        }

		public function parseLazada($url){
            usleep(500000);
            $headers = [
                'authority' => 'member.lazada.vn',
                'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36',
                'accept' => '*/*',
                'origin' => 'https://www.lazada.vn',
                'referer' => 'https://www.lazada.vn/',
                'cookie' => 'client_type=desktop; client_type=desktop; _uab_collina=159678274908052370389752; lzd_sid=12d115367620cdb381d1022eef2a4209; _tb_token_=3331757573135; t_fv=1578566388388; cna=9PCdFsIylBcCARtIkjLRXKVj; _ga=GA1.2.839197112.1578566392; miidlaz=miid5h31do1e1os8hfbjdkd; G_ENABLED_IDPS=google; fbm_1503824746501801=base_domain=.lazada.vn; lzd_uid=959693; _bl_uid=C3kgedzmjaRv5z8qv06jlXz04zhF; lzd_uti=%7B%22fpd%22%3A%222017-12-14%22%2C%22lpd%22%3A%222017-12-19%22%2C%22cnt%22%3A%222%22%7D; _gcl_au=1.1.493617997.1605061468; lzd_b_csg=552ed2b6; _gcl_aw=GCL.1606991614.Cj0KCQiAtqL-BRC0ARIsAF4K3WEkzCLbZP85wEzzYn5tzP61yW75Kj7a0XmZcTIyS2EBVjdLOJEVQtEaAsduEALw_wcB; exlaz=c_wXqiSuUNMYBD7DOl0qoYJP3TBn3hGNfQfwr8wDpzj5o%3D; lzd_click_id=clk5h319a1epgh95k2dihb; lzd_cid=fad69ceb-a71d-4c0a-ff8a-d299afa8c0b2; t_uid=fad69ceb-a71d-4c0a-ff8a-d299afa8c0b2; hng=VN|vi|VND|704; xlly_s=1; _gid=GA1.2.1265600797.1611645876; cto_axid=C-71iUDBsbKgQVIcYOW16nXdbTjifw9j; t_sid=p83L2U1htMcgdUyglJxwpaIQzEj4mZEq; utm_channel=Referral; l=eBLpCSX7QN9ZuOztBOfanurza77OSIRYYuPzaNbMiOCPODfB5RFdW6MvygY6C3MNh6rMR3PSIv6UBeYBcQAonxvTdR3JZQDmn; tfstk=ckghBIMSeR6X9BZiceaIg4YtP58Awc_zn4us7VnFf5-FJw1crGS4XEOgKv0RV; isg=BIeH6XSoqw5B9BGFS1HXY0tqFj1RjFtujT4ln1l0opY9yKeKYV-lvyaJapCWIDPm'
            ];
			$text = wp_remote_retrieve_body(wp_remote_get( $url, ['headers' => $headers] ));
			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			$dom->loadHTML($text);
			$xpath = new DOMXpath($dom);
			$nodes = $xpath->query("//script[contains(.,'app.run')]");
			if($nodes->length <= 0){
				return false;
			}

            preg_match("/var __moduleData__[\s\S]*?var __googleBot__/", $nodes[0]->nodeValue, $match);
            if(empty($match[0])){
                return false;
            }

            $string = substr(trim(substr($match[0],strlen('var __moduleData__ = '), -strlen('var __googleBot__'))),0, -1);
            $data = json_decode($string, true);
            $product = $data['data']['root']['fields']['skuInfos']['0'];

			if(empty($product) || $product['stock'] <= 0){
				return false;
			}

			$statusShop = 1;
			$rs = [
				'shop_id' => $product['sellerId'],
				'item_id' => $product['itemId'],
				'status_shop' => $statusShop,
                'item_name' => $product['dataLayer']['pdt_name'],
				'image' => 'https:'.$product['image'],
				'price' => (empty($product['price']['originalPrice']['value'])) ? 0 : $product['price']['originalPrice']['value'],
				'sale_price' => $product['price']['salePrice']['value'],
				'stock' => $product['stock'],
				'offer' => self::OFFER_LAZADA
			];
			return $rs;
		}

		public function parseSendo($parseUrl){
			$productPath = strpos($parseUrl['path'],'.html');
			$productSlug = explode('/', substr($parseUrl['path'],0,$productPath) );
			$productSlug = end($productSlug);
			$apiSendo  = self::SENDO_API_GET_ITEM.$productSlug."?platform=web";
			$data = wp_remote_get( $apiSendo );
			if ( !is_array( $data ) && is_wp_error( $data ) ) {
				return false;
			}
			$data = json_decode($data['body'],true);

			if(empty($data)
			   || $data['result']['data']['quantity'] <= 0
			   || $data['result']['data']['stock_status'] === 0
			   || $data['status']['code'] !== 200){
				return false;
			}
			$statusShop = 1;

			return [
				'shop_id' => $data['result']['data']['shop_info']['shop_id'],
				'item_id' => $data['result']['data']['id'],
				'status_shop' => $statusShop,
				'item_name' => $data['result']['data']['name'],
				'image' => $data['result']['data']['media'][0]['image'],
				'price' => (float)$data['result']['data']['price'],
				'sale_price' => (float)$data['result']['data']['final_price'],
				'stock' => $data['result']['data']['quantity'],
				'offer' => self::OFFER_SENDO
			];
		}

		public function insertShortcodeToDb($post){
			$dataInsert = [
				'name'           => sanitize_text_field( $post['name'] ),
				'aff_sub1'       => sanitize_text_field( $post['aff_sub1'] ),
				'aff_sub2'       => sanitize_text_field( $post['aff_sub2'] ),
				'aff_sub3'       => sanitize_text_field( $post['aff_sub3'] ),
				'aff_sub4'       => sanitize_text_field( $post['aff_sub4'] ),
				'type'           => (int)sanitize_text_field( $post['type'] ),
				'button_title'   => sanitize_text_field( $post['button_title'] ),
				'show_shop_logo' => (int)sanitize_text_field( isset($post['show_shop_logo']) ? $post['show_shop_logo'] : 0 ),
				'show_shop_name' => (int)sanitize_text_field( isset($post['show_shop_name']) ? $post['show_shop_name'] : 0 ),
                'show_price'     => (int)sanitize_text_field( isset($post['show_price']) ? $post['show_price'] : 0 ),
			];
			global $wpdb;
			$wpdb->insert( $this->tableNameShortcode, $dataInsert);
			return $wpdb->insert_id;
        }

        public function updateShortcodeToDb($post){
			$dataUpdate = [
				'name'     => sanitize_text_field( $post['name'] ),
				'aff_sub1' => sanitize_text_field( $post['aff_sub1'] ),
				'aff_sub2' => sanitize_text_field( $post['aff_sub2'] ),
				'aff_sub3' => sanitize_text_field( $post['aff_sub3'] ),
				'aff_sub4' => sanitize_text_field( $post['aff_sub4'] ),
				'type'     => (int)sanitize_text_field( $post['type'] ),
				'button_title'   => sanitize_text_field( $post['button_title'] ),
                'show_shop_logo' => (int)sanitize_text_field( isset($post['show_shop_logo']) ? $post['show_shop_logo'] : 0 ),
                'show_shop_name' => (int)sanitize_text_field( isset($post['show_shop_name']) ? $post['show_shop_name'] : 0 ),
                'show_price'     => (int)sanitize_text_field( isset($post['show_price']) ? $post['show_price'] : 0 ),
			];
			global $wpdb;
	        $wpdb->update($this->tableNameShortcode, $dataUpdate, [ 'id' => sanitize_text_field($post['id']) ] );
			return $wpdb->insert_id;
        }

        public function saveUrlToDb($urls, $shorcodeId){
	        global $wpdb;
	        $arrayColumnType = [
                'shortcode_id' => '%d',
                'url' => '%s',
                'shop_id' => '%s',
                'item_id' => '%s',
                'status_shop' => '%d',
                'item_name' => '%s',
                'image' => '%s',
                'price' => '%f',
                'sale_price' => '%f',
                'stock' => '%d',
                'offer' => '%s',
            ];

	        $column = implode(', ',array_keys($arrayColumnType));
		    $values = [];
	        foreach ($urls as $url){
		        $value = "($shorcodeId, $url)";
		        $values[] = $value;
            }
	        $values = implode(',',$values);
	        $query = "INSERT INTO $this->tableNameUrl ( $column ) VALUES $values";
	        $wpdb->query($query);
	        return true;
        }

        public function updateUrlToDb($urls, $shorcodeId){
	        global $wpdb;
	        $arrayColumnType = [
                'status_shop' => '%d',
                'item_name' => '%s',
                'image' => '%s',
                'price' => '%f',
                'sale_price' => '%f',
                'stock' => '%d',
                'offer' => '%s',
            ];

	        $column = implode(', ',array_keys($arrayColumnType));
		    $values = [];
	        foreach ($urls as $url){
		        $value = "($shorcodeId, $url)";
		        $values[] = $value;
            }
	        $values = implode(',',$values);
	        $query = "INSERT INTO $this->tableNameUrl ( $column ) VALUES $values";
	        $wpdb->query($query);
	        return true;
        }

		function runCronJob( $hookname ) {
			$crons = _get_cron_array();
			foreach ( $crons as $time => $cron ) {
				if ( isset( $cron[ $hookname ] ) ) {
				    $detailCron = array_shift($cron[$hookname]);
					$args = $detailCron['args'];
					delete_transient( 'doing_cron' );
					$scheduled = $this->force_schedule_single_event( $hookname, $args ); // UTC

					if ( false === $scheduled ) {
						return $scheduled;
					}

					add_filter( 'cron_request', function( array $cron_request_array ) {
						$cron_request_array['url'] = add_query_arg( 'crontrol-single-event', 1, $cron_request_array['url'] );
						return $cron_request_array;
					} );

					spawn_cron();

					sleep( 1 );

					return true;
				}
			}
			return false;
		}

		function force_schedule_single_event( $hook, $args = array() ) {
			$event = (object) array(
				'hook'      => $hook,
				'timestamp' => 1,
				'schedule'  => false,
				'args'      => $args,
			);
			$crons = (array) _get_cron_array();
			$key   = md5( serialize( $event->args ) );

			$crons[ $event->timestamp ][ $event->hook ][ $key ] = array(
				'schedule' => $event->schedule,
				'args'     => $event->args,
			);
			uksort( $crons, 'strnatcasecmp' );

			return _set_cron_array( $crons );
		}
	}

	$plugin_name = new MasOffer_ProductListing();
}

// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'MasOffer_Shortcode_List_Table' ) ) {
	class MasOffer_Shortcode_List_Table extends WP_List_Table {
		const TABLE_SHORTCODE = 'mo_product_listing_shortcode';
		const TABLE_URL = 'mo_product_listing_url';

		/**
		 * Prepare the items for the table to process
		 *
		 * @return Void
		 */
		public function prepare_items() {
			$search = @$_GET['s'];
			$columns  = $this->get_columns();
			$hidden   = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();

			$data = $this->table_data($search);
			usort( $data, array( &$this, 'sort_data' ) );

			$perPage     = 10;
			$currentPage = $this->get_pagenum();
			$totalItems  = count( $data );

			$this->set_pagination_args( array(
				'total_items' => $totalItems,
				'per_page'    => $perPage
			) );

			$data = array_slice( $data, ( ( $currentPage - 1 ) * $perPage ), $perPage );

			$this->_column_headers = array( $columns, $hidden, $sortable );
			$this->items           = $data;
		}

		/**
		 * Override the parent columns method. Defines the columns to use in your listing table
		 *
		 * @return array
		 */
		public function get_columns() {
			$columns = array(
				'id'        => 'ID',
				'name'      => 'Name',
				'total_url' => 'Total url',
				'total_url_error' => 'Total url error',
				'shortcode'    => 'Shortcode',
				'action'    => 'Action',
			);

			return $columns;
		}

		/**
		 * Define which columns are hidden
		 *
		 * @return array
		 */
		public function get_hidden_columns() {
			return array();
		}

		public function get_sortable_columns() {
			return array( 'id' => array( 'id', false ) );
		}

		private function table_data($search) {
			global $wpdb;
			$tableNameShortcode = $wpdb->prefix . self::TABLE_SHORTCODE;
			$tableNameUrl = $wpdb->prefix . self::TABLE_URL;
			$searchUrlDecode = urldecode($search);
			$conditionShortcode = '';
			if(!empty($search)){
				$conditionShortcode = "WHERE shortcode.name like '%$search%'
				OR shortcode.id like '%$search%'
				OR url.url like '$search%'
				OR url.url like '$searchUrlDecode%' ";
			}
			$queryListShortcode =
				"SELECT shortcode.*, count( url ) AS total_url
			FROM $tableNameShortcode AS shortcode
			INNER JOIN $tableNameUrl AS url ON shortcode.id = url.shortcode_id
			$conditionShortcode
			GROUP BY shortcode.id";

			$listShortCode = $wpdb->get_results($queryListShortcode,ARRAY_A);

			$queryTotalUrlError =
				"SELECT shortcode_id, count(*) as total_url_error
			FROM $tableNameUrl
			where stock = 0
			OR status_shop = 0
			GROUP BY shortcode_id";
			$urlErrors = $wpdb->get_results($queryTotalUrlError,ARRAY_A);
			if(empty($urlErrors)){
				return $listShortCode;
			}
			$dataUrlErrors = [];
			foreach ($urlErrors as $val){
				$dataUrlErrors[$val['shortcode_id']] = $val['total_url_error'];
			}

			foreach ($listShortCode as &$val){
				if(array_key_exists($val['id'],$dataUrlErrors)){
					$val['total_url_error'] = $dataUrlErrors[$val['id']];
				}else{
					$val['total_url_error'] = 0;
				}
			}

			return $listShortCode;
		}

		public function column_default( $item, $column_name ) {
			$id = $item['id'];
			switch ( $column_name ) {
				case 'id':
				case 'name':
				case 'total_url':
					return $item[ $column_name ];
				case 'total_url_error':
					if(empty($item['total_url_error'])){
						return '';
					}
					return "<span class='badge-danger'>".$item['total_url_error']."</span>";
				case 'shortcode':
					return "[mo_product_listing id=$id]";
				default:
					return "<a class='button' href='admin.php?page=mo-prod-page-edit-short-code&id=$id'>Edit</a>
							<a class='button' href='admin.php?page=mo-prod-page-short-code&id=$id&delete=true' onclick=\"return confirm('Are you sure you want to delete this shortcode?');\">Delete</a> ";
			}
		}

		private function sort_data( $a, $b ) {
			// Set defaults
			$orderby = 'id';
			$order   = 'desc';

			// If orderby is set, use this as the sort column
			if ( ! empty( $_GET['orderby'] ) ) {
				$orderby = sanitize_text_field($_GET['orderby']);
			}

			// If order is set use this as the order
			if ( ! empty( $_GET['order'] ) ) {
				$order = sanitize_text_field($_GET['order']);
			}

			$result =  (int)$a[ $orderby ] - (int)$b[ $orderby ] ;

			if ( $order === 'asc' ) {
				return $result;
			}

			return - $result;
		}

		public function extra_tablenav( $which ) {
			if ( $which == "top" ) {
				echo '<a class="button" href="/wp-admin/admin.php?page=mo-prod-page-add-short-code">Add shortcode</a>';
				echo '<button class="button" id="checkUpdateBtn" style="margin-left: 6px">Update now</button>';
				echo '<button class="button" id="checkNowBtn" style="margin-left: 6px">Check now</button>';
			}
		}
	}
}
?>
