<?php

class Recipe_Pro_Licensing_Page {
	
	public function init () {
		$this->register_license_option();
		$this->recipepro_activate_license();
		$this->recipepro_deactivate_license();
	}

	public function page_display () {
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

	private function register_license_option() {
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
	
}