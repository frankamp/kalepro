<?php

class Recipe_Pro_Main_Page {

	private $cssOptions = array(
		"Simple" => "simple.css",
		"Foodie Pro" => "foodie-pro.css",
		"Brunch Pro" => "brunch-pro.css",
		"No CSS / Custom" => ""
	);

	public function ensure_defaults() {
		$options = get_option( 'recipepro_main_settings', array() );
        if (! array_key_exists('css', $options) || ! in_array( $options['css'], array_values( $this->cssOptions ) ) )  {
        	$options['css'] = array_values( $this->cssOptions )[1];
        	update_option('recipepro_main_settings', $options, false);
        }
	}

	public function register_page() {
		register_setting( 'recipepro-main', 'recipepro_main_settings' ); // could santize option values on save via callback here
		add_settings_section(
			'recipepro-main-section', 
			__( 'Settings', 'recipe-pro' ),
			array(&$this, 'recipepro_main_settings_header'), 
			'recipepro-main'
		);
		add_settings_field(
			'recipepro_css_choice',
			'Recipe Theme Stylesheet',
			array(&$this, 'recipepro_css_choice_render'),
			'recipepro-main',
			'recipepro-main-section',
			array()
		);
	}

	public function recipepro_main_settings_header(  ) { 
		echo __( 'Label overrides', 'recipe-pro' );
	}

	public function recipepro_css_choice_render( $args ) {
		$options = get_option( 'recipepro_main_settings' );
		$default = array_values( $this->cssOptions )[1];
		if ( $options && array_key_exists( 'css', $options) && in_array( $options['css'], array_values( $this->cssOptions ) ) ) {
			$default = $options['css'];
		}
		?>
		<select name='recipepro_main_settings[css]'>
			<?php foreach ($this->cssOptions as $label => $cssFile) { ?>
				<option value="<?=$cssFile?>" <?= $cssFile == $default ? 'selected=selected' : '' ?> ><?=$label?></option>
			<?php } ?> 
		</select>
		<?php
	}

	public function page_display() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'recipepro-main' );
				do_settings_sections( 'recipepro-main' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}