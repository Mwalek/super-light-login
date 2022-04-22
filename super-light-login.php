<?php
/*
 * @since             1.0.0
 * @package           Super_Light_Login
 *
 * @wordpress-plugin
 * Plugin Name:       Super Light Login
 * Description:       This simple plugin adds super light customization options to your wordpress login screen to make it look super neat and professional.
 * Version:           1.0.0
 * Author:            Mwale Kalenga
 * Author URI:        https://mwale.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// Include sll-functions.php, use require_once to stop the script if sll-functions.php is not found
require_once plugin_dir_path(__FILE__) . 'includes/sll-functions.php';

class Super_Light_Login_Plugin {
    public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'create_plugin_settings_page' ) );
        // Add Settings and Fields
    	add_action( 'admin_init', array( $this, 'setup_sections' ) );
    	add_action( 'admin_init', array( $this, 'setup_fields' ) );
		add_filter( 'register', 'sll_register_link' );
		add_action('login_head', 'control_logo_settings');
    }
    public function create_plugin_settings_page() {
    	// Add the menu item and page
    	$page_title = 'Super Light Login Settings';
    	$menu_title = 'Super Light Login';
    	$capability = 'manage_options';
    	$slug = 'sll_fields';
    	$callback = array( $this, 'plugin_settings_page_content' );
		add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
    }
    public function plugin_settings_page_content() {?>
    	<div class="wrap">
    		<h2>Super Light Login Settings</h2><?php
            if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ){
                  $this->admin_notice();
            } ?>
    		<form method="POST" action="options.php">
                <?php
                    settings_fields( 'sll_fields' );
                    do_settings_sections( 'sll_fields' );
                    submit_button();
                ?>
    		</form>
    	</div> <?php
    }
    
    public function admin_notice() { ?>
        <div class="notice notice-success is-dismissible">
            <p>Your settings have been updated!</p>
        </div><?php
    }
    public function setup_sections() {
        add_settings_section( 'section_one', 'Register Text and Link Settings', array( $this, 'section_callback' ), 'sll_fields' );
        add_settings_section( 'section_two', 'Logo Replacement Settings', array( $this, 'section_callback' ), 'sll_fields' );
    }
    public function section_callback( $arguments ) {
    	switch( $arguments['id'] ){
    		case 'section_one':
    			echo 'Register Text and Link Settings';
    			break;
    		case 'section_two':
    			echo 'Replace WP Logo and link with your site title.';
    			break;
			case 'section_three':
    			echo 'Control whether WP Logo is shown on your login page';
    			break;
    	}
    }
    public function setup_fields() {
        $fields = array(
        	array(
        		'uid' => 'register_text_field',
        		'label' => 'Change register text',
        		'section' => 'section_one',
        		'type' => 'text',
        		'placeholder' => 'Sign up',
        		'helper' => 'For example change register to sign up or create account',
        		'supplimental' => '',
        	),
			array(
        		'uid' => 'register_url_field',
        		'label' => 'Change register url',
        		'section' => 'section_one',
        		'type' => 'text',
        		'placeholder' => 'join-now',
        		'helper' => 'Use only the page slug. For example use <em><strong>create-account</strong></em> NOT <em><strong>example.com/create-account<em></strong>',
        		'supplimental' => '',
        	),
			array(
        		'uid' => 'logo_settings_one',
        		'label' => 'Enable logo replacement',
        		'section' => 'section_two',
        		'type' => 'select',
				'helper' => 'Replace WP logo with your Site Title',
        		'options' => array(
        			'option1' => 'Yes',
        			'option2' => 'No',
        		),
                'default' => array()
        	),
						array(
        		'uid' => 'logo_settings_url',
        		'label' => 'Change replacement text link (optional)',
        		'section' => 'section_two',
        		'type' => 'text',
        		'placeholder' => 'about-mysite',
        		'helper' => 'Use only the page slug',
        		'supplimental' => 'This will send users to a page of your choice.<em> Your Homepage is the default. </em>',
        	)
        );
    	foreach( $fields as $field ){
        	add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), 'sll_fields', $field['section'], $field );
            register_setting( 'sll_fields', $field['uid'] );
    	}
    }
    public function field_callback( $arguments ) {
        $value = get_option( $arguments['uid'] );
        if( ! $value ) {
            $value = $arguments['default'];
        }
        switch( $arguments['type'] ){
            case 'text':
            case 'password':
            case 'number':
                printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
                break;
            case 'textarea':
                printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>', $arguments['uid'], $arguments['placeholder'], $value );
                break;
            case 'select':
            case 'multiselect':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $attributes = '';
                    $options_markup = '';
                    foreach( $arguments['options'] as $key => $label ){
                        $options_markup .= sprintf( '<option value="%s" %s>%s</option>', $key, selected( $value[ array_search( $key, $value, true ) ], $key, false ), $label );
                    }
                    if( $arguments['type'] === 'multiselect' ){
                        $attributes = ' multiple="multiple" ';
                    }
                    printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>', $arguments['uid'], $attributes, $options_markup );
                }
                break;
            case 'radio':
            case 'checkbox':
                if( ! empty ( $arguments['options'] ) && is_array( $arguments['options'] ) ){
                    $options_markup = '';
                    $iterator = 0;
                    foreach( $arguments['options'] as $key => $label ){
                        $iterator++;
                        $options_markup .= sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>', $arguments['uid'], $arguments['type'], $key, checked( $value[ array_search( $key, $value, true ) ], $key, false ), $label, $iterator );
                    }
                    printf( '<fieldset>%s</fieldset>', $options_markup );
                }
                break;
        }
        if( $helper = $arguments['helper'] ){
            printf( '<span class="helper"> %s</span>', $helper );
        }
        if( $supplimental = $arguments['supplimental'] ){
            printf( '<p class="description">%s</p>', $supplimental );
        }
    }
	
	//Change Register Link and Text on wp-login page
}
new Super_Light_Login_Plugin();

	function sll_register_link( $link ) {
		$custom_register_text = get_option('register_text_field');
		/*Required: Replace Register_URL with the URL of registration*/
		$custom_register_link = get_option('register_url_field');
		/*Optional: You can optionally change the register text e.g. Signup*/
		$register_text = $custom_register_text;
		$link = '<a href="'.$custom_register_link.'">'.$register_text.'</a>';
		return $link;
	}
	
	function plugin_add_settings_link( $links ) {
		$settings_link = '<a href="options-general.php?page=sll_fields">' . __( 'Settings' ) . '</a>';
		array_push( $links, $settings_link );
		return $links;
	}
	
	$plugin = plugin_basename( __FILE__ );
	
	add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );
	
	function control_logo_settings() {
	
		$logo_settings_control = get_option('logo_settings_one');

			if ( 'option1' == $logo_settings_control[0] ) {
	
				// Remove logo

				?>
					<style type="text/css">
						body.login div#login h1 a {
							background-image: none;
							background-size: 0 0;
							height: 0;
							margin: 0 auto 0;
							width: 0;
						}
						body.login div#login a:focus {box-shadow:none;}
						.login h1.sll_login-title {padding-bottom:20px;}
						.sll_box {text-align:center;}
						body.login div#login .sll_box a {display:inline-block; text-decoration:none;}
					</style>
				<?php 
				
				// Add custom text - site title

				function sll_custom_login_message() {
					$site_title = get_bloginfo( 'name' );
					$site_url = get_site_url();
					$sll_title = '<h1 class="pd_login-title">' . $site_title . '</h1>';
					$sll_box = '<div class="sll_box"><a href=" ' . $site_url . '//"><h1 class="sll_login-title"> ' . $site_title . ' </h1></a></div>';
					return $sll_box;
				}
				add_filter('login_message', 'sll_custom_login_message');
				
			}
	}
	
	