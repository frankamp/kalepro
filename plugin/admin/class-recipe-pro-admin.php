<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.joshuafrankamp.com
 * @since      1.0.0
 *
 * @package    Recipe_Pro
 * @subpackage Recipe_Pro/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Recipe_Pro
 * @subpackage Recipe_Pro/admin
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

    }

    public function create_menu() {
        add_menu_page(
            'Recipe Pro',          // The title to be displayed on the corresponding page for this menu
            'Recipe Pro',                  // The text to be displayed for this actual menu item
            'manage_options',            // Which type of users can see this menu
            'recipepro',                  // The unique ID - that is, the slug - for this menu item
            array(&$this, 'menu_page_display'),// The name of the function to call when rendering the menu for this page
            'dashicons-carrot'
        );
    }

    public function settings_init(  ) { 

        register_setting( 'pluginPage', 'recipepro_settings' );

        add_settings_section(
            'recipepro_pluginPage_section', 
            __( 'Labels', 'wordpress' ), 
            array(&$this, 'recipepro_settings_section_callback'), 
            'pluginPage'
        );

        add_settings_field( 
            'recipepro_text_field_0', 
            __( 'Recipes', 'wordpress' ), 
            array(&$this, 'recipepro_text_field_0_render'), 
            'pluginPage', 
            'recipepro_pluginPage_section' 
        );

        add_settings_field( 
            'recipepro_text_field_1', 
            __( 'Rating', 'wordpress' ), 
            array(&$this, 'recipepro_text_field_1_render'), 
            'pluginPage', 
            'recipepro_pluginPage_section' 
        );
    }


    public function recipepro_text_field_0_render(  ) { 

        $options = get_option( 'recipepro_settings' );
        ?>
        <input type='text' name='recipepro_settings[recipepro_text_field_0]' value='<?php echo $options['recipepro_text_field_0']; ?>'>
        <?php

    }


    public function recipepro_text_field_1_render(  ) { 

        $options = get_option( 'recipepro_settings' );
        ?>
        <input type='text' name='recipepro_settings[recipepro_text_field_1]' value='<?php echo $options['recipepro_text_field_1']; ?>'>
        <?php
    }

    public function recipepro_settings_section_callback(  ) { 
        echo __( 'Label overrides', 'wordpress' );
    }

    public function menu_page_display () {
        $html = '';
        ?>
        <div class="wrap">
            <form action='options.php' method='post'>
                <h2>Recipe Pro</h2>
                <?php
                settings_fields( 'pluginPage' );
                do_settings_sections( 'pluginPage' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_recipe( $atts ) {
        $html = '<h3>oh look its a recipe</h3>';
        return $html;
    }

    public function add_button( $plugin_array ) {
        //wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js', array( 'jquery' ), $this->version, false );

        $plugin_array['recipe-pro'] = plugin_dir_url( __FILE__ ) . 'js/recipe-pro-button.js';
        return $plugin_array;
    }

    public function register_button( $buttons ) {
        array_push( $buttons, 'showrecent' ); // dropcap', 'recentposts
        return $buttons;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Recipe_Pro_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Recipe_Pro_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/recipe-pro-admin.css', array(), $this->version, 'all' );

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Recipe_Pro_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Recipe_Pro_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/recipe-pro-admin.js', array( 'jquery' ), $this->version, false );

    }

}
