<?php
require_once __DIR__."/../includes/class-option-defaults.php";

class Recipe_Pro_Label_Page {

	public function register_page() {
		register_setting( 'recipepro-labels', 'recipepro_settings' ); // could santize option values on save via callback here
		add_settings_section(
			'recipepro-labels-section', 
			__( 'Label Overrides', 'recipe-pro' ),
			array(&$this, 'recipepro_settings_section_callback_labels'), 
			'recipepro-labels'
		);
		foreach ( Recipe_Pro_Option_Defaults::get_labels() as $key => $value ) {
			add_settings_field(
				'recipepro_text_label_' . $key,
				$value,
				array(&$this, 'recipepro_text_label_render'),
				'recipepro-labels',
				'recipepro-labels-section',
				array('label' => $key)
			);
		}
	}

	public function recipepro_settings_section_callback_labels(  ) { 
		//echo __( 'Label overrides', 'recipe-pro' );
	}

	public function recipepro_text_label_render( $args ) {
		$options = get_option( 'recipepro_settings' );
		?>
		<input type='text' name='recipepro_settings[recipepro_text_label_<?= $args['label'] ?>]' value='<?= $options['recipepro_text_label_' . $args['label']]; ?>'>
		<?php
	}

	public function page_display () {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'recipepro-labels' );
				do_settings_sections( 'recipepro-labels' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

}
