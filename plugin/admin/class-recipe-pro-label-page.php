<?php
require_once __DIR__."/../includes/class-option-defaults.php";

class Recipe_Pro_Label_Page {

	public function register_label_page() {
		register_setting( 'recipepro_settings_group', 'recipepro_settings' ); // could santize option values on save via callback here
		add_settings_section(
			'recipepro_settings_section_labels', 
			__( 'Label Overrides', 'recipe-pro' ),
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
		//echo __( 'Label overrides', 'recipe-pro' );
	}

	public function label_page_display () {
		$html = '';
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
		<div class="wrap">
			<form action='options.php' method='post'>
				<?php
				settings_fields( 'recipepro_settings_group' );
				do_settings_sections( 'recipepro_settings_group' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

}
