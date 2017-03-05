<?php
require_once __DIR__."/../includes/class-option-defaults.php";
require_once __DIR__."/../includes/class-recipe-pro-service.php";
require_once __DIR__."/../import/class-recipe-pro-importer.php";
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.joshuafrankamp.com
 * @since      1.0.0
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    recipe-pro
 * @subpackage recipe-pro/admin
 * @author     Josh Frankamp <frankamp@gmail.com>
 */
class Recipe_Pro_Admin {


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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->importer = new Recipe_Pro_Importer();
	}


	/**
	 * Gets a label value. This will be i18n default, or if the user
	 * has overridden the label in the admin, it will be that value.
	 *
	 * @since    1.0.0
	 */
	public function get_label( $key ) {
		$options = get_option( 'recipepro_settings', null );
		if ( isset( $options ) && array_key_exists( 'recipepro_text_label_' . $key, $options )) {
			return $options['recipepro_text_label_' . $key];
		}
		$options = Recipe_Pro_Option_Defaults::get_labels();
		if ( array_key_exists( $key, $options )) {
			return $options[$key];
		};
		return "";
	}

	public function register_shortcodes() {
		add_shortcode( 'recipepro', array( $this, 'render_recipe_shortcode' ) );
	}

	public function render_recipe_shortcode( $atts ) {
		// TODO: IT IS MY RESPONSIBILITY TO SECURE THE OUTPUT
		// https://developer.wordpress.org/plugins/security/securing-output/
		$post = get_post();
		$meta_result = get_post_meta( (int) $post->ID, (string) 'recipepro_recipe', true );
		if( ! $meta_result ) {
			$recipe = new Recipe_Pro_Recipe();
		} else {
			$recipe = new Recipe_Pro_Recipe(json_decode($meta_result, true));
		}
		return $this->render_recipe($recipe);
	}

	public function render_recipe( $recipe ) {
		return $recipe->render();
	}

	public function create_menu() {
		add_menu_page(
			'Recipe Pro',          // The title to be displayed on the corresponding page for this menu
			'Recipe Pro',                  // The text to be displayed for this actual menu item
			'manage_options',            // Which type of users can see this menu
			'recipepro',                  // The unique ID - that is, the slug - for this menu item
			array(&$this, 'label_page_display'),// The name of the function to call when rendering the menu for this page
			'dashicons-carrot'
		);
		add_submenu_page( 
			'recipepro',
			'License',
			'License',
			'manage_options',
			RECIPE_PRO_LICENSE_PAGE,
			array(&$this, 'license_page_display')
		);
		add_submenu_page( 
			'recipepro',
			'Import Recipes From Other Plugins',
			'Import Recipes',
			'manage_options',
			'import-recipes-menu',
			array(&$this, 'import_page_display')
		);
	}

	public function on_admin_init(  ) { 
		$this->recipepro_register_option();
		$this->recipepro_activate_license();
		$this->recipepro_deactivate_license();
		register_setting( 'recipepro_settings_group', 'recipepro_settings' ); // could santize option values on save via callback here
		add_settings_section(
			'recipepro_settings_section_labels', 
			__( 'Labels', 'recipe-pro' ),
			array(&$this, 'recipepro_settings_section_callback_labels'), 
			'recipepro_settings_group'
		);

		foreach ( Recipe_Pro_Option_Defaults::get_labels() as $key => $value ) {
			add_settings_field(
				'recipepro_text_label_' . $key,
				$value,
				array(&$this, 'recipepro_text_label_render'),
				'recipepro_settings_group',
				'recipepro_settings_section_labels',
				array('label' => $key)
			);
		}
	}

	public function recipepro_text_label_render( $args ) {
		$options = get_option( 'recipepro_settings' );
		?>
		<input type='text' name='recipepro_settings[recipepro_text_label_<?= $args['label'] ?>]' value='<?= $options['recipepro_text_label_' . $args['label']]; ?>'>
		<?php

	}

	public function recipepro_settings_section_callback_labels(  ) { 
		echo __( 'Label overrides', 'recipe-pro' );
	}

	public function label_page_display () {
		$html = '';
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<form action='options.php' method='post'>
				<h2><?= __( 'Recipe', 'recipe-pro' ) ?></h2>
				<?php
				settings_fields( 'recipepro_settings_group' );
				do_settings_sections( 'recipepro_settings_group' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function license_page_display () {
		$license = get_option( RECIPE_PRO_LICENSE_OPTION );
		$status  = get_option( RECIPE_PRO_LICENSE_OPTION . '_status' );
		?>
		<div class="wrap">
			<h2><?php __('License Key and Activation', 'recipepro'); ?></h2>
			<form method="post" action="options.php">

				<?php settings_fields('recipepro_license'); ?>

				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('License Key'); ?>
							</th>
							<td>
								<input id="<?=RECIPE_PRO_LICENSE_OPTION?>" name="<?=RECIPE_PRO_LICENSE_OPTION?>" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
								<label class="description" for="<?=RECIPE_PRO_LICENSE_OPTION?>"><?php _e('Enter your license key'); ?></label>
								<?php submit_button(); ?>
							</td>
						</tr>
						<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('License status'); ?>
								</th>
								<td><?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('The license is active. '); ?></span>
									<?php } else { ?>
									<span style="color:orange;"><?php _e('Please activate a license to receive support and upgrades.'); ?></span>
									<?php } ?>
								</td>
						</tr>
						<?php if( false !== $license ) { ?>
							<tr valign="top">
								<th scope="row" valign="top">
									<?php _e('Activation'); ?>
								</th>
								<td>
									<?php wp_nonce_field( 'recipepro_nonce', 'recipepro_nonce' ); ?>
									<?php if( $status !== false && $status == 'valid' ) { ?>
										<input type="submit" disabled="disabled" class="button-secondary" name="recipepro_license_activate" value="<?php _e('Activate License'); ?>"/>
										<input type="submit" class="button-secondary" name="recipepro_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
									<?php } else { ?>
										<input type="submit" class="button-secondary" name="recipepro_license_activate" value="<?php _e('Activate License'); ?>"/>
										<input type="submit" disabled="disabled" class="button-secondary" name="recipepro_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
									<?php } ?>
									<div style="margin-top: 20px;"><?php _e('If you have changed it, you must save the license key before activating or deactivating.'); ?></div>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
				
			</form>
		<?php
	}

	private function recipepro_register_option() {
		// creates our settings in the options table
		register_setting('recipepro_license', RECIPE_PRO_LICENSE_OPTION, array(&$this, 'recipepro_sanitize_license') );
	}
	

	public function recipepro_sanitize_license( $new ) {
		$old = get_option( RECIPE_PRO_LICENSE_OPTION );
		if( $old && $old != $new ) {
			delete_option( RECIPE_PRO_LICENSE_OPTION . '_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}

	private function recipepro_activate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['recipepro_license_activate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'recipepro_nonce', 'recipepro_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( RECIPE_PRO_LICENSE_OPTION ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( RECIPE_PRO_EDD_SL_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( RECIPE_PRO_EDD_SL_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :

							$message = __( 'Your license key has been disabled.' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), RECIPE_PRO_EDD_SL_ITEM_NAME );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default :

							$message = __( 'An error occurred, please try again.' );
							break;
					}

				}

			}

			$base_url = admin_url( 'admin.php?page=' . RECIPE_PRO_LICENSE_PAGE );
			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				$redirect = add_query_arg( array( 'recipepro_sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
				wp_redirect( $redirect );
				exit();
			}

			// $license_data->license will be either "valid" or "invalid"
			update_option( RECIPE_PRO_LICENSE_OPTION . '_status', $license_data->license );
			$message = __( "Thanks for activating! Time to get cooking!" );
			$redirect = add_query_arg( array( 'recipepro_sl_activation' => 'true', 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();
		}
	}

	private function recipepro_deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['recipepro_license_deactivate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'recipepro_nonce', 'recipepro_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( RECIPE_PRO_LICENSE_OPTION ) );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( RECIPE_PRO_EDD_SL_ITEM_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( RECIPE_PRO_EDD_SL_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			$base_url = admin_url( 'admin.php?page=' . RECIPE_PRO_LICENSE_PAGE );
			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}

				$redirect = add_query_arg( array( 'recipepro_sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( $redirect );
				exit();
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( RECIPE_PRO_LICENSE_OPTION . '_status' );
			}
			$message = __( 'License deactivated.' );
			$redirect = add_query_arg( array( 'recipepro_sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );
			wp_redirect( $redirect );
			exit();
		}
	}
	
	
	public function display_admin_notices() {
		if ( isset( $_GET['recipepro_sl_activation'] ) && ! empty( $_GET['message'] ) ) {
			$message = $_GET['message']; //urldecode()
			switch( $_GET['recipepro_sl_activation'] ) {
				case 'false':
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					?>
					<div class="notice notice-success is-dismissible">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;
			}
		}
	}



	public function import_page_display () {
		$html = '';
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<style>
			#progressbar {
			  background-color: grey;
			  border-radius: 0; /* (height of inner div) / 2 + padding */
			  padding: 3px;
			}
			
		   #progressbar > div {
			   background-color: lightblue;
			   width: 0%; /* Adjust with JavaScript */
			   height: 20px;
			   border-radius: 0;
		   }	
		</style>
		<div class="wrap">
			<form action='options.php' method='post'>
				<h2><?= __( 'Import recipes from other plugins', 'recipe-pro' ) ?></h2>
				<div id="importer">
					
					<div>Import Status: {{statusValues[status]}}</div>
					<li v-for="item in importers">
						<strong>{{ item.name }}</strong> {{ item.description }}
						<button v-bind:disabled="status != 'ready'" v-on:click="beginImport" v-bind:name="item.name" v-bind:tag="item.tag">Start Import</button>
					</li>

					<div v-if="importer != null">
						Importing using {{ importer.name }}
						<div style="width:50%; float left;">
							<div id="progressbar">
							  <div></div>
							</div>
						</div>
						<div style="float: right;">
							<button v-bind:disabled="status == 'ready'" v-on:click="cancel">Close</button>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}


	// public function GUIDv4 ()
	// {
	// 	// Windows
	// 	if (function_exists('com_create_guid') === true) {
	// 		return trim(com_create_guid(), '{}');
	// 	}

	// 	// OSX/Linux
	// 	if (function_exists('openssl_random_pseudo_bytes') === true) {
	// 		$data = openssl_random_pseudo_bytes(16);
	// 		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
	// 		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
	// 		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	// 	}

	// 	// Fallback (PHP 4.2+)
	// 	mt_srand((double)microtime() * 10000);
	// 	$charid = strtolower(md5(uniqid(rand(), true)));
	// 	return substr($charid,  0,  8).chr(45).
	// 			  substr($charid,  8,  4).chr(45).
	// 			  substr($charid, 12,  4).chr(45).
	// 			  substr($charid, 16,  4).chr(45).
	// 			  substr($charid, 20, 12);
	// }
	
	/**
	 * Adds a meta box for the current screen (by omitting the screen arg)
	 * The screen types are chosen by hooking the screen types directly in the main plugin
	 *
	 * @since    1.0.0
	 */
	public function add_meta_box ( ) {
		add_meta_box( 'recipe-pro-recipe-data', __( 'Recipe', 'recipe-pro' ), array( $this, "render_editor_markup" ), null, 'normal', 'high' );
	}

	public function ajax_cancel_import ( ) {
		$importer_status = $this->importer->cancel();
		header ( "Content-Type: application/json" );
		echo json_encode( $importer_status );
		wp_die();
	}

	public function ajax_do_import_work ( ) {
		$importer_status = $this->importer->do_work();
		header ( "Content-Type: application/json" );
		echo json_encode( $importer_status );
		wp_die();
	}

	public function ajax_begin_import ( ) {
		$importerName = $_POST['importerName'];
		$importer_status = $this->importer->begin_import( $importerName );
		header ( "Content-Type: application/json" );
		echo json_encode( $importer_status );
		wp_die();
	}

	public function ajax_get_recipe ( ) {
		header ( "Content-Type: application/json" );
		$postid = str_replace('/', '', $_GET['postid']);
		$payload = get_post_meta( (int) $postid, (string) 'recipepro_recipe', true );
		if( ! $payload ) {
			$payload = json_encode( new Recipe_Pro_Recipe() );
		}
		echo $payload;
		wp_die();
	}

	public function render_editor_markup ( $post ) {
//		$post_id = $post->ID;
//		$hits = get_post_meta( $post_id, 'hits2', true );
//		echo 'hits while hits are ' . $hits;
		//bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator
		
		$ingredient_settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => false,
			'tinymce'       => array(
				// 'selector' => '#recipe-pro-editor',
				// 'inline' => true,
				//'plugins' => 'paste',
				'external_plugins' => "{'recipeproingredient': '" . plugin_dir_url( __FILE__ ) . "js/mce-recipe-pro-ingredient/plugin.min.js'}",
				//'toolbar' => false,
				'toolbar1' => 'bold,italic,link,unlink,removeformat,recipepro_media',
				'toolbar2' => '',
				'statusbar' => false,
				'theme_advanced_buttons1' => '',
				'theme_advanced_buttons2' => '',
				'force_p_newlines' => true,
				'paste_remove_styles' => true,
				'paste_remove_spans' => true,
				'paste_strip_class_attributes' => 'none',
				'paste_as_text' => true,
				'paste_preprocess' =>  "function(plugin, args) {
					console.log('before its: ' + args.content);
					var tag = 'p';
					args.content = '<' + tag + '>' + args.content.replace(/<p>/g,'').replace(/<\/p>/g, '<br />').split('<br />').join('</' + tag + '><' + tag + '>') + '</' + tag +'>';
					args.content = args.content.replace(new RegExp('<' + tag + '>\\s*<\/' + tag + '>','g'),'');
					args.content = args.content.replace(new RegExp('<\/' + tag + '>','g'), \"<div class='mceNonEditable'><input type='text' value='editme' /></div>\");
					console.log(args.content);
				}",
				"content_style" => "body#tinymce p {background-image: url(" . plugin_dir_url( __FILE__ ) . "css/carrot.svg); background-position: right center; background-repeat: no-repeat; padding-right: 50px; margin-bottom: 5px; }"
				//,'protect' => "[/<div class='helper'>.*?<\/div>/g]"
			),
			'media_buttons' => false,
			'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
		);

// // 'selector' => '#recipe-pro-editor',
// 				// 'inline' => true,
// 				'plugins' => 'paste',
// 				//'toolbar' => false,
// 				//'toolbar1' => 'bold italic | link image',
// 				'statusbar' => false,
// 				'theme_advanced_buttons1' => 'bold',
// 				//'theme_advanced_buttons2' => '',
// 				'force_p_newlines' => true,
// 				'paste_remove_styles' => true,
// 				'paste_remove_spans' => true,
// 				'paste_strip_class_attributes' => 'none',
// 				'paste_as_text' => true,
// 				'paste_preprocess' =>  "function(plugin, args) {
// 					console.log('before its: ' + args.content);
// 					var tag = 'p';
// 					args.content = '<' + tag + '>' + args.content.replace(/<p>/g,'').replace(/<\/p>/g, '<br />').split('<br />').join('</' + tag + '><' + tag + '>') + '</' + tag +'>';
// 					args.content = args.content.replace(new RegExp('<' + tag + '>\\s*<\/' + tag + '>','g'),'');
// 					args.content = args.content.replace(new RegExp('<\/' + tag + '>','g'), \"<div class='mceNonEditable'><input type='text' value='editme' /></div>\");
// 					console.log(args.content);
// 				}"

		$instruction_settings = array(
			'textarea_name' => 'excerpt',
			'quicktags'     => false,
			'tinymce'       => array(
				'toolbar1' => 'bold,italic,link,unlink,removeformat,recipepro_media'
			),
			'media_buttons' => false,
			'editor_css'    => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>'
		);
		wp_enqueue_media();
		?>
		<!--
		aggregateRating
			ratingValue span
			ratingCount span
		name
		image -> eg: <img itemprop="image" src="http://cdn3.minimalistbaker.com/wp-content/uploads/2016/07/Go-to-Smoothie-Bowl-SQUARE.jpg" width="205">
		prepTime -> <time itemprop="prepTime" datetime="PT5M">
		totalTime -> <time itemprop="totalTime" datetime="PT5M">
		description
		author
		recipeCategory span "recipe type" aka breakfast
		recipeCuisine span aka Thai food
		recipeYield
		ingredients (many)
		recipeInstructions (many)
		-->
		<script type="text/template" id="recipe-pro-recipe-template">
			<ul id="recipe-pro-tabs">
				<li class="<%= currentTab == 'recipe-pro-tab-overview' ? 'active' : '' %>">
					<label for="recipe-pro-tab-overview"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('overview') ?></button></label>
				</li>
				<li class="<%= currentTab == 'recipe-pro-tab-ingredient' ? 'active' : '' %>">
					<label for="recipe-pro-tab-ingredient"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('ingredients') ?></button></label>
				</li>
				<li class="<%= currentTab == 'recipe-pro-tab-instruction' ? 'active' : '' %>">
					<label for="recipe-pro-tab-instruction"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('instructions') ?></button></label>
				</li>
				<li class="<%= currentTab == 'recipe-pro-tab-nutrition' ? 'active' : '' %>">
					<label for="recipe-pro-tab-nutrition"><button class="recipe-pro-tab-button" type="button"><?= $this->get_label('nutrition_information') ?></button></label>
				</li>
			</ul>
			<div id="recipe-pro-content">
				<div id="recipe-pro-tab-overview" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-overview' ? 'block' : 'none' %>;">
					<div class="left">
						<p><label for="recipepro_title"><?= $this->get_label('title') ?></label> <input id="recipepro_title" name="title" type="text" value="<%= _.escape(title) %>" /></p>
						<p><label for="recipepro_author"><?= $this->get_label('author') ?></label> <input id="recipepro_author" name="author" type="text" value="<%= _.escape(author) %>" /></p>
						<p><label for="recipepro_type"><?= $this->get_label('recipe_type') ?></label> <input id="recipepro_type" name="type" type="text" value="<%= _.escape(type) %>" />  </p>
						<p><label for="recipepro_cuisine"><?= $this->get_label('cuisine') ?></label><input id="recipepro_cuisine" name="cuisine" type="text" value="<%= _.escape(cuisine) %>" /></p>
					</div>
					<div class="right">
						<p>
						<div class='image-preview-wrapper'>
							<img id='image-preview' src='<%= _.escape(imageUrl) %>' width='100' height='100' style='max-height: 100px; width: 100px;'>
						</div>
						<input id="upload_image_button" type="button" class="button" value="<?php _e( 'Select Recipe Image', 'recipe-pro' ); ?>" />
						<input type='hidden' name='recipepro_imageurl' id='imageUrl' value='<%= _.escape(imageUrl) %>' />
						</p>
					</div>
					<div class="clear"/>
				</div>
				<div id="recipe-pro-tab-ingredient" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-ingredient' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor-ingredient", $ingredient_settings  ) ?>
					<!-- <ul>
						<% _.each(ingredients, function(ing){ %>
						<li>
							<input name="quantity" type="text" value="<%= _.escape(ing.quantity) %>" />
							<input name="unit" type="text" value="<%= _.escape(ing.unit) %>" />
							<input name="name" type="text" value="<%= _.escape(ing.name) %>" />
						</li>
						<% }); %>
					</ul> -->
				</div>
				<div id="recipe-pro-tab-instruction" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-instruction' ? 'block' : 'none' %>;">
					<?= wp_editor( "", "recipe-pro-editor-instruction", $instruction_settings  ) ?>
				</div>
				<div id="recipe-pro-tab-nutrition" class="recipe-pro-tab" style="display: <%= currentTab == 'recipe-pro-tab-nutrition' ? 'block' : 'none' %>;">
					<div class="left">
						<p><label for="recipepro_servingSize"><?= $this->get_label('serving_size') ?></label> <input id="recipepro_servingSize" name="servingSize" type="text" value="<%= _.escape(servingSize) %>" /></p>
						<p><label for="recipepro_calories"><?= $this->get_label('calories') ?></label> <input id="recipepro_calories" name="calories" type="text" value="<%= _.escape(calories) %>" /></p>
						<p><label for="recipepro_fatContent"><?= $this->get_label('total_fat') ?></label> <input id="recipepro_fatContent" name="fatContent" type="text" value="<%= _.escape(fatContent) %>" /></p>
						<p><label for="recipepro_saturatedFatContent"><?= $this->get_label('saturated_fat') ?></label> <input id="recipepro_saturatedFatContent" name="saturatedFatContent" type="text" value="<%= _.escape(saturatedFatContent) %>" /></p>
						<p><label for="recipepro_carbohydrateContent"><?= $this->get_label('carbohydrates') ?></label> <input id="recipepro_carbohydrateContent" name="carbohydrateContent" type="text" value="<%= _.escape(carbohydrateContent) %>" /></p>
					</div>
					<div class="right">
						<p><label for="recipepro_sugarContent"><?= $this->get_label('sugars') ?></label> <input id="recipepro_sugarContent" name="sugarContent" type="text" value="<%= _.escape(sugarContent) %>" /></p>
						<p><label for="recipepro_sodiumContent"><?= $this->get_label('sodium') ?></label> <input id="recipepro_sodiumContent" name="sodiumContent" type="text" value="<%= _.escape(sodiumContent) %>" /></p>
						<p><label for="recipepro_fiberContent"><?= $this->get_label('fiber') ?></label> <input id="recipepro_fiberContent" name="fiberContent" type="text" value="<%= _.escape(fiberContent) %>" /></p>
						<p><label for="recipepro_proteinContent"><?= $this->get_label('protein') ?></label> <input id="recipepro_proteinContent" name="proteinContent" type="text" value="<%= _.escape(proteinContent) %>" /></p>
					</div>
					<div class="clear"/>
				</div>
			</div>
		</script>
		<div id="recipe-pro-admin-container" data-post="<?= $post->ID ?>"></div>
		<script type="text/template" id="recipe-pro-recipe-output-template">
			<input type="hidden" name="doc" value="<%= _.escape(doc) %>" />
		</script>
		<div id="recipe-pro-admin-container-output"></div>
		<?php
	}

	private function get_test_recipe_json() {
		return '
			{
				"title":"Coconut Curry Ramen",
				"description":"Savory vegan ramen infused with curry and coconut milk. Serve with sautéed portobello mushrooms and gluten free noodles for the ultimate plant-based meal.",
				"imageUrl":"http://cdn3.minimalistbaker.com/wp-content/uploads/2016/09/Curry-Ramen-SQUARE.jpg",
				"author":"Minimalist Baker",
				"type":"Entrée, Soup",
				"cuisine":"Vegan, Gluten Free",
				"yield":"2-3",
				"ingredientSections": [
					{
						"name": "BROTH",
						"items": [
							{"quantity":"", "unit":"", "name":"", "description":"1 Tbsp (15 ml) toasted or untoasted sesame oil*"},
							{"quantity":"", "unit":"", "name":"", "description":"1 small knob ginger, sliced lengthwise (into long strips)"},
							{"quantity":"", "unit":"", "name":"", "description":"5 cloves garlic, chopped"},
							{"quantity":"", "unit":"", "name":"", "description":"1 large onion, chopped lengthwise"},
							{"quantity":"", "unit":"", "name":"", "description":"2 1/2 Tbsp (40 g) yellow or green curry paste"},
							{"quantity":"", "unit":"", "name":"", "description":"4 cups (960 ml) vegetable broth"},
							{"quantity":"", "unit":"", "name":"", "description":"2 cups (480 ml) light coconut milk"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional: </em>1-2 Tbsp coconut sugar (more to taste)"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> 1/2 tsp ground turmeric (for color and more curry flavor)"},
							{"quantity":"", "unit":"", "name":"", "description":"1 Tbsp (15 g) white or yellow miso paste"}						
						]
					},
					{
						"name": "FOR SERVING",
						"items": [
							{"quantity":"", "unit":"", "name":"", "description":"2-3 cups noodles of choice (i.e. <a href=\"http://minimalistbaker.com/zucchini-pasta-with-lentil-bolognese/\" target=\"_blank\">spiralized zucchini squash</a>, cooked <a href=\"http://minimalistbaker.com/easy-vegan-ramen/\" target=\"_blank\">ramen noodles</a>*, or cooked <a href=\"http://www.amazon.com/dp/B0048IAIOS/?tag=minimalistbaker-20\" target=\"_blank\" rel=\"nofollow\">brown rice noodles</a>)"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> 2 portobello mushrooms, stems removed, sliced into 1/2-inch pieces (+ sautéed in 1 Tbsp sesame oil + 1 Tbsp tamari + 1 tsp maple syrup)"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> Fresh green onion, chopped"},
							{"quantity":"", "unit":"", "name":"", "description":"<em>optional:</em> Sriracha or <a href=\"http://www.amazon.com/dp/B000LO25RG/?tag=minimalistbaker-20\" target=\"_blank\" rel=\"nofollow\">chili garlic sauce</a>"}
						]
					}
				],
				"instructions": [
				 	{"description": "Heat a large pot over medium-high heat. Once hot, add oil, garlic, ginger and onion. Sauté, stirring occasionally for 5-8 minutes, or until the onion has developed a slight sear (browned edges)."},
					{"description": "Add curry paste and sauté for 1-2 minutes more, stirring frequently. Then add vegetable broth and coconut milk and stir to deglaze the bottom of the pan."},
					{"description": "Bring to a simmer over medium heat, then reduce heat to low and cover. Simmer on low for at least 1 hour, up to 2-3, stirring occasionally. The longer it cooks, the more the flavor will deepen and develop."},
					{"description": "Taste broth and adjust seasonings as needed, adding coconut sugar for a little sweetness, turmeric for more intense curry flavor, or more sesame oil for nuttiness."},
					{"description": "About 10 minutes before serving, prepare any desired toppings/sides, such as noodles, sautéed portobello mushrooms, or green onion (optional)."},
					{"description": "Just before serving, scoop out 1/2 cup of the broth and whisk in the miso paste. Once fully dissolved, add back to the pot and turn off the heat. Stir to combine."},
					{"description": "Either strain broth through a fine mesh strainer (discard onions and ginger or add back to the soup), or ladle out the broth and leave the onions and mushrooms behind."},
					{"description": "To serve, divide noodles of choice between 2-3 serving bowls. Top with broth and desired toppings. Serve with chili garlic sauce or sriracha for added heat."},
					{"description": "Best when fresh, though the broth can be stored (separate from sides/toppings) in the refrigerator for up to 5 days, or in the freezer for up to 1 month."}
				],
				"notes": [
					{"description": "*You can sub sesame oil for coconut, but the sesame adds a nice rich nutty flavor to the ramen that I prefer.<br>*Nutrition information is a rough estimate for 1 of 3 servings calculated using brown rice noodles and no additional toppings.<br>*If using ramen noodles, this recipe would not be gluten free."}
				],
				"servingSize":"1/3 of recipe*",
				"calories":"310",
				"cholesterolContent":"",
				"fatContent":"19.6 g",
				"transFatContent":"",
				"saturatedFatContent":"8.8 g",
				"unsaturatedFatContent":"",
				"carbohydrateContent":"26 g",
				"sugarContent":"5.3 g",
				"sodiumContent":"1253 mg",
				"fiberContent":"0.8 g",
				"proteinContent":"10.1 g",
				"prepTime": "PT15M",
				"cookTime": "PT1H15M",
				"ratingValue": 5.0,
				"ratingCount": 5
			}';
	}

	public function save_meta_box ( $post_id, $post ) {
		// todo: sanitize and validate the input
		// todo: nonce
		if (isset($_POST['doc'])) {
			// deserialize/serialize to prove we can prior to saving it to the database
			//error_log( "save meta called with: " . $_POST['doc'] ); //stripslashes( )
			if ( strpos($_POST['doc'], 'generatetestrecipe') !== false ) {
				error_log( "generating a test post " );
				$json = json_decode( $this->get_test_recipe_json(), true );
			} else {
				$json = json_decode( stripslashes( $_POST['doc'] ), true );
			}
			//error_log("Attempt decode json error: " . json_last_error() );
			$recipe = new Recipe_Pro_Recipe( $json );
			//error_log( "inflated");
			//error_log( "recipe back to json " . json_encode($recipe) );
			//error_log( "Preparing to save");
			$success = Recipe_Pro_Service::saveRecipe( $post_id, $recipe );
	//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
	//		error_log( "hits are " . $hits . " but type is " . gettype($hits));
	//		$hits += 1;
	//		error_log( "hits are " . $hits . " after incrementing type is " . gettype($hits));
	//		$success = update_post_meta( (int) $post_id, (string) 'hits2', (string) $hits );
			if ($success) {
				//error_log( "you are successful" );
			} else {
				//error_log( "you not successful" );
			}
		} else {
			error_log( "save_meta_box called with no doc" );
		}
//		error_log( "some success metrics for your update are: " . strval($success) . "type is " . gettype($success));
//		$hits = get_post_meta( (int) $post_id, (string) 'hits2', true );
//		error_log( "after update hits are " . $hits . " but type is " . gettype($hits));
	}

	public function register_mce_carrot_button( $buttons ) {
		array_push( $buttons, 'recipepro_addeditrecipe' ); // dropcap', 'recentposts
		return $buttons;
	}

	public function add_mce_carrot_button_action( $plugin_array ) {
		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js', array( 'jquery' ), $this->version, false );
		$plugin_array['recipe-pro'] = plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js';
		return $plugin_array;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/recipe-pro-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recipe-pro-admin.js', array( 'jquery', 'backbone', 'underscore' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "vue", plugin_dir_url( __FILE__ ) . 'js/vue.js', array(), $this->version, false );
		wp_enqueue_script( $this->plugin_name . "importer", plugin_dir_url( __FILE__ ) . 'js/recipe-pro-importer.js', array( 'jquery' ), $this->version, false );
	}

}
