<?php

/*Set Actions and Filters*/
add_action ('login_head', 'sll_plugin_defaults');


/*Set up Functions*/

/*Set Plugin defaults*/

function sll_plugin_defaults() {
	if ( get_option( 'register_text_field' ) === '' ) { // Nothing yet saved
    update_option( 'register_text_field', 'Sign Up' );
	}
}
